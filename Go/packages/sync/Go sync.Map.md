Go 中的 map 不是并发安全的，所以在 Go 1.9 引入了`sync.Map`来解决 map 的并发安全问题。

不过`sync.Map`却没有实现`len()`函数，如果要计算其长度，需要使用`sync.Range()`方法来循环累计。

`sync.Map` 有如下一些方法：

* `Load()` 加载 key 的数据
* `Store()` 更新或新增 key 的数据
* `Delete()` 删除 key 的数据
* `Range()` 遍历数据
* `LoadOrStore()` 如果 key 存在则取出数据返回，反之则进行设置
* `LoadAndDelete()` 如果存在 key 则删除

### 1. map 的并发问题

```go
func main() {
  demo := make(map[int]int)
  
  go func() {
    for j := 0; j < 1000; j++ {
      demo[j] = j
    }
  }()
  
  go func() {
    for j := 0; j < 1000; j++ {
      fmt.Println(demo[j])
    }
  }
  
  time.Sleep(1 * time.Second)
}
```

执行输出：

```
fatal error: concurrent map read and map write
```

### 2. sync.Map 解决并发问题

将上面的程序改成如下方式：

```go
func main() {
  demo := sync.Map{}
  
  go func() {
    for j := 0; j < 1000; j++ {
      demo.Store(j, j)
    }
  }()
  
  go func() {
    for j := 0; j < 1000; j++ {
      fmt.Println(demo.Load(j))
    }
  }
  
  time.Sleep(1 * time.Second)
}
```

执行输出类似如下：

```
<nil> false
1 true

...

999 true
```

`sync.Map`内部其实是使用了`sync.atomic`库的相关功能来实现并发读取和写入操作的。

### 3. 计算 sync.Map 长度

map 的长度可以直接使用`len()`方法得到，但是`sync.Map`则不能由`len()`方法计算长度。此时需要使用`sync.Range()`来循环并累计计算：

```go
func main() {
  demo := sync.Map{}
  
  for j := 0; j < 1000; j++ {
    demo.Store(j, j)
  }
  
  lens := 0
  demo.Range(func(key, value interface{}) bool {
    lens++
    return true
  })
  
  fmt.Println("len of demo: ", lens)
}
```

执行得到输出如下：

```
len of demo: 1000
```

