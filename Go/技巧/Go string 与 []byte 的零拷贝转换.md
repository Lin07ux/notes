> 转摘：
> 
> 1. [面试官：搞Go连这2个Header都不了解？回去等通知哈](https://mp.weixin.qq.com/s/Co3q-GprhTapmSX7RZIsRQ)
> 2. [你所知道的 string 和 []byte 转换方法可能是错的](https://mp.weixin.qq.com/s/T--shUtArU-asFthtR7waA)

### 1. 基础知识

Slice 在运行时对应的是`reflect.SliceHeader`结构体：

```go
type SliceHeader struct {
  Data uintptr // 指向具体的底层数组
  Len  int // 表示切片的长度
  Cap  int // 表示切片的容量
}
```

String 在运行时对应的是`reflect.StringHeader`结构体：

```go
type StringHeader struct {
  Data uintptr // 存放指针，指向具体的存储数据的内存区域
  Len  int // 字符串的长度
}
```

Slice 和 String 结构体中的`Data`字段都是一个指向底层数据的指针值。另外，String 结构体中的`Data`指向的是一个`[]byte`类型的切片，示意图如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639377668956-ff65d6db4277.jpg)

常会有需要将一个长字符串转换`[]byte`切片或者将大`[]byte`转为 string 的操作，但是直接转换会牵扯到内存拷贝。

为了避免内存拷贝操作，可以结合 stirng 和 slice 在底层结构体的相似性，通过`unsafe.Pointer`进行类型的强转处理，从而提升性能。

### 2. 初级零拷贝转换

#### 2.1 实现代码

常见的转换代码如下：

```go
func string2bytes(s string) []byte {
  stringHeader := (*reflect.StringHeader)(unsafe.Pointer(&s))
  
  bh := reflect.SliceHeader{
    Data: stringHeader.Data,
    Len:  stringHeader.Len,
    Cap:  stringHeader.Len,
  }
  
  return *(*[]byte)(unsafe.Pointer(&bh))
}
```

但这其实是错误的，官方明确表示：

> the Data field is not sufficient to guarantee the data it references will not be garbage collected, so programs must keep a separate, correctly typed pointer to the underlying data.

也就是说，因为 SliceHeader 和 StringHeader 中的`Data`字段是 uintptr 类型的值，并不是一个真实的指针变量，不会持有它指向的数据。而 Go 语言只有值传递，在上述代码中会出现将`Data`作为值拷贝的情况，这就会导致**无法保证它所引用的数据没有被垃圾回收（GC）**。

#### 2.2 GC 问题验证

可以使用下面的代码来验证上述转换存证 GC 问题：

```go
package main

import (
  "fmt"
  "reflect"
  "runtime"
  "unsafe"
)

func main() {
  fmt.Printf("%s\n", test())
}

func test() []byte {
  defer runtime.GC()
  x := make([]byte, 5)
  x[0] = 'h'
  x[1] = 'e'
  x[2] = 'l'
  x[3] = 'l'
  x[4] = 'o'
  return StringToSliceByte(string(x))
}

func StringToSliceByte(s string) []byte {
  l := len(s)
  return *(*[]byte)(unsafe.Pointer(&reflect.SliceHeader{
    Data: (*(*reflect.StringHeader)(unsafe.Pointer(&s))).Data
    len:  l,
    Cap:  l,
  }))
}
```

> 因为静态字符串存储在 Text 区，不会被 GC 回收，所以使用了动态字符串替代。

运行上面的代码并不会按预期输出`hello`，而是会输出乱码，因为在`test()`方法执行完成时会强制进行 GC 处理，对应的数据就被 GC 回收了。而如果去掉`defer runtime.GC()`再运行，那么大概率就能输出预期的`hello`了。

### 3. 有效的零拷贝转换

有上述的分析和验证可知，对于`uintptr`类型的`Data`字段，任何对它的赋值都是不安全的。

但是在`runtime.Pointer`文档中确实有一个直接对`Data`赋值的例子。对此，文档中有详细的说明：

> the reflect data structures SliceHeader and StringHeader declare the field Data as a uintptr to keep callers from changing the result to an arbitrary type without first importing "unsafe". However, this means that SliceHeader and StringHeader are only valid when interpreting the content of an actual slice or string value.

也就是说，**只有当操作真实存在的 slice 或 string 的时候，SliceHeader 或 StringHeader 才是有效的。**而前面实现的零拷贝转换中，并没有实际存在的 slice，所以是不符合`unsafe.Pointer`使用规范的。

#### 3.1 实现代码

所以，为了避免这个问题，应该使用一个单独的、具有正确类型的、指向底层数据的指针变量来进行引用：

```go
func string2bytes(s string) []byte {
  stringHeader := (*reflect.StringHeader)(unsafe.Pointer(&s))
  
  var b []byte
  pbytes := (*reflect.SliceHeader)(unsafe.Pointer(&b))
  pbtyes.Data = stringHeader.Data
  pbytes.Len = stringHeader.Len
  pbytes.Cap = stringHwader.Len
  
  return b
}
```

#### 3.2 GC 问题验证

`uintptr`类型的指针是不能阻止数据被 GC 回收的，但是为什么使用上面这个新版的代码就能避免 GC 问题呢？实际上这是因为编译器对`*reflect.SliceHeader`和`*reflect.StringHeader`做了 [特殊处理](https://github.com/golang/go/issues/19168)，具体可以看对应的 issue。

可以使用自定义的类型来反向验证一下：

```go
type StringHeader struct {
  Data uintptr
  Len  int
}

type SliceHeader struct {
  Data uintptr
  Len  int
  Cap  int
}

func StringToSliceByte(s string) []byte {
  var b []byte
  l := len(s)
  p := (*SliceHeader)(unsafe.Pointer(&b))
  p.Data = (*StringHeader)(unsafe.Pointer(&s)).Data
  p.Len = l
  p.Cap = l
  return b
}
```

这段代码，使用了自定义的 StringHeader 和 SliceHeader 结构体，而不是 reflect 包中的。在运行的时候会发现又会出现 GC 问题，导致输出不正常。这就能反向验证了编译器确实对`*reflect.SliceHeader`和`*reflect.StringHeader`做了特殊的处理。

### 4. 其他的零拷贝转换

#### 4.1 改变 Data 字段类型

既然`unintptr`类型不能避免 GC 问题，那么可以考虑将`Data`字段改用其他的类型，比如`unsafe.Pointer`类型。

代码如下：

```go
type StringHeader struct {
  Data unsafe.Pointer
  Len  int
}

type SliceHeader struct {
  Data unsafe.Pointer
  Len  int
  Cap  int
}

func StringToSliceByte(s string) []byte {
  var b []byte
  l := len(s)
  p := (*SliceHeader)(unsafe.Pointer(&b))
  p.Data = (*StringHeader)(unsafe.Pointer(&s)).Data
  p.Len = l
  p.Cap = l
  return b
}
```

#### 4.2 不关心 Cap 的转换

如果只是期望单纯的转换，对容量（Cap）等字段值不敏感，也可以使用下面的方式：

```go
func string2bytes(s string) []byte {
  return *(*[]byte)(unsafe.Pointer(&s))
}
```

这样会比前面的方式性能略高一些，但是也会导致一个**小问题：转换后的切片的容量非常大。**

示例代码如下：

```go
func main() {
  s := "脑子进煎鱼了"
  v := string2bytes2(s)
  println(len(v), cap(v))
}

func string2bytes(s string) []byte {
  return *(*[]byte)(unsafe.Pointer(&s))
}
```

输出结果如下：

```
18 824633927632
```

一般还是推荐使用标准的 SliceHeader 和 StringHeader 方式进行转换，也便于其他人理解和维护。

#### 4.3 好用且简单的转换

一种更简单的做法的是彻底抛弃 reflect 包，使用 [gin 中的`bytesconv`](https://github.com/gin-gonic/gin/blob/master/internal/bytesconv/bytesconv.go)：

```go
func StringToBytes(s string) []byte {
  return *(*[]byte)(unsafe.Pointer(
    &struct {
      string
      Cap int
    }{s, len(s)},
  ))
}

func BytesToString(b []byte) string {
  return *(*string)(unsafe.Pointer(&b))
}
```

可以看到，string 转 bytes 的时候，使用的是结构体组合方式，定义了一个临时的结构体，包含了 string 类型的功能和字段，并增加了`Cap`字段以能正常的转成 slice 类型。


