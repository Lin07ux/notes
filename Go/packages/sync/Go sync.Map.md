> 转摘：[一口气搞懂 Go sync.Map 所有知识点](https://mp.weixin.qq.com/s/w0_PeD9Xcm3XDhWeA_D1_g)

Go 中的 map 不是并发安全的，所以在 Go 1.9 引入了`sync.Map`来解决 map 的并发安全问题。

在 Go 官方文档中明确指出`sync.Map`类型的一些建议：

* 在多个 goroutine 中并发使用是安全的，不需要额外的锁定或协调控制；
* 大多数代码应该使用原生的 map 类型，而不是单独的锁定或协调控制，以获得更好的类型安全性和维护性。

## 一、基本

`sync.Map` 有如下一些方法：

* `Load()` 加载 key 的数据
* `Store()` 更新或新增 key 的数据
* `Delete()` 删除 key 的数据
* `Range()` 遍历数据
* `LoadOrStore()` 如果 key 存在则取出数据返回，反之则进行设置
* `LoadAndDelete()` 如果存在 key 则删除

### 1.1 map 的并发问题

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

### 1.2 sync.Map 解决并发问题

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

### 1.3 计算 sync.Map 长度

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

## 二、性能

### 2.1 性能特点

`sync.Map`类型针对以下场景进行了性能优化：

* 当一个给定的键的条目只被写入一次但被多次读取时，例如在仅会增长的缓存中。
* 当多个 goroutines 进行读取、写入和覆盖不相干的键集合的条目时。

总结：**`sync.Map`对读多写少的情形有更好的性能**。

### 2.2 压测结果

对 *原生 map + mutex*、*原生 map + rwmutex*、*sync.Map* 三种方式上进行压测，结果如下：

* **写入** 从慢到快的排序为：`sync.Map` < 原生的 map + 互斥锁（mutex）方式 < 原生 map + 读写锁（rwmutex）；
* **查找** 从慢到快的排序为：原生的 map + 互斥锁（mutex）方式 < 原生 map + 读写锁（rwmutex） < `sync.Map`；
* **删除** 从慢到快的排序为：原生 map + 读写锁（rwmutex） < 原生的 map + 互斥锁（mutex）方式 < `sync.Map`。

根据上述的压测结果，可以得出`sync.Map`类型：

* **在读取和删除场景上性能是最佳的**，领先一倍有余；
* **在写入场景上的性能非常差**，落后原生的 map + 互斥锁整整一倍之多。

### 三、源码解析

`sync.Map`的读写速度差别如此之大，在于其源码中对并发读取功能做了很大的优化有关。

`sync.Map`类型本质上是维护了两个 map，一个叫`read`，一个叫`dirty`，它们实际存储的数据也差不多的：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635827444115-55c600e6c067.jpg)

### 3.1 数据结构

`sync.Map`类型的底层数据结构如下：

```go
type Map struct {
  mu     Mutex
  read   atomic.Value // readOnly
  dirty  map[interface{}]*entry
  misses int
}

// Map.read 属性实际存储的是 readOnly
type readOnly struct {
  m       map[interface{}]*entry
  amended bool
}

type entry struct {
  p unsafe.Pointer // *interface{}
}
```

其中，`sync.Map`的各个属性的用处如下：

* `mu` 互斥锁，用于保护`read`和`dirty`；
* `read` 只读数据，支持并发读取（`atomic.Value`类型）。如果涉及到更新操作，则只需要加锁类保证数据安全。其实际存储的是一个`readOnly`结构体；
* `dirty` 读写数据，是一个原生的 map，也就是非并发安全的。操作`dirty`需要加锁来保证数据安全；
* `misses` 统计有多少次读取`read`没有命中。每次从`read`中读取失败后，该值都会加 1。

`readOnly`和`entry`都是辅助结构，用于支撑`sync.Map`结构体的功能：

* `readOnly`内部也有一个原生的 map，用于存放和`dirty`一样的数据结构；另外的一个`amended`属性用于标记`Map.read`和`Map.dirty`数据是否一致；
* `entry`只包含一个指针 p，用于指向用户存储的元素（key）所指向的 value 值。

`read`和`dirty`各自维护了一套 key，并都指向相同的`entry`，所以当修改了这个`entry`的指向之后，对`read`和`dirty`都是可见的。
    
`entry.p`这个指针有三种状态：

