> 转摘：[Go 中类型转换的那些事](https://mp.weixin.qq.com/s/0-updq-YURiBzC7FxZj5pw)

Go 中的数据不会隐式的转变类型，所以需要使用 strconv 包进行一些类型转换。

strconv 包大致可以分成 string-数字类型转换、rune-string 类型转换。而常用的 stirng-数字类型转换又可以分为三大类：Parse/Format/Append，也就是将数字类型接自成字符串、数字类型格式化话成字符串和数字类型拼接三类方法。

## 一、Parse

Parse 系列函数主要有如下：

|  方法名       |  输入                       |  输出             |
|:------------:|:--------------------------:|:-----------------:|
| ParseBool    | s string                   | bool, error       |
| ParseInt     | s string, 基数 int, 位数 int | int64, error      |
| ParseUint    | s string, 基数 int, 位数 int | int64, error      |
| ParseFloat   | s string, 位数 int          | float64, error    |
| ParseComplex | s string, 位数 int          | complex128, error |

### 1.1 ParseBool

将字符串转换成布尔类型时，因为要转换的目标有限，而且结果也只有两个：true、false，所以这个函数非常简单，就是直接枚举长的像 bool 型的字符串，对应到 bool 结果上。

> `t`和`f`也会分别解析成 true 和 false。

```go
func ParseBool(str string) (bool, error) {
  switch str {
  case "1", "t", "T", "true", "TRUE", "True":
    return true, nil
  case "0", "f", "F", "false", "FALSE", "False":
    return false, nil
  }
  return false, syntaxError("ParseBool", str)
}
```

### 1.2 ParseInt/ParseUint

`ParseInt()`函数的主要逻辑是对要转换的数据进行预处理（处理正负号）和结果校验（数值范围校验），实质的转换操作是在`ParseUint()`函数中的。

`ParseUint()`函数的除了会做一些数据合法性的校验之外，核心的逻辑就是逐个循环参数的每一项进行转换操作。核心源码如下：

```go
func ParseUint(s string, base int, bitSize int) (uint64, error) {
  // ...
  for _, c := range []byte(s) {
    var d byte
    switch {
    case c == '_' && base0:
      underscores = true
      continue
    case '0' <= c && c <= '9':
      d = c - '0'
    case 'a' <= lower(c) && lower(c) <= 'z'
      d = lower(c) - 'a' + 10
    default:
      return 0, syntaxError(fnParseUint, s0)
    }

    if d >= byte(base) {
      return 0, syntaxError(fnParseUint, s0)
    }

    if n >= cutoff {
      // n*base overflows
      return maxVal, rangeError(fnParseUint, s0)
    }
    n *= uint64(base)
    
    n1 := n + uint64(d)
    if n1 < n || n1 > maxVal {
      // n+d overflows
      return maxVal, rangeError(fnParseUint, s0)
    }
    n = n1
  }

  if underscores && !underscoreOK(s0) {
    return 0, syntaxError(fnParseUint, s0)
  }

  return n, nil
}
```

`ParseUint()`函数中有对下划线的检查和处理。这里的下划线必须是在数字中间的，而且是相同进制的数字中间，且不能连续使用下划线。数字中间使用下划线只是为了增加可读性，没有什么特殊含义。

另外，使用下划线的时候，传入的`base`参数需要为 0，否则会抛出错误。另外，`bitSize`参数要设置正确，否则会出现溢出而返回最大值。

例如：

```go
func main() {
  var b string
  b = "12345_12345"
  s, _ := strconv.ParseUint(b, 0, 16)
  fmt.Print(s, math.MaxUint16) // 65535 65535
}
```

### 1.3 ParseFloat

ParseFloat 函数的逻辑稍微复杂点，不过也是类似的思路：检查系统位数 ——> 检查极限字符 ——> 拆分字符串 ——> 遍历字符串。

比如，对于字符串`"1.1"`来说，解析流程大致如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649257695704-2e483d9fa80b.jpg)

ParseFloat 首先选择 32/64位操作，32 位转换和 64 位转换差别不大。以 64 位转换为例，其先使用内部函数`atof64()`进行处理，该函数依次调用了三个重要的函数：

* `special()` 该函数名副其实，就是检查是否存在特殊情况，比如无穷和非数，但是它只能识别`infinity/inf/nan`；

* `readFloat()` 该函数将字符串拆解成 mantissa uint64 尾数、exp int 指数、neg 是否为负数、trunc 是否溢出、hex bool 是否为十六进制数、i int 占用字节数、ok bool 转换是否成功。有些这些基础的判断，后面就可以进行直接处理了。

