## 一、基础

### 1.1 定义

Go 语言中 string 类型的结构定义如下：

```go
// string is the set of all strings pf 8-bit bytes, conventionally but not
// necessarily representing UTF-8-encoded text. A string may be empty, but
// not nil. Values of string type are ummutable.
type string string
```

从 string 定义的注释中可以知道，Go 中的字符串是一个 8 位字节的集合，通常但不必须代表 UTF-8 编码的文本。Go string 可以为空，但不能是 nil。而且，**string 的值是不可变的**。

### 1.2 底层结构

string 类型在底层中也是一个结构体，定义如下：

```go
type stringStruct struct {
  str unsafe.Pointer
  len int
}
```

可以看到 string 的底层结构 stringStruct 与 slice 的结构类似：`str`字段是一个指针，指向的是某个数组的首地址，`len`字段代表的就是数组长度。

string 实例化的代码如下：

```go
//go:nosplit
func gostringnocopy(str *byte) string {
  ss := stringStruct{str: unsafe.Pointer(str), len: findnull(str)}
  s := *(*string)(unsafe.Pointer(&ss))
  return s
}
```

可以看到，初始化 string 实例时，传入的是一个 byte 类型的指针。所以，string 类型底层本质上是一个 byte 类型的数组，示意图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1638859305712-5731908639fa.jpg)

在 Go 语言中，string 类型被设计为不可更改的，这样的好处是：在并发场景下，可以在不加锁的控制下，多次使用同一字符串，在保证高效共享的情况下而不用担心安全问题。

string 类型虽然是不能更改的，但是可以被替换。因为 stringStruct 中的 str 指针是可以改变的，只是指针指向的内容是不可改变的。也就是说每一次更改字符串，都需要重新分配一个内存，之前占用的空间则会被 GC 回收。



