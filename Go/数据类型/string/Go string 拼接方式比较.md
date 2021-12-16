> 转摘：[Go语言如何高效的进行字符串拼接（6种方式进行对比分析）](https://mp.weixin.qq.com/s/-beSznRTsD3oVxA4KrCyHw)

Go 语言中，string 类型是不可变的，但是可以被替换。字符串的拼接也大都使用这种方式来实现的。

字符串拼接有多种方法，这些方法按照使用场景来比较性能，排行如下：

`strings.Join ≈ strings.Builder > bytes.Buffer > []byte 转换 string > "++ > fmt.Sprintf`

使用`strings.Builder`时能够提前初始好合适的容量会有更好的性能，而如果只是少量的字符串拼接直接使用`+`也是更优的方案。

### 1. 原生拼接方式“+”

Go 语言原生支持使用`+`操作符直接对两个字符串进行拼接。示例如下：

```go
var s string
s += "asong"
s += "真帅"
```

这种方式使用起来最简单，拼接时，会对字符串进行遍历，计算并开辟一个新的空间来存储原来的两个字符串。

### 2. fmt.Sprintf

Go 语言中默认使用函数`fmt.Sprintf`进行字符串格式化，所以也可以使用这种方式进行字符串拼接：

```go
str := "asong"
str = fmt.Sprintf("%s%s", str, str)
```

`fmt.Sprintf`实现原理主要是使用了反射，会产生性能的损耗。

### 3. String.Builder

Go 语言中的`strings`库提供了`string.Builder`类型，可以使用`WriteString()`进行字符串拼接：

```go
var builder strings.Builder
builder.WriteString("asong")
builder.String()
```

而`strings.Builder`的实现原理也很简单，结构如下：

```go
type Builder struct {
  addr *Builder // of receiver, to detect copies by value
  buf  []byte   // 
}
```

`addr`字段主要是做 copycheck，`buf`字段是一个 byte 类型的切片，用来存放字符串内容。提供的`WriteString()`方法就是向切片`buf`中追加数据：

```go
func (b *Builder) WriteString(s string) (int, error) {
  b.copycheck()
  b.buf = append(b.buf, s...)
  return len(s), nil
}
```

提供的`String`方法就是将`buf`转换为 string 类型。为了避免内存拷贝，使用了强制转换：

```go
func (b *Builder) String() string {
  return *(*string)(unsafe.Pointer(&b.buf))
}
```

### 4. bytes.Buffer

因为 string 类型底层就是一个 byte 数组，所以可以使用 Go 语言的`bytes.Buffer`类型实例进行字符串拼接。`bytes.Buffer`是一个缓冲 byte 类型数据的缓冲器。使用方式如下：

```go
buf := new(bytes.Buffer)
buf.WriteString("asong")
buf.String()
```

`bytes.Buffer`底层也是一个`[]byte`切片，结构体定义如下：

```go
type Buffer struct {
  buf      []byte // contents are the types buf[off : len(buf)]
  off      int    // read at &buf[off], write at &buf[len(buf)]
  lastRead readOp // last read operation, so that unread* can work correctly.
}
```

因为`bytes.Buffer`可以持续向 Buffer 尾部写入数据，从 Buffer 头部读取数据，所以`off`字段用来记录读取位置，再利用切片的`cap`特性来知道写入位置。

`bytes.Buffer`类型的`WriteString`方法用来拼接字符串，实现如下：

```go
func (b *Buffer) WriteString(s string) (n int, err error) {
  b.lastRead = opInvalid
  m, ok := b.tryGrowByReslice(len(s))
  if !ok {
    m = b.grow(len(s))
  }
  return copy(b.buf[m:], s), nil
}
```

`bytes.Buffer`中的切片在创建时并不会申请内存块，只有在往里写数据时才会申请，首次申请的大小即为写入数据的大小。如果写入的数据小于 64 字节，则按 64 字节申请。采用动态扩展 slice 的机制，字符串追加采用 copy 的方式将追加的部分拷贝到尾部。`copy`函数是内置的拷贝函数，可以减少内存分配。

但是在将其转为 string 类型的`String`函数中，依旧使用了标准类型，所以会发生内存分配：

```go
func (b *Buffer) String() string {
  if b == nil {
    // Special case, useful in debugging.
    return "<nil>"
  }
  return string(b.buf[b.off:])
}
```

### 5. strings.Join

`strings.Join`方法可以将一个 string 类型的切片拼接成一个字符串，可以定义连接操作符，使用如下：

```go
baseSlice := []string{"asong", "真帅"}
strings.Join(baseSlice, "")
```

`strings.Join`也是基于`string.Builder`来实现的，代码如下：

```go
func Join(elems []string, sep string) string {
  switch len(elems) {
  case 0:
    return ""
  case 1:
    return elems[0]
  }
  n := len(sep) * (len(elems) - 1)
  for i := 0; i < len(elems); i++ {
    n += len(elems[i])
  }
  
  var b Builder
  b.Grow(n)
  b.WriteString(elems[0])
  for _, s := range elems[1:] {
    b.WriteString(sep)
    b.WriteString(s)
  }
  return b.String()
}
```

有一点不同的在于，`strings.Join`方法中在拼接之前调用了`b.Grow(n)`方法，进行初步的容量分配。而其中`n`就表示全部参与拼接的字符串的长度加上分隔符的长度之和。因为传入的切片长度固定，所以提前进行容量分配可以减少内存分配，比较高效（非并发安全）。

### 6. append

因为 string 类型的底层也是 byte 类型数组，所以可以重新声明一个切片，使用 append 函数进行拼接。示例如下：

```go
buf := make([]byte, 0)
base = "asong"
buf = append(buf, base...)
string(base)
```

> 如果想减少内存分配，在将`[]byte`转换为`string`类型时可以考虑使用强制转换。


