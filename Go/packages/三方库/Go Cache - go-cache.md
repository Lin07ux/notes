> 转摘：[Go 每日一库之 go-cache 缓存](https://mp.weixin.qq.com/s/f4FAt-RgraOFXSfZmWjeoQ)

## 一、总览

### 1.1 简介

[patrickmn/go-cache](https://github.com/patrickmn/go-cache) 是一个轻量级的基于内存的 K-V 存储组件，其内部实现了一个线程安全的`map[string]interface{}`，适用于单机应用，具备如下功能：

* 线程安全，支持多 goroutine 并发安全访问；
* 每个 Value 可以设置独自的过期时间（可以无过期时间）；
* 自动定期清理过期的 Value；
* 可以自定义清理回调函数。

> Value 指的就是 go-cache 中 map 里存储的元素。

go-cache 一般用作临时数据缓存，而不是持久性的数据存储。不过，对于某些停机后快速恢复的场景，go-cache 支持将缓存数据保存到文件，恢复时从文件中将数据加载到内存。

### 1.2 使用

go-cache 的使用比较简单，就是初始化一个 cache 实例，然后向其中添加 K-V，然后使用 K 从中取出对应的 V。如果 cache 实例中存在 K，且对应的 V 没有失效，则能够正常的取出，否则会得到 nil。

示例如下：

```go
func main() {
  // 第一个参数为默认过期时间，设置为 10s；第二个参数为清理间隔，设置为 30s，即每 30s 会自动清理过期的键值对
  c := cache.New(10*time.Second, 30*time.Second)
  
  // 设置一个键值对，过期时间为 3s
  c.Set("a", "testa", 3*time.Second)
  
  // 设置一个键值对，采用缓存实例的默认过期时间，也就是 10s
  c.Set("foo", "bar", cache.DefaultExpiration)
  
  // 设置一个键值对，没有过期时间，不会自动过期，需要手动调用 Delete() 才能删除掉
  c.Set("bar", 42, cache.NoExpiration)
  
  v, found := c.Get("a")
  fmt.Println(v, found) // testa, true
  
  <-time.After(5*time.Second) // 延时 5s
  v, found = c.Get("a")
  fmt.Println(v, found) // nil, false
  
  <-time.After(6*time.Second) // 延时 10s
  v, found = c.Get("foo")
  fmt.Println(v, found) // nil, false
  
  v, found = c.Get("bar")
  fmt.Println(v, found) // 42, true
  
  c.Delete("bar")
  fmt.Println(v, found) // nil, false
}
```

## 二、源码

### 2.1 常量

go-cache 中定义了两个过期时间相关的常量，分别表示无过期时间和默认的过期时间：

```go
const (
  // For use with functions that take an expiration time.
  NoExpiration time.Duration = -1
  // For use with functions that take an expiration time. Equivalent to
  // passing in the same expiration duration as was given to New() or
  // NewFrom() when the cache was created (e.g. 5 minutes.)
  DefaultExpiration time.Duration = 0
)
```

如果添加的 K-V 的过期时间不大于 0，那么该 K-V 将不会被自动过期清理函数清理掉，也就保持永久有效了。

这两个常量可以用在`New()/NewFrom()`函数中，为缓存实例设置默认的过期时间。也可以用在添加 K-V 的时候，来表示其永久有效或者使用默认缓存实例的过期时间。

### 2.2 结构体

go-cache 中主要有四个结构体，其中 Cache 和 Item 类型是暴露出来的，可以被业务方直接使用，而 cache 和 janitor 则是 go-cache 内部使用的：

```go
type Cache struct {
  *cache // cache 实例，这样包裹一层是有其他考虑的，在后面创建缓存实例的时候会有说明
}

type cache struct {
  defautExpiration time.Duration   // 默认过期时间
  items            map[string]Item // 键值对集合
  mu               sync.RWMutex    // 读写锁
  onEvicted        func(string, interface{}) // 删除 key 时的回调函数
  janitor          *janitor        // 定期清理器，定期检查并删除过期的 Item
}

type Item struct {
  Object     interface{} // 存放 K-V 的值，可以存储任何类型的值
  Expiration int64 // K-V 的过期数据（绝对时间）
}

type janitor struct {
  Interval time.Duration // 清理时间间隔
  stop     chan bool // 是否停止
}
```

这里，对外暴露的 Cache 类型中，仅持有一个未暴露的 cache 对象指针，之所以要这样包裹一层，是为了能够不影响 GC 正常清理 Cache 实例，具体在后面创建实例的时候有说明。

cache 类型则是 go-cache 缓存的核心类型，实现了缓存相关操作的各种方法。

Item 类型是 cache 中的 map 存储的值的类型，封装了该值的过期时间，以便实现 K-V 的过期处理。

janitor 是辅助 cache 实例进行定期过期清理的结构，其实现了定期清理过期缓存的相关方法。

### 2.3 实例化 Cache

go-cache 提供了两种实例化 Cache 的方式：`New()`函数初始化一个空数据的 Cache 实例，`NewFrom()`函数从用户传入的数据集初始化一个 Cache 实例。

代码如下：

```go
func New(defaultExpiration, cleanupInterval time.Duration) *Cache {
  items := make(map[string]Item)
  return newCacheWithJanitor(defaultExpiration, cleanupInterval, items)
}

func NewFrom(defaultExpiration, cleanupInterval time.Duration, items map[string]Item) *Cache {
  return newCacheWithJanitor(defaultExpiration, cleanupInterval, items)
}

func newCacheWithJanitor(de, ci time.Duration, items map[string]Item) *Cache {
  c := newCache(de, m)
  // This trick ensures that the janitor goroutine (which--granted it was enabled--
  // is running DeleteExpired on c forever) does not keep the returned C object from
  // being garbage collected. When it is garbage collected, the finalizer stops the
  // janitor goroutine, after which c can be collected.
  C := &Cache{c}
  if ci > 0 {
    runJanitor(c, ci)
    runtime.SetFinalizer(C, stopJanitor)
  }
  return C
}

func newCache(de time.Duration, m map[string]Item) *cache {
  if de == 0 {
    de == -1
  }
  return &cache{
    defaultExpiration: de,
    times:             m,
  }
}
```

可以看到，不论是`New()`函数`NewFrom()`函数，在初始化 Cache 的时候都调用了`newCacheWithJanitor()`函数。而这个函数除了依次初始化 cache 和 Cache 实例，还会在设置了默认清理时间的时候，开始一个自动清理器的 goroutine。

因为 janitor 在清理过期 K-V 的时候，自然要操作缓存的核心数据 items 这个 map，所以就要持有缓存实例对象。如果直接持有 Cache 实例，那么当 Cache 用完了，需要被 GC 回收的时候，会看到 janitor 还持有 Cache 的引用，就不能完成 Cache 的回收了。

因此，janitor 持有 Cache 中的 cache 实例，避免了 GC 回收 Cache 的问题。这也是为什么在 go-cache 在对外提供的 Cache 类型的时候多封装了一层。

同时，因为 Go 语言没有面向对象语言中常会提供的析构函数，所以在开启 janitor 自动清理的 goroutine 后，也通过`runtime.SetFinalizer()`函数来监听返回的 Cache 实例的回收操作。当 GC 准备回收 Cache 的时候，就会自动触发`stopJanitor()`函数的执行，从而停止 janitor 自动清理 goroutine。

### 2.4 janitor 自动清理器

Cache 在实例化的时候，如果设置了自动清理时间，就会开启一个 janitor 来自动清理过期的 K-V。启动和停止 janitor 的源码如下：

```go
func runJanitor(c *cache, ci time.Duration) {
  j := &janitor{
    Interval: ci,
    stop:     make(chan bool),
  }
  c.janitor = j
  go j.Run(c)
}

func stopJanitor(c *Cache) {
  c.janitor.stop <- true
}

type janitor struct {
  Interval time.Duration,
  stop     chan bool
}

func (j *janitor) Run(c *cache) {
  ticker := time.NewTicker(j.Interval)
  for {
    select {
    case <-ticker.C:
      c.DeleteExpired()
    case <-j.stop:
      ticker.Stop()
      return
    }
  }
}
```

可以看到，janitor 的实现很简单，其原理就是通过`for...select`来不断的监听定时器和结束清理 Channel，这样就能周期性的触发 Cache 的清理，并且能优雅的停止自动清理。

在开启 janitor 的时候，是通过一个新的 goroutine 来执行这个`for...select`循环的，避免了对 Cache 主流程的影响。

另外，由于 Cache 的 janitor 是不对外暴露的，所以外界是无法主动关闭 Cache 的清理器的。

### 2.5 添加操作

在得到 Cache 实例时候，就能够对其进行数据添加了。go-cache 的数据添加有多个方法：

* `Set(k string, x interface{}, d time.Duration)` 添加一个 K-V 到缓存中，如果已经存在相同的 Key，则进行替换，否则就新增；
* `SetDefault(k stirng, x interface{})` 与`Set()`方法类似，只是其不需要设置过期时间，而是使用 cache 实例的默认过期时间；
* `Add(k string, x interface{}, d time.Duration)` 新增一个 K-V 到缓存中，如果已经存在相同的 Key，且没有过期，则会产生错误；
* `Replace(k string, x interface{}, d time.Duration)` 替换缓存中 K-V 的值，如果该 Key 不存在，或已过期，则会产生错误。

其中，最常用的还是`Set()`方法，其源码如下：

```go
func (c *cache) Set(k string, x interface{}, d time.Duration) {
  var e int64
  if d == DefaultExpiration {
    d = c.defaultExpiration
  }
  if d > 0 {
    e = time.Now().Add(d).UnixNano() // 设置绝对过期时间
  }
  c.mu.Lock()
  c.items[k] = Item{
    Object:     x,
    Expiration: e,
  }
  c.mu.Unlock()
}
```

可以看到，该方法的逻辑很简单，就是在加锁之后，向 cache 的 items map 中添加元素。主要注意的是，Item 的过期时间为绝对时间。

### 2.6 删除操作

go-cache 的删除操作主要有两个：

* `Delete()` 一般的删除操作，不论 K-V 是否过期，都会删除；
* `DeleteExpired()` 用于执行批量的删除操作，只会删除已过期的键值对。

执行删除操作的时候，都会判断是否需要执行删除 K-V 的回调函数。

源码如下：

```go
type keyAndValue struct {
  key   string
  value interface{}
}

// Delete an item from the cache. Does nothing if the key is not in the cache.
func (c *cache) Delete(k string) {
  c.mu.Lock()
  v, evicted := c.delete(k)
  c.mu.Unlock()
  if evicted {
    c.onEvicted(k, v)
  }
}

// Delete all expired items from the cache
func (c *cache) DeleteExpired() {
  var evictedItems []keyAndvalue
  now := time.Now().UnixNano()
  c.mu.Lock()
  for k, v := range c.items {
    if v.Expiration > 0 && now > v.Expiration {
      ov, evicted := c.delete(k)
      if evicted {
        evictedItems = append(evictedItems, keyAndValue{k, ov})
      }
    }
  }
  c.mu.Unlock()
  for _, v := range evictedItems {
    c.onEvicted(v.key, v.value)
  }
}

func (c *cache) delete(k string) (interface{}, bool) {
  // 如果设置了 onEvicted 删除回调，且 K-V 存在，则返回存储的值，并表示可以触发删除回调
  if c.onEvicted != nil {
    if v, found := c.items[k]; found {
      delete(c.items, k)
      return v.Object, true
    }
  }
  // 否则，在删除后，不返回存储的值，且表示不需要触发回调
  delete(c.items, k)
  return nil, false
}
```

再删除相关的流程，设置了一个新的内部类型`keyAndValue`，这仅仅是为了后续触发删除 K-V 的回调函数而使用的，不需要关注。

由于触发删除 K-V 回调函数的时候，该 K 已经完成删除了，所以此时就不需要再持有读写锁了。因此，不论是`Delete()`还是`DeleteExpired()`方法，都是先释放锁，再执行回调函数。

### 2.7 其他操作

除了常用的增删操作，go-cache 中还提供了很多其他方法：

* `Flush()` 清空缓存数据；
* `Items()` 返回缓存中未过期的数据，其结果可以用于`NewFrom()`函数来恢复数据；
* `ItemCount()` 获取缓存中数据的数量，包括已过期但未删除的数量，底层使用的就是`len()`方法；
* `OnEvicted()` 设置删除 K-V 时的回调函数；
* 自增自减操作：go-cache 中有大量的 Increment 和 Decrement 方法，分别针对不同类型的值进行增加更新；
* 备份恢复操作：虽然 go-cache 是专注于本地使用的数据缓存，但是也提供了备份数据和回复数据的操作，如`Save()/SaveFile()`和`Load()/LoadFile()`操作等。备份的数据使用`encoding/gob`进行序列化。

## 三、总结

**go-cache 的定位就是一个本地使用的数据缓存逻辑**，偏向于临时使用，对持久化保存并没有过多关注，而且**不适合大数据量的缓存使用场景**。

由于其数据都存放在一个 map 中，没有向其他缓存系统那样进行分片，所以不适合存储数据量很大或者写入频繁的缓存来使用，否则会在高并发场景下存在读写性能。

另外，其过期清理操作是通过`DeleteExpired()`方法来执行，而该方法在清理的时候，在遍历整个 map 的过程中，一直持有写锁，当数据量很大，或者碰到缓存雪崩（大量 Key 过期）的时候，会验证影响性能。


