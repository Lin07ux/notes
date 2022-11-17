> 转摘：
> 
> 1. [Go学设计模式--原型模式的考查点和使用推荐](https://mp.weixin.qq.com/s/Mb6g8tzqhKa9XiOMaRlL5w)
> 2. [Go设计模式04-原型模式](https://lailin.xyz/post/prototype.html)
> 3. [Design Patterns in Golang: Prototype](https://blog.ralch.com/articles/design-patterns/golang-prototype/)

### 1. 概览

如果一个类有非常多的属性，层级还很深，那么每次构造起来，不管是直接构造还是用构造者模式，都要对太多属性进行复制。而原型模式则能够很好的解决这个问题。

原型模式属于创造型设计模式。在 JavaScript 的继承实现中有广泛的应用，在拷贝出来的原型的基础上再继续添加或者修改属性/方法来实现。

### 2. 概念

**通过复制、拷贝或者克隆已有对象的方式来创建新对象的设计模式叫做原型模式**，被拷贝的对象也被称作原型对象。

按照惯例，原型对象会暴露出一个 Clone 方法，给外部调用者一个机会来从自己这里“零成本”的克隆出一个新对象。

这里的“零成本”说的是：调用者不需要做别的操作，只需要调用原型对象的 Clone 方法后就能得到一个新的对象，而具体的克隆实现则有原型对象来完成。

原型模式更多的是阐述一种编程模式，并没有限制使用什么方法来实现。所以，原型对象克隆自己的时候，可以使用深拷贝，也可以使用浅拷贝，或者两者结合，根据需要（节省空间、减少 Clone 方法的复杂度等）来决定即可。

下面的 UML 图，描述了原型模式中各角色拥有的行为以及它们之间的关系：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668591606)

### 3. 实现

按照原型模式的约定，所有原型对象都要实现一个 Clone 方法，定义如下的原型接口：

```go
type Prototype interface {
    Clone() SpecificType
}
```

原型对象则需要实现该 Prototype。

下面是一个搜索关键词拷贝的例子，使用了深拷贝和浅拷贝结合的方式：

```go
// 示例代码来自：https://lailin.xyz/post/prototype.html
package prototype

import (
    "encoding/json"
    "time"
)

// Keyword 搜索关键词
type Keyword struct {
    word     string
    visit    int
    UpdateAt *time.Time
}

// Clone 使用序列化和反序列化的方式深拷贝
func (k *Keyword) Clone() *Keyword {
    var newKeyword Keyword
    b, _ := json.Marshal(k)
    json.Unmarshal(b, &newKeyword)
  return &newKeyword
}

// Keywords 关键词 map
type Keywords map[string]*Keyword

// Clone 复制一个新的 keywords
// updatedWords 需要更新的关键词列表，由于从数据库中获取数据常为数组格式
func (words Keywords) Clone(updatedWords []*Keywords) Keywords {
    newKeywords := Keywords{}
    
    for k, v := range words {
        // 浅拷贝，只拷贝了 Keyword 的地址
        newKeywords[k] = v
    }
    
    // 替换掉需要更新的字段，这里用的是深拷贝
    for _, word := range updatedWords {
        newKeywords[word.word] = word.Clone()
    }
    
    return newKeywords
}
```

### 4. 使用目的和场景

使用原型模式的目的主要是为了节省创建对象所花费的时间和资源消耗，提升性能。而且，对于在程序运行过程中应保持不变的对象（如全局配置对象），可以通过原型模式快速拷贝出一份，再在副本上做运行时自定义修改，这样可以避免影响其他线程/协程对原型对象的争用问题。

当对象的创建成本比较大，并且同一类型不通对象间的差别不大（大部分属性值相同）但属性值需要经过复杂处理（计算、排序）或者获取较慢（从网络、DB 中获取），亦或属性值拥有很深的层级，这时就是原型模式发挥作用的地方了。因为对象在内存中复制字节远比每次创建对象时重走一遍上面说的操作要高效的多。

### 5. 实例

下面是一个类似 DOM 树对象操作的例子。因为 DOM 树对象往往层级很深，那么创建类似的 DOM 树的时候就能够很耗费的发挥原型模式的优势。

```go
// 示例代码来自：https://blog.ralch.com/articles/design-patterns/golang-prototype/
package dom

import (
    "bytes"
    "fmt"
)

// Node a document object model node
type Node struct {
    // String returns nodes text representation
    String() string
    // Parent returns the node parent
    Parent() Node
    // SetParent sets the node parent
    SetParent(node Node)
    // Children returns the node children nodes
    Children() []Node
    // AddChild adds a child node
    AddChild(child Node)
    // Clone clones the node
    Clone() Node
}

// Element represents an element in document object model
type Element struct {
    text     string
    parent   Node
    children []Node
}

// NewElement makes a new element
func NewElement(text string) *Element {
    return &Element{
        text:     text,
        parent:   nil,
        children: make([]Node, 0),
    }
}

// Parent returns the element parent
func (e *Element) Parent() Node {
    return e.parent
}

// SetParent sets the element parent
func (e *Element) SetParent(node Node) {
    e.parent = node
}

// Children returns the element children elements
func (e *Element) Children() []Node {
    return e.children
}

// AddChild adds a child element
func (e *Element) AddChild(child Node) {
    copy := child.Clone()
    copy.SetParent(e)
    e.children = append(e.children, copy)
}

// Clone makes a copy of particular element. Note that
// the element becomes a root of new orphan tree
func (e *Element) Clone() Node {
    copy := &Element{
        text:     e.text
        parent:   nil,
        children: make([]Node, 0),
    }
    for _, child := range e.children {
        copy.AddChild(child)
    }
    return copy
}

// String returns string representation of element
func (e *Element) String() string {
    buffer := bytes.NewBufferString(e.text)
    
    for _, c := range e.Children() {
        text := c.String()
        fmt.Fprintf(buffer, "\n %s", text)
    }
    
    return buffer.String()
}
```

上面的 DOM 对象：Node、Element，都支持原型模式要求的 Clone 方法。那么有了这个原型克隆的能力后，如果想根据创建好的 DOM 树克隆出来一个子分支作为一个独立的 DOM 树对象的时候，就可以像项目这样简单地执行`Node.Clone()`把节点和其下面的子节点全部拷贝出去，这样比使用构造方法再重新构造树形结构要方便许多。

下面的例子是用 DOM 树结构创建公司里的职级关系，然后可以从任意层级克隆出一棵新的树：

```go
func main() {
    // 职级节点 -- 总监
    directorNode := dom.NewElement("Director of Engineering")
    // 职级节点 -- 研发经理
    engManagerNode := dom.NewElement("Engineering Manager")
    engManagerNode.AddChild(dom.NewElement("Lead Software Enginerr"))
    // 研发经理是总监的下级
    directorNode.AddChild(engManagerNode)
    // 办公室经理也是总监的下级
    officeManagerNode := dom.NewElement("Office Manager")
    directorNode.AddChild(officeManagerNode)
    fmt.Println("")
    fmt.Println("# Company Hierarchy")
    fmt.Print(directorNode)
    fmt.Println("")
    // 从研发经理节点克隆出一颗心的树
    fmt.Println("# Team Hiearachy")
    fmt.Print(engManagerNode.Clone())
}
```

### 6. 总结

原型模式的优点：

* 某些时候克隆比直接 new 一个对象再逐个属性赋值的过程更简洁高效。比如创建层级很深的对象的时候，克隆比直接构造会方便很多；
* 可以使用深刻克隆的方式保存对象的状态，辅助实现撤销操作。

原型模式的缺点：

* Clone 方法位于类型内部，当对已有类型进行改造的时候就需要修改代码，违背了开闭原则；
* 当实现深克隆的时候，需要编写较为复杂的代码，尤其当对象质检存在多重嵌套引用时。而且为了实现深克隆，每一层对象对应的类都必须支持深克隆。因此，深克隆、浅克隆需要运用得当。

在项目中使用原型模式时，可能需要在项目初始化时就把提供克隆能力的原型对象创建好，在多线程环境下，每个线程处理任务的时候，用到了相关对象，可以去原型对象那里拷贝。不过，适合当做原型对象的数据并不多，所以原型模式在开发中使用的频率并不高。