* `p == nil` 说明这个键值对已被删除，此时有：`m.dirty == nil`，或`m.dirty[k]`指向该`entry`；
* `p == expunged` 说明这个键值对已被删除，并且有`m.dirty != nil`，而且`m.dirty`中没有这个 key；
* 其他情况下表示`p`指向一个正常值，其值为实际的`interface{}`值的地址，并且被记录在了`m.read.m[key]`中。而且，如果`m.dirty != nil`，那么它也存在于`m.dirty[key]`中。两者实际上指向的是同一个值。

如果`entry.p != expunged`，和`entry`相关联的这个 value 可以被原子地更新；否则，仅当它被初次设置到`m.dirty`之后才可以被更新。

> `expunged`的定义为：`var expunged = unsafe.Pointer(new(interface{}))`。

这三个结构体的关系图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635846057742-76f6be003271.jpg)

### 3.2 查找过程

当从`sync.Map`类型中读取数据是，会先从`read`中查看是否包含所需要的元素，如果找不到才会根据情况加锁后从`dirty`中查找。

源码如下：

```go
func (m *Map) Load(key interface{}) (value interface{}. ok bool) {
  read, _ := m.read.Load().(readOnly)
  e, ok := read.m[key]
  if !ok && read.amended {
    m.mu.Lock()
    read, _ = m.read.Load().(readOnly)
    e, ok = read.m[key]
    if !ok && read.amended {
      e, ok = m.dirty[key]
      m.missLocked()
    }
    m.mu.Unlock()
  }
  if !ok {
    return nil, false
  }
  return e.load()
}
```

`sync.Map`的读取逻辑如下：

1. 直接从`read`中获取 key，如果获取到就直接返回；

2. 未获取到，而且`read.amended == true`，说明`dirty != nil`，那么`dirty`中就可能存在要找的 key，此时需要：

    - 加锁；
    - 重新从`read`中找一遍。这个操作是 free lock 逻辑中的 double check，可以避免在上锁的过程中，`dirty`被提升为`read.m`；
    - 如果依旧没有取到，而且`read.amended == true`，则从`dirty`中查找；
    - 不论是否找到了对应的 key，都需要将`m.misses`进行递增；
    - 解锁；

3. 如果上面都没有要找的 key，则直接返回；否则调用`entry.Load()`方法来获取对应的值。

`sync.Map`的读操作性能如何之高的原因就在于存在`read`这一巧妙的设计，其作为一个缓存层，提供了快路径(`fast path`)的查找。

同时，其结合`amended`属性，配套解决了每次读取都涉及到锁的问题，实现了读这一使用场景的高性能。每次从`dirty`中取值的时候，不论是否取到了，都会调用`Map.missLocked()`方法。这个方法除了会累加 miss 的次数，还会同步`dirty`和`read`的值：

```go
func (m *Map) missLocked() {
  m.misses++
  if m.misses < len(m.dirty) {
    return
  }
  m.read.Store(readOnly{m: m.dirty})
  m.dirty = nil
  m.misses = 0
}
```

可以看到，当 miss 达到了一定的次数（`dirty`的长度），则会使用`dirty`重建`read`，并且将`dirty`和`misses`都重置为零值。

另外，在最后取值的时候，调用的`entry.load()`方法也会判断一下值是否已被标记为删除状态：

```go
func (e *entry) load() (value interface{}, ok bool) {
  p := atomic.LoadPointer(&e.p)
  if p == nil || p == expunged {
    return nil, false
  }
  return *(*interface{})(p), true
}
```

### 3.3 写入过程

`syn.Map.Store`方法，是为其新增或更新一个元素。源码如下：

```go
func (m *Map) Store(key, value interface{}) {
  // 如果 read 中包含该元素（且没有被标记为删除状态），则尝试进行存储
  read, _ := m.read.Load().(readOnly)
  if e, ok := read.[key]; ok && e.tryStore(&value) {
    return
  }
  
  // 否则，进行 dirty 的存储更新
  m.mu.Lock()
  read, _ = m.read.Load().(readOnly)
  if e, ok := read.m[key]; ok {
    if e.unexpungeLocked() {
      // The entry was previously expunged, which implies that there is a
      // non-nil dirty map and this entry is not in it
      m.dirty[key] = e
    }
    e.storeLocked(&value)
  } else if e, ok := m.dirty[key]; ok {
    e.storeLocked(&value)
  } else {
    if !read.amended {
      // We're adding the first new key to the dirty map.
      // Make sure it is allocated and mark the read-only map as incomplete.
      m.dirtyLocked()
      m.read.Store(readOnly{m: read.m, amended: true})
    }
    m.dirty[key] = newEntry(value)
  }
  m.mu.Unlock()
}
```

