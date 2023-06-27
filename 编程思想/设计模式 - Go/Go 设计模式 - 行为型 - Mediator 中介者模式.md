> 转摘：[Go设计模式--中介者，最后的模式！](https://mp.weixin.qq.com/s/91-XUm5Gn9oQLd_F4dLb3A)

### 1. 介绍

中介者模式又叫做调解者模式，是一种行为型设计模式，能让程序减少对象之间混乱无序的依赖关系。该模式会限制对象之间的直接交互，迫使它们都通过一个中介者对象进行交互。

![](https://cnd.qiniu.lin07ux.cn/markdown/048cc7a26369430481569703b39b0add.jpg)

中介者模式使修改、扩展和重用单个组件变得更容易，因为组件之间不再相互依赖。

中介者模式与观察者模式挺像，但是两者还是有一些区别，使用场景也不同。

### 2. 组成

中介者模式的核心是一个中介者对象 Mediator，其他的组件对象都与该中介者对象发生关系，类似于一个中心集线器。

![](https://cnd.qiniu.lin07ux.cn/markdown/8791d1baa3a668a09a13458a9f1c5f1f.jpg)

Component 组件实现类中需要持有指向中介者的引用，而且中介者中也要能够保持对各个组件对象（需要的情况下）的引用。

实现中介者模式的步骤如下：

1. 定义一组会相互调用、拥有强耦合的中介者和组件接口；
2. 指定中介者接口以及中介者和各个组件之间的通信方式（方法）。在大多数情况下中介者接口中必须拥有一个`Notify/Notification`方法以从组件中接收通知；
3. 创建具体中介者实现，其会存储所管理的全部 Component 对象的引用；
4. 创建组件实现，组件对象应保存对中介者（实现或接口）的引用；
5. 将组件对象调用其他组件对象的方法提炼到中介者中，组件对象调用中介者的通知方法，由中介者再去调用对应的组件的方法，从而完成组件之间的解耦。

### 3. 示例

在现实生活中，机场的控制塔是一个典型的中介者角色，飞机在起飞和降落前都会向控制塔发出问询，控制塔给飞机发送指令以协调它们的起飞降落次序，避免造成事故。

假设机场只有一条跑道，所以同一时刻只能承载一架飞机的起飞或降落，而且飞机直接不能直接沟通，必须由控制塔作为一个中介者向各架飞机同步跑道的可用状态。

先定义飞机（组件）和指挥塔（中介者）的 Interface 接口：

```go
// 中介者 - 机场指挥塔接口
type mediator interface {
  canLanding(airplane airplane) bool
  notifyAboutDeparture()
}

// 组件 - 飞机接口
type airplane interface {
  landing()
  takeOff()
  permitLanding()
}
```

然后再实现具体的组件：一架波音飞机和一架空客飞机。每架飞机在降落`landing()`方法中都需要跟作为中介者的指挥塔发出问询，确定能否降落。如果跑到当前正在背使用，就需要等待指挥塔调用它自己的`permitLanding()`通知可以降落后再进行降落。而其他占用跑到的飞机在起飞后会通过中介者提供的`notifyAboutDeparture()`告知指挥塔自己已经离去。

```go
// 组件 1 - 波音飞机
type boeingPlane struct {
  mediator mediator
}

func (b *boeingPlane) landing() {
  if !b.mediator.canLanding(b) {
    fmt.Println("Airplane Boeing: 飞机跑道正在被占用，无法降落！")
    return
  }
  fmt.Println("Airplane Boeing: 已经成功降落！")
}

func (b *boeingPlane) takeOff() {
  fmt.Println("Airplane Boeing: 正在起飞离开跑道！")
  b.mediator.notifyAboutDeparture()
}

func (b *boeingPlane) permitLanding() {
  fmt.Println("Airplane Boeing: 收到指挥塔信号，允许降落，正在降落！")
  b.landing()
}

// 组件 2 - 空客飞机
type airBusPlane struct {
  mediator mediator
}

func (a *airBusPlane) landing() {
  if !a.mediator.canLanding(a) {
    fmt.Println("Airplane AirBus: 飞机跑道正在被占用，无法降落！")
    return
  }
  fmt.Println("Airplane AirBus: 已经成功降落！")
}

func (a *airBusPlane) takeOff() {
  fmt.Println("Airplane AirBus: 正在起飞离开跑道！")
  a.mediator.notifyAboutDeparture()
}

func (a *airBusPlane) permitLanding() {
  fmt.Println("Airplane AirBus: 收到指挥塔信号，允许降落，正在降落！")
  a.landing()
}
```

作为指挥塔的中介者需要提供两个方法给组件调用：

* `canLanding()` 提供给需要降落的飞机组件问询是否可以降落的方法，如果当前不能降落，则将该飞机加入到中介者的等待队列中，在后续跑道空闲后会进行通知；
* `notifyAboutDeparture()` 提供给占用跑道的飞机通知指挥塔已起飞，指挥塔会向排队降落的飞机中的首位发送降落指令（通过飞机组件的`permitLanding()`方法）。

```go
// 中介者实现 - 指挥塔
tyoe manageTower struct {
  isRunwayFree bool
  airportQueue []airplane
}

func (mt *manageTower) canLanding(airplane airplane) bool {
  // 跑道空闲，允许降落，同时把状态变为忙碌
  if mt.isRunwayFree {
    mt.isRunwayFree = false
    return true
  }
  
  // 跑道忙碌，把飞机加入等待通知降落的队伍
  mt.airportQueue = append(mt.airportQueue, airplane)
  return false
}

func (mt *manageTower) notifyAbountDeparture() {
  if !mt.isRunwayFree {
    mt.isRunwayFree = true
  }
  if len(mt.airportQueue) > 0 {
    firstPlaneInWaitingQueue := tower.airplaneQueue[0]
    mt.airportQueue = mt.airportQueue[1:]
    firstPlaneInWaitingQueue.permitLanding()
  }
}

func newManageTower() *manageTower {
  return &manageTower{
    isRunwayFree: true,
  }
}
```

> 修改跑道的空闲状态时未考虑并发问题。另外，一般情况下，飞机在降落完成后应通知指挥塔将跑道改为空闲状态。

这样就可以通过指挥塔来协调多个飞机使用机场跑道进行有序的起飞和降落了。

```go
func main() {
  tower := newManageTower()
  boeing := &boeingPlane{
    mediator: tower,
  }
  airbus := &airBusPlane{
    mediator: tower,
  }
  boeing.landing()
  airbus.landing()
  boeing.takeOff()
}
```

执行程序后，会有类似下面的输出：

```text
Airplane Boeing: 已经成功降落！
Airplane AirBus: 飞机跑道正在被占用，无法降落！
Airplane Boeing: 正在起飞离开跑道！
Airplane AirBus: 收到指挥塔信号，允许降落，正在降落！
Airplane AirBus: 已经成功降落！
```

### 4. 总结

中介者模式用一个中介者对象封装一系列对象交互，使各个对象之间不需要显式地相互作用，从而使其耦合松散，而且可以独立地改变它们之间的交互。

中介者模式主要适用于以下场景：

* 系统中对象之间存在复杂的引用关系，产生的相互依赖关系结构混乱且难以理解；
* 交互的公共行为，如果需要改变行为，则可以增加新的中介者类。

其优点如下：

* 减少类之间的依赖，将多对多依赖转换为一对多依赖，降低了类之间的耦合度；
* 各个类各司其职，符合迪米特法则。

缺点如下：

* 中介者模式将原本多个对象直接的相互依赖变成了中介者和多个组件类的依赖关系；
* 当组件类越多时，中介者就会越臃肿，变得复杂且难以维护。

中介者模式和观察者模式在结构上很相似：观察者模式中的`EventDispatcher`和中介者模式中的`Mediator`的作用看起来很像，都是把多个组件之间的关系维护到自身，实现组件间的间接通信而达到解耦效果。不过这两者在使用场景或者要解决的问题上还是有些差别的：

* 观察者模式：组件间的沟通是单向的，从被观察者（发送事件的实体）到观察者（监听器）单向传递。一个参与者要么是观察者，要么是被观察者，不会同时兼具两种身份。
* 中介者模式：参与者之间可以相互沟通，而且相互之间可能会互相依赖对方的行为。当参与者之间关系复杂、维护成本很高时可以考虑使用中介者模式。