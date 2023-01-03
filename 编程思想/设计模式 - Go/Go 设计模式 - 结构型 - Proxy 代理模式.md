> 转摘：[Go学设计模式--怕把核心代码改乱，记得用代理模式](https://mp.weixin.qq.com/s/FTXkgxkUzsHMIspCK60G4w)

### 1. 介绍

代理模式中，代理控制着对原对象的访问，并允许在将请求提交给原对象的前后进行一些处理，从而增强原对象的逻辑处理。

其中：代理者一般叫做代理对象或者直接嘉做代理 Proxy，进行逻辑处理的原对象通常被称作服务对象。而且，**代理要跟服务对象实现相同的接口**，才能让客户端无感其使用的是带还是真正的服务对象。

所谓的逻辑增强处理，就是在核心功能之外，增加一些其他的功能。比如，处理客户端查询用户订单信息的 API Hanlder 就是核心处理逻辑，而增强逻辑就是在查询订单信息之前验证请求是否是有效的、记录请求的参数和返回的响应数据等。

通常情况下，**代理会对其服务对象的整个生命周期进行管理，来增强服务对象**，这样，与核心业务逻辑不相关的增强逻辑就可以由代理来实现。

### 2. 组成

代理模式中的角色一共有四种角色：客户端、服务接口、服务类和代理类。它们之间的关系用 UML 类图表示如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672654142)

这四个角色在代理模式中的职责分别为：

* 服务接口 Service Interface：声明了服务类要实现的接口。服务类的业务处理逻辑就是实现在这里定义的接口方法中，而且代理类也必须遵循该接口；

* 服务类 Service：提供核心业务逻辑的原对象；

* 代理 Proxy：包含一个服务对象作为成员变量。代理在完成其增强功能（例如延迟初始化、记录日志、访问控制和缓存等）前后会将请求传递给服务对象。

* 客户端 Client：通过统一的服务接口与服务类或代理进行交互。因为服务类和代理实现了相同的接口，所以客户端不会感知到交互对象的区别。

### 3. 示例

假设有个代表小汽车的 Car 类型，而小汽车的主要行为就是可以驾驶，所以 Car 需要实现一个代表驾驶行为的接口：Vehicle interface。

```go
type Vehicle interface {
  Drive()
}

type Car struct{}

func (c *Car) Drive() {
  fmt.Println("Car is being drive")
}
```

在实际生活中，驾驶车辆会有年龄、驾照等相关的限制的，也就是说在调用`car.Drive()`之前，需要做一些验证，验证通过才能执行`Drive()`。

如果直接为`Car`结构添加`age`字段做年龄验证，那必然是不合适的，因为`age`是驾驶员的属性，而且每个地区驾驶年龄的限制也不同。

所以，更通畅的做法是，加一个表示驾驶员的类型`Driver`，并增加一个包装 Driver 和 Vehicle 类型的包装类型：

```go
type Driver struct {
  Age int
}

type CarPorxy struct {
  vehicle Vehicle
  dirver  *Driver
}

func NewCarProxy(driver *Driver) *CarProxy {
  return &CarProxy{&Car{}, driver}
}

func (c *CarProxy) Driver() {
  if c.dirver.Age >= 16 {
    c.vehicle.Driver()
  } else {
    fmt.Println("Driver too young!")
  }
}
```

这就是代理模式：通过 CarProxy 扩充了车辆驾驶时的验证逻辑，而不影响车辆原本的驾驶核心逻辑。

此时，就可以通过如下的方式调用 CarProxy 类型的 Drive 方法，和直接调用真实的 Car 实例一样：

```go
func main() {
  car := NewCarProxy(&Driver{12})
  car.Drive() // Driver too young!
  
  car2 := NewCarProxy(&Driver{22})
  car2.Drive() // Car is being drive
}
```

### 4. 延伸

在代理模式中，通过让代理类实现跟服务类相同的接口，从而把代理类伪装成了服务类，客户端请求代理时，代理再把请求委派给其持有的真实服务类对象，在委派的过程中就可以添加增强逻辑。

如果把代理类当成服务对象再给代理类添加代理，那么就变成了另外一种设计模式——装饰器模式。装饰器模式本身就是代理模式的一种特殊应用。

