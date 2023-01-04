> 转摘：[你也是业务开发？提前用这个设计模式预防产品加需求吧](https://mp.weixin.qq.com/s/zCh12E10JM24EGTyFS7hPQ)

### 1. 介绍

模板模式、策略模式和职责链模式是解决业务系统流程复杂多变这个痛点的利器。

职责链模式，Chain Of Responsibility，也被称为责任链模式。它的核心思想是为请求创建一条由多个处理器组成的链路，每个处理器各自负责自己的职责，相互之间没有耦合。在完成自己的处理逻辑后，就将请求对象传递到链路中的下一个处理器进行处理。

职责链模式在很多 Web 框架中都有被用到，比如中间件、拦截器等组件就是应用这种设计模式的。

在核心的业务逻辑中，应用职责链模式能够方便的扩展业务流程的步骤。

比如，对于电商的下单流程，初始情况流程可能就是很简单的：参数校验、库存校验、运费计算、扣库存、生成订单等。而随着后续的营销推广，可能会在购买流程中增加折扣计算、优惠券核销等流程。

在厘清各步骤的逻辑后，就可以使用责任链模式来定义下单逻辑，方便的进行扩展。

### 2. 组成

职责链模式中，每个步骤都应由一个处理对象来完成逻辑功能的抽象、处理，并在自身的逻辑处理完成后，把请求交给职责链后面的步骤。而串联整个职责链的对象则是代表这该请求的完整数据对象。

总结下来，实现责任链模式的对象最起码需要包含如下特性：

* 成员属性：

    - `nextHandler` 下一个等待被调用的对象实例；

* 成员方法：

    - `SetNext` 设置职责链中的下一个处理对象实例；
    - `Execute` 负责职责链上请求的处理和传递，它会串联起整个职责链的处理过程；
    - `Do` 当前处理对象的业务逻辑入口，是每个处理对象处理逻辑的核心。

由于一种职责链的处理过程中，每个职责实例只有最核心的`Do`方法是各异的，而`SetNext`和`Execute`方法的逻辑是相同的，可以将这两个方法的实现提取出来，为每个处理器共享，从而减少重复操作和实现。

所以，责任链模式的抽象关系如下面的 UML 图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672755903)

不过，由于 Go 语言不支持类继承，而是支持匿名组合嵌套，所以`SetNext`和`Execute`方法可以通过一个通用的类型来实现，然后嵌套到实际的处理类型中。

### 3. 示例

下面以病人去医院看病这个处理流程为例来实现职责链模式。

看病的具体流程如下：挂号 --> 诊室看病 --> 收费处缴费 --> 药房拿药。

使用职责链模式来实现该过程的各个步骤，可以做到相互之间不耦合，且支持后续继续向流程中增加步骤。

首先先实现职责链模式中的公共部分，也就是模式的接口和公共类型：

```go
type PatientHandler interface {
  SetNext(PatientHandler) PatientHandler
  Execute(*patient) error
  Do(*patient) error
}

// 充当抽象类型，实现公共方法
type Next struct {
  nextHandler PatientHandler
}

func (n *Next) SetNext(handler PatientHandler) PatientHandler {
  n.nextHandler = handler
  return handler
}

func (n *Next) Execute(patient *patient) (err error) {
  // 嵌套类型无法调用外部类型的 Do 方法，所以 Next 不能实现 Do 方法
  if n.nextHandler != nil {
    if err = n.nextHandler.Do(patient); err != nil {
      return
    }
    return n.nextHandler.Execute(patient)
  }
  return
}
```

这里的`Next`类型充当了抽象类的角色，它实现了职责链模式中的`SetNext`和`Execute`方法。而`Do`方法属于每个处理类型的核心逻辑，不应该由`Next`类型来实现。

并且，由于 Go 不支持继承，所以即使`Next`实现了`Do`方法，也不能达到在父类方法中调用子类方法的效果——即在上面的`Next.Execute`方法中，调用不到外部实现类型的`Do`方法。

所以这里`Next`类型不实现`Do`方法，也是暗示这个类型是专门用作实现类进行内嵌组合使用的。

在看病的过程中，串起整个流程的是病人，可以定义一个患者类作为流程的请求数据：

```go
type patient struct {
  Name              string
  RegistrationDone  bool
  DoctorCheckUpDone bool
  MedicineDone      bool
  PaymentDone       bool
}

// Reception 挂号处理器
type Reception struct {
  Next
}

func (r *Reception) Do(p *patient) (err error) {
  if p.RegistrationDone {
    fmt.Println("Patient registration already done")
    return
  }
  fmt.Println("Reception registering patient")
  p.RegistrationDone = true
  return
}

// Clinic 门诊处理器
type Clinic struct {
  Next
}

func (c *Clinic) Do(p *patient) (err error) {
  if p.DoctorCheckUpDone {
    fmt.Println("Doctor checkup already done")
    return
  }
  fmt.Println("Doctor checking patient")
  p.DoctorCheckUpDone = true
  return
}

// Cashier 收费处理器
type Cashier struct {
  Next
}

func (c *Cashier) Do(p *patient) (err error) {
  if p.PaymentDone {
    fmt.Println("Payment Done")
    return
  }
  fmt.Println("Cashier getting money from patient")
  p.PaymentDone = true
  return
}

// Pharmacy 药房处理器
type Pharmacy struct {
  Next
}

func (p *Pharmacy) Do(p *patient) (err error) {
  if p.MedicineDone {
    fmt.Println("Medicine already give to patient")
    return
  }
  fmt.Println("Pharmacy giving medicine to patient")
  p.MedicineDone = true
  return
}
```

定义好各个环节的处理器后，就可以将其串成一条链来完成整个就诊流程：

```go
func main() {
  patient := &patient{Name: "abc"}
  receptionHandler := &Reception{}
  
  receptionHandler.SetNext(&Clinic{}).SetNext(&Cashier{}).SetNext(&Pharmacy{})
  receptionHandler.Execute(patient)
}
```

不过上面这个实现还有一个问题：Reception 接诊挂号这个步骤并没有调用到，也就是`Reception.Do()`方法并没有执行。这需要为职责链定义一个起始处理器类型，它不提供具体的处理逻辑，仅作为第一个处理器来开始整个职责链的处理：

```go
type StartHandler struct {
  Next
}

func (h *StartHandler) Do(p *patient) (err error) {
  // 空处理器，不做任何处理
  return
}

func main() {
  patient := &patient{Name: "abc"}
  patientHealthHandler := startHandler{}
  
  patientHealthHandler.SetNext(&Reception{}).
    SetNext(&Clinic{}).
    SetNext(&Cashier{}).
    SetNext(&Pharmacy{})
    
  if err := patientHealthHandler.Execute(patient); err != nil {
    fmt.Println("Fail | Error:" + err.Error())
  } else {
    fmt.Println("Success")
  }
}
```

之所以要添加一个空处理器作为起始处理器，也是因为 Go 的语法限制：公共方法`Execute`并不能像面向对象那样先调用`this.Do()`再调用`this.nextHandler.Do()`。

### 4. 总结

职责链模式所拥有的的特点让流程中的每个处理节点都只需关心满足自己处理条件的请求进行处理即可，不需要关注其他的逻辑。

另外，职责链也可以设置中止条件，对前面处理失败的情况下提前中止流程，不再继续往链路的下级节点传递请求。也就是在示例代码中的`Execute`方法中增加判断。Gin 的中间件的`abort`方法就是按照这个原理实现的。

能提前中止也是职责链模式跟装饰器模式的一个重要区别。


