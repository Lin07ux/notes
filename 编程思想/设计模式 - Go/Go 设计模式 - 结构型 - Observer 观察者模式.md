> 转摘：[拒绝Go代码臃肿，其实在这几块可以用下观察者模式](https://mp.weixin.qq.com/s/4NqjkXVqFPamEc_QsyRipA)

### 1. 简介

观察者模式（Observer Pattern）定义了对象间的一种一对多的依赖关系，使得每当一个对象状态发生变化时，其相关依赖对象皆得到通知。依赖对象在收到通知后，可自行调用自身的处理程序，实现想要做的事情，比如更新自己的状态。

观察者模式中，发布者对观察者唯一了解的是它必然实现了某个借楼（观察者接口）。这种松散耦合的设计最大限度地减少了对象之间的相互依赖，因此能够构建出灵活的系统。

观察者模式也经常被叫做发布-订阅模式，主要是用来实现事件驱动编程，其中发布变更的主体是一对多关系中的“一”，订阅变更通知的订阅者对象是“多”。

发布者发布的状态变更信息会被包装到一个对象中，这个对象被称为事件，比如用户注册完成时发布一个`UserCreated`事件。事件发布给订阅者的过程就是遍历订阅者，并依次调用订阅者实现的观察者接口方法，方法的参数一般就是当前的事件对象。而事件的助力可以是异步的也可以是同步的，具体根据需求来定。

观察者模式因为在事件发布者和订阅者之间做了中转，所以效率上一般并没有提升，更多的是为了实现程序结构上的解耦，以方便后续的代码维护。

### 2. 组成

最简单的观察者模式就是由事件发布者自身定义并实现订阅和通知接口，订阅者可以随时调用发布者的订阅接口告诉发布者在特定时候需要通知它。而事件发布者在特定的条件下会遍历所有的订阅者，向其通知事件的发生。

在事件较多、订阅较复杂的情况下，可以将订阅和触发操作单独提取到一个独立的事件总线上，由事件总线来完成订阅操作，发布者调用总线的触发接口来触发特定事件。

这两种方式虽然结构上有所区别，但是核心逻辑是一致的。

### 3. 示例

**最简单的观察者模式**

```go
package main

import "fmt"

// Subject 接口，相当于是发布者的定义
type Subject interface {
  Subscribe(observer Observer)
  Notify(msg string)
}

// Observer 观察者接口
type Observer interface {
  Update(msg string)
}

// Subject 实现
type SubjectImpl struct {
  observers []Observer
}

// Subscribe 添加观察者（订阅者）
func (sub *SubjectImpl) Subscribe(observer Observer) {
  sub.observers = append(sub.observers, observer)
}

// Notify 发布事件通知
func (sub *SubjectImpl) Notify(msg string) {
  for _, o := range sub.observers {
    o.Update(msg)
  }
}

// Observer 实现
type Observer1 struct{}

// Update 实现观察者接口
func (Observer1) Update(msg string) {
  fmt.Printf("Observer1: %s\n", msg)
}

type Observer2 struct{}

func (Observer2) Update(msg string) {
  fmt.Printf("Observer2: %s\n", msg)
}

func main() {
  sub := &SubjectImpl{}
  sub.Subscribe(&Observer1{})
  sub.Subscribe(&Observer2{})
  sub.Notify("Hello")
}
```

**事件总线时观察者模式**

> 下面这个事件总线实现支持异步不阻塞和任意参数值，代码来自：[https://lailin.xyz/post/observer.html](https://lailin.xyz/post/observer.html)。

```go
package eventbus

import (
  "fmt"
  "reflect"
  "sync"
)

type Bus interface {
  Subscribe(topic string, handler interface{}) error
  Publish(topic string, args ...interface{})
}

// AsyncEventBus 异步事件总线
type AsyncEventBus struct {
  handlers map[string][]reflect.Value
  lock     sync.Mutex
}

func NewAsyncEventBus() *AsyncEvents {
  return &AsyncEventBus{
    handlers: map[string][]reflect.Values{},
    lock:     sync.Mutext{},
  }
}

// Subscribe 订阅事件
func (bus *AsyncEventBus) Subscribe(topic string, f interface{}) error {
  bus.lock.Lock()
  defer bus.lock.Unlock()
  
  v := reflect.ValueOf(f)
  if v.Type().Kind() != reflect.Func {
    return fmt.Errorf("handler is not a function")
  }
  
  handlers, ok := bus.handlers[topic]
  if !ok {
    handlers = []reflect.Value{}
  }
  handlers = append(handlers, v)
  bus.handlers[topic] = handlers
  
  return nil
}

// Publish 发布事件，异步执行，不会等待返回结果
func (bus *AsyncEventBus) Publish(topic string, args ...interface{}) {
  handlers, ok := bus.handlers[topic]
  if !ok {
    fmt.Println("not found handlers in topic: ", topic)
    return
  }
  
  params := make([]reflect.Vaue, len(args))
  for i, arg := range args {
    params[i] = reflect.ValueOf(arg)
  }
  
  for i := range handlers {
    go handlers[i].Call(params)
  }
}
```

对应的单测如下：

```go
package eventbus

import (
  "fmt"
  "testing"
  "time"
)

func sub1(msg1, msg2 string) {
  time.Sleep(1 * time.Microsecond)
  fmt.Printf("sub1, %s %s\n", msg1, msg2)
}

func sub2(msg1, msg2 string) {
  fmt.Printf("sub2, %s %s\n", msg1, msg2)
}

func TestAsyncEventBus_Publish(t *testing.T) {
  bus := NewAsyncEventBus()
  bus.Subscribe("topic:1", sub1)
  bus.Subscribe("topic:2", sub2)
  bus.Publish("topic:1", "test1", "test2")
  bus.Publish("topic:2", "testA", "testB")
  time.Sleep(1 * time.Second)
}
```

### 4. 总结

观察者模式对于代码的解耦有很好的效果，方便后续的扩展和维护。不过这对于代码的追踪并不直观，需要对整体的流程比较了解。

观察者模式的实现上很简单，而且很多框架中都会内置该模式来提供事件机制，比较常用。