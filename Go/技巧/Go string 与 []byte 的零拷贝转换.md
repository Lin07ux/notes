> 转摘：[面试官：搞Go连这2个Header都不了解？回去等通知哈](https://mp.weixin.qq.com/s/Co3q-GprhTapmSX7RZIsRQ)

### 1. 基础

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

String 结构体中的`Data`字段指向的是一个`[]byte`类型的切片，示意图如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639377668956-ff65d6db4277.jpg)

### 2. 零拷贝转换

常会有需要将一个长字符串转换`[]byte`切片的操作，为了避免内存拷贝操作，可以结合 stirng 和 slice 在底层结构体的相似性，进行类型的强转处理。

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

也就是说，因为 SliceHeader 和 StringHeader 中的`Data`字段是 uintptr 类型的值，而 Go 语言只有值传递，所以上述代码中会出现将`Data`作为值拷贝的情况，这就会导致**无法保证它所引用的数据没有被垃圾回收（GC）**。

为了避免这个问题，应该使用一个单独的、具有正确类型的、指向底层数据的指针变量来进行引用：

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

### 3. 更简单的零拷贝转换

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

