> 转摘：
> 
> 1. [一些实用的 Go 编程模式 | Builder模式能用来解决什么问题？](https://mp.weixin.qq.com/s/petcuOx-wrOX4oQlJ5b_bA)
> 2. [Go开源库、大项目的公共包，是这么用建造者模式的](https://mp.weixin.qq.com/s/eVmuRYMOxLdAXt4U1NEoCA)

### 1. 解决的问题

Builder 模式被称为建造模式，也被称为生成器模式，一般用来构建类型实例对象，可以将复杂的对象的构建与它的表示进行分离，使得同样的构建过程可以创建不同的表示。

适合使用建造模式的场景是：**要构建的对象很大并且需要多个步骤时，使用构建起模式有助于减小构造函数的大小。**

由于 OOP 的语法限制，构造函数不是实例方法，所以不能出现在接口中，从而也就不能依赖倒置。Builder 构造模式和工厂模式、单例模式、原型模式等，都是用不同的思路来解决上面的这个问题的。

Builder 模式把构造器的功能放到实例方法上，从而一切其他的设计模式就都可以使用了。

### 2. 实现方式

Builder 模式一般会提供链式接口来进行对象字段的初始化，比如：

```Java
Coffee.builder().name("Latti").price(30).build()
```

在 Go 中实现 Builder 模式也可以仿照上面的模式，定义一系列的构造方法。并且，在对每个属性赋值时，对参数的校验可以内聚到参数自己的建造者步骤里。

示例如下：

```go
package dbpool

// 定义一个 DB 连接池
type DBPool struct {
    dsn             string
    maxOpenConn     int
    maxIdleConn     int
    ...
    maxConnLifetime time.Duration
}

// 为 DBPool 定义建造者
type DBPoolBuilder struct {
    DBPool
    err error
}

func Builder() *DBPoolBuilder {
    b : = new(DBPoolBuilder)
    // 设置 DBPool 的默认值
    b.DBPool.dsn = "127.0.0.1:3306"
    b.DBPool.maxConnLifeTime = 1 * time.Second
    b.DBPool.maxOpenConn = 30
    return b
}

func (b *DBPoolBuilder) DSN(dns string) *DBPoolBuilder {
    if b.err != nil {
        return b
    }
    
    if dsn == "" {
        b.err = fmt.Errorf("invalid dsn, current is %s", dsn)
    }
    
    b.DBPool.dsn = dsn
    return b
}

func (b *DBPoolBuilder) MaxOpenConn(connNum int) *DBBuilderPool {
    if b.err != nil {
        return b
    }
    
    if connNum < 1 {
        b.err = fmt.Errorf("invalid MaxOpenConn, current is %d", connNum)
    }
    
    b.DBPool.maxOpenConn = connNum
    return b
}

func (b *DBPoolBuilder) MaxConnLifeTime(lifetime time.Duration) *DBPoolBuilder {
    if b.err != nil {
        return b
    }
    
    if lifetime < 1 * time.Second {
        b.err = fmt.Errorf("connection max life time can not litte than 1 second, current is %v", lifetime)
    }
    
    b.DBPool.maxConnLifetime = lifetime
}

func (b *DBPoolBuilder) Build() (*DBPool, error) {
    if b.err != nil {
        return nil, b.err
    }
    
    if b.DBPool.maxOpenConn < b.DBPool.maxIdleConn {
        return nil, fmt.Errorf("max total(%d) cannot < max idle(%d)", b.DBPool.maxOpenConn, b.DBPool.maxIdleConn)
    }
    
    return &b.DBPool, nil
}
```

然后就可以使用构建模式来创建一个`DBPool`类型的对象了：

```go
package main

import (
    "fmt"
    "xxx/dbpool"
)

func main() {
    dbPool, err := dbpool.Builder()
        .DSN("localhost:3306")
        .MaxOpenConn(50)
        .MaxConnLifetime(0 * time.Second)
        .Build()
    if err != nil {
        fmt.Println(err)
    }
    fmt.Println(dbPool)
}
```

### 3. 总结

Builder 模式比定义一个参数居多的类型实例会好一些，能够将每个参数的设置逻辑和校验逻辑尽量的内聚。

在实现 Builder 模式的时候，一般为了能够链式调用，中间的构建步骤方法会返回构建者实例。

另外，为了简化外部调用时的错误判断，在构建过程的每个参数设置步骤中，都聚合了对应的错误判断。这样，外部调用者在调用构建者构建实例的时候，只需在最终的生成步骤中进行一次错误判断了。

> 可以参考：[不想Go 错误处理太臃肿，可以参考这个代码设计](https://mp.weixin.qq.com/s/TQLmdyi7Z9ZKE8NIXojwCQ)

