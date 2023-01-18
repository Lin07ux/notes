> 转摘：[Go 设计模式｜组合，一个对数据结构算法和职场都有提升的设计模式](https://mp.weixin.qq.com/s/JKWbyr4Yt7A6l1nFsANUcQ)

### 1. 介绍

组合模式（Composite Pattern）又叫做部分-整体（Part-Whole）模式，属于结构型设计模式。

组合模式的核心思想是将一个大的逻辑功能分拆到多个独立的小逻辑中，而多个小逻辑组合在一起就成为了组合对象。在实现中，常会将组合对象和单个小对象用相同的接口进行表示，使得客户对小对象和组合对象的使用具有一致性。

组合模式的使用要求业务场景中的实体必须能够表示成树型结构才行。由组合模式将一组对象组织成树形结构，客户端（代码的使用者）可以将单个对象和组合对象都看做树中的节点，以统一处理逻辑。并且，利用树形结构的特点，可以将对树、子树的处理转化成对叶节点的递归处理，以此简化代码实现。

比如，对于文件系统、公司组织架构这些有层级结构的事物的操作就适合应用组合模式。

### 2. 组成

组合模式的类图如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1673959009)

组合模式由以下几个角色构成：

1. 组件 Component：是一个接口，描述了树中单个对象和组合对象都要实现的操作；
2. 叶节点 Leaf：单个对象节点，是树的基本结构，其不包含子节点，因此也就无法将工作指派给下一级。叶节点最终会完成大部分的实际工作；
3. 组合对象 Composite：包含叶节点或其他组合对象等子项目的复合对象。组合对象不需要知道其子节点所属的具体类型，只通过统一的组件接口与其交互；
4. 客户端 Client：通过组件接口与所有项目进行交互。

### 3. 示例

下面用一个公司组织架构的例子来演示组合模式的代码实现。

大公司下会有很多子公司、项目部、部门等。这些组织都要会有相同的一些功能，下面就先定义组织的能力接口：

```go
type Organization interface {
  display() // 打印组织结构
  duty() // 展示组织职责
}
```

然后定义和实现组合对象行为：

```go
type CompositeOrganization struct {
  orgName string
  depth   int
  list    []Organization
}

func NewCompositeOrganization(name string, depth int) *CompositeOrganization {
  return &CompositeOrganization{name, depth, []Organization}
}

func (c *CompositeOrganization) add(org Organization) {
  if c == nil {
    return
  }
  c.list = append(c.list, org)
}

func (c *CompositeOrganization) remove(org Organization) {
  if c == nil {
    return
  }
  for i, val := range c.list {
    if val == org {
      c.list = append(c.list[:i], c.list[i+1:]...)
      return
    }
  }
}

func (c *CompositeOrganization) display() {
  if c == nil {
    return
  }
  fmt.Println(strings.Repeat("-", c.depth * 2), " ", c.orgName)
  for _, val := range c.list {
    val.display()
  }
}

func (c *CompositeOrganization) duty() {
  if c == nil {
    return
  }
  for _, val := range c.list {
    val.duty()
  }
}
```

组合对象用来表示有下属部门的组织，从代码中可以看到，它持有一个`[]Organization`类型的列表，存放的就是其下属组织。组合对象的`display`和`duty`方法的实现就是简单的将工作委托给它们的下属组织来做，这也是组合模式的特点。

下面再实现两个职能部门：

```go
// HR 部门
type HRDOrg struct {
  orgName string
  depth   int
}

func (h *HRDOrg) display() {
  if h == nil {
    return
  }
  fmt.Println(strings.Repeat("-", h.depth * 2), " ", h.orgName)
}

func (h *HRDOrg) duty() {
  if h == nil {
    return
  }
  fmt.Println(o.orgName, "员工招聘培训管理")
}

// 财务部门
type FinanceOrg struct {
  orgName string
  depth   int
}

func (f *FinanceOrg) display() {
  if f == nil {
    return
  }
  fmt.Println(strings.Repeat("-", f.depth * 2), " ", f.orgName)
}

func (f *FinanceOrg) duty() {
  if f == nil {
    return
  }
  fmt.Println(f.orgName, "财务报销工资发放")
}
```

然后就可以在客户端中组合好组织结构，不管有几层组织，客户端对整个组织的调用都是不会改变的：

```go
func main() {
  root := NewCompositeOrganization("北京总公司", 1)
  root.add(&HRDOrg{orgName: "总公司人力资源部", depth: 2})
  root.add(&FinanceOrg{orgName: "总公司财务", depth: 2})
  
  comSh := NewCompositeOrganization("上海分公司", 2)
  comSh.add(&HRDOrg{orgName: "上海分公司人力资源部", depth: 3})
  comSh.add(&FinanceOrg{orgName: "上海分公司财务部", depth: 3})
  root.add(comSh)
  
  comGd := newCompositeOrganization("广东分公司", 2)
  comGd.add(&HRDOrg{orgName: "广东分公司人力资源部", depth: 3})
  comGd.add(&FinanceOrg{orgName: "广东分公司财务部", depth: 3})
  root.add(comGd)
  
  fmt.Println("公司组织架构：")
  root.display()
  
  fmt.Println("各组织的职责：")
  root.duty()
}
```

### 4. 总结

组合模式的优点主要有：

1. 实现类似树形结构，可以清楚地定义各层次的复杂对象，表示对象的全部或部分层次；
2. 简化客户端调用，让客户端能忽略各层次的差异，方便对整个层次结构进行控制。

实际上，组合模式与其说是一种设计模式，倒不如说是对业务场景的一种数据结构和算法的抽象，场景中的数据可以表示成树这种结构，业务需求的逻辑可以通过对树的递归遍历算法实现。

组合模式和装饰器模式在结构上很像，拥有非常相似的类结构，但是两者在使用意图上是有区别的：

* **组合模式**：为独立对象和组合对象提供了统一的接口，独立对象分担组合对象要做的工作；
* **装饰器模式**：核心的功能是由被装饰对象实现，而各个装饰器则是做好核心之外的增强功能。

