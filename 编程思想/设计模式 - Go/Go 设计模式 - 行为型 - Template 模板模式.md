> 转摘：[用Go学设计模式-提炼流程，减少重复开发就靠它了!](https://mp.weixin.qq.com/s/-Ysho1jI9MfrAIrplzj7UQ)

### 1. 介绍

模板、策略和职责链三个设计模式是解决业务系统流程复杂多变这个痛点的利器。

模板模式，也被称为模板方法模式，主要是因为这个模式里有个模板方法。不过这个模板方法在设计一些有客户端和服务多次交互的场景里，其实也可以是一个虚拟的逻辑概念，并不一定是要在设计模式的类中真实的实现的。

模板模式适用于流程和步骤相对固定、但是每一个具体的业务的具体步骤的实现逻辑并不相同的一类业务中，它在超类中定义了一个算法或业务的框架，允许子类在不修改结构的情况下重写算法或业务的特定步骤。

模板模式惯常的用法是：在一个模板方法中定义一个算法或逻辑的流程和步骤，比如先调用内部的方法 A 在调用内部方法 B，满足某个添加的时候不调用方法 C 等，而流程中每个步骤对应的方法都可以推迟到子类中去实现。这就能使程序在不改变大流程、步骤的情况下，完成相似性业务的能力。

### 2. 组成

模板模式实现起来非常简单，用抽象类定义好步骤，提供步骤的默认实现，然后具体的业务逻辑上每个步骤的实现差异交给子类去实现。

模板模式的结构用 UML 类图可以这么表示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672928390)

由于 Go 不支持面向对象语言中的类继承，所以用抽象类定义和实现基本步骤的方式在 Go 中并不适用。但是 Go 可以使用匿名嵌套的方式进行组合，可以实现类似的继承功能，只是代码会比较绕。

### 3. 示例

下面用在银行中办理业务的场景来做示例，用模板模式来实现存款、取款、购买理财等这些业务的流程。

银行业务办理各项业务基本都会有：取号、排位、处理具体业务、服务评价这几个步骤，而如果是 VIP 客户则有专属窗口不需要排队。检查用户是不是 VIP 用户这样的步骤叫做钩子方法。

首先，先定义银行业务的模板接口，包括主要的步骤方法：

```go
type BankBusinessHandler interface {
  TaskRowNumber()  // 排队取号
  WaitInHead()     // 等待叫号
  HandleBusiness() // 处理业务
  Commentate()     // 服务评价
  checkVipIdentity() bool // 钩子方法：检查是否为 VIP 用户
}
```

由于 Go 需要用匿名嵌套方式来实现基础方法的继承，所以再定义一个外层类型来包装组合一个 BankBusinessHandler 接口的实现，达到与抽象类和子类继承类似的效果：

```go
type BankBusinessExecutor struct {
  handler BankBusinessHandler
}

// ExecuteBankBusiness 模板方法
func (b *BankBusinessExecutor) ExecuteBusiness() {
  b.handler.TakeRowNumber()
  if !b.handler.CheckVipIdentity() {
    b.handler.WaitHead()
  }
  b.handler.HandleBusiness()
  b.handler.Commentate()
}
```

银行的存款、取款、购买理财业务都属于 BankBusinessExecutor 处理的一种，在这三种业务的具体处理类型中，可以根据其业务的需求来实现具体的逻辑。

另外，不管是哪个业务，取号、等号、服务评价这几个步骤基本都是相同的，可以提取出来作为公用的功能嵌入到各个业务的类型实现中，减少代码的重复率：

```go
// 默认业务类型
type DefaultBusinessHandler struct {
}

func (*DefaultBusinessHandler) TakeRowNumber() {
  fmt.Println("请拿好您的排位号：" + strconv.Itoa(rand.Intn(100)) + "，注意排队情况，过号后顺延三个安排")
}

func (*DefaultBusinessHandler) WaitInHead() {
  fmt.Println("排队等号中...")
  time.Sleep(5 * time.Second)
  fmt.Println("请去窗口 xxx 办理业务")
}

func (*DefaultBusinessHandler) Commentate() {
  fmt.Println("请对我的服务做出评价，满意请按 0")
}

func (*DefaultBusinessHandler) CheckVipIdentity() bool {
  return false
}

// 存款业务类型
type DepositBusinessHandler struct {
  *DefaultBusinessHandler
  userVip bool
}

func (*DepositBusinessHandler) HandleBusiness() {
  fmt.Println("账户存储了很多很多人民币...")
}

func (dh *DepositBusinessHandler) CheckVipIdentify() bool {
  return dh.userVip
}
```

上面的`DefaultBusinessHandler`并没有实现全部的`BankBusinessHandler`接口声明的方法，所以它就不能作为`BankBusinessHandler`接口的实现，只能被用于实现类型包装。

在使用的时候，就可以用对应的业务的类型对象来初始化外部包装类，完成对应的业务处理：

```go
func NewBankBusinessExecutor(businessHandler BankBusinessHandler) *BankBusinessExecutor {
  return &BankBusinessExecutor{handler: businessHandler}
}

func main() {
  dh := &DepositBusinessHandler{userVip: false}
  be := NewBankBusinessExecutor(dh)
  be.ExecuteBusiness()
}
```

### 4. 总结

由于继承关系自身的缺点，如果父类添加新的抽象方法，则所有子类都要改一遍。所以模板模式也会因为流程的变动而造成大量的改动。所以模板模式更适合于经过大量实践后，能把某个核心流程提炼成固定步骤的时候再应用。如果提炼的不到位、不全面，后续就要频繁的增加或者修改流程里的步骤。

另外，在使用模板模式的过程中，并不用局限于模式的定义，可以更灵活的使用。

#### 4.1 不一定非要有明确的模板方法

示例中实现的模板方法方式适用于与客户端单词交互的流程。如果需要与客户端多次交互才能完成整个流程，可以由每个交互的操作去使用模板里定义的方法。这个时候并不需要定义一个调用所用方法的模板方法。

这种情况也可以理解成：整个流程用到的 RESTful API 接口组合在一起扮演的就是模板方法的角色。

在互联网的 C 端产品里有很多典型的应用场景可以使用这种虚拟的模板模式。比如：用户经营类的活动，都可以抽象成展示活动信息、展示奖品信息、判断用户资格、参与活动、抽奖、查看中奖记录、核销奖品这些步骤。那么就可以利用模板设计模式对这些业务流程做抽象，每个步骤对一个一个或者多个 API 接口，实现各种用户活动你那个都能用一套统一的 RESTful API 来支撑业务的效果。

#### 4.2 模板模式与工厂模式结合使用

在实际的开发中，从来没有哪个设计模式是可以独立应用的，更多的时候是多个设计模式联合使用，相互配合来达到项目设计的效果。

由于模板模式把流程的实现推迟到子类，所以创建模板子类这个工作由工厂模式来完成是最合适的。一般情况下，在项目刚开始的时候使用简单工厂就可以达到目标，后续随着业务需求和流程提炼总结的深入和全面，再升级为抽象工厂模式也为时不晚。

