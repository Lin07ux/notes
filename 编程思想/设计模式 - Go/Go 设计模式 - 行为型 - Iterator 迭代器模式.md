> 转摘：[Go设计模式-迭代器到底是不是一个多此一举的模式？](https://mp.weixin.qq.com/s/sABibBRsC2kknbAH18oatA)

### 1. 介绍

迭代器模式（Iterator Pattern）也叫做游标模式（Cursor Pattern），它提供了一种方法顺序地访问一个聚合对象中的元素，而不是直接暴露该对象的内部表示。

这里说的聚合对象也常被称为集合，是编程中常使用的数据类型之一，很多编程语言或者框架中都内置了集合类型和功能，比如 Java 中内置的 Collection 和 Map 类簇。

无论集合采用什么数据结构实现，都需要提供某种访问元素的方式，以便于其他代码访问其中的元素。而迭代器模式就为其定义了一种顺序遍历集合元素的方式，使客户端可以使用迭代器定义的方法来对不同的集合实现进行迭代遍历。

迭代器的思想是将集合对象的遍历操作从集合类中拆分出来，放到独立的迭代器实现类中，让两者的职责更单一。此时迭代器模式的组成会与代理模式有些类似，但是迭代器模式属于行为型设计模式，更关注的是行为结果，而且迭代器模式并不是对原实例的功能代理，而是实现功能扩充。

### 2. 组成

迭代器模式的结构，可以用下面的 UML 类图表示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1679989106-f999978bd5ebf901577154f17114b203.png)

* Iterator 接口：定义迭代器需要的基础接口，如`HasNext()`、`GetNext()`等，以帮助客户端执行集合遍历操作；
* Collection 接口：代表了要被遍历的集合，其中定义了一个`CreateIterator`方法，返回一个能对自身进行遍历的 Iterator 实例；
* Concrete Iterator：Iterator 接口的具体实现类；
* Concrete Collection：Collection 接口的具体实现类；
* Client：通过集合和迭代器的接口与两者进行交互，无需与具体的类进行耦合，允许同一客户端代码使用各种不同的集合和迭代器。

在实际的迭代器接口中，还可以定义更多的方法，比如重置迭代进度等，可以根据需要进行设计和实现。

### 3. 实例

下面为使用 Go 语言实现的迭代器模式实例，其底层使用的是列表数据结构，但是使用其他的底层数据类型也是可以的，只要其迭代器实例实现了迭代器接口即可。

首先是两个接口的定义：

```go
type Iterator interface {
  HasNext() bool
  GetNext() *User
}

type Collection interface {
  CreateIterator() Iterator
}
```

这里定义的 Iterator 和 Collection 接口都是针对特定的类型的，如果使用了泛型的话是可以针对任意类型的数据的。

下面是具体类型的实现：

```go
type User struct {
  name string
  age  int
}

type userIterator struct {
  index int
  users []*User
}

func (ui *userIterator) HasNext() bool {
  return ui.index < len(ui.users)
}

func (ui *userIterator) GetNext() *User {
  if ui.HasNext() {
    user := ui.users[ui.index]
    ui.index++
    return user
  }
  return nil
}

type userCollection struct {
  users []*User
}

func (uc *userCollection) CreateIterator() Iterator {
  return &userIterator{
    users: u.users,
  }
}
```

有了这些实现之后，客户端就能使用迭代器完成对集合对象的遍历了：

```go
func main() {
  userK := &User{
    name: "Kevin",
    age:  18,
  }
  userD := &User{
    name: "Diamond",
    age:  25,
  }
  
  userCollection := &userCollection{
    users: []*User{userK, userD}
  }
  iterator := userCollection.CreateIterator()
  for iterator.HasNext() {
    user := iterator.GetNext()
    fmt.Printf("User is %v\n", user)
  }
}
```

这是一个简单的迭代器模式的实践实例，但是也能看出迭代器模式的运行方式。这里的`userCollection`好像没有存在的意义，只是因为这个示例比较简单，其实`userCollection`也是能实现很多自身相关的功能的，并非只是一个数据容器。另外，这里迭代器遍历的时候没有加锁，所以不是并发安全的，需要的话可以在`GetNext`方法中加锁来避免并发问题。

### 4. 总结

迭代器模式在平常的框架中基本都配置了，基本并不需要自行实现。但是在一些特别的场景，为特定类型的数据实现迭代器能够更方便对齐进行处理。

迭代器的主要思想是将集合对象的遍历操作从集合类型中拆分出来，独立实现于迭代器类中，让两者的职责更单一的同时也让客户端不必关心该怎样去实现集合的迭代算法，屏蔽了不同集合类型的数据结构。