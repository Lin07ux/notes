> 转摘：[Go并发编程(十二) Singleflight](https://lailin.xyz/post/go-training-week5-singleflight.html)

### 1. 使用场景

[SingleFlight](golang.org/x/sync/singleflight) 是属于 Go 官方扩展同步包`sync`中的一个库，最常见的适用场景就是减少缓存击穿时数据库层的压力。

**缓存击穿**一般指热点 key 缓存失效（到期、删除了），导致同一时刻大量对热点 key 的并发请求都找不到缓存数据，直接打入到 DB 层。示意图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635337034664-6c0afd5f7eb5.jpg)

而使用`sync/singleflight`可以将这些并发请求拦截在一起，并指放一个请求去 DB 层取数据，然后在取到数据之后可以分享给其他的被拦截请求共同使用。这样就能防止缓存击穿造成的 DB 压力大增。示意图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635337074880-a216a64e0206.jpg)


### 2. 使用方式

SingleFlight 最常用的就是其中的`Group`结构体，它具有三个公开方法，如下所示：

* `func (g *Group) Do(key string, fn func() (interface{}, error)) (v interface(), err error, shared bool)`

    执行函数，对同一个 key 的多次调用，在第一次调用没有执行完的时候，只会执行一次`fn`方法，其他的调用会阻塞住，等待 fn 的调用返回。
    
    `v`、`err`是传入的`fn`的返回值，`shared`表示是真正执行了`fn`得到的结果，还是返回的共享的结果。
    
* `func (g *Group) DoChan(key string, fn func() (interface{}, error)) <-chan Result`

    和`Do()`类似，只是`DoChan()`返回一个 channel，也就是同步和异步的区别。


* `func (g *Group) Forget(key string)`

    用于通知 Group 删除某个 key，这样后续这个 key 的第一次调用就不会阻塞等待了。

假设有一个获取文章详情的函数，在函数里面使用一个`count`变量模拟不同并发下的耗时，并发数越多该值就越高：

```go
func getArticleDetail(id int) (string, error) {
	// 假设这里会对数据库进行调用，模拟不同并发下耗时不同
	atomic.AddInt32(&count, 1)
	time.Sleep(time.Duration(count) * time.Millisecond)

	return fmt.Sprintf("article: %d", id), nil
}
```

然后再定义一个 SingleFlight 版本的获取文章详情的方法：

```go
func singleFlightGetArticle(sg *singleflight.Group, id int) (string, error) {
	v, err, _ := sg.Do(string(id), func() (interface{}, error) {
		return getArticleDetail(id)
	})

	return v.(string), err
}
```

最后分别对这两个版本的方法进行 1000 次获取进行测试：

```go
var count int32

func main() {
	// 避免延时太长
	time.AfterFunc(1*time.Second, func() {
		atomic.AddInt32(&count, -count)
	})

	var (
		wg    sync.WaitGroup
		now = time.Now()
		n   = 1000
		sg  = &singleflight.Group{}
	)

	for i := 0; i < n; i++ {
		wg.Add(1)
		go func() {
			//res, _ := getArticleDetail(1)
			res, _ := singleFlightGetArticle(sg, 1)
			if res != "article: 1" {
				panic("err")
			}
			wg.Done()
		}()
	}

	wg.Wait()
	fmt.Printf("同时发起 %d 次请求，耗时: %s", n, time.Since(now))
}
```

直接调用的输出类似为：`同时发起 1000 次请求，耗时: 1.0022831s`；而使用 SingleFlight 时输出类似于：`同时发起 1000 次请求，耗时: 2.5119ms`。

可以明显看到，使用 SingleFlight 之后，调用请求耗时（对应 DB 层并发数量）大大降低了。

### 3. 注意事项

SingleFlight 虽然能够很好的避免并发请求时底层服务（如 DB）的压力，但是也还是存在一些坑的。

#### 3.1 一个阻塞，全员等待

使用 SingleFlight 时比较常见的计算直接使用`Do`方法，但是在极端情况下这会导致整个程序 hang 住。

对于前面的例子，在获取数据时增加一个`select`模拟阻塞：

```go
func singleFlightGetArticle(sg *singleflight.Group, id int) (string, error) {
	v, err, _ := sg.Do(string(id), func() (interface{}, error) {
	  // 模拟出现问题，hang 住
	  select {}
	  return getArticleDetail(id)
	})

	return v.(string), err
}
```

此时执行的话，就会导致死锁了：

```
fatal error: all goroutines are asleep - deadlock!

goroutine 1 [select (no cases)]:
```

这种情况可以使用`DoChan`结合`select`做超时控制：

```go
func singleFlightGetArticle(ctx context.Context, sg *singleflight.Group, id int) (string, error) {
	result := sg.DoChan(string(id), func() (interface{}, error) {
		select {}
		return getArticleDetail(id)
	})
	
	select {
	case r := <-result:
		return r.Val.(string), r.Err
	case <-ctx.Done():
		return "", ctx.Err()
	}
}
```

在调用的时候传入一个含有超时的 context 即可。如果执行超时就会返回超时错误。

```
panic: context deadline exceeded
```

#### 3.2 一个出错，全部出错

这个本身不是什么问题，因为 SingleFlight 就是这么设计的。但是实际使用的时候，如果一次调用要 1s，数据库或是下游服务可以支撑 10rps 的请求的时候，就会导致错误阈提高。因为实际上可以 1s 内尝试 10 次，但是用了 SingleFlight 之后就只能尝试一次了，而且只要出错那么这段时间内的所有请求都会收到影响。

这种情可以启动一个 goroutine 定时 forget 一下，相当于将 rps 从 1 提高到了 10：

```go
// g = singleflight.Group{}
// key = "group string"
go func() {
  time.Sleep(100 * time.Millisecond)
  g.Forget(key)
}()
```


