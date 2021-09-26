> 转摘：[终于识破了这个 Go 编译器把戏](https://mp.weixin.qq.com/s/rAIhapDHrA7jQVr_uvQpEg)

### 1. 背景

定义一个带有`WriteGoCode()`方法的`Gopher`接口，同事定义了`person`结构体，并为其实现`WriteGoCode()`方法：

```go
type Gopher interface {
  WriteGoCode()
}

type person struct {
  name string
}

func (p person) WriteGoCode() {
  fmt.Printf("I am %s, i am writing go code!\n", p.name)
}
```

在 Go 语言中，只要某对象拥有接口的所有方法，那该对象即实现了该接口。对于下面的代码是可以正常编译和执行的：

```go
func Codeing(g Gopher) {
 g.WriteGoCode()
}

func main() {
  p := person{name: "Lin07ux"}
  Coding(p)
}

// output:
// I am Lin07ux, i am writing go code!
```

这里，`p`是`person`结构体的实例化对象，`Coding()`函数的入参是`Gopher`接口，`person`结构体实现了`Gopher`接口，因此`p`入参成功被运行。

### 2. 问题

如果将上面代码中的`Coding()`函数的入参改为`[]Gopher`类型，运行时提供的参数为`[]person`，如下所示：

```go
func Coding(gs []Gopher) {
  for _, g := range gs {
    g.WriteGoCode()
  }
}

func main() {
  p := []person{
    {name: "Lin07ux"},
    {name: "Lin'07"},
  }
  
  Coding(p)
}
```

但是，这个时候却无法编译通过：

```
./main.go:29:8: cannot use p (type []person) as type []Gopher in argument to Coding
```

明明`person`类型实现了`Gopher`接口，且当函数入参为`Gopher`类型时，能够顺利执行；但参数变为`[]Gopher`时却无法通过编译了，

### 3. 语法通用规则

这个问题在 Stack Overflow 上也有被讨论：[Type converting slices of interfaces](https://stackoverflow.com/questions/12753805/type-converting-slices-of-interfaces/12754757#12754757)。

**在 Go 中，有一个通用规则：即语法不应隐藏复杂/昂贵的操作。**

转换一个`string`到`interface{}`的时间复杂度是`O(1)`，转换`[]string`到`interface{}`复杂度同样也是一个`O(1)`操作，因为它还是一个单一值的转换。

如果要将`[]string`转换为`[]interface{}`，它是`O(N)`操作。因为切片的每个元素都必须转换为`interface{}`，这违背了 Go 的语法原则。

当前，此规则存证一个李炜：转换字符串。再将`string`转换为`[]byte`或`[]rune`时，即使需要`O(N)`操作，但 Go 会允许执行。

### 4. InterfaceSlice 问题

Ian Lance Taylor（Go 核心开发者）在 Go 官方仓库中也回答了这个问题：[InterfaceSlice](https://github.com/golang/go/wiki/InterfaceSlice)。他给出了这样做的两个主要原因：

* 原因一：类型为`[]interface{}`的变量不是 interface！它仅仅是一个元素类型恰好为`interface{}`的切片。
* 原因二：`[]interface{}`变量有特定大小的内存布局，在编译期间可知，这与`[]MyType`是不同的。

每个`interface{}`（运行时通过`runtime.eface`表示）占两个字长：一个字代表所包含内容的类型`_type`，另一个字表示锁包含的数据`data`或指向它的指针。

![](http://cnd.qiniu.lin07ux.cn/markdown/1632622120074-5c8aa7493f2d.jpg)

因此，类型为`[]interface{}`的长度为 N 的变量，它是由`N*2`个字长的数据块支持。而这与类型为`[]MyType`的长度为 N 的变量的数据块大小是不同的，因为后者的数据块是`N*sizeof(MyType)`字长。

**数据块的不同，造成的结果是编译器无法快速地将`[]MyType`类型的内容分配给`[]interface{}`类型的内容。**

同理，`[]Gopher`变量也是特定大小的内存布局（运行时通过`runtime.iface`表示）。这同样不能快速地将`[]MyType`类型的内容分配给`[]Gopher`类型。

![](http://cnd.qiniu.lin07ux.cn/markdown/1632622337917-f399f0a8fc96.jpg)

因此，Ian Lance Taylor 回答闭环了 Go 的语法通用规则：**Go 语法不应隐藏复杂/昂贵的操作，编译器会拒绝它们**。

### 5. 代码解决方案

对于文章开头的例子，如果需要`[]person`类型的`p`参数能够成功入参`Coding()`函数，需要对`p`进行一个`[]Gopher`类型的转换：

```go
func main() {
  p := []person{
    p := []person{
    {name: "Lin07ux"},
    {name: "Lin'07"},
  }
  
  var interfaceSlice []Gopher = make([]Gopher, len(p))
  for i, g := range p {
    interfaceSlice[i] = g
  }
  
  Coding(interfaceSlice)
}
```


