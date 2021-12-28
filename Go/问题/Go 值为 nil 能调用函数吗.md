> [Go 读者提问：值为 nil 能调用函数吗？](https://mp.weixin.qq.com/s/mH51UmolTqCAb7E0LxEjDA)

### 1. 问题

下面的程序能否正常执行：

```go
type T struct{}

func (t *T) Hello() string {
  if t == nil {
    fmt.Println("脑子进煎鱼了")
    return ""
  }
  return "煎鱼进脑子了"
}

func main() {
  var t *T
  t.Hello()
}
```

### 2. 答案

可以正常运行，且运行结果为：

```
脑子进煎鱼了
```

### 3. 解释

Go 中结构体的方法只是一种书写上的语法糖，在调用时，结构体实例会作为方法的第一个参数（接收者）传入。因为方法是允许接收一个值为 nil 的参数的，所以结构体实例的值为 nil 依旧可以调用该结构体类型的方法。

实际上，在 Go 表达式中，`Expression.Method()`的语法所调用的方法完全由`Expression`的类型决定。其调用函数的指向不是由该表达式的特定运行时的值来决定。

也就是说：

```go
func (p *SomeType) SomeMethod(firstArg int) {
  // TODO
}
```

本质上是：

```go
func SomeTypeSomeMethod(p *SomeType, firstArg int) {
  // TODO
}
```

由于参数`p *SomeType`是有具体上下文累心的，自然也就能调用到相应的方法。如果没有任何上下文类型，例如`nil.SomeMethod()`，那肯定就是无法运行的。

所以，调用类型的方法与实例的值是不是 nil 没有太多直接影响，只要有预期的上下文类型就可以了。



