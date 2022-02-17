> 转摘：
> 
> 1. [一口气搞懂 Go sync.Map 所有知识点](https://mp.weixin.qq.com/s/w0_PeD9Xcm3XDhWeA_D1_g)
> 2. [浅谈Golang两种线程安全的map](https://mp.weixin.qq.com/s/H5HDrwhxZ_4v6Vf5xXUsIg)

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

`sync.Map`是用读写分离实现的，其思想就是用空间换时间。read map 可以无锁访问，而且会优先操作。倘若只操作 read map 就可以满足要求（增删改查遍历），那就不用去操作 write map(这个 map 的读写都要加锁)。所以在某些特定的场景中它发生锁竞争的频率会远远小于 map + RWLock 的实现方式。

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
  amended bool  // 为 true 时，表示 dirty 中有 read 中没有的 entry
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

### 3.2 Load 查找

当从`sync.Map`类型中读取数据时，会先从`read`中查看是否包含所需要的元素，如果找不到才会根据情况加锁后从`dirty`中查找。

#### 3.2.1 流程图

![](http://cnd.qiniu.lin07ux.cn/markdown/1645023750798-0a985be7f884.jpg)

#### 3.2.2 源码

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
    - 不论是否找到了对应的 key，都需要执行`missLocked()`操作，将`m.misses`进行递增；
    - 解锁；

3. 如果上面都没有要找的 key，则直接返回；否则调用`entry.Load()`方法来获取对应的值。

`sync.Map`的读操作性能如何之高的原因就在于存在`read`这一巧妙的设计，其作为一个缓存层，提供了快路径(`fast path`)的查找。同时，其结合`amended`属性，配套解决了每次读取都涉及到锁的问题，实现了读这一使用场景的高性能。

#### 3.2.3 其他

每次从`dirty`中取值的时候，不论是否取到了，都会调用`Map.missLocked()`方法。这个方法除了会累加 miss 的次数，还会在 miss 达到了一定的次数（`dirty`的长度）时，同步`dirty`和`read`的值，并将`dirty`和`misses`重置为零值：

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

### 3.3 Store 写入

`syn.Map.Store`方法，是为其新增或更新一个元素。

#### 3.3.1 流程图

![](http://cnd.qiniu.lin07ux.cn/markdown/1645067981451-478e8cd0eca8.jpg)

#### 3.3.2 源码

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
    // 存在于 readOnly 中
    // 如果 entry.p 之前的状态是 expunged，则将其设置为 nil
    if e.unexpungeLocked() {
      m.dirty[key] = e
    }
    e.storeLocked(&value)
  } else if e, ok := m.dirty[key]; ok {
    // 存在于 dirty 中
    e.storeLocked(&value)
  } else {
    // 是一个新的 key，需要添加到 dirty 中
    if !read.amended {
      // 把 readOnly 中未标记为删除的数据拷贝到 dirty 中 
      m.dirtyLocked()
      // 重置 readOnly 的 amended 为 true
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

#### 3.3.3 其他

函数`entry.tryStore()`代码如下：

```go
func (e *entry) tryStore(i *interface{}) bool {
  // 循环确保可以更新时必然成功更新
  for {
    p := atomic.LoadPointer(&e.p)
    if p == expunged {
      return false
    }
    if atomic.CompareAndSwapPointer(&e.p, p, unsafe.Pointer(i)) {
      return true
    }
  }
}
```

函数`entry.unexpungeLocked()`代码如下：

```go
func (e *entry) unexpungeLocked() (wasExpunged bool) {
  return atomic.CompareAndSwapPointer(&e.p, expunged, nil)
}
```

函数`sync.Map.dirtyLocked()`代码如下：

```go
func (m *Map) dirtyLocked() {
  // 只要调用 dirtyLocked，此时 dirty 肯定等于 nil
  if m.dirty != nil {
    return
  }
  // dirty 为 nil 时，把 readOnly 中没有被标记成删除的 entry 添加到 dirty
  read, _ := m.read.Load().(readOnly)
  m.dirty = make(map[interface{}]*entry, len(read.m))
  for k, e := range read.m {
    // tryExpungeLocked 函数在 entry 未被删除(e.p != expunged && e.p != nil)时返回 false
    // 并在 e.p == nil 时将其重置为 expunged 并返回 true
    if !e.tryExpungeLocked() {
      m.dirty[k] = e // entry 没被删除则添加到 dirty 中
    }
  }
}
```
        
### 3.4 Delete 删除

一般的 map 的写入和删除操作基本类似，所以性能理论上都差不太多。但是`sync.Map`对删除做了一些特别的处理，所以其性能比读取更好一些。

#### 3.4.1 流程图

![](http://cnd.qiniu.lin07ux.cn/markdown/1645069673444-831378d5a927.jpg)

#### 3.4.2 源码

```go
func (m *Map) Delete(key interface{}) {
  m.LoadAndDelete(key)
}

func (m *Map) LoadAndDelete(key interface{}) (value interface{}, loaded bool) {
  read, _ := m.read.Load().(readOnly)
  e, ok := read.m[key]
  
  // readOnly 中不存在，但 dirty 中可能存在
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
    // 将 entry 标记为软删除
    return e.delete()
  }
  return nil, false
}
```

可以看到，删除操作其实就是`Map.LoadAndDelete()`方法的直接调用，处理逻辑上依旧是使用 double check 的方式从`read`中先读取数据，读取不到就从`dirty`中再次读取。

在读取到对应的`entry`之后，会使用`delte()`操作将其直接从`dirty`中删除，再使用`entry.delete()`方法将其自身标记为软删除状态（`entry.p = nil`）。

所以，如果能直接从`read`中找到对应的元素并进行删除，则处理过程是非常高效的。

而对于从`dirty`中找到对应元素的情况下，会再次触发`Map.missLocked()`方法，从而使得在从`dirty`中删除元素后，能促使`read`和`dirty`尽早的进行同步。

另外，由于`sync.Map`的删除操作在能从`read`中取到的情况下，是进行标记而不是删除，所以会有内存无法释放的问题。比如：将一个连接作为 key 存进到`sync.Map`中，于是和这个连接相关的资源（如：buffer）的内存就永远无法释放了。

#### 3.4.3 其他

函数`entry.delete`代码如下：

```go
func (e *entry) delete() (value interface{}, ok bool) {
  for {
    p := atomic.LoadPointer(&e.p)
    if p == nil || p == expunged {
      return nil, false
    }
    if atomic.CompareAndSwapPointer(&e.p, p, nil) {
      return *(*interface{})(p), true
    }
  }
}
```

### 3.5 Range 遍历

`sync.Map.Range`方法可以遍历 Map，参数是一个函数（入参：key 和 value；返回值：是否停止遍历）。

#### 3.5.1 流程图

![](http://cnd.qiniu.lin07ux.cn/markdown/1645070915055-c332355169df.jpg)

#### 3.5.2 源码

```go
func (m *Map) Range(f func(key, value interface{}) bool) {
  read, _ := m.read.Load().(readOnly)
  // dirty 中存在 readOnly 中不存的元素
  if read.amended {
    m.mu.Lock()
    read, _ = m.read.Load().(readOnly)
    if read.amended {
      read = readOnly{m: m.dirty}
      m.read.Store(read)
      m.dirty = nil
      m.misses = 0
    } 
    m.mu.Unlock()
  }
  
  // 遍历 readOnly.m
  for k, e := range read.m {
    v, ok := e.load()
    if !ok {
      continue
    }
    if !f(k, v) {
      break
    }
  }
}
```

结合上面的代码可知：

1. 当 Map 的全部的 key 都存在于 readOnly 中时，是无锁遍历的，此时`Range`性能最高；
2. 当 dirty 中有 readOnly 中没有的 key 时，会一次性加锁拷贝 dirty 中的元素到 readOnly 中，以减少多次加锁访问 dirty 中的数据。

## 四、总结

### 4.1 使用场景

**`sync.Map`更适合读多更新多而插入新值少的场景**（appendOnly 模式，尤其是 key 存一次，多次读取而不删除的情况），因为在 key 存证的情况下读写删操作都可以不用加锁直接访问 readOnly。

**`sync.Map`不适合反复插入与读取新值的场景**，因为这种场景会频繁操作 dirty，需要频繁加锁和更新 read（此场景下开源库 [orcaman/concurrent-map](https://github.com/orcaman/concurrent-map) 更合适）。

> `orcaman/concurrent-map`对原生 map 采用分片加锁的方式，降低锁粒度，从而达到最少的锁等待时间（锁冲突）。

另外，`sync.Map`在一些时候是标记删除，而非真正的删除，这在某些时候会造成内存泄露。

### 4.2 expunged 的设计

`entry.p`取值有 3 种：`nil`、`expunged`和真实值。这个属性是用来标注当前`entry`是否被删除了。

#### 4.2.1 什么时候设置为 expunged？

当用`Store`方法插入新的 key 时，会加锁访问 dirty，并把 readOnly 中的未被标记为删除的所有 entry 指针复制到 dirty。此时之前被`Delete`方法标记为软删除的 entry(`entry.p = nil`)，都会被标记为`entry.p = expunged`。而那些被标记为 expunged 的 entry 将不会出现在 dirty 中。

#### 4.2.2 如果只有 nil 没有 expunged 会怎么样

* 直接删除`entry.p == nil`的元素，而不是设置为`expunged`

    在用`Store`方法插入新 key 的时候，将 readOnly 数据拷贝到 dirty 中，直接把`entry.p == nil`的 entry 删掉。但是这样做需要对 readOnly 加锁，与`sync.map`读写分离的设计理念冲突。
    
* 不删除`entry.p == nil`的元素，全部拷贝：

    在用`Store`方法插入新 key 时，readOnly 中`entry.p == nil`的数据全部拷贝到 dirty 中。那么在 dirty 提升为 readOnly 后，这些被删除的脏数据仍会保留，也就是说它们用于得不到清除，占用的内存会越来越大。
    
* 不拷贝`entry.p == nil`的元素：

    在用`Store`方法插入新 key 时，不把 readOnly 中`entry.p == nil`的数据拷贝到 dirty 中，那在用`Store`更新值时，就会出现 readOnly 和 dirty 不同步的状态，即 readOnly 中存在 dirty 中不存在的 key。当 dirty 提升为 readOnly 时会出现数据丢失的问题。

### 4.3 未说明不实现 len 方法

这是成本和收益的权衡：

1. 实现`len`方法要统计 readOnly 和 dirty 的数据量，势必会引入锁竞争，导致性能下降，而且会额外增加代码实现复杂度；
2. 对`sync.map`的并发操作导致其数据量可能变化很快，`len`方法的统计结果参考价值不大。