* `atof64exact()`和`eiselLemire64`算法。对于`"1.1"`来说，`atof64exact()`就足够了，它使用 float64 对尾数进行转换，对指数进行处理，再与尾数相乘即可。

顺便看下 16 进制浮点数是如何转换成 10 进制浮点数的。比如，对于`"0x1a.2p1"`：

* `0x`是 16 进制标识符，`1a.2`是尾数，`p1`就是`e1`；
* 先将`1a.2`转换成二进制`11010.001`；
* 指数是 1，那么就将小数点向右移动一位，得到`110100.01`；
* 再将二进制转换成十进制：`110100.01 => 52.25`。

`atofHex()`其实就是做类似这样的事情（ParseFloat 对 16 进制处理的时候在`readFloat()`函数中对 p 做了单独识别，而且 p 是判断 16 进制浮点数的条件）。

### 1.4 ParseComplex

ParseComplex 基本就是使用`parseFloatPrefix()`处理字符串，可以简单理解为执行两次 ParseFloat 函数。

`ParseComplex(s string, bitSize int)`中，bitSize 是 complex 的位数，64 或 128。默认情况下，函数会使用 64 位浮点数进行解析。所以解析复数的时候一定要指定 bitSize 对应的目标位数，不然处理浮点型实虚数时会有精度缺失问题。

例如：

```go
v := "1.1+1i"
c1, _ := strconv.ParseComplex(v, 128)
fmt.Print(c1) // (1.1+1i)

c2, _ := strconv.ParseComplex(v, 64)
fmt.Print(c2) // (1.100000023841858+1i)
```

## 二、Format

FormatXxx 类型的函数是用来将数字转换成字符串的，主要有如下一些函数：

|  方法名        |  输入                                    |  输出   |
|:-------------:|:----------------------------------------:|:------:|
| FormatBool    | b bool                                   | string |
| FormatInt     | i int64, 基数 int                         | string |
| FormatUint    | i uint64, 基数 int                         | string |
| FormatFloat   | f float64, fmt byte, prec, bitSize int    | string |
| FormatComplex | c complex128, fmt byte, prec, bitSize int | string |

### 2.1 FormatBool

`FormatBool()`函数很简单，就是直接返回`"true"`或者`"false"`字符串：

```go
func FormatBool(b bool) string {
  if b {
    return "true"
  }
  return "false"
}
```

### 2.2 FormatInt/FormatUint

`FormatInt()`和`FormatUint()`函数的逻辑基本类似，都是根据传入参数的大小分为`small()`和`formatBits()`函数的调用。

源码如下：

```go
const fastSmalls = true // enable fast path for small integers
const nSmalls = 100

// FormatUint returns the string respresentation of i in the given base,
// for 2 <= base <= 36. The result uses the lower-case letters 'a' to 'z'
// for digit values >= 10.
func FormatUint(i uint64, base int) string {
  if fastSmalls && i < nSmalls && base == 10 {
    return small(int(i))
  }
  _, s := formatBits(nil, i, base, false, false)
}

func FormatInt(i int64, base int) string {
  if fastSmalls && 0 <= i && i < nSmalls && base == 10 {
    return small(int(i))
  }
  _, s := formatBits(nil, uint64(i), base, i < 0, false)
  return s
}
```

可以看到，这两个函数对于十进制的小数(100)来说，都是通过`small()`函数来转换的。而`small()`函数则是通过查表来实现转换的：

```go
const digits = "0123456789abcdefghijklmnopqrstuvwxyz"
const smallsString = "00010203040506070809" +
  "10111213141516171819" +
  "20212223242526272829" +
  "30313233343536373839" +
  "40414243444546474849" +
  "50515253545556575859" +
  "60616263646566676869" +
  "70717273747576777879" +
  "80818283848586878889" +
  "90919293949596979899"

func small(i int) string {
  if i < 10 {
    return digitis[i : i+1]
  }
  return smallsString[i*2 : i*2+2]
}
```

对于更大的数据，或者非十进制数字的转换，则会使用更复杂的`formatBits()`函数来解决了。

比如，对于`0xA3`这个十六进制数字，转换成十进制的流程如下（从`Itoa()`函数开始）：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649388970385-1768ddbbff29.jpg)

> 需要注意的是，在计算偏移时，`smallsString[127]`是 3，对应的 ASCII 码值为 51，所以存储的 rune 的值就是 51。

### 2.3 FormatFloat

`FormatFloat()`需要四个参数，分别是：`f float64`表示要转换的浮点数，`fmt byte`格式参数，`prec int`小数点保留位数（如果是 -1 则全保留），`bitSize int`表示转换的基数。

