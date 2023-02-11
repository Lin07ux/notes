> 转摘：[Go 设计模式｜项目依赖耦合度太高？可以用适配器做下优化](https://mp.weixin.qq.com/s/r8975amH-DcJkWQKytIeJQ)

### 1. 介绍

适配器模式(Adapter Pattern)又叫做变压器模式，它的功能是将一个类的接口变成客户端所期望的另一种接口，从而使原本因接口不匹配而导致无法一起工作的类能够一起工作。

简单来说，适配器模式就是通过增加一个中间层包装，使得服务类能够按照另一种接口约束来工作。

### 2. 组成

适配器模式的结构比较简单，核心就是使用适配器类包装原本的服务类，从而提供所需要的接口约束。对应的 UML 类图如下所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1675688185)

适配器模式中的角色构成如下：

* 客户端接口 Client Interface：也可以被叫做适配器接口，其描述了适配器与客户端代码协作时需要遵循的约定；
* 客户端 Client：可以理解成通过适配器调用服务的代码程序，其只需要通过接口与适配器交互即可，无需与具体的适配器类耦合；
* 适配器 Adapter：作为同时与客户端和服务交互的中介类，它在实现了客户端接口的同时封装了服务对象，并且在接受客户端通过客户端接口发起调用时，将其转换为对被封装的服务对象的调用；
* 服务 Server：服务通常是一些第三方功能类库或者是一些遗留系统的功能类，客户端与其不兼容，无法直接调用其功能需要适配器进行转换。

从上面的类图可以看出，客户端代码只需要通过接口与适配器交互即可，无需与具体的适配器类耦合。这样，如果有需求就可以向程序中添加新类型的适配器而无需修改已有适配器实现。

### 3. 实例

#### 3.1 简单实现

下面是一个最简单的例子，展示了如何实现适配器模式：

```go
// Target 适配器接口，描述客户端期望使用的接口
type Target interface {
  Request() string
}

// Adaptee 是被适配的目标接口
type Adaptee interface {
  SpecificRequest() string
}

// adapteeImpl 被适配的目标类
type adapteeImpl struct{}

func (*adapteeImpl) SpecificRequest() string {
  return "adaptee method"
}

func NewAdaptee() Adaptee {
  return &adapteeImpl()
}

// adapter 是转换 Adaptee 接口实现为 Target 接口实现的适配器
type adapter struct {
  Adaptee
}

// Request 实现 Target 接口
func (a *adapter) Request() string {
  return a.SpecificRequest()
}

func NewAdapter(adaptee Adaptee) Target {
  return &adapter{adaptee}
}
```

客户端代码直接通过适配器来间接使用被适配对象的功能，解决了两者不兼容的问题：

```go
import "testing"

var expect = "adaptee method"

func TestAdapter(t *testing.T) {
  adaptee := NewAdaptee()
  target := NewAdapter(adaptee)
  res := target.Request()
  if res != expect {
    t.Fatal("expect: %s, actual: %s", expect, res)
  }
}
```

#### 3.2 用适配器模式引入三方依赖

项目使用第三方类库的时候，为了方式未来有更换同等功能类库的可能，一般会使用适配器模式对第三方类库做一层封装。这样未来需要用同等功能的其他服务类进行替换时，实现一个新的适配器包装服务类即可，不要对已有的客户端代码进行更改。

下面是一个使用适配器适配 redigo 库为项目提供 Redis Cache 功能的例子：

* 首先定义适配器接口，未来所有的 Cache 类的适配器需要实现此接口（为了简洁只定义了三个简单的方法）：

```go
import "github.com/gomodule/redigo/redis"

// Cache 定义适配器实现类需要实现的接口
type Cache interface {
  Put(key string, value interface{})
  Get(key string) interface{}
  GetAll(keys []string) map[string]interface{}
} 
```

* 然后定义适配器实现类，RedisCache 类型会实现 Cache 接口，而且其封装了 redisgo 的功能来提供 Redis Cache 服务。RedisCache 的实现中使用了 Redis 连接池来避免重复的建立和释放 Redis 连接：
 
```go
// RedisCache 实现适配器接口
type RedisCache struct {
  conn *redis.Pool
}

func (rc *RedisCache) Put(key string, value interface{}) {
  if _, err := rc.conn.Get().Do("SET", key, value); err != nil {
    fmt.Println(err)
  }
}

func (rc *RedisCache) Get(key string) interface{} {
  value, err := redis.String(rc.conn.Get().Do("Get", key))
  if err != nil {
    fmt.Println(err)
    return ""
  }
  return value
}

func (rc *RedisCache) GetAll(keys []string) map[string]interface{} {
  intKeys := make([]interface{}, len(keys))
  for i, _ := range keys {
    intKeys[i] = keys[i]
  }
  
  c := rc.conn.Get()
  values, err := redis.Strings(c.Do("MGET", intKeys...))
  if err != nil {
    fmt.Println(err)
    return entries
  }
  
  entries := make(map[string]interface{})
  for i, k := range keys {
    entries[k] = values[i]
  }
  
  return entries
}

func NewRedisCache() Cache {
  cache := &RedisCache {
    conn: &redis.Pool{
      MaxIdle:     7,
      MaxActive:   30,
      IdleTimeout: 60 * time.Second,
      Dial: func() (redis.Conn, error) {
        conn, err := redis.Dial("tcp", "localhost:6379")
        if err != nil {
          fmt.Println(err)
          return nil, err
        }
        
        if _, err := conn.Do("SELECT", 0); err != nil {
          conn.Close()
          fmt.Println(err)
          return nil, err
        }
        
        return conn, nil
      },
    },
  }
  
  return cache
}
```

* 客户端在使用 Cache 的时候，直接用 Cache 接口中定义的方法即可，而 RedisCache 适配器会将客户端的调用转换为 redisgo 提供的功能：

```go
func main() {
  var rc Cache
  rc = NewRedisCache()
  rc.Put("Gogogo", "rub fish")
}
```

### 4. 总结

适配器模式的优点是适配器类和原服务类解耦，提高程序的扩展性。在很多业务场景中符合开闭原则：不改变原有接口，却还能使用新的服务提供的功能。

不过因为增加了适配器层，所以适配器模式也会增加系统的复杂性。

适配器模式和代理模式同属于结构型的设计模式，在类结构上也非常类似，都是由一个包装对象持有原有对象，然后把客户端对包装对象的请求转发到原对象上。不过它们也有一些区别：

* **适配器模式的特点在于兼容**：适配器对象与原对象（被适配对象）实现的是不同的接口，客户端通过适配器的接口完成跟自己不兼容的原对象的访问交互；
* **代理模式的特点在于隔离和控制**：代理与原对象（被代理对象）实现相同的接口，即便没有代理对象的存在，客户端也能与原对象直接进行交互访问。但是代理可以在调用原始对象接口的前后做一些额外的辅助工作，AOP 编程的实现也是利用这个原理。

