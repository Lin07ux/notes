> 转摘：[面试官：两个 nil 比较结果是什么？](https://mp.weixin.qq.com/s/D8XJn8WkhNrEfHZw2ZycKw)。
> 
> 延伸：[GopherCon 2016: Francesc Campoy - Understanding nil](https://www.youtube.com/watch?v=ynoY2xz-F8s)。

### 1. nil 的定义

在 Go 官方文档中，对`nil`的定义如下：

```go
// nil is a predeclared identifier representing the zero value for a
// pointer, channel, func, interface, map or slice type.
var nil Type // Type must be a pointer, channel, func, interface, map, or slice type
```

`nil`是一个预先声明的标识符，代表指针（`pointer`）、通道（`channel`）、函数（`func`）、接口（`interface`）、哈希表（`map`）、切片（`slice`）的零值。就像布尔类型的零值是`false`、整型的零值是`0`一样。

### 2. 深入理解 nil

**`nil`不是关键字**。

先看一段代码：

```go
func main() {
  nil := "this is nil"
  fmt.Println(nil)  // This is nil
}
```

如果改成下面的：

```go
func main() {
  nil := "this is nil"
  fmt.Println(nil)  // This is nil
  
  var slice []string = nil // cannot use nil (type string) as type []string in assignment
  fmt.Println(slice)
}
```

这时候编译就会报错，因为这里的`nil`是一个 string 类型。所以可以确定，`nil`在 Go 语言中并不是关键字，可以随意的定义变量名为`nil`（这并不是好的行为）。

### 3. nil 的默认类型

一般预声明标识符都会有一个默认类型，比如 Go 语言中的`itoa`默认类型就是 int，那么`nil`的默认类型呢？

```go
func main() {
  var value = nil  // use of untyped nil
  fmt.Printf("%T\n", value)
}
```

编译时就报错说使用了无类型的`nil`，所以：

**`nil`是没有默认类型的，它的类型具有不确定性，在使用它的时候必须要提供足够的信息，以能够让编译器推断出`nil`期望的类型。**


### 4. nil 的比较

`nil`的比较分为两种情况：

* `nil`标识符的比较
* `nil`值的比较

Go 中的值都是有类型的，所以`nil`值的比较又分为相同类型和不同类型的`nil`比较。

#### 4.1 nil 标识符比较

对于`nil`直接进行比较：

```go
fmt.Println(nil == nil)
```

在编译时会报错：`invalid operation: nil == nil (operator == not defined on nil)`。

也就是说，**`==`操作符号对于`nil`来说是一种未定的操作**，所以是是不可以比较两个`nil`的。

#### 4.2 相同类型 nil 值比较

如下：

```go
func main() {
  // pointer 指针类型的 nil 比较
  fmt.Println((*int64)(nil) == (*int64)(nil))
  
  // channel 通道类型的 nil 比较
  fmt.Println((chan int)(nil) == (chan int)(nil))
  
  // func 函数类型的 nil 比较
  fmt.Println((func())(nil) == (func())(nil))
  
  // interface 接口类型的 nil 比较
  fmt.Println((interface{})(nil) == (interface{})(nil))
  
  // map 哈希表类型的 nil 比较
  fmt.Println((map[string]int)(nil) == (map[string]int)(nil))
  
  // slice 切片类型的 nil 比较
  fmt.Println(([]int)(nil) == ([]int)(nil))
}
```

在编译的时候会报如下错误：

```shell
./nil.go:13:28: invalid operation: (func())(nil) == (func())(nil) (func can only be compared to nil)
./nil.go:17:36: invalid operation: (map[string]int)(nil) == (map[string]int)(nil) (map can only be compared to nil)
./nil.go:19:27: invalid operation: ([]int)(nil) == ([]int)(nil) (slice can only be compared to nil)
```

也就是说，`pointer`、`channel`、`interface`类型的`nil`值是可以相互比较的，因为这几种类型是定义了`==`操作符的；而`func`、`map`、`slice`类型的`nil`值是不能相互比较的，只能与`nil`标识符相比较，因为这几种类型没有定义`==`操作符。

### 4.3 不同类型的 nil 值比较

```go
func main()  {
  var ptr *int64 = nil
  var cha chan int64 = nil
  var fun func() = nil
  var inter interface{} = nil
  var ma map[string]string = nil
  var slice []int64 = nil
 
  fmt.Println(ptr == cha)
  fmt.Println(ptr == fun)
  fmt.Println(ptr == inter)
  fmt.Println(ptr == ma)
  fmt.Println(ptr == slice)

  fmt.Println(cha == fun)
  fmt.Println(cha == inter)
  fmt.Println(cha == ma)
  fmt.Println(cha == slice)

  fmt.Println(fun == inter)
  fmt.Println(fun == ma)
  fmt.Println(fun == slice)

  fmt.Println(inter == ma)
  fmt.Println(inter == slice)

  fmt.Println(ma == slice)
}
```

编译结果：

```shell
./nil.go:14:18: invalid operation: ptr == cha (mismatched types *int64 and chan int64)
./nil.go:15:18: invalid operation: ptr == fun (mismatched types *int64 and func())
./nil.go:17:18: invalid operation: ptr == ma (mismatched types *int64 and map[string]string)
./nil.go:18:18: invalid operation: ptr == slice (mismatched types *int64 and []int64)
./nil.go:20:18: invalid operation: cha == fun (mismatched types chan int64 and func())
./nil.go:22:18: invalid operation: cha == ma (mismatched types chan int64 and map[string]string)
./nil.go:23:18: invalid operation: cha == slice (mismatched types chan int64 and []int64)
./nil.go:25:18: invalid operation: fun == inter (operator == not defined on func)
./nil.go:26:18: invalid operation: fun == ma (mismatched types func() and map[string]string)
./nil.go:27:18: invalid operation: fun == slice (mismatched types func() and []int64)
./nil.go:27:18: too many errors
```

可以看到，只有`pointer`和`channel`类型的`nil`可以与`interface`类型的`nil`相比较，其他类型之间是不能相互比较的。

> 为什么`pointer`和`channel`类型的`nil`可以与`interface`类型的`nil`相比较，暂不清楚。

### 5. nil 在不同类型中使用需要注意的问题

#### 5.1 interface 与 nil

先看示例：

```go
// 空接口
type Err interface {}

// 实现了 Err 接口的 struct
type err struct {
  Code int64
  Msg  string
}

func Todo() Err {
  var res *err
  return res
}

func main() {
  fmt.Println(Todo() == nil) // false
}
```

这里输出的结果是`false`。这是因为：

指针类型的变量`res`，其零值为`nil`。此时如果`res`直接与`nil`标识符进行比较，得到的结果就是`true`。由于`Todo`函数声明的返回值类型是`Err`接口类型，所以调用`Todo()`得到的结果就是一个零值`nil`的接口类型值。**interface 不是单纯的值，而是分为类型和值，只有类型和值都同时为`nil`的情况下，interface 的`nil`才会等同于`nil`的值。**


#### 5.2 map 与 nil

**值为`nil`的 map 类型的变量，可以安全的进行取值，但是不能写入**，否则会触发 panic 错误：

```go
func main() {
  var m map[string]string
  fmt.Println(m["message"]) // ""
  m["message"] = "Golang"   // panic: assignment to entry in nil map
}
```

所以使用 map 类型时一定要使用`make`进行初始化。


#### 5.3 chanel 与 nil

**关闭值为`nil`的 channel 会引发 panic；读写值为`nil`的 channel 都会造成永远阻塞**。

```go
func main() {
  var ch chan int
  close(ch)  // panic: close of nil channel
}
```

#### 5.4 slice 与 nil

**值为`nil`的切片不能通过索引取值，但可以进行其他操作。**

```go
func main() {
  var slice []int64
  fmt.Println(len(slice)) // 0
  fmt.Println(cap(slice)) // 0
  for range slice {
    //
  }
  fmt.Println(slice[0]) // panic: runtime error: index out of range [0] with length 0
}
```

#### 5.5 方法接收者与 nil

在定义 struct 时，可以为其附加一些方法：

```go
type man struct {
  Name string
}

func (m *man)GetName() string {
  return "Golang"
}

func main() {
  var m *man
  fmt.Println(m.GetName()) // "Golang"
}
```

此时是可以正常调用和输出的。如果将`GetName()`方法改为如下的定义：

```go
func (m *man)GetName() string {
  return m.Name
}
```

然后再调用，则会抛出 panic 错误：`panic: runtime error: invalid memory address or nil pointer dereference`。

> 如果定义成`func (m man)GetName() string { return m.Name }`那么调用的时候不会报错，但是输出的是空字符串。

**所以为了程序健壮性，最好再做一次指针判空处理。**

#### 5.6 pointer 与 nil

**空指针是一个没有任何值的指针。**

```go
func main() {
  var a = (*int64)(unsafe.Pointer(uintptr(0x0)))
  fmt.Println(a == nil)  // true
}
```

