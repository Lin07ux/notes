> 转摘：[Go学设计模式--装饰器和职责链，哪个模式实现中间件更科学？](https://mp.weixin.qq.com/s/_e9Qa97gZvgv9n-pFB4lFw)

### 1. 介绍

装饰器模式(Decorator Pattern)也叫做包装器模式(Wrapper Pattern)，指在不改变原有对象的基础上，动态的给一个对象添加一些额外的职责。

给对象添加新行为最简单直观的方式就是扩展本地推向，通过继承达到目的，但是继承有如下两个弊端：

1. 继承是静态的，在编译期间就已经确定，无法在运行时改变对象的行为；
2. 子类只能有一个父类，当需要添加的新功能太多时，容易导致类的数量剧增。

就增加功能来说，装饰器模式相比生成子类更为灵活。装饰器模式能够将现有对象放置在实现了一套相同接口的包装器对象中来动态的向现有对象添加新行为。在包装器中进行代码的扩展，有助于重用功能并且不会修改现有对象的代码，符合“开闭原则”。

装饰器模式需要将原有对象放置到包装器对象中，这里被放置在包装器对象的“现有对象”通常会被叫做“组件”(Component)，而用来包装组件的包装器对象就是常说的“装饰器”(Decorator)。因为装饰器会和组件实现相同的接口，所以客户端就无法识别两者的差异，也就不需要在增加装饰器时对客户端调用代码进行修改了。

可以看到，装饰器模式和代理模式在结构上几乎一样，可以将其看做是代理模式的一种特殊应用：装饰器模式的一个特点是可以嵌套多层装饰器，相当于给代理加代理。不过，这两种模式的使用目的并不一样：

* **代理模式强调的是对本体对象的访问控制**；
* **装饰器模式是用来对本体对象进行增强**。

### 2. 组成

用 UML 类图表示装饰器模式的结构如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/1673934628)

装饰器模式主要有如下几个组成角色：

1. 客户端：用一层或多层装饰器来封装组件，最后调用装饰好的包装器的方法，启动执行；
2. 组件接口：声明装饰器对象和被装饰的组件对象要实现的公用接口；
3. 组件实现：具体的组件实现类，用来完成业务功能；
4. 基础装饰类：拥有一个指向被封装对象的成员变量，并实现基本的一些操作方法；
5. 具体装饰类：组合基础装饰类的功能，并实现组件接口，除了实现自身的增强功能外，还要调用被装饰对象的组件接口方法。

这里的基础装饰类可以不存在，可以简化结构，去除之后与代理模式的结构就基本相同了。

### 3. 示例

下面演示用装饰器模式实现增强游戏主机的一个例子。

首先定义游戏主机的产品接口，和基础的产品实现类，就是上面类图中组件接口和组件实现：

```go
// PS5 产品接口
type PS5 interface {
  StartGPUEngine()
  GetPrice() int64
}

// CD 版 PS 主机
type PS5WithCD struct{}

func (p PS5WithCD) StartCPUEngine() {
  fmt.Println("start engine")
}

func (p PS5WitchCD) GetPrice() int64 {
  return 5000
}

// PS5 数字版主机
type PS5WithDigital struct{}

func (p PS5WithDigital) StartGPUEngine() {
  fmt.Println("start normal gpu engine")
}

func (p PS5WithDigital) GetPrice() int64 {
  return 3600
}
```

以这两款基础的产品类型为核心，厂商一般还会推出各种主体限定配色的主机、增加硬件配置的主机等。有了这些改变，主机的价格或行为就会发生一些改变。使用不同装饰器进行组合包装即可方便的实现对基础主机版本的扩展和增强，而避免生成不同的子类和对基础组件的更改：

```go
// Plus 版的装饰器
type PS5MachinePlus struct {
  ps5Machine PS5
}

func (p *PS5MachinePlus) SetPS5Machine(ps5 PS5) {
  p.ps5Machine = ps5
}

func (p *PS5MachinePlus) StartGPUEngine() {
  p.ps5Machine.StartGPUEngine()
  fmt.Println("start plus plugin")
}

func (p *PS5MachinePlus) GetPrice() int64 {
  return p.ps5Machine.GetPrice() + 500
}

// 主题色的装饰器
type PS5WithTopicColor struct {
  ps5Machine PS5
}

func (p *PS5WithTopicColor) SetPS5Machine(ps5 PS5) {
  p.ps5Machine = ps5
}

func (p *PS5WithTopicColor) StartGPUEngie() {
  p.ps5Machine.StartGPUEngine9)
  fmt.Println("尊贵的主题色主机 GPU 启动")
}

func (p *PS5WithTopicColor) GetPrice() int64 {
  return p.ps5Machine.GetPrice() + 200
}
```

有了组件和装饰器之后，就可以进行不同的组件和装饰器的组合来得到不同的增强功能。比如：

```go
func main() {
  ps5MachinePlus := PS5MachinePlus{}
  ps5MachinePlus.SetPS5Machine(PS5WithCD{})
  ps5MachinePlus.StartGPUEngine()
  price := ps5MachinePlus.GetPrice()
  fmt.Pricef("PS5 CD 豪华 Plus 版的价格为：%d 元\n\n", price)
  
  ps5WithTopicColor := PS5WithTopicColor{}
  ps5WithTopicColor.SetPS5Machine(ps5MachinePlus)
  ps5WithTopicColor.StartGPUEngine()
  price = ps5WithTopicColor.GetPrice()
  fmt.Printf("PS5 CD 豪华 Plus 经典主体配色版价格：%d 元\n", price)
}
```

### 4. 总结

装饰器模式是继承的有力补充，比继承灵活，能在不改变原有对象和调用逻辑的情况下，动态的给一个对象扩展功能，即插即用。而且能够组合不同的装饰类来实现不同的效果，完全遵循程序设计的开闭原则。

但装饰器模式的使用会给程序带来更高的复杂性和更低的可读性，而子类继承的代码结构会更直白易懂一些。而且，虽然装饰器模式符合开闭原则，但是会给程序带来更多的类，在进行多层装饰时也会更复杂。

所以总体上，使用装饰器模式的时候要进行权衡，合理的选择装饰器模式或子类继承方式。而且在使用的时候一定要记住：装饰器是为了增强某个事物，而不应该实现事物的主逻辑。

另外，装饰器模式和代理模式、职责链模式有一定的异同，需要进行分辨和选择使用哪个：

**装饰器模式 VS 代理模式**

* 装饰器模式就是代理模式的一个特殊应用
* 装饰器模式强调对本体功能的增强扩展
* 代理模式强调对本地对象代理过程中的控制

**装饰器模式 VS 职责链模式**

装饰器模式和职责链模式在行为上看都是多个单元进行组合完成逻辑处理，但是它们在使用目的上也有区别：

* 装饰器模式注重给某样东西添加扩展，得到一个增强的产品；
* 职责链模式更强调分步骤完成某个流程，更像是一个任务链表，而且职责链模式能随时终止。

比如，对 OA 系统请假审批这个场景，一般是需要逐步的向上级传递已被下级批准的申请，一旦中间有任何人拒绝了申请，改申请就要被中断掉，不能继续向上传递了，此时就适合使用职责链模式。如果使用装饰器模式就会造成已经被拒绝的申请依旧会被传递给每一级审批节点。

同样的，Web 框架的中间件也更适合职责链模式而非装饰器模式。

