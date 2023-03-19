> 转摘：[外观模式，一个每天都在用，却被多数人在面试中忽视的模式](https://mp.weixin.qq.com/s/tC9xfZFJvcNEbTXHQYvT6w)

### 1. 介绍

现代的软件系统都非常复杂，尽管采用各种方法将其分而治之，将一个大系统拆分为多个较小的子系统，但是仍然可能会存在这样的问题：子系统内有非常多的类，客户端要完成一个功能往往需要和许多对象打交道。这时候就可以考虑使用外观模式来屏蔽具体的实现方式，简化客户端的调用。

外观模式又称为门面模式(Facade Pattern)，是一种结构型模式。引入外观模式后调用方与多个子系统的通信必须通过一个统一的外观对象进行。

外观模式定义了一个高层接口，这个接口为子系统中的功能接口提供了一个一致的界面，使得这些子系统更加容易使用。

可以简单理解外观模式为一个中间层，其封装了复杂的调用逻辑，而对外提供简单一致的接口。外观对象本身可以认为是一个代理，它将客户端的请求交给对应的子系统进行执行。

Java 中著名的 Slf4j(Simple Logging Facade For Java) 就是一个 Facade 模式的日志库，它提供了一个记录日志的抽象层，由 Slf4j 来对接 Log4j、LogBack 等日志库。用户对日志的操作统一由 Slf4j 接入，但是底层具体用什么日志工具、如果进行记录日志则不需要用户关心，这样就可以方便地移植了。

![](https://cnd.qiniu.lin07ux.cn/markdown/1679238908)


### 2. 组成

外观模式的结构其实很简单，如下是其 UML 图：

![](https://cnd.qiniu.lin07ux.cn/markdown/1679238176)

### 3. 实例

一个电脑中，总是有 CPU、RAM 和硬盘子系统，调用方想启动电脑就要分别依次启动这三个子系统才行。所以在这些子系统上增加一个外观对象，让调用方直接调用外观对象，再由外观对象分别对接子系统最终完成电脑的启动。

```go
package main

import "fmt"

const (
  BOOT_ADDRESS = 0
  BOOT_SECTOR  = 0
  SECTOR_SIZE  = 0
)

type CPU struct{}

func (c *CPU) Freeze() {
  fmt.Println("CPU.Freeze()")
}

func (c *CPU) Jump(position int) {
  fmt.Println("CPU.Jump()")
}

func (c *CPU) Execute() {
  fmt.Println("CPU.Execute()")
}

type Memory struct{}

func (m *Memory) Load(position int, data []byte) {
  fmt.Println("Memory.Load()")
}

type HardDrive struct{}

func (hd *HardDrive) Read(lba int, size int) []byte {
  fmt.Println("HardDrive.Read()")
  return make([]byte, 0)
}

type ComputerFacade() struct {
  processor *CPU
  ram       *Memory
  hd        *HardDrive
}

func newComputerFacade() *ComputerFacade {
  return &ComputerFacade{new(CPU), new(Memorr), new(HardDrive)}
}

func (c *ComputerFacade) start() {
  c.processor.Freeze()
  c.ram.Load(BOOT_ADDRESS, c.hd.Read(BOOT_SECTOR, SECTOR_SIZE))
  c.processor.Jump(BOOT_ADDRESS)
  c.processor.Execute()
}

func main() {
  computer := NewComputerFacade()
  computer.start()
}
```

### 4. 总结

外观模式的优点：

* 简化了调用过程，不用深入了解子系统，以防给子系统带来风险；
* 减少系统依赖，松散耦合；
* 更好地访问层次，提高；额安全性；
* 遵循迪米特法则（最少知识原则，一个类对其他类知道的越少越好）。

外观模式的缺点：

* 不符合开闭原则：增加子系统和扩展子系统的行为时，需要对外观对象进行修改，可能容易带来未知风险。