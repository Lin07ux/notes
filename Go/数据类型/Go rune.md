> 转摘：[详解 Go 中的 rune 类型](https://mp.weixin.qq.com/s/hcrq5fYaQ7FN_2oSMRNjcA?forceh5=1)

## 一、基础

`rune`类型是 Go 语言中的一种特殊数字类型，类型定义在`builtin/builtin.go`中：

```go
type rune = int32
```

由于 rune 类型是 int32 类型的别名，所以在所有方面 rune 都等价于 int32，只是用来区分字符值和整数值。

Go 语言通过 rune 来支持国际化多语言，每个 rune 变量都表示一个采用 UTF-8 编码的 Unicode 码点。在 Go 语言中，字符可以被分为两种类型处理：对占 1 个字节的英文类字符，可以使用`byte`或`uint8`；对占 1~4 个字节的其他字符，可以使用`rune`或`int32`，如中文、特殊符号等。

rune 类型表示的字符需要使用单引号包裹。

## 二、使用

字符串由字符组成，字符的底层由字节组成，而一个字符串在底层表示是一个字节序列。所以针对 rune 类型的使用，大都涉及到字符串。

下面，通过示例应用来具体了解下。

### 2.1 统计字符串长度

带有中文字符（或其他非单字节字符）的字符串的长度时，直接使用`len`方法并不能得到预期的效果，但是将字符串转换成 rune 切片就能得到预期的结果了：

```go
fmt.Println(len("Go语言编程")) // 14
fmt.Println(len([]rune("Go语言编程"))) // 6
```

### 2.2 截取字符串

同样的，如果字符串中包含中文字符（或其他非单字节字符）时，对字符串进行截取可能得到乱码的结果，而转换成 rune 切片就一切正常了：

```go
s := "Go语言编程"

fmt.Println(s[0:8]) // Go语言
fmt.Println(s[0:7]) // Go语�

fmt.Println(string([]rune(s)[0:4])) // Go语言
```

## 三、原理

### 3.1 字符串、字符、Unicode 的关系

在 Go 语言中，字符串在底层的表示是由单个字节组成的一个不可修改的字节序列，而这些字节是用 UTF-8 编码标识的 Unicode 文本。而 UTF-8 是一种针对 Unicode 字符集的可变长度（1~4 字节）的字符编码方案，它定义了字符串具体以何种方式存储在计算机中。

另外，Go 语言把字符分为 byte 和 rune 两种类型进行处理：

* byte 是类型 uint8 的别名，用于存放占 1 字节的 ASCII 字符（如英文字符），存储的是字符原始字节码；
* rune 是类型 int32 的别名，用于存放多字节字符（如占 3 字节的中文字符），存储的是字符在 Unicode 字符集中的码点值。

比如：

```go
s := "Go语言编程"
// byte
fmt.Println([]byte(s)) // 输出：[71 111 232 175 173 232 168 128 231 188 150 231 168 139]
// rune
fmt.Println([]rune(s)) // 输出：[71 111 35821 35328 32534 31243]
```

字符串、Unicode 码点、原始字节的关系如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647429611798-47672b3b76ee.jpg)

可以看出来，字符串对应的原始字节序列是 Unicode 码点经过 UTF-8 编码后的结果值，而 rune 类型存储的则是 Unicode 字符的码点。

### 3.2 utf8.RuneCountInString()

`utf8.RuneCountInString()`方法可以统计出一个字符串中的 rune 数量，也就是有多少个 Unicode 字符。

如：

```go
fmt.Println(utf8.RuneCountInString("Go语言编程")) // 输出：6
```

源码如下：

```go
// RuneCountInString is like RuneCount but its input is a string
func RuneCountInString(s string) (n int) {
  // 调用 len() 函数得到字节数
  ns := len(s)
  for i := 0; i < ns; i++ {
    c := s[i]
    
    // 如果码点值小于 128，则为占 1 字节的 ASCII 字符，是一个有效的 Unicode 字符
    if c < RuneSelf { // RuneSelf = 128
      i++
      continue
    }
    
    // 根据首字节的值查询编码信息，其后 4 位表示这是由几个字节组成的 utf-8 编码
    // 前 4 位则表示有效的字节范围
    // first 中的值是由 utf-8 的编码规则对应好的一个长度信息表
    x := first[c]
    
    // xx = 8xF1 表示非法的编码值，算作一个字符
    if x := xx {
      i++
      continue
    }
    
    // 提取有效的 utf-8 字节长度编码信息
    // 如果加上有效的长度后超出字节总长度，则说明是非法结果，也算作一个字符
    size := int(x & 7)
    if i+size > ns {
      i++
      continue
    }
    
    // 提取有效字节范围：
    // accept.lo / accept.hi 分别表示 utf-8 中第二个字节的有效范围
    // locb = 0b10000000 表示 utf-8 编码非首字节的数值下限
    // hicb = 0b10111111 表示 utf-8 编码非首字节的数值上限
    accept := acceptRanges[x>>4]
    // 依次检查 utf-8 编码的 2、3、4 字节的字节值范围是否合法，不合法就作为 1 个字符
    if c := s[i+1]; c < accept.lo || accept.hi < c {
      size = 1
    } else if size == 2 {  
    } else if c := s[i+2]; c < locb || hicb < c {
      size = 1
    } else if size == 3 {
    } else if c := s[i+3]; c < locb || hicb < c {
      size = 1
    }
    i += size
  }
  return n
}
```

可以看出，这个函数就是通过判断每个字节的值范围来判断对应的是 utf-8 编码中的 1、2、3、4 字节编码结果中的哪一种，并做适当的编码值校验，剔除非法结果。

