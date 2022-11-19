> 转摘：[工厂模式有三个Level，你能用Go写到第几层？](https://mp.weixin.qq.com/s/zlMRyDG6tS7TbrYpoYd6Bw)

### 1. 解决的问题

工厂模式也属于建造型模式，用于创建指定类的实例。

最常规的创建类型示例的方式就是使用`new`方式创建。

在实际的业务中，常会有一些执行流程明确、但是流程细节有差异的逻辑，它们对应的类型会实现一个统一流程的接口。但是在程序里该创建哪个类型的实例是不能预先知道的，而是要根据运行参数动态的判断和生成。此时就不能简单的 new 实例，而是需要使用一种统一的方式来生成所需要的实例，这就是工厂模式。

另外，在有些场景中，对于类实例化的过程可能需要进行收敛，以保证得到的实例符合预期，也可以使用工厂模式。

Go 的很多类库中，都会暴露出一个或多个`NewXXXX`之类的函数，这种函数能够根据条件生产出具体类型的实例，这就属于工厂模式了。

### 2. 实现方式

设计模式里的工厂模式可以分成三类工厂：

* 简单工厂
* 工厂方法
* 抽象工厂

这三类工厂模式的抽象度依次提高。类库中的`NewXXXX`之类的函数就属于简单工厂模式。随着流程定型的类库越来越复杂，要求的抽象度变高后，就会逐步的应用到后面的两种工厂模式。

#### 2.1 简单工厂

Go 语言没有构建函数一说，所以一般会定义`NewXXXX`之类的函数来初始化相关类实例。当`NewXXXX`之类的函数返回的是接口时，就属于简单工厂模式了。

简单工厂模式主要包含 3 个角色：

* 抽象产品：负责描述所有实例的行为，是简单工厂创建的所有对象实例的抽象父类/接口。
* 具体产品：是实现抽象产品的一系列具体类型，它们的功能实现不同，但是都符合抽象产品的定义，是简单工厂创建的目标。
* 简单工厂：这是简单工厂模式的核心，负责实现创建所有实例的内部逻辑。它返回的是抽象产品类型。工厂创建产品类实例的方法可以被外界直接调用，创建所需的产品对象。

假如一个场景中有很多语言的打印机，都源于一个 Printer 接口：

```go
// Printer 简单工厂要返回的接口类型
type Printer interface {
    Print(name string) string
}
```

程序通过简单工厂向客户端提供需要的语种的打印机：

```go
func NewPrinter(lang string) Printer {
    switch lang {
    case "cn":
        return new(CnPrinter)
    case "en":
        return new(EnPrinter)
    default:
        return new(CnPrinter)
    }
}

// CnPrinter 是 Printer 接口的中文实现
type CnPrinter struct {}

func (*CnPrinter) Print(name string) string {
    return fmt.Sprintf("你好，%s", name)
}

// EnPrinter 是 Printer 接口的英文实现
type EnPrinter struct {}

func (*EnPrinter) Print(name string) string {
    return fmt.Sprintf("Hello, %s", name)
}
```

这个场景中，实现了两个语种的打印机类型，它们都是 Printer 接口的具体实现类型。在`NewPrinter`函数中，通过参数来判断需要初始化哪个语种的打印机，而且返回结果是作为 Printer 接口类型的实例。

这样，业务方调用`NewPrinter`函数的时候，只需要告诉工厂需要哪个语种的打印机产品，工厂就会返回一个 Printer 实例，然后业务方就能执行`Print()`方法进行打印了：

```go
printer := NewPrinter("en")
fmt.Println(printer.Print("Bob"))
```

这个场景下，工厂、产品接口、具体产品类型的关系可以用下图表示：

![简单工厂模式](https://cnd.qiniu.lin07ux.cn/markdown/1668748664)

简单工厂的优点就是简单，缺点则是不利于扩展。因为如果要增加新的产品，那么就需修改工厂内部逻辑，类似于现实中的工厂要扩产品生成线。当产品类别过多时，就会导致简单工厂过于臃肿。这也就有了下一级别的工厂方法模式。

#### 2.2 工厂方法

工厂方法模式（Factory Method Pattern）又叫做多态性工厂模式，指的是定义一个创建对象的接口，并有多个实现了该接口的工厂类型，每个工厂类型决定实例化哪个产品类。所以，工厂方法模式把类的实例化推迟到子类中进行了。

在工厂方法模式中，不再由单一的一个工厂来生成产品，而是由工厂类的子类实现具体的产品的创建。因此，当增加一个产品时，只要增加一个相应的工厂类的子类。这样就解决了简单工厂生产太多产品时导致其内部代码臃肿（switch...case 分支过多）的问题。

这就相当于现实中生产手机、电脑、平板等的工厂，每种类型的工厂都能生产特定的产品。这些工厂都属于消费电子产品工厂，它们相当于都实现了于消费电子产品工厂的流程，可以使用相同的方式进行开工、生成、包装、发货。

工厂方法模式的优点：

* 灵活性增强，对于新产品的创建，只需增加一个新的给工厂类型即可。
* 典型的解耦方式，高层模块只需要知道产品的抽象类，无需关心具体的实现，满足迪米特法则、依赖倒置原则和里式替换原则。

工厂方法模式的缺点：

* 类型的个数容易过多，增加复杂度。
* 增加了系统的抽象性和理解难度。
* 单一工厂只能生成一种产品（此弊端可以使用抽象工厂模式解决）。

比如，有工厂能生产数学计算器，每种数学计算器能做的运算是不同的，可以安排不同的子工厂生产不同类型的计算器，但是这些子工厂都要按照相同的流程（实现相同的工厂接口）。

> Go 中没有继承，所以这里说的工厂子类，其实是实现了工厂接口的具体工厂类型。

首先，定义工厂接口和计算器接口：

```go
// OperatorFactory 工厂接口，由具体的工厂类实现
type OperatorFactory interface {
    Create() MathOperator
}

type MathOperator interface {
    SetOperatorA(int)
    SetOperatorB(int)
    ComputeResult() int
}
```

然后，定义具体的计算器类型，比如加法计算器和乘法计算器：

```go
// BaseOperator 是所有 Operator 的基类，封装公用方法
// 因为 Go 不支持继承，具体 Operator 类只能组合它来实现类似继承的行为表现
type BaseOperator struct {
    operatorA, operatorB int
}

func (o *BaseOperator) SetOperatorA(operand int) {
    o.operatorA = operand
}

func (o *BaseOperator) SetOperatorB(operand int) {
    o.operatorB = operand
}

// PlusOperator 加法计算器
type PlusOperator struct {
    *BaseOperator
}

// ComputeResult 计算两个操作数之和
func (p *PlusOperator) ComputeResult() int {
    return p.operatorA + p.operatorB
}

// MultiOperator 乘法计算器
type MultiOperator struct {
    *BaseOperator
}

// ComputeResult 计算两个操作数之积
func (m *MultiOperator) ComputeResult() int {
    return m.operatorA * m.operatorB
}
```

再为每一类计算机设计一个工厂子类：

```go
// PlusOperatorFactory 是生产加法计算器的工厂类
type PlusOperatorFactory struct {}

func (pf *PlusFactory) Create() MathOperator {
    return &PlushOperator{
        BaseOperator: &BaseOperator{},
    }
}

// MultiOperatorFactory 是生产乘法计算器的工厂类
type MultiOperatorFactory struct {}

func (mf *MultiOperatorFactory) Create() MathOperator {
    return &MultiOperator{
        BaseOperator: &BaseOperator{},
    }
}
```

> 这里仅为简单示例，所以在创建产品类实例的时候只是简单的 new，但实际业务中是可以设置复杂逻辑的。

之后，客户端就可以使用具体的工厂子类来创建不同的产品实例了：

```go
func main() {
    var factory OperatorFactory
    var mathOp MathOperator
    
    factory = &PlusOperatorFactory{}
    mathOp = factory.Create()
    mathOp.SetOperatorA(3)
    mathOp.SetOperatorB(2)
    fmt.Printf("Plus operation result: %d\n", mathOp.ComputeResult())
    
    factory = &MultiOperatorFacotry
    mathOp = facotry.Create()
    mathOp.SetOperatorA(3)
    mathOp.SetOperatorB(2)
    fmt.Printf("Multiple operation result: %d\n", mathOp.ComputeResult())
}
```

这个场景下，工厂接口、具体工厂类、产品接口、具体产品类的关系可以用下面这个图表示：

![工厂方法模式](https://cnd.qiniu.lin07ux.cn/markdown/1668750788)

无论是简单工厂还是工厂方法，每个工厂都只能生产一种类型的产品（==*因为工厂方法只有一个返回值*==）。如果一个工厂需要创建生态里的多个产品，就需要更进一步，使用抽象工厂模式（==*就是为每个工厂增加多个生产方法*==）。

#### 2.3 抽象工厂

抽象工厂模式用于创建一系列相关或者相互依赖的对象。

比如，智能家居领域多家公司，其工厂除了生产手机外，还会生产电视、空调等加点设备。那么，在进行工厂生产管理中，就不能使用工厂方法模式了，因为工厂方法模式只能让一个工厂生产一种产品。

抽象工厂模式相当于有多个工厂，而每个工厂中都设计了多个车间，每个车间可以生产一种产品。

抽象工厂相当于在工厂方法模式的基础上，为每个工厂赋予了多种能力。同样的，抽象工厂也具备工厂方法把产品的创建推迟到工厂子类中去做的特性，而且也便于新品牌产品的增加。

抽象工厂的优点：

* 当需要生产产品族时，抽象工厂可以保证客户端始终只使用同一个产品的产品族；
* 抽象工厂增强了程序的可扩展性，对于新产品族的增加，只需要实现一个新的工厂类型和产品类型，不需要对已有的工厂和产品进行修改，符合开闭原则。

抽象工厂的缺点：

* 规定了所有可能被创建的产品集合，产品族中扩展新的产品困难，需要修改抽象工厂的接口；
* 增加了系统的抽象性和理解难度。

下面是使用抽象工厂模式设计的多品牌-多产品的形态：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668753961)

首先，定义功能的能力和不同产品的功能接口：

```go
// AbstractFactory 抽象工厂接口，定义工厂的能力
type AbstractFactory interface {
    CreateTelevision() ITelevision
    CreateAirConditioner() IAriConditioner
}

// ITelevision 电视机接口，定义电视功能
type ITelevision interface {
    Watch()
}

// IAirConditioner 空调接口，定义空调的功能
type IAirConditioner interface {
    SetTemperature(int)
}
```

然后，定义华为品牌的电视和空调类型和华为工厂类型：

```go
// 华为电视
type HuaWeiTV struct {}

func (ht *HuaWeiTV) Watch() {
    fmt.Println("Watch HuaWei TV")
}

// 华为空调
type HuaWeiAirConditioner struct {}

func (ha *HuaWeiAirConditioner) SetTeperature(temp int) {
    fmt.Printf("HuaWei AirConditioner set temperature to %d ℃\n", temp)
}

// 华为工厂
type HuaWeiFactory struct {}

func (hf *HuaWeiFactory) CreateTelevison() ITelevision {
    return &HuaWeiTV{}
}

func (hf *HuaWeiFactory) CreateAirConditioner() IAirConditioner {
    return &HuaWeiAirConditioner{}
}
```

在定义小米品牌的电视和空调类型，以及小米工厂：

```go
// 小米电视
type MiTV struct {}

func (mt *MiTV) Watch() {
    fmt.Println("Watch XiaoMi TV")
}

// 小米空调
type MiAirConditioner struct {}

func (ma *MiAriConditioner) SetTemperature(temp int) {
    fmt.Printf("XiaoMi AirConditioner set temperature to %d ℃\n", temp)
}

// 小米工厂
type MiFactory struct{}

func (mf *MiFactory) CreateTelevision() ITelevision {
    return &MiTV{}
}

func (mf *MiFactory) CreateAirConditioner() IAirConditioner {
    return &MiAirConditioner{}
}
```

这样就能用两种工厂分别生产不同品牌的不同产品了：

```go
func main() {
    var factory AbstractFactory
    var tv ITelevision
    var air IAirConditioner
    
    factory = &HuaWeiFactory{}
    tv = factory.CreateTelevision()
    air = facotry.CreateAirConditioner()
    tv.Watch()
    air.SetTemperature(25)
    
    factory = &MiFactory{}
    tv = factory.CreateTelevision()
    air = factory.CreateAirConditioner()
    tv.Watch()
    air.SetTemperature(25)
}
```

抽象工厂的特点可以总结如下：

* 当系统所提供的工厂所需生产的具体产品并不是一个简单的对象，而是多个位于不同产品等级结构中属于不同类型的具体产品时，需要使用抽象工厂模式；
* 抽象工厂模式是所有形式的工厂模式中最为抽象和最具一般性的一种形式；
* 抽象工厂模式与工厂方法模式最大的区别在于，工厂方法模式针对的是一个产品等级结构，而抽象工厂模式则需要面对多个产品等级结构，一个工厂等级结构可以负责多个不同产品等级结构中的产品对象的创建；
* 当一个工厂等级结构可以创建出分属于不同产品等级结构的一个产品族中的所有对象时，抽象工厂模式比工厂方法模式更为简单、高效。



