> 转摘：[一些实用的 Go 编程模式 | Builder模式能用来解决什么问题？](https://mp.weixin.qq.com/s/petcuOx-wrOX4oQlJ5b_bA)

### 1. 解决的问题

Builder 模式被称为建造模式，也被称为生成器模式，一般用来构建类型实例对象，可以将复杂的对象的构建与它的表示进行分离，使得同样的构建过程可以创建不同的表示。

适合使用建造模式的场景是：

**要构建的对象很大并且需要多个步骤时，使用构建起模式有助于减小构造函数的大小。**

由于 OOP 的语法限制，构造函数不是实例方法，所以不能出现在接口中，从而也就不能依赖倒置。

Builder 构造模式和工厂模式、单例模式、原型模式等，都是用不同的思路来解决上面的这个问题的。

Builder 模式把构造器的功能放到实例方法上，从而一切其他的设计模式就都可以使用了。

### 2. 实现方式

Builder 模式一般会提供链式接口来进行对象字段的初始化，比如：

```Java
Coffee.builder().name("Latti").price(30).build()
```

在 Go 中实现 Builder 模式也可以仿照上面的模式，定义一系列的构造器方法。

示例如下：

```go
package myserver

// 使用一个 builder 类来做包装
type serverBuilder struct {
  server Server
}

func Build() *serverBuilder {
  return &serverBuilder{
    server: http.Server{
      // 设置成员的默认值
      Addr: "127.0.0.1:8080"
    }
  }
}

func (sb *serverBuilder) Addr(addr string) *serverBuilder {
  sb.server.Addr = addr
  return sb
}

func (sb *serverBuilder) Protocol(protocol string) *serverBuilder {
  sb.server.Protocol = protocol
  return sb
}

func (sb *serverBuilder) Port(port string) *serverBuilder {
  sb.server.Port = port
  return sb
}

func (sb *serverBuilder) MaxConns(maxConns int) *serverBuilder {
  sb.server.MaxConns = maxConns
  return sb
}

func (sb *serverBuilder) Timeout(timeout time.Duration) *serverBuilder {
  sb.server.Timeout = timeout
  return sb
}

func (sb *serverBuilder) Build() Server {
  return sb.server
}
```

接下来，就可以使用构建模式来创建一个`Server`类型的对象了：

```go
server := myserver.Builder()
    .Addr("127.0.0.1")
    .Port("10880")
    .MaxConns(20)
    .Build()
```

Builder 模式比定义一个参数居多的类型实例会好一些。

