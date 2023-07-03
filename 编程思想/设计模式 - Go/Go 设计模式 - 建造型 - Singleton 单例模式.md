> 转摘：[最简单的单例模式，Go版本的实现你写对了吗？](https://mp.weixin.qq.com/s/1ZuhUA9Lt2uLFlamIY6fLQ)

### 1. 简介

单例模式是用来控制类型实例的数量的，当需要确保一个类型只有一个实例的时候，就需要使用单例模式。

由于要控制实例数量，所以就只能把实例的访问进行收口，所以单例模式还会提供一个访问该实例的全局端口，一般都会命名为`GetInstance`之类的函数，用作实例访问的端口。

根据创建实例的时机，可以将单例模式划分为：

* **饿汉模式**：适用于在程序早期初始化时就创建已经确定需要加载的类型实例，比如数据库实例；
* **懒汉模式**：就是延迟加载模式，适合程序执行过程中在条件成立时才创建加载的类型实例。

### 2. 组成

单例模式中，最重要的是对实例的访问进行控制，不允许用户之间创建实例，所以单例模式中都会定义一个获取实例的函数，以及一个不能在外部被初始化的类型。

在 Go 语言中，获取实例的函数需要被导出，而实例类型在被定义为不能导出。至于饿汉模式和懒汉模式的区别就是在什么时候初始化实例了。

### 3. 示例

**饿汉模式**

饿汉模式要求在程序初始化的就创建好所需要的实例，而 Go 语言中，每个包的`init`函数都会在程序初始化的时候被执行，所以就可以借助`init`函数来完成实例的初始化。

如下即为用饿汉模式实现数据库连接实例的初始化：

```go
package dao

// 需要将类型定义为非导出的
type databaseConn struct {
  ...
}

var dbConn *databaseConn

func init() {
  dbConn = &databaseConn{}
}

// 导出的获取实例方法
func Db() *databaseConn {
  return dbConn
}
```

**懒汉模式**

懒汉模式的核心就是在需要的时候才生成类型实例，这就要求实例的创建是动态的，不能在程序初始化的时候就生成了。在 Go 语言中，动态创建实例就不可避免要注意并发问题。为了避免并发访问获取实例方法时导致创建了多个实例，就需要使用锁等方式来限制实例的创建过程。

如下是使用懒汉模式创建单例对象的代码：

```go
import (
  "sync"
  "sync/atomic"
)

var initialized uint32
var mu sync.Mutex

type singleton struct {
  ...
}

func GetInstance() *singleton {
  if atomic.LoadUint32(&initialized) == 1 {
    return instance
  }
  
  mu.Lock()
  defer mu.Unlocked()
  
  if initialized == 0 {
    instance = &singleton{}
    atomic.StoreUint32(&initialized, 1)
  }
  
  return instance
}
```

这里使用了原子操作和并发锁来限制只会创建一个实例，当然也可以使用`sync.Once`来简化这个逻辑。

### 4. 总结

单例模式的核心就是控制住获取实例的入口，使得外部不能直接进行实例化，只能通过统一的入口来获取实例。

单例模式能够有效的控制实例数量，避免创建多个实例造成的空间浪费。