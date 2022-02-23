> 转摘：[浅析 unsafe.Pointer 与 uintptr](https://mp.weixin.qq.com/s/NG0k9KpBry9bC_m30tmu1A)

Go 中指针类型、`unsafe.Pointer`、`uintptr`之间可以互相转换使用，关系图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645596156813-58245b2ae77c.jpg)

### 1. 普通指针类型

一般将`*T`看做指针类型，表示一个指向 T 类型的指针。

Go 是强类型语言，声明变量之后，变量的类型是不可以改变的，不同类型的指针也不允许相互转换。

例如：

```go
func main() {
  i := 30
  iPtr1 := &i
  
  var iPtr2 *int64 = (*int64)(iPtr)
  
  fmt.Println(iPtr2)
}
```

在编译的时候就会报错：`cannot convert iPtr1 (type *int) to type *int64`，提示不能进行强制转换。

如果要实现类型转换，就需要使用到`unsafe.Pointer`类型了。

### 2. unsafe.Pointer

`unsafe.Pointer`**通用指针类型**，是一种特殊类型的指针，可以包含任意类型的地址，能实现不同的指针类型之间的转换，类似于 C 语言中的`void *`指针。

`unsafe.Pointer`的定义如下：

```go
type ArbitraryType int
type Pointer *ArbitraryType
```

可以看出，`unsafe.Pointer`实际上是`*int`。

官方文档中描述了`unsafe.Pointer`的四种操作规则：

1. 任何类型的指针都可以转换成`unsafe.Pointer`；
2. `unsafe.Pointer`可以转换成任何类型的指针；
3. `uintptr`可以转换为`unsafe.Pointer`；
4. `unsafe.Pointer`可以转换为`uinptr`。

不同类型的指针进行相互转换实际上是运用了前两条规则：

```go
func main() {
  i := 30
  iPtr1 := &i
  
  var iPtr2 *int64 = (*int64)(unsafe.Pointer(iPtr1))
  *iPtr2 = 8
  
  fmt.Println(i)  // 8
}
```

这段代码可以把`*int`转换为`*int64`类型，并且成功的对`*int64`类型进行操作，而且实际上也影响到了原`int`变量的值。

可以说，**`unsafe.Pointer`是桥梁，可以让任意类型的指针实现相互转换**。

Go 语言是不支持指针运算的，如果要进行指针运算，就需要使用到`uintptr`了。

### 3. uintptr

`uintptr`是 Go 的内置类型，表示无符号整数，可以存储一个完整的地址。源码定义如下：

```go
// uintptr is an integer type that is large enough to hold the bit pattern of
// any pointer
type uintptr uintptr
```

`uinptr`常用于指针运算，只需要将`unsafe.Pointer`类型转换成`uintptr`类型，做完加减法后，再转换成`unsafe.Pointer`，然后通过`*`操作即可完成取值或者修改值操作。

下面是一个使用`uintptr`通过指针偏移修改结构体成员的例子：

```go
type Admin struct {
  Name string
  Age  int
}

func main() {
  admin := Admin{"seekload", 18}
  ptr := &admin

  name := (*string)(unsafe.Pointer(ptr))
  *name = "四哥"
  fmt.Println(*ptr) // {四哥 18}
  
  age := (*int)(unsafe.Pointer(uintptr(unsafe.Pointer(ptr)) + unsafe.Offsetof(ptr.Age)))
  *age = 35
  fmt.Println(*ptr) // {四哥 35}
}
```

这里用到的`unsafe.Offsetof`方法的作用是返回成员变量 x 在其所属结构体当中的偏移量，即返回结果头初始内存地址到 x 之间的字节数。因为结构体初始地址就是第一个成员的地址，所以作为`Admin`结构体的第一个成员的`Name`是不需要偏移的。

这段代码中，为了获取`admin.age`的指针，通过`uintptr`类型进行加减运算，然后再将结果转换成`unsafe.Pointer`类型，再将`unsafe.Pointer`转换成`*int`类型，从而得到正确的指针了。

### 4. 总结

记住`*T`、`unsafe.Pointer`、`uintptr`之间的关系和各自的用途：

1. `unsafe.Pointer`可以实现不同类型指针之间的相互转换；
2. `unintptr`搭配着`unsafe.Pointer`使用，即可实现指针运算。

同时，由于`unsafe`包涉及到内存操作，绕过了 Go 本身设计的安全机制，而且包内的逻辑可能不稳定，会由于一些不当的操作破坏内存，从而造成问题。