这里的逻辑分为加锁和不加锁两部分，其中

* 不加锁部分是直接从`read`中获取并存储更新的过程，在一定时候能加快存储速度。如果存储的对象存在于`read`中，则利用 atomic 库实现原子性的存储操作(`entry.tryStore()`方法中)，直接更新原本的值为新的值。

* 加锁部分则按照情况分为如下三个处理分支：

    1. 再次判断`read`中是否存在该元素（这是为了防止在第一次获取的时候该元素被写入了），如果存在，则：

        - 先判断是否已已被标记为已删除（expunged），是的话说明`dirty`中肯定没有该元素了。此时需要将其删除标记解除（恢复成`nil`），并重新存入到`dirty`中；
        - 然后直接使用 atomic 原子性的更新为新的值。

    2. 若`read`中不存在，但`dirty`中存在该元素，则直接更新其值（这时`read.m`中已然没有该值）；

    3. 否则的话，说明`read`和`dirty`中都没有该元素，则需要

        - 确保`read.amended`为`true`：如果此时不是 true，则重新构造`readOnly`实例，并原子性的更新到`m.read`字段中；
        - 构造一个新的`entry`实例，并存储到`m.dirty`中。
       
总结下，整个写入流程其实就是：

1. 首先从`read`中查找，有的话则直接更新；
2. `read`中没有则进行加锁，操作`dirty`，根据各种数据情况和状态进行处理。

写入性能差的原因也就明确了：

* 写入一定会经过对`read`的操作，无论如何都多了一层操作，后续还要区分情况和状态进行处理，性能开销比较大；
* 初始化并存入一个新的`entry`时，会从`read`中复制全量的数据`read.m`，如果该数据量比较大，则会更影响性能。

也因此，`sync.Map`类型不适合写多的场景，读多写少的场景还是比较好的。若有大数据量的场景，则需要考虑`read`复制数据时的偶然性性能抖动是否能够接受。
        
### 3.4 删除过程

一般的 map 的写入和删除操作基本类似，所以性能理论上都差不太多。但是`sync.Map`对删除做了一些特别的处理，所以其性能比读取更好一些。

源码如下：

```go
func (m *Map) Delete(key interface{}) {
  m.LoadAndDelete(key)
}

func (m *Map) LoadAndDelete(key interface{}) (value interface{}, loaded bool) {
  read, _ := m.read.Load().(readOnly)
  e, ok := read.m[key]
  if !ok && read.amended {
    m.mu.Lock()
    read, _ = m.read.Load().(readOnly)
    e, ok = read.m[key]
    if !ok && read.amended {
      e, ok = m.dirty[key]
      delete(m.dirty, key)
      m.missLocked()
    }
    m.mu.Unlock()
  }
  if ok {
    return e.Delete()
  }
  return nil, false
}
```

可以看到，删除操作其实就是`Map.LoadAndDelete()`方法的直接调用，处理逻辑上依旧是使用 double check 的方式从`read`中先读取数据，读取不到就从`dirty`中再次读取。

在读取到对应的`entry`之后，会使用`delte()`操作将其直接从`dirty`中删除，或者使用`entry.Delete()`方法将其自身标记为`expunged`删除状态。

所以，如果能直接从`read`中找到对应的元素并进行删除，则处理过程是非常高效的。

而对于从`dirty`中找到对应元素的情况下，会再次触发`Map.missLocked()`方法，从而使得在从`dirty`中删除元素后，能促使`read`和`dirty`尽早的进行同步。

另外，由于`sync.Map`的删除操作在能从`read`中取到的情况下，是进行标记而不是删除，所以会有内存无法释放的问题。比如：将一个连接作为 key 存进到`sync.Map`中，于是和这个连接相关的资源（如：buffer）的内存就永远无法释放了。

## 四、总结

1. `sync.Map`虽然支持并发读写 map，但更适合读多写少的场景。其写入性能比较差。
2. `sync.Map`在一些时候是标记删除，而非真正的删除，这在某些时候会造成内存泄露。

