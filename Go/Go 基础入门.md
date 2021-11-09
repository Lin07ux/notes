## 一、运行和开发环境

### 1.1 安装

Go 预约官方安装指引可参见 [这里](https://golang.org/doc/install)。

在 Mac 中还可以使用 Homebrew 进行安装：

```shell
# Brew 依赖 Xcode
xcode-select --install

# 安装 Homebrew
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"

# 安装 Go
brew install go

# 查看 Go 的版本确定是否安装成功
go version
```

### 1.2 GOPATH

Go 有一套特别的惯例：

> 所有 Go 代码都存在与一个工作区（文件夹）内。这个工作区可以在机器的任何地方。如果不指定，Go 将假定`$HOME/go`为默认工作区。

Go 工作区由环境变量`GOPATH`标识和修改。建议设置 Go 环境变量，便于后续可以在脚本或 Shell 中使用它。

Go 假设工作区内包含一个特定的目录结构：

* `$GOPATH/src` 存放源代码
* `$GOPATH/pkg` 存放包对象
* `$GOPATH/bin` 存放编译好的程序

在命令行的配置中添加以下导出语句（在`~/.bash_profile`或`~/.zshrc`等配置文件中）：

```shell
export GOPATH=$HOME/go
export PATH=$PATH:$GOPATH/bin
```

然后执行如下命令使其生效：

```shell
source ~/.zshrc
```

### 1.3 环境变量

Go 自身有一些环境选项可以进行配置：

* `go env -w GOPROXY=https://goproxy.cn,direct` 使用国内第三方包镜像

### 1.4 包与大小写

Go 代码都是属于某个包的，包是一种将相关的 Go 代码组合到一个的方式。

Go 项目中需要定义一个`main`包，并在其中定义一个`main`函数。

在 Go 中，常量、方法、类型、interface 的名称如果是大写字母开头的，则表示其是公开的，可以被外部访问和使用的；如果是小写字母开头的，则表示其是私有的，外部不可访问和使用。

## 二、语法

### 2.1 声明变量

Go 是强类型语言，所以每个变量都要有指定的类型。这个类型可以在声明的时候指定，也可以让 Go 自动推断。

```go
var name1 string
name2 := name1
```

### 2.2 定义常量

定义常量使用`const`关键词，且无需为其指定类型。

```go
const spanish = "Spanish"
const (
    helloPrefix = "Hello, "
    spanishHelloPrefix = "Hola, "
)
```

### 2.3 if

Go 的`if`非常类似于其他编程语言，用作于根据条件来决定是否执行代码块。

`if`的条件可以是变量、表达式，但是其类型需为 bool 类型。`if`语句也支持`else`及`else if`子句，跟随在`if`后面即可。

关键字`if`、`else-if`和`else`之后的左大括号`{`必须和关键字在同一行；关键`else-if`和`else`的前段代码块的右大括号`}`必须和`else-if`关键字在同一行。这两条规则都是被编译器强制规定的。

`if`语句可以在条件表达式前执行一个很简单的语句，而且该语句中声明的变量的作用域仅在`if`及其后续的`else/else-if`语句块之内。

```go
const englishHelloPrefix = "Hello, "

func Hello(name string) string {
    if name == "" {
        name = "World"
    } else if name == "anon" {
         name = "anonymous"
    } else {
         name = strings.TrimSpace(name)
    }
    return englishHelloPrefix + name
}

func pow(x, n, lim float64) float64 {
	if v := math.Pow(x, n); v < lim {
		return v
	} else {
		fmt.Printf("%g >= %g\n", v, lim)
	}
	// 这里开始就不能使用 v 了
	return lim
}
```

### 2.4 for

在 Go 中循环和迭代都只能使用`for`语法，因为 Go 语言中没有`while`、`do`、`until`这几个关键字。

基本的`for`循环由三部分组成，分别用分号隔开：

* 初始化语句：在第一次迭代之前执行，在这里定义的变量只能在当前`for`循环作用域中可见；
* 条件表达式：在每次迭代前求值，一旦表达式结果为`false`循环迭代就会终止；
* 后置语句：在每次迭代的结尾执行。

`for`语句后面的三个语句不需要使用小括号`()`包裹，而且这三个语句都是可选的，不提供初始化语句或后置语句时，相关的分隔分号`;`是可以省略的

```go
func Repeat(character string) string {
    var repeated string
    for i := 0; i < 5; i++ {
        repeated += character
    }
    return repeated
}

func AutoSum() {
  sum := 1
  for sum < 1000 {
    sum += sum
  }
  return sum
}
```

### 2.5 switch

Go 的`switch`语句类似于 C、C++、PHP 等其他语言中的语法，但是明显的区别在于：

* Go 中的`switch`默认只运行选定的`case`，而不需要在`case`块中添加`break`，该分支就会自动终止。相反的，如果想要不自动终止分支，就需要为`case`块添加`fallthrough`语句。
* Go 中的`switch`的`case`无需为常量，且取值不必为整数。

`switch`中`case`语句从上到下顺次执行，直到匹配成功时停止。

```go
switch i {
case 0:
case f(): // i == 0 时，f() 将不会被执行
}
```

没有条件的`switch`等同于`switch true`，这种形式能将一长串的`if-else.if-else`写的更加清晰：

```go
func main() {
  t := time.Now()
  switch {
  case t.Hour() < 12:
    fmt.Println("Good morning!")
  case t.Hour() < 17:
    fmt.Println("Good afternoon.")
  default:
    fmt.Println("Good evening.")
  }
}
```

### 2.6 defer

`defer`语句会将函数推迟到当前函数返回之后执行。

`defer`语法具有如下三种特性：

1. 推迟调用的函数的参数会被立即求值，但直到当前函数返回前，该函数都不会被调用。
2. 被推迟的函数会被压入一个栈中，按照现进后出的顺序被调用。
3. 推迟的函数中，可以访问（读、改）当前函数的返回值。

### 2.7 range

`range`语句可以用于`for`循环中，对数组、切片和 map 进行遍历。

当使用`for`循环遍历切片和数组时，每次迭代都会返回两个值：第一个值为当前元素的下标，第二个值为该下标所对应元素的一份副本。

```go
var pow = []int{1, 2, 4, 8, 16, 32, 64, 128}

func main() {
	for i, v := range pow {
		fmt.Printf("2**%d = %d\n", i, v)
	}
}
```

可以使用`_`来忽略`range`返回的下标或值，而且**如果只需要索引的时候，可以直接忽略第二个变量**：

```go
for i, _ := range pow
for _, i := range pow
for i := range pow
```

### 2.8 类型断言和类型选择

类型断言提供了访问接口值底层具体指的方式：

* `t := i.(T)`
* `t, ok := i.(T)`

这两种方式都能进行类型断言，断言接口值`i`保存了具体类型`T`，并将其底层类型为`T`的值赋予给变量`t`。但是在`i`未保存`T`类型的值时，前者会引发一个 panic，后者则会将变量`ok`赋值为`false`。

**类型选择是一种按顺序从几个类型断言中选择分支的结构。**类型选择与一般的`switch`语句相似，不过类型选择中的`case`为类型（而非值），它们针对给定接口值所存储的值的类型进行比较：

```go
// 使用 type 关键字替换类型断言中的具体类型
switch v := i.(type) {
case T:
  // v 的类型为 T
case S:
  // v 的类型为 S
default：
  // 没有匹配，v 与 i 的类型相同
}
```

### 2.9 select

`select`语句使一个 Go 协程可以等待多个通道通信。

`select`会阻塞到某个分支可以继续执行位置，这时就会执行该分支。当多个分支都准备好时货随机选择一个执行。

当`select`中的其他分支都没有准备好时，`default`分支就会执行。一般为了在尝试发送或者接收时不发生阻塞，就可以使用`default`分支。

```go
func main() {
	tick := time.Tick(100 * time.Millisecond)
	boom := time.After(500 * time.Millisecond)
	for {
		select {
		case <-tick:
			fmt.Println("tick.")
		case <-boom:
			fmt.Println("BOOM!")
			return
		default:
			fmt.Println("    .")
			time.Sleep(50 * time.Millisecond)
		}
	}
}
```

## 三、数据类型

Go 语言将数据类型分为四类：

* **基础类型**：数字、字节、字符、字符串、布尔值；
* **复合类型**：数组、结构体；
* **引用类型**：切片、字典、指针、函数、通道；
* **接口类型**：接口。

引用类型虽然种类很多，但是它们都是对程序中一个变量或状态的间接引用，这意味着，对任一引用类型数据的修改都会影响所有该引用的拷贝。

### 3.1 运算符

  运算符      |  说明
:-----------:|:-----------------:
 `+ - * / %` | 加、减、乘、除、取模
 `== !==`    | 相等、不等
 `< <=`      | 小于、小于等于
 `> >=`      | 大于、大于等于
 `&& ||`     | 逻辑且、逻辑或
 `<< >>`     | 左移、右移
 `& | ^`     | 位运算中的且（AND）、或（OR）、异或（XOR）
 `&^`        | 位清空（AND NOT）
 
需要注意的是：

* `%`取模运算的符号和被取模数的符号总是一致的，因此`-5 % 3`和`-5 % -3`的结果都是`-2`。
* `^`作为二元运算符时是按位异或，作为一元运算符时表示按位取反（也就是返回每个 bit 都取反之后的数据）。
* `&^`表示按位清空，也就是说，对于`z = x &^ y`公式，如果`y`中的某一位为 1，那么`z`中对应的位会被置位 0，否则该位就是`x`中对应位的值。

示例如下：

```go
var x uint8 = 1<<1 | 1<<5
var y uint8 = 1<<1 | 1<<2

// %08b 按照二进制打印，且至少打印 8 个字符宽度，不足的时候前缀部分用 0 填充

fmt.Printf("%08b\n", x) // "00100010"
fmt.Printf("%08b\n", y) // "00000110"

fmt.Printf("%08b\n", x&y)  // "00000010"
fmt.Printf("%08b\n", x|y)  // "00100110"
fmt.Printf("%08b\n", x^y)  // "00100100"
fmt.Printf("%08b\n", x&^y) // "00100000"
```

### 3.1 基础数据类型

1. **整型**：

    - `int8`、`int16`、`int32`、`int64`：分别表示 8、16、32、64 位大小的有符号整数类型；
    - `uint8`、`uint16`、`uint32`、`uint64`：分别表示 8、16、32、64 位大小的无符号整数类型。
    - `int`、`uint`：分别表示与 CPU 字大小相关的有符号和无符号整数类型，由编译器最终确定其位的大小是 32 还是 64 位。
    - `uintptr`：无符号整型指针类型，没有指定具体的位大小，但足以容纳指针。该类型只有在底层编程时才需要，特别是 Go 语言和 C 语言函数库或操作系统接口交互的地方。

2. **字符**：rune

    Unicode 字符 rune 类型是和 int32 等价的类型，通常用于表示一个 Unicode 码点，这两个名字可以互换使用。

3. **字节**：byte

    byte 与 uint8 是等价类型，一般用于强调数值是一个原始的数据而不是一个小的整数。

### 3.2 指针

Go 拥有指针，但是不支持指针运算。指针保存了值的内存地址。

类型`*T`是指向类型`T`值的指针，其零值为`nil`：

```go
var p *int
```

`&`操作符会生成一个指向其操作数的指针：

```go
i := 42
p := &i
```

`*`操作符表示指针指向的底层值：

```go
fmt.Println(*p) // 通过指针 p 读取变量 i 的值
*p = 21         // 通过指针 p 设置变量 i 的值
```

### 3.3 结构体

一个结构体(`struct`)就是组字段(`field`)，结构体的字段使用点号(`.`)来访问。

结构体字段可以通过结构体指针来访问：`(*p).X`。Go 也允许使用隐式间接引用，也就是`p.X`。

结构体初始化时，可以不指定任何字段，也可以指定部分字段（为指定的字段为零值）：

```go
type Vertex struct {
  X, Y int
}

var (
  v1 = Vertex{1, 2} // X:1 Y:2
  v2 = Vertex{X: 1} // X:1 Y:0
  v3 = Vertex{}     // X:0 Y:0
)  
```

### 3.4 map

`map`将键名映射到一个特定的值，文法和结构体相似，只是必须要指定键名：

```go
type Vertex struct {
	Lat, Long float64
}

var m = map[string]Vertex{
	"Bell Labs": Vertex{
		40.68433, -74.39967,
	},
	"Google": Vertex{
		37.42202, -122.08408,
	},
}
```

如果`map`的顶级类型只是一个类型名，可以在文法的元素中省略掉：

```go
type Vertex struct {
	Lat, Long float64
}

var m = map[string]Vertex{
  "Bell Labs": {40.68433, -74.39967},
  "Google": {37.42202, -122.08408},
}
```

`map`的零值为`nil`，此时其既没有键，也不能添加键。`make`函数会返回给定类型的映射，并将其初始化备用。

### 3.5 方法

方法就是一类带有特殊的**接收者**参数的函数，和普通的函数没有其他的区别。

方法接收者在它自己的参数列表内，位于`func`关键字和方法名之间。

只能为在同一个包内定义的类型声明方法，而不能为其他包内定义的类型（包括`int`之类的内建类型）声明方法。

另外，函数的参数如果声明的是值类型，调用的时候就只能传递对应的值类型；如果声明的是指针类型，调用的时候就必须传递对应的类型指针。而类型的方法则没有这个限制，因为 Go 编译器会自动进行转换。

方法的接收者如果是类型指针，则在方法内对接收者做的修改就能在方法外部看到；接收者如果是普通类型，则方法内的修改不会影响到外部，只是因为如果接收者是普通类型，那么 Go 在调用方法的时候会进行值拷贝。

### 3.6 接口

**接口类型是由一组方法签名定义的集合**。接口类型的变量可以保存任何实现了这些方法的值。

类型通过实现一个接口的所有方法来实现该接口，无需专门显示声明。隐式接口从接口的实现中解耦了定义，这样接口的实现可以出现在任何包中，无需提前准备。

需要注意的是，结构体的方法的接收者类型是值类型还是指针，会影响到变量是否实现了接口的判断。比如，下面的代码中，由于`Abs`方法只为`*Vertex`（指针类型）定义，`Vertex`（值类型）并未实现`Abs`。因此，在调用`a.Abs()`的时候就会报错：

```go
type Vertex struct {
	X, Y float64
}

func (v *Vertex) Abs() float64 {
	return math.Sqrt(v.X*v.X + v.Y*v.Y)
}

func main() {
	var a Abser
	v := Vertex{3, 4}

	a = &v // a *Vertex 实现了 Abser
	a = v  // v 是一个 Vertex（而不是 *Vertex），所以没有实现 Abser

	fmt.Println(a.Abs())
}
```

接口也是一种值，可以像其他值一样传递。接口值可以用做函数的参数或返回值。接口值保存了一个具体底层类型的具体值，和该类型。接口值调用方法时会执行其底层类型的同名方法。

在内部，接口值可以看做包含值和具体类型的元组：

```go
(value, type)
```

所以，即便接口内的具体值为`nil`，但是具体类型不为`nil`，那么这个接口值就不是`nil`：

```go
type I interface {
	M()
}

type T struct {
	S string
}

func (t *T) M() {
	if t == nil {
		fmt.Println("<nil>")
		return
	}
	fmt.Println(t.S)
}

func main() {
	var i I

	var t *T
	i = t
	describe(i) // (<nil>, *main.T)
	i.M()       // <nil>

	i = &T{"hello"}
	describe(i) // (&{hello}, *main.T)
	i.M()       // hello
}

func describe(i I) {
	fmt.Printf("(%v, %T)\n", i, i)
}
```

`nil`接口值既不保存具体值，也不保存具体类型。所以为`nil`接口调用方法会产生运行时错误，因为接口的元组内并未包含能够指明该调用哪个具体方法的类型。

不指定任何方法的接口值被称为空接口，如：`interface{}`。空接口可以保存任何类型的值，因为每个类型都至少实现了零个方法。空接口常被用来处理未知类型的值。


