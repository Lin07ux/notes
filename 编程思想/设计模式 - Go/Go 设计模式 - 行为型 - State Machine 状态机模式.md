> 转摘：[Go设计模式实战--用状态模式实现系统工作流和状态机](https://mp.weixin.qq.com/s/X9dKNO6sd-OY2VfsZpaElA)

### 1. 介绍

状态机模式(State Machine Pattern)也叫做状态模式，允许对象的内部状态方法是改变时，改变其自身的行为，看起来就好像对象修改了它实例化的类。

状态机是一种行为型的模式，用于解决系统中复杂对象的状态转换以及不同状态下行为的封装问题。

当系统中某个对象存在多个状态，这些状态之间可以进行转换，而且对象在不同状态下有不同的行为时，就可以使用状态机模式，把特定于状态的状态的代码逻辑抽象到一组独立的状态类中，避免过多的状态条件判断，减少维护成本。

### 2. 组成

状态模式的结构如下面的 UML 图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676737142)

状态模式的十分简单清晰，主要包含三种角色：

* Context 环境类，也被称为上下文类。它实现客户端需要的接口，内部维护一个当前状态实例，并负责具体状态的切换；
* State 抽象状态接口，定义每种状态的行为，可以有一个或多个行为；
* ConcreteState 具体状态类，每一个具体状态类对应一个具体状态，不同的状态类的行为实现不同。

### 3. 示例

日常生活中，常见的拥有状态机的业务场景有：

* OA 系统的考勤请假审批，每个环节中审批的状态不一样时，允许进行的操作也不一样；
* 马路上的红绿灯，在不通过状态下拍到路上行驶的汽车和检测到车的行驶速度时也会有不同的行为。

下面用状态机模式实现红绿灯在不同状态下所具有的行为。

首先定义红绿灯的状态接口，每种灯状态下都有亮灯、变灯、测速的行为：

```go
// State interface
type LightState interface {
  // 亮起当前状态的交通灯
  Light()
  // 转换到新状态的时候
  EnterState()
  // 设置下一个要转变的状态
  NextLight(light *TrafficLight)
  // 检测车速
  CarPassingSpeed(*TrafficLight, int, string)
}
```

然后定义环境类 Context，由它提供客户端调用状态行为的能力：

```go
// Context
type TrafficLight struct {
  State LightState
  SpeedLimit int
}

func NewSimpleTrafficLight(speedLimit int) *TrafficLight {
  return &TrafficLight{
    SpeedLimit: speedLimit,
    State: NewRedState(),
  }
}

func (tl *TrafficLight) TransitionState(newState LightState) {
  tl.State = newState
  tl.State.EnterState()
}
```

接下来在实现具体的状态类之前，可以先定义一个默认的状态类型，用它提供一些公用的状态行为，并嵌入到其他的状态类型中，以避免具体的 LightState 实现类中重复实现：

```go
type defaultLightState struct {
  StateName string
}

func (state *defaultLightState) CarPassingSpeed(road *TrafficLight, speed int, licensePlate string) {
  if speed > road.SpeedLimit {
    fmt.Printf("Car with license %s was speeding\n", licensePlate)
  }
}

func (state *defaultLightState) EnterState() {
  fmt.Println("changed state to:", state.StateName)
}
```

在默认的状态类型中，并没有实现全部的 LightState 接口中的方法，这样就不算一个 LightState 实现了。而且这个默认类型只是实现通用方法的默认版本，具体的类型可以对其进行覆盖重写。

然后来定义三个具体的状态类型。首先是红灯状态：

```go
// 红灯状态
type redState struct {
  defaultLightState
}

func NewRedState() *redState {
  state := &redState{}
  state.StateName = "RED"
  return state
}

func (state *redState) Light() {
  fmt.Println("红灯亮起，不可通行")
}

func (state *redState) CarPassingSpeed(light *TrafficLight, speed int, licensePlate string) {
  // 红灯亮起时不可通行，所以这里重写了 defaultLightState 中的方法
  if speed > 0 {
    fmt.Printf("Car with license %s ran a red light!\n", licensePlate)
  }
}

func (state *redState) NextLight(light *TrafficLight) {
  light.TransitionState(NewGreenState())
}
```

由于红灯亮起时不能通行，所以这里要重写默认状态中的通行检测方法，

然后是绿灯和黄灯状态：

```go
// 绿灯状态
type greenState struct {
  defaultLightState
}

func NewGreenState() *greenState {
  state := &greenState{}
  state.State = "GREEN"
  return state
}

func (state *greenState) Light() {
  fmt.Println("绿灯亮起，请形式")
}

func (state *greenState) NextLight(light *TrafficLight) {
  light.TransitionState(NewAmberState())
}

// 黄灯状态
type amberState struct {
  state := &amberState{}
  state.StateName = "AMBER"
  return state
}

func (state *amberState) Light() {
  fmt.Println("黄灯亮起，请注意")
}

func (state *amberState) NextLight(light *TrafficLight) {
  light.TransitionState(NewRedState())
}
```

通过上面的代码可以看到，状态实现类在内部确定了状态可以转换的下个状态，这样就把系统流程的状态机留在了内部，避免让客户端代码再去做状态链的初始化和转换判断，符合噶哦内聚的设计原则，解放了客户端。

使用代码如下：

```go
func main() {
  trafficLight := NewSimpleTrafficLight(500)
  interval := time.NewTicker(5 * time.Second)
  
  for {
    select {
    case <- interval.C
      trafficLight.State.Light()
      trafficLight.State.CarPassingSpeed(trafficLight, 25, "CN1024")
      trafficLight.State.NextLight(trafficLight)
    default:
    }
  }
}
```

执行后就能在终端中看到几个灯的状态会循环切换。

### 4. 总结

状态机模式适用的场景如下：

* 如果对象需要根据自身当前状态进行不同行为，同时状态的数量非常多，且与状态相关的代码会频繁变更的话，就可以使用状态机模式。

    状态机模式将所有特定于状态的代码逻辑抽取到一组独立的类中，这样可以在独立于其他状态的情况下添加新状态或者修改已有状态，减少维护成本。
    
* 如果某个类需要根据成员变量的当前值改变自身行为，从而需要使用大量的条件语句时，可以用该模式。

    状态模式会将这些条件语句的分支抽取到相应状态类的方法中，通过业务逻辑内聚减少客户端类的工作。
    
* 当相似状态和基于条件的状态机转换中存在许多重复代码时，可以使用此模式。

    状态模式能够生成状态类层次结构，通过将公用代码抽取到抽象基类中减少重复。
    
状态机模式的缺点如下：

* 状态模式的使用必然会增加系统中类和对象的个数，导致系统运行开销增大；
* 状态模式的结构和实现都较为复杂，如果使用不当会导致程序结构和代码的混乱，增加系统设计的难度；
* 状态模式对“开闭原则”的支持并不太好，增加新的状态类需要修改那些负责状态转换的代码，否则无法转换到新增状态；而且修改某个状态类的行为也需要修改对应类的源码。



