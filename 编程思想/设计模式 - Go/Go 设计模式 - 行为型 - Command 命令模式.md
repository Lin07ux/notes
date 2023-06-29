> 转摘： [Go设计模式--命令模式](https://mp.weixin.qq.com/s/n1R1fnRZuDwlaQqsDh5y3g?forceh5=1)

### 1. 简介

命令模式是一种行为型模式，在 DDD 风格的框架中使用很频繁。

命令模式通过将请求封装成一个独立的对象（即命令对象），来解耦请求的调用者和接受者，使它们不直接交互。在命令对象中会包含请求相关的全部信息，每一个命令都是一个操作的全部流程：请求方发出请求执行操作、接收方收到请求并执行操作。

### 2. 组成

命令模式中有如下必须要存在的基础组件：

* Receiver：命令接收方，也就是请求的最终处理者，是唯一包含业务逻辑的类。命令对象会将请求传递给它；
* Command：命令对象，其中组装了一个 Receiver 成员，其`execute`方法中实现了对 Receiver 的一个特定行为的调用；
* Invoker：请求发起者，组装了 Command 成员，通过调用 Command 实例的`execute`方法来触发对应的请求操作；
* Client：通过将 Receiver 实例和请求信息传递给 Command 构造器来创建 Command 对象并将其与 Invoker 绑定。

其 UML 类图如下所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/af1401108705ecd9e1f76475ff3665d9.jpg)

图中各个类的特性和具有的行为解释如下：

* Invoker 发送者：负责对请求进行初始化，其中必须包含一个成员变量来存储对命令对象的引用。发送者触发命令，由命令对象来完成请求的执行，而不是直接向接受者发送请求。发送者并不负责创建命令对象，而是由客户端负责调用构建函数创建命令对象。而且，发送者可以绑定一个或多个命令，表示它能支持多种请求的执行。
* Command 命令接口：通常接口中仅声明一个执行命令的方法`Execute()`。
* Concrete Command 具体命令：实现各种类型的请求。命令对象自身并不完成请求的执行，而是将请求委派给一个接收者对象，由接收者完成请求对应的业务逻辑。接收者所需要的参数可以声明为具体命令的成员变量。一般会约定命令对象为不可变对象，仅允许通过构造函数来对命令对象的成员变量进行初始化。
* Receiver 接收者：接收者用来处理业务逻辑，完成对请求的操作。命令对象只负责处理如何将请求传递到接收者的细节，接收者会完成实际的工作。几乎任何对象都可以作为接收者，根据场景的复杂度也可以将其进一步抽象出接收和实现类。
* Client 客户端：创建并配置具体命令对象。客户端必须将包括接收者对象在内的所有请求参数传递给命令对象的构造函数，完成命令与执行操作的接收者的关联。

发送者是通常能接触到的终端，比如电视的遥控器，点击音量按钮发送加音量的命令，电视机里的芯片（就是接收者）负责完成音量的增加处理。

### 3. 示例

PS5 能够完成一些指令操作，最简单的实现方式如下：

```go
type CPU struct{}

func (CPU) ADoSomething() {
  fmt.Println("a do something")
}

func (CPU) BDoSomething() {
  fmt.Println("b do something")
}

type PS5 struct {
  cpu CPU
}

func (p PS5) ACommand() {
  p.cpu.ADoSomething()
}

func (p PS5) BCommand() {
  p.cpu.BDoSomething()
}

func main() {
  cpu := CPU{}
  ps5 := PS5{cpu}
  ps5.ACommand()
  ps5.BCommand()
}
```

在后续的更新迭代中，可能会给 CPU 增加其他的命令操作，以及需要支持命令宏（即命令组合操作）。如果每次新增的功能都要修改 PS5 的定义，显然不符合面向对象的开闭原则设计理念。

通过命令模式，把 PS5 抽象成命令发送者，把 CPU 对象作为执行业务逻辑的命令接收者，然后引入 Command 接口把两者做解耦，即可满足开闭原则。

使用命令模式实现的 PS5 的功能代码如下：

```go
// 命令接收者，负责业务逻辑的执行
type CPU struct{}

func (CPU) ADoSomething(param int) {
  fmt.Printf("a do something with param %v\n", param)
}

func (CPU) BDoSomething(param1 string, param2 int) {
  fmt.Printf("b do something with params %v and %v\n", param1, param2)
}

func (CPU) CDoSomething() {
  fmt.Println("c do something with no params")
}

// 命令接口，声明一个执行命令的方法
type Command interface {
  Execute()
}

// A 命令
type ACommand struct {
  cpu   *CPU
  param int
}

// 命令不执行业务逻辑，而是委托给接收者进行执行，可以定义为执行多个接收者操作的命令宏
func (a ACommand) Execute() {
  a.cpu.ADoSomething(a.param)
  a.cpu.CDoSomething()
}

func NewACommand(cpu *CPU, param int) Command {
  return ACommand{cpu, param}
}

// B 命令
type BCommand struct {
  state  bool // Command 中可以添加一些状态用作逻辑判断
  cpu    *CPU
  param1 string
  param2 int
}

func (b BCommand) Execute() {
  if b.state {
    return
  }
  b.cpu.BDoSomething(b.param1, b.param2)
  b.state = true
  b.cpu.CDoSomething()
}

func NewBCommand(cpu *CPU, param1 string, param2 int) Command {
  return BCommand{false, cpu, param1, param2}
}

// 命令接收者
type PS5 struct {
  commands map[string]Command // 可以支持发起多种命令
}

// SetCommand 赋予 PS5 可执行的命令，扩展其能力
func (p *PS5) SetCommand(name string, command Command) {
  p.commands[name] = command
}

// DoCommand 执行命令（未做命令是否存在的判断）
func (p *PS5) DoCommand(name string) {
  p.commands[name].Execute()
}

func main() {
  cpu := CPU{}
  ps5 := PS5{make(make[string]Command)}

  ps5.SetCommand("a", NewACommand{&cpu, 1})
  ps5.SetCommand("b", NewBCommand{&cpu, "hello", 2})
  
  ps5.DoCommand("a")
  ps5.DoCommand("b")
}
```

### 4. 总结

命令模式的核心是将接收者执行业务逻辑的功能进行封装，作为一个整体提供给发起者调用，从而使发起者和接收解耦，能够方便的对业务逻辑进行修改和升级。

命令模式的优点：

1. 通过引入中间件（抽象命令接口），解耦了命令请求与实现；
2. 扩展性良好，可以很容易地增加新命令（功能）；
3. 支持组合命令和命令队列；
4. 可以在现有命令的基础上增加额外功能，如日志记录等。

命令模式的缺点：

1. 具体命令类可能会过多，特别是业务功能比较多的情况下；
2. 命令模式的结果其实就是接收方的执行结果，但是为了以命令的形式进行架构、解耦请求与实现，引入了额外类型结构，增加了理解上的困难。