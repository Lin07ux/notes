> 转摘：[Printf()、Sprintf()、Fprintf()函数的区别用法是什么？](https://mp.weixin.qq.com/s/Ov64hjAe2to16qM9F81JDg)

## 一、概述

简单来说，这三个打印输出函数的作用分别是：

* `Printf` 格式化并输出到标准输出；
* `Sprintf` 格式化并返回一个字符串，不直接输出；
* `Fprintf` 格式化并输出到指定的输出流（文件、标准输出等）。

## 二、源码实现

这三个函数的实现都在`src/fmt/print.go`文件中。

### 2.1 整体实现

`Printf()`、`Sprintf()`、`Fprintf()`这三个函数的实现都很简单：

```go
// Printf formats according to a format specifier and writes to standard output.
// It returns the number of bytes written and any write error encountered.
func Printf(format string, a ...interface{}) (n int, err error) {
  return Fprint(os.Stdout, format, a...)
}

// Fprintf formats according to a format specifier and writes to w.
// It returns the number of bytes written and any write error encountered.
func Fprintf(w io.Writer, format string, a ...interface{}) (n int, err error) {
  p := newPrinter()
  p.doPrintf(format, a)
  n, err = w.Write(p.buf)
  p.free()
  return
}

// Sprintf formats according to a format specifier and returns the resulting string.
func Sprintf(format string, a ...interface{}) string {
  p := newPrinter()
  p.doPrintf(format, a)
  s := string(p.buf)
  p.free()
  return s
}
```

可以看到，这三个函数最终都是通过调用`pp.doPrintf()`方法来实现的，而`Printf()`方法默认设置的输出流是`os.Stdout`，也就是标准输出。

### 2.2 pp.doPrintf 方法

这个方法是用来解析各种格式化参数，并解析对应的参数，输出相关的 byte 流。主体的处理流程是：

1. 输出`%`之前的所有字符；
2. 如果所有`format`格式符处理完毕，则跳出处理逻辑；
3. 往后移动一位，跳过`%`字符，开始处理其他占位符（verb）；
4. 清空 fmt 配置；
5. 处理占位符（verb）：
    - 若当前字符为简单占位符（`#`、`0`、`+`、`-`、' '），则直接处理该标记，借着往后移动到下一个字符；
    - 对于其他占位符则会变更 fmt 配置项，便于后续处理，并且调用`p.printArg()`格式化输出当前占位符对应的参数；
6. 处理参数索引；
7. 处理参数宽度；
8. 处理参数精度；
9. `%`之后若不存在占位符，则返回`noVerbString`；
10. 处理特殊占位符（如：`%%`、`%#V`、`%+v`等）、错误情况（如：参数索引指定错误、参数个数与占位符数量不匹配等），或进行格式化参数集；
11. 在特殊情况下，若提供的参数集比占位符多，则会继续检查下去，将多出的参数以特定的格式化输出。

下面是`pp.doPrintf()`方法的源码：

```go
func (p *pp) doPrintf(format string, a []interface{}) {
  end := len(format)
  argNum := 0         // we process one argument per non-trivial format
  afterIndex := false // previous item in format was an index like [3]
  p.reordered = false
  
formatLoop:
  for i := 0; i < end; {
    p.goodArgNum = true
    lasti := i
    for i < end && format[i] != '%' {
      i++
    }
    // 写入 % 之前的字符
    if i > lasti {
      p.buf.writeString(format[lasti:i])
    }
    // 如果所有 format 格式字符处理完毕，则跳过处理逻辑
    if i >= end {
      // done processing format string
      break;
    }
    // 往后移动一位以跳过 %，开始处理其他占位符(verb)
    i++
    // 清空 fmt 配置
    p.fmt.clearflags()

simpleFormat:
    // 处理占位符
    for ; i < end; i++ {
      c := format[i]
      switch c {
      case '#':
        p.fmt.sharp = true
      case '0':
        p.fmt.zero = !p.fmt.minus // Only allow zero padding to the left
      case '+':
        p.fmt.plus = true
      case '-':
        p.fmt.minus = true
        p.fmt.zero = false // Do not pad with zeros to the right
      case ' ':
        p.space = true
      default:
        // Fast path for common case of ascii lower case simple verbs
        // without precision or width or argument indices.
        if 'a' <= c && c <= 'z' && argNum < len(a) {
          if c == 'v' {
            // Go syntax
            p.fmt.sharpV = p.fmt.sharp
            p.fmt.sharp = false
            // Struct-field syntax
            p.fmt.plusV = p.fmt.plus
            p.fmt.plus = false
          }
          p.printArg(a[argNum], rune(c))
          argNum++
          i++
          continue formatLoop
        }
        // Format is more complex than simple flags and a verb or is malformed
        break simpleFormat
      }
    }
    
    // 处理参数索引、参数宽度、参数精度
    // ...
    
    if !afterIndex {
      argNum, i, afterIndex = p.argNumber(argNum, format, i, len(a))
    }
    
    if i >= end {
      p.buf.writeString(noVerbString)
      break
    }
    
    varb, size := rune(format[i]), 1
    if verb >= utf8.RuneSelf {
      verb, size = utf8.DecodeRuneInString(format[i:])
    }
    i += size
    
    switch {
    case verb == '%': // Percent does not absorb operands and ignores f.wid and f.prec
      p.buf.writeByte('%')
    case !p.goodArgNum:
      p.badArgNum(verb)
    case argNum >= len(a): // No argument left over to print for the current verb
      p.missingArg(verb)
    case verb == 'v':
      // Go syntax
      p.fmt.sharpV = p.fmt.sharp
      p.fmt.sharp = false
      // Struct-field syntax
      p.fmt.plusV = p.fmt.plus
      p.fmt.plus = false
      fallthrough
    default:
      p.printArg(a[argNum], verb)
      argNum++
    }
  }
  
  // Check for extra arguments unless the call accessed the arguments
  // out of order, in which case it's too expensive to detect if
  // the've all been used and arguably Ok if they're not.
  if !p.reordered && argNum < len(a) {
    p.fmt.clearflags()
    p.buf.writeString(extraString)
    for i, arg := range a[argNum:] {
      if i > 0 {
        p.buf.writeString(commaSpceString)
      }
      if arg == nil {
        p.buf.writeString(nilAngleString)
      } else {
        p.buf.writeString(reflect.TypeOf(arg).String())
        p.buf.writeByte('=')
        p.printArg(arg, 'v)
      }
    }
    p.buf.writeBytes(')')
  }
}
```

### 2.3 p.printArg()

接下来看下`p.printArg()`方法是如何格式化输出参数的。该方法接收两个参数：

* `arg` 任意类型的参数值；
* `verb` rune 类型的占位符。

源码如下：

```go
func (p *pp) printArg(arg interface{}, verb rune) {
  p.arg = arg
  p.value = reflect.Value{}
  
  // 异常处理，占位符 verb 对应的参数 arg 为空
  if arg == nil {
    switch verb {
    case 'T', 'v':
      p.fmt.padString(nilAngleString)
    default:
      p.badVerb(verb)
    }
    return
  }
  
  // %T 和 %p 这两种占位符特殊处理
  // Special processing considerations
  // %T (the value's type) and %p (its address) are special;
  // we always do them first
  switch verb {
  case 'T':
    p.fmt.fmtS(reflect.TypeOf(arg).String())
    return
  case 'p':
    p.fmtPointer(reflect.ValueOf(arg), 'p')
    return
  }
  
  // Some types can be done without reflection
  switch f := arg.(type) {
  case bool:
    p.fmtBool(f, verb)
  case flat32:
    p.fmtFloat(float64(f), 32, verb)
  case float64
    p.fmtFloat(f, 64, verb)
  case complex64:
    p.fmtComplex(complex128(f), 64, verb)
  case complex128:
    p.fmtComplex(f, 128, verb)
  case int:
    p.fmtInteger(uint64(f), signed, verb)
  case int8:
    p.fmtInteger(uint64(f), signed, verb)
  case int16:
    p.fmtInteger(uint64(f), signed, verb)
  case int32:
    p.fmtInteger(uint64(f), signed, verb)
  case int64:
    p.fmtInteger(uint64(f), signed, verb)
  case uint:
    p.fmtInteger(uint64(f), unsigned, verb)
  case uint8:
    p.fmtInteger(uint64(f), unsigned, verb)
  case uint16:
    p.fmtInteger(uint64(f), unsigned, verb)
  case uint32:
    p.fmtInteger(uint64(f), unsigned, verb)
  case uint64:
    p.fmtInteger(f, unsigned, verb)
  case uintptr:
    p.fmtInteger(uint64(f), unsigned, verb)
  case string:
    p.fmtString(f, verb)
  case []byte:
    p.fmtBytes(f, verb, "[]byte")
  case reflect.Value:
    // Handle extractable values with special methods
    // since printValue does not handle them at depth 0.
    if f.IsValid() && f.CanInterface() {
      p.arg = f.Interface()
      if p.handleMethods(verb) {
        return
      }
    }
    p.printValue(f, verb, 0)
  default:
    // If the type is not simple, it might have methods.
    if !p.handleMethods(verb) {
      // Need to use reflection, since the type had no
      // interface methods that could be used for formatting.
      p.printValue(reflect.ValueOf(f), verb, 0)
    }
  }
}
```

可以看出，`p.printArg()`对于不同的参数值（arg）类型，会调用不同的 fmt 方法进行处理后输出。

## 三、占位符

输出的占位符除了常用的`%s`、`%d`、`%v`之外，还有更多其他的占位符。

### 3.1 普通占位符

| 占位符 | 说明                   |
|-------|-----------------------|
| `%v`  | 相应值的默认格式         |
| `%+v` | 打印结构体时，会添加字段名 |
| `%#v` | 相应值的 Go 语法表示     |
| `%T`  | 相应值的类型的 Go 语法表示 |
| `%%`  | 字面上的百分号（%）       |

### 3.2 布尔占位符

| 占位符 | 说明                   |
|-------|-----------------------|
| `%t`  | true 或 false          |

### 3.3 整型占位符

| 占位符 | 说明                               |
|-------|------------------------------------|
| `%b`  | 二进制表示                           |
| `%c`  | 相应 Unicode 码所表示的字符           |
| `%d`  | 十进制表示                           |
| `%o`  | 八进制表示                           |
| `%q`  | 单引号围绕的字符字面值，由 Go 语法转义   |
| `%x`  | 十六进制表示，字母形式为小写`a - f`     |
| `%X`  | 十六进制表示，字母形式为大写`A - F`     |
| `%U`  | Unicode 格式，`U+1234`等同于`U+%04X` |

### 3.3 浮点数和复数占位符

| 占位符 | 说明                                           |
|-------|-----------------------------------------------|
| `%b`  | 无小数部分的、指数为二的幂的科学计数法，与`strconv.FormatFloat`的 `'b'`转换格式一致。例如：`-123456p-78`|
| `%e`  | 科学计数法，例如：`-1234.456e+78`                 |
| `%E`  | 科学计数法，例如：`-1234.456E+78`                 |
| `%f`  | 有小数点而无指数，例如: `123.456`                  |
| `%g`  | 根据情况选择`%e`或`%f`以产生更紧凑的（无末尾的 0）输出 |
| `%G`  | 根据情况选择`%E`或`%f`以产生更紧凑的（无末尾的 0）输出 |

### 3.4 字符串和切片占位符

| 占位符 | 说明                          |
|-------|-------------------------------|
| `%s`  | 字符串表示                      |
| `%q`  | 双引号围绕的字符串，由 Go 语法转义 |
| `%x`  | 十六进制，小写字母，每字节两个字符  |
| `%X`  | 十六进制，大写字母，每字节两个字符  |

### 3.5 指针占位符

| 占位符 | 说明                   |
|-------|-----------------------|
| `%p`  | 十六进制表示，前缀`0x`   |

### 3.6 其他占位符

| 占位符 | 说明                                             |
|-------|-------------------------------------------------|
| `+`   | 总打印数值的正负号                                 |
| `-`   | 在右侧而非左侧填充空格（左对齐该区域）                 |
| `#`   | 备用格式                                          |
| `' '` | (空格)为数值中省略的正负号留出空白(`%d`)；以十六进制(`%x`、`%X`)打印字符串或切片时，在字节之间用空格隔开。   |
| `0`   | 填充前导的 0 而非空格；对于数字，这会将填充移到正负号之后 |

`#`备用格式有如下功能：

* 为八进制添加前导 0(`%#o`)；
* 为十六进制添加前导`0x`(`%#x`)或`0X(`%#X`)；
* 为`%p`(`%#p`)去掉前导`0x`；
* 如果可能的话，`%#q`会打印原始（即反引号围绕的）字符串；
* 如果是可打印字符，`%#U`会写出该字符的 Unicode 编码形式（如字符`x`会被打印成 `U+0078`）。



