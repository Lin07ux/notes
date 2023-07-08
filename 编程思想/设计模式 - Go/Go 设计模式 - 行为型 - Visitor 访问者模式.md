> 转摘：[Go设计模式--访客模式](https://mp.weixin.qq.com/s/qsw89qI8DOXyb4C1XI5QtA)

### 1. 简介

访问者模式（Visitor Pattern）是一种将数据结构对象与数据操作分离的设计模式，可以在不改变数据结构对象类结构的前提下定义作用于这些对象的新的操作，属于行为型设计模式。

访问者模式主要适用于以下应用场景：

1. 数据结构稳定，作用于数据结构的操作经常变化的场景；
2. 需要数据结构与数据操作分离的场景；
3. 需要对不同数据类型（元素）进行操作，而不使用分支判断具体类型的场景。

### 2. 组成

访问者模式通过将算法（操作）与对象结构（数据）分离来工作，所以需要定义一个表示算法的接口：Visitor。该接口将为对象结构中的每个类（一般称为元素类）提供一个方法，每个方法都将元素类的一个实例作为参数。

表示对象结构的所有元素类也会实现一个 Element 接口，该接口定义了接受访问者的方法 Accept，将访问者接口的实现作为参数。当 Accept 方法被调用时，访问者实例对应的方法就会被调用，通过访问者完成对元素类实例的操作。

下面是访问者模式的 UML 类图：

![](https://cnd.qiniu.lin07ux.cn/markdown/cad702e8e5f34917ce5590ec0d392767.jpg)

* Visitor 访客接口：声明了一系列以表示对象结构的具体元素为参数的访问者方法。如果编程语言支持重载，这些方法的名称是可以相同的，但是其参数一定是不同的；
* Concrete Visitor 具体访客：为不同的具体元素类实现相同行为的几个不同版本；
* Element 元素接口：声明了一个方法来接收访问者，该方法必须有一个参数被声明为访问者接口类型；
* Concrete Element 具体元素：必须实现接收方法，该方法的目的是根据当前元素类将其调用重定向到相应访问者的方法。注意：即使元素基类实现了该方法，所有子类都必须对其进行重写并调用访客对象中的合适方法。

### 3. 示例

下面用访客模式实现不同维度的订单统计示例，假设建设了一个订单管理系统，现在系统中要求能够按照不同维度统计分析销售订单：

* 区域销售报表：需按销售区域，统计销售情况；
* 品类销售报表：需根据不同产品，统计销售情况；
* 更多其他类型的销售报表。

针对这个需求，可以使用访问者模式，将不同的报表设计为订单的访问者，而订单数据就是固定的销售数据。

首先定义生成报表的访客类接口和订单数据要的 Element 接口：

```go
// 订单服务接口
type IOrderServer interface {
  Save(order *Order) error
  Accept(visitor IOrderVisitor)
}

// 报表访客接口
type IOrderVisitor interface {
  // 这里的参数不能定义成 IOrderService 类型
  Visit(order *Order)
  Report()
}
```

然后实现订单实体类型：

```go
// 订单实体类，实现 IOrderService 接口
type Order struct {
  ID       int
  Custoner string
  City     string
  Product  string
  Quntity  int
}

func (os *OrderService) Save(o *Order) error {
  os.orders[o.Id] = o
  return nil
}

func (os *OrderService) Accept(visitor IOrderVisitor) {
  for _, v := range os.orders {
    visitor.Visit(v)
  }
}

func NewOrder(id int, customer string, city string, product string, quantity int) *Order {
  return &Order{
    id, customer, city, product, quantity,
  }
}
```

再实现生成各种销售报表的访客类型：

```go
// 区域销售报表：按城市区域汇总销售数量
type CityVisitor struct {
  cities map[string]int
}

func (cv *CityVisitor) Visit(o *Order) {
  n, ok := cv.cities[o.City]
  if ok {
    cv.cities[o.City] = n + o.Quantity
  } else {
    cv.cities[o.City] = o.Quantity
  }
}

func (cv *CityVisitor) Report() {
  for k, v := range cv.cities {
    fmt.Printf("city=%s, sum=%v\n", k, v)
  }
}

func NewCityVisitor() IOrderVisitor {
  return &CityVisitor{
    cities: make(map[string]int, 0),
  }
}

// 品牌销售报表：按产品汇总销售数据
type ProductVisitor struct {
  products map[string]int
}

func (pv *ProductVisitor) Visit(o *Order) {
  n, ok := pv.products[o.Product]
  if ok {
    pv.products[o.Product] = n + o.Quantity
  } else {
    pv.products[o.Product] =  it.Quantity
  }
}

func (pv *ProductVisitor) Report() {
  for k, v := range pv.products {
    fmt.Printf("product=%s, sum=%v\n", k, v)
  }
}

func NewProductVisitor() IOrderVisitor {
  return &ProductVisitor{
    products: make(map[string]int, 0),
  }
}
```

完成之后就可以使用 Visitor 生成各种销售报表了：

```go
func main() {
  orderService := NewOrderService()
  orderService.Save(NewOrder(1, "张三", "广州", "电视", 10))
  orderService.Save(NewOrder(2, "李四", "深圳", "冰箱", 20))
  orderService.Save(NewOrder(3, "王五", "东莞", "空调", 30))
  orderService.Save(NewOrder(4, "张三三", "广州", "空调", 10))
  orderService.Save(NewOrder(5, "李四四", "深圳", "电视", 20))
  orderService.Save(NewOrder(6, "王五五", "东莞", "冰箱", 30))
  
  cv := NewCityVisitor()
  orderService.Accept(cv)
  cv.Report()
  
  pv := NewProductVisitor()
  orderService.Accept(pv)
  pv.Report()
}
```

### 4. 总结

访问者模式优点如下：

* 解耦了数据结构与数据操作，使得操作集合可以独立变化；
* 可以通过扩展访问者角色，实现对数据集的不同操作，扩展性更好；
* 元素具体类型单一，访问者均可操作；
* 各角色职责分离，复合单一职责原则。

访问者模式的缺点如下：

* 无法增加元素类型：若系统数据结构对象易于变化，经常有新的数据对象增加进来，则访问者类必须增加对应元素类型的操作，违背了开闭原则；
* 具体元素变更困难：具体原色增加属性、删除属性等操作，会导致对应的访问者类需要进行相应的修改，尤其有大量访客类时，修改范围太大；
* 违背依赖倒置原则：为了达到“区别对待”，访问者角色依赖的是具体元素类型，而不是抽象接口。