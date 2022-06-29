> 转摘：
> 
> 1. [一文吃透 Go 语言解密之接口 interface](https://mp.weixin.qq.com/s/vSgV_9bfoifnh2LEX0Y7cQ)
> 2. [Go 面试题：Go interface 的一个 “坑” 及原理分析](https://mp.weixin.qq.com/s/vNACbdSDxC9S0LOAr7ngLQ)

在 Go 语言的语义上，只要某个类型实现了所定义的一组方法集，就认为其就是同一种类型，可以 作为一类东西使用。也就是常说的鸭子类型（Duck typing）。


## 一、基础

### 1.1 声明

Go 语言中的接口声明语法如下：

```go
type Human interface {
  Say(s string) error
}
```

接口声明的关键主体为`type xxx interface`，紧接着的大括号中编写任意多个（包含零个）方法，用于声明和定义该接口所包含的方法集。

### 1.2 示例

更进一步的接口使用代码演示如下：

```go
type Human interface {
  Say(s string) error
}

type TestA string

func (t TestA) Say(s string) error {
  fmt.Printf("煎鱼：%s\n", s)
  return nil
}

func main() {
  var h Human
  var t TestA
  
  _ = t.Say("炸鸡翅") // 煎鱼：炸鸡翅
  h = t
  _ = h.Say("烤羊排") // 煎鱼：烤羊排
}
```

在上面的代码中，先是声明了一个名为`Human`的 interface，其中包含了`Say`方法；然后又声明了一个`TestA`结构体，也为其定义了一个`Say`方法。`Human`和`TestA`的`Say`方法的入参和出参类型都一样，所以可以认为`TastA`类型就是一个`Human`接口实现，或者说`TestA`类型实现了`Human`接口。

在主函数`main`中通过声明和赋值，成功的将类型为`TestA`的变量`t`赋给了类型为`Human`的变量`h`。正是因为两者都含有同样的方法`Say`，所以 Go 编译器就认为它们是一样的了（所以才可以完成赋值）。

## 二、数据结构

Go 语言的 interface，在底层实现上对应了两种数据结构：

* `runtime.iface` 结构体，表示包含方法的接口
* `runtime.eface` 结构体，表示不包含任何方法的空接口，也称为`empty interface`。

![](http://cnd.qiniu.lin07ux.cn/markdown/1646740799896-ddd960f36bd6.jpg)

### 2.1 runtime.eface

`runtime.eface`表示不包含任何方法的空接口，定义如下：

```go
type eface struct {
  _type *_type
  data  unsafe.Pointer
}

type _type struct {
  size       uintptr // 类型的大小
  ptrdata    uintptr // 包含所有指针的内存前缀的大小
  hash       uint32  // 类型的 hash 值，此处提前计算好，可以避免在哈希表中计算
  tflag      tflag   // 额外的类型信息标志，此处为类型的 flag 标志，主要用于反射
  align      uint8   // 对应变量与该类型的内存对齐大小
  fieldAlign uint8   // 对应类型结构体的内存对齐大小
  kind       uint8   // 类型的枚举值，包含 Go 中的所有类型，如：kindBool/kindInt 等
  equal      func(unsafe.Pointer, unsafe.Pointer) bool // 用于比较此对象的回调函数
  gcdata     *byte    // 存储垃圾收集器的 GC 类型数据
  str        nameOff
  ptrToThis  typeOff
}

type nameOff int32
type typeOff int32
```

从结构上来看，`runtime.eface`由于没有方法集的包袱，只需要存储类型和值信息指针即可，所以非常简单：

* `_type` 代表底层指向的类型信息；
* `data` 代表底层指向的值指针。

其所指向的底层数据的类型信息都保存在了`_type`属性中，比如：字节大小、类型标志、内存对齐、GC 数据等。

### 2.2 runtime.iface

`runtime.iface`包含了方法集数据，是平常使用较多的情况。定义如下：

```go
type iface struct {
  tab  *itab
  data unsafe.Pointer
}
```

与`runtime.eface`结构体类型一样，也是分为类型和值信息，但是其类型信息会存在`itab`结构体中：

```go
type itab struct {
  inter *interfacetype // 接口类型信息
  _type *_type         // 具体类型信息（和 runtime.eface 一样）
  hash  uint32         // _type.hash 的副本
  _     [4]byte        // 
  fun   [1]uinptr      // 底层数组，存储接口方法集的具体实现的地址
}
```

`itab.fun`属性中包含一组函数指针，实现了接口方法的动态分派，且每次在接口发生变更时都会被更新。虽然`fun`属性的类型是`[1]uintptr`只有一个元素，但其实就是存放了接口方法集的收个方法的地址信息。因为方法集是按照顺序存入的，所以获取的时候就从第一个方法顺序往后计算位置并获取即可。

`itab`结构体中的`interfacetype`是用来存储接口本身的信息的，其源码定义如下：

```go
type interfacetype struct {
  typ     _type      // 接口的具体类型信息
  pkgpath name       // 接口的包（package）名信息
  mhdr    []imethod  // 接口所定义的函数列表
}

// name is an encoded type name with optional extra data.
// See reflect/type.go for details.
type name struct {
	bytes *byte
}

type imethod struct {
  name nameOff
  ityp typeOff
}

type nameOff int32
type typeOff int32
```

### 2.3 小结

总体来讲，接口的数据结构基本表示形式比较简单，就是类型和值描述。再根据其具体的区别，例如是否包含方法集，具体的接口类型等进行组合使用。

![](http://cnd.qiniu.lin07ux.cn/markdown/1646742550695-62695c526b47.jpg)

## 三、类型断言

### 3.1 类型断言方式

在 Go 语言的接口使用中，常会进行类型断言（type assertio）：

```go
var i interface{} = "吃煎鱼"

// 进行变量断言，若不符合则会抛出 panic
s := i.(string)

// 进行安全断言，不会发生 panic
s, ok := i.(string)
```

在`switch case`中，会采用`(变量).(type)`的调用方式，再给予`case`不同的类型进行判断识别。写法如下：

```go
var i interface{} = "炸煎鱼"

// 进行 switch 断言
switch i.(type) {
case string:
  // do something
case int:
  // do something
case float64:
  // do something
}
```

### 3.2 实现逻辑

在 Go 语言的背后，类型断言其实是在编译器翻译后，根据`runtime.iface`和`runtime.eface`分别对应了下述方法：

```go
func assertI2I(inter *interfacetype, i iface) (r iface)
func assertI2I2(inter *interfacetype, i iface) (r iface, b bool)
func assertE2I(inter *interfacetype, e eface) (r iface)
func assertE2I2(inter *interfacetype, e eface) (r iface, b bool)
```

比如，对于`assertI2I2`方法，实现如下：

```go
func assertI2I2(inter *interfacetype, i iface) (r iface, b bool) {
  tab := i.tab
  if tab == nil {
    return
  }
  if tab.inter != inter {
    tab = getitab(inter, tab._type, true)
    if tab == nil {
      return
    }
  }
  r.tab = tab
  r.data = i.data
  b = true
  return
}
```

主要就是根据接口的类型信息进行一轮判断和识别，基本就完成了。核心在于`getitab`方法。

## 四、使用注意

### 4.1 值接受者和指针接收者

对于类型实现具体接口的方法集时，是使用值接收者还是指针接收者来声明，是容易产生问题的地方。

#### 4.1.1 示例

演示代码如下：

```go
type Human interface {
  Say(s string) error
  Eat(s string) error
}

type TestA struct{}

func (t TestA) Say(s string) error {
  fmt.Printf("说煎鱼：%s\n", s)
  return nil
}

func (t *TestA) Eat(s string) error {
  fmt.Printf("吃煎鱼：%s\n", s)
  return nil
}

func main() {
  var h Human = &TestA{}
  _ = h.Say("催更")
  _ = h.Eat("真香")
}
```

这里定义的`TestA`结构体实现了`Human`接口，`TestA.Say`和`TestA.Eat`方法在声明时，其值接收者类型并不相同：

* `Say`方法的值接收者是对象；
* `Eat`方法的值接收者是指针对象。

最终的输出结果是：

```
说煎鱼：催更
吃煎鱼：真香
```

#### 4.1.2 值和指针

修改演示代码中的`main`函数：

```go
func main() {
  var h Human = TestA{}
  _ = h.Say("催更")
  _ = h.Eat("真香")
}
```

这将会导致代码在编译的时候出现如下报错：

```
./main.go:23:6: cannot use TestA literal (type TestA) as type Human in assignment:
 TestA does not implement Human (Eat method has pointer receiver)
```

根据错误信息可以知道，是因为`TestA`未能实现`Human`接口而导致的错误。

由于`Eat`方法的值接收者是指针对象，对于`TestA{}`值来说，是没有`Eat`方法的。所以当`h`被赋值为`TestA{}`时，就会出现没有`Eat`方法，导致接口的校验未通过。

那为什么示例代码中将`h`赋值为`&TestA{}`时会有`Say`方法呢？这是 Go 的编译器在背后做了一些事情。

因此，如果实现了一个值对象的接收者时，也会相应拥有了一个指针接收者。两者并不会互相影响，因为值对象会产生值拷贝，对象会独立开来。

而指针对象的接收者不行，因为指针引用的对象在应用上是期望能够直接对源接收者的值进行修改的。若有支持值接收者，显然是不符合其语义的。

### 4.1.3 如何使用

实现接口时是选择值接收者还是指针接收者，其实应根据业务逻辑的期望来决定。

如果想使用指针接收者，可以考虑是否有以下的诉求：

* 期望接收者能够直接修改源值；
* 期望在大结构体的情况下，性能更好，可以在理论上避免每次值拷贝，但也会有别的开销。

如果两种方式使用都没有区别，则进行适度的统一即可。

