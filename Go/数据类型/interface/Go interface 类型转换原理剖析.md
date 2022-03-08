> 转摘：[Go interface 原理剖析：类型转换](https://mp.weixin.qq.com/s/6aEV4qY6KvL7nb1P8Nwkow?forceh5=1)

Go interface 有两个相关的底层结构体（位于`runtime/runtime2.go`）：

* `eface` 表示不包含任何方法的空接口，也就是`interface{}`；
* `iface` 表示包含有方法的接口。

下面是两者的结构示意图：

![eface](http://cnd.qiniu.lin07ux.cn/markdown/1636468498526-1c81821b4318.jpg)

![iface](http://cnd.qiniu.lin07ux.cn/markdown/1636468507111-d7b2650ed714.jpg)

### 1. eface

`eface`结构表示的没有任何方法的空接口，所以下面的代码就声明了这样的一个接口：

```go
func main() {
	var ti interface{}
	var a int = 20
	ti = a
	fmt.Println(ti) // 20
}
```

#### 1.1 汇编结果

这段代码的汇编结果如下（省略不相关部分）：

```asm
// 将 20 转换成 int64 类型
0x0040 00064 (main.go:8)	MOVQ   $20, (SP)
0x0048 00072 (main.go:8)	CALL   runtime.convT64(SB)
// 将转换后的结果的内存地址放到 AX 寄存器
0x004d 00077 (main.go:8)	MOVQ   8(SP), AX
0x0052 00082 (main.go:8)	MOVQ   AX, ""..autotmp_3+64(SP)
// 将类型 type.int 放入到 74(SP) 中
0x0057 00087 (main.go:8)	LEAQ   type.int(SB), CX
0x005e 00094 (main.go:8)	MOVQ   CX, "".ti+72(SP)
// 将包含数字 20 的内存指针放入到 80(SP) 中
0x0063 00099 (main.go:8)	MOVQ   AX, "".ti+80(SP)
```

从这段汇编中可以看出，在为变量`ti`进行赋值时，分为了两步：先将放入其对应的具体指的类型指针，再放入指向具体值的指针。

#### 1.2 gdb 查看

上面的汇编也是无法看出`ti`这个 interface 是`eface`类型的，需要使用 gdb 调试工具的`pt ti`命令来查看：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636470023093-1995c2f63dc1.jpg)

#### 1.3 代码验证

当然，也可以使用内存分布来验证变量`ti`确实是`eface`类型的：

```go
package main

import (
	"fmt"
	"unsafe"
)

type eface struct {
	_type *_type
	data  unsafe.Pointer
}

type nameOff int32
type typeOff int32
type tflag uint8

type _type struct {
	size       uintptr
    ptrdata    uintptr // size of memory prefix holding all pointers
    hash       uint32
    tflag      tflag
    align      uint8
    fieldAlign uint8
    kind       uint8
    equal      func(unsafe.Pointer, unsafe.Pointer) bool
    gcdata     *byte
    str        nameOff
    ptrToThis  typeOff
}

func main() {
	var ti interface{}
	var a = 20
	ti = a

	fmt.Println("type: ", *(*eface)(unsafe.Pointer(&ti))._type)
	fmt.Println("data: ", *(*int)((*eface)(unsafe.Pointer(&ti)).data))
	fmt.Println((*eface)(unsafe.Pointer(&ti)))
}
```

输出结果类似如下：

```
type: {8 0 4149441018 15 8 8 2 0x10032e0 0x10e6800 959 27264}
data: 20
&{0x10adb40 0x1155940}
```

从这个内存结果分配上基本看出来了`eface`的内存布局及对应的最终`eface`的类型转换结果，可以确定`ti`变量此时就是`eface`结构的。

### 2. iface

下面的代码定义了有方法的接口，也就是会对应着`iface`结构：

```go
package main

type Person interface {
	Say() string
}

type Man struct {}

func (m *Man) Say() string {
	return "Man"
}

func main() {
	var p Person

	m := &Man{}
	p = m
	println(p.Say()) // Man
}
```

#### 2.2 汇编结果

将上面的代码进行汇编，得到关键代码如下：

```asm
// 创建一个空对象（Man 结构没有任何字段，所以是空对象），并将指针赋值给变量 m
0x0029 00041 (main.go:16)	LEAQ   runtime.zerobase(SB), AX
0x0030 00048 (main.go:16)	MOVQ   AX, ""..autotmp_4+32(SP)
0x0035 00053 (main.go:16)	MOVQ   AX, "".m+24(SP)
0x003a 00058 (main.go:17)	MOVQ   AX, ""..autotmp_2+40(SP)
// 将 go.itab.*"".Man 类型加载到 48(SP)
0x003f 00063 (main.go:17)	LEAQ   go.itab.*"".Man,"".Person(SB), CX
0x0046 00070 (main.go:17)	MOVQ   CX, "".p+48(SP)
// 将 m 指针加载到 56(SP)
0x004b 00075 (main.go:17)	MOVQ   AX, "".p+56(SP)
```

可以看到，在汇编中会将`Man`类型指针先赋值到`p`这个 Person interface 中，再将`Man`类型实例的指针赋值其中。

#### 2.3 gdb 查看类别

同样，上面的汇编也无法确认变量`p`的底层结构是不是`iface`，依然可以使用 gdb 调试查看：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636472157293-23583b8740c6.jpg)

#### 2.4 代码验证

下面依然通过自定义`iface`类型来验证变量`p`的结构就是`iface`：

```go
package main

import (
	"fmt"
	"unsafe"
)

// ... _type 相关

type name struct {
	bytes *byte
}

type imethod struct {
	name nameOff
	ityp typeOff
}

type interfacetype struct {
	typ     _type
	pkgpath name
	mhdr    []imethod
}

type itab struct {
	inter *interfacetype
    _type *_type
    hash  uint32
    _     [4]byte
    fun   [1]uintptr
}

type iface struct {
	tab  *itab
	data unsafe.Pointer
}

type Person interface {
	Say() string
}

type Man struct {}

func (m *Man) Say() string {
	return "Man"
}

func main() {
	var p Person

	m := &Man{}
	p = m

	fmt.Println("itab:", *(*iface)(unsafe.Pointer(&p)).tab)
	fmt.Println("data:", *(*Man)((*iface)(unsafe.Pointer(&p)).data))
}

```

输出的结果如下：

```
itab: {0x10b3880 0x10b1580 1224794265 [0 0 0 0] [17444384]}
data: {}
```

可以看出，其内存布局确实与`iface`结构相同。

### 3. 类型断言

下面的代码中使用了 interface 的类型断言：

```go
type Person interface {
  Say() string
}

type Man struct {
  Name string
}

func (m *Man) Say() string {
  return "Man"
}

func main() {
  var p Person

  m := &Man{Name: "hhf"}
  p = m

  if m1, ok := p.(*Man); ok {
    fmt.Println(m1.Name)
  }
}
```

对应的关键汇编代码如下：

```asm
// 将 p.itab 和 p.data 的值分别加载到 AX 和 CX 寄存器中
0x0087 00135 (main.go:25)	MOVQ   "".p+104(SP), AX
0x008c 00140 (main.go:25)	MOVQ   "".p+112(SP), CX
// 将 Man 类型的地址加载到 DX 寄存器中
0x0091 00145 (main.go:25)	LEAQ   go.itab.*"".Man,"".Person(SB), DX
// 比较 p.itab 和 Man 是否相同
0x0098 00152 (main.go:25)	CMPQ   DX, AX
// ...
// 将 p.data 赋值给变量 m1
0x00ac 00172 (main.go:25)	MOVQ   CX, ""..autotmp_4+80(SP)
0x00b1 00177 (main.go:25)	MOVB   AL, ""..autotmp_5+55(SP)
0x00b5 00181 (main.go:25)	MOVQ   ""..autotmp_4+80(SP), AX
// 将比对的结果赋值给变量 ok
0x00ba 00186 (main.go:25)	MOVQ   AX, "".m1+56(SP)
0x00bf 00191 (main.go:25)	MOVBLZX   ""..autotmp_5+55(SP), AX
0x00c4 00196 (main.go:25)	MOVB   AL, "".ok+54(SP)
// 根据变量 ok 的值决定如何跳转
0x00c8 00200 (main.go:25)	CMPB   "".ok+54(SP), $0
```

从上面的代码中可以看出，类型断言其实就是将`iface.itab`与具体类型进行比较，如果两者相等就说明是该类型的。`switch`类型断言的方式也跟这个基本相同，没有本质上的区别。