`fmt`参数可选的值如下：

* `'b'` 二进制表达式，包含小写的`p`，如`-ddddp±ddd`；
* `'e'` 十进制表达式，包含小写的`e`，如`-d.dddde±dd`；
* `'E'` 十进制表达式，包含大写的`E`，如`-d.ddddE±dd`；
* `'f'` 普通的浮点表达式，如`-ddd.dddd`；
* `'g'` 对于较大的浮点数使用`'e'`格式，否则使用`'f'`格式，这是大多数情况下使用的标识；
* `'G'` 对于较大的浮点数使用`'E'`格式，否则使用`'f'`格式；
* `'x'` 十六进制表达式，包含小写的`x`和`p`，如`-0xd.ddddp±ddd`；
* `'X'` 十六进制表达式，包含大写的`X`和`P`，如`-0Xd.ddddP±ddd`。

`FormatFloat()`内部调用了`genericFtoa()`，该函数做的事情就是根据参数计算出原始 float 的十进制表达式。后续会根据`fmt`参数使用不同的计算过程。

精度参数`prec`指定小数点位置，使用的是`Grisu3`算法计算浮点数，精度是普通算法的四倍。

### 2.4 FormatComplex

和`ParseComplex()`函数类似，`FormatComplex()`函数相当于执行了两次`FormatFloat()`函数。

也需要注意位数传参，否则会造成精度丢失问题。

## 三、Append

AppendXxx 类别的函数，将数字类型按照字符串方式进行转换后再拼接，最终输出数组。

因为最终拼接结果是字符串数组，所以拼接前需要有和 FormatXxx 类别的函数一致的转换过程，如`AppendFloat()`底层处理逻辑就和`FormatFloat()`是相同的逻辑。

### 3.1 AppendInt

源码如下：

```go
// AppendInt appends the string form of the integer i,
// as generated by FormatInt, to dst and returns the extended buffer.
funct AppendInt(dst []byte, i int64, base int) []byte {
  if fastSmalls && 0 <= i && i < nSmalls && base == 10 {
    return append(dst, small(int(i))...)
  }
  dst, _ = formatBits(dst, uint64(i), base, i < 0, true)
  return dst
}
```

### 3.2 AppendUint

源码如下：

```go
// AppendUint appends the string form of the unsigned integer i,
// as generated by FormatUint, to dst and returns the extended buffer.
func AppendUint(dst []byte, i uint64, base int) []byte {
  if fastSmalls && i < nSmalls && base == 10 {
    return append(dst, small(int(i))...)
  }
  dst, _ = formatBits(dst, i, base, false, true)
  return dst
}
```

## 四、其他

strconv 还有 QuoteXxx 系列处理 rune-ASCII-图形符号直接的转换函数，以及 rune-图形判断系列函数，逻辑都相对较为简单。

### 4.1 Atoi 和 Itoa

`Atoi()`函数表示将 ASCII 转换成十进制整形数。

源码如下：

```go
func Atoi(s string) (int, error) {
  const fnAtoi = "Atoi"

  sLen := len(s)
  if intSize == 32 && (0 < sLen && sLen < 10) ||
    intSize == 64 && (0 < sLen && sLen < 19) {
    // Fast path for small integers that fit int type.
    s0 := s
    if s[0] == '-' || s[0] == '+' {
      s = s[1:]
      if len(s) < 1 {
        return 0, &NumError{fnAtoi, s0, ErrSyntax}
      }
    }

    n := 0
    for _, ch := range []byte(s) {
      ch -= '0'
      if ch > 9 {
        return 0, &NumError{fnAtoi, s0, ErrSyntax}
      }
      n = n*10 + int(ch)
    }
    if s0[0] == '-' {
      n = -n
    }
    return n, nil
  }

  // Slow path for invalid, big, or underscored integers.
  i64, err := ParseInt(s, 10 0)
  if nerr, ok := err.(*NumError); ok {
    nerr.Func = fnAtoi
  }
  return int(i64), err
}
```

可以看到，其实现逻辑分为两部分：

* 对于较小的十进制数字转换，只需要对字符串进行遍历，并按位乘上基数，补全符号即可；
* 对于其他情况（超过 int 最大值的数、异常情况、有下划线的情况），就需要通过`ParseUint()`函数进行转换了。

比如，对于字符串`"0xA3"`调用`Atoi()`函数的流程如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649316275795-438f28d26e73.jpg)

而`Itoa()`函数更为简单，就是直接使用`FormatInt()`函数：

```go
// Itoa is equivalent to FormatInt(int64(i), 10).
func Itoa(i int) string {
  return FormatInt(int64(i), 10)
}
```


