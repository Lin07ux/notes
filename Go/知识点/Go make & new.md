> 转摘：[深入理解make和new，从底层到应用！](https://mp.weixin.qq.com/s/5WQIu3LLb5BhOqyaXPM0Sw)

`make`和`new`是 Go 语言内置的用来为特定类型申请内存空间的方法。

### 1. new

`new`方法会根据变量类型返回一个指向该类型的指针，函数声明如下：

```go
func new(Type) *Type
```

> 这里的`Type`是指变量的类型。

对于下面的例子：

```go
// age 字段为指针
type Student struct {
  age *int
}

// 获取结构体对象指针
func getStudent() *Student {
  s := new(Student)
  return s
}

func main() {
  s := getSudent()
  *(s.age) = 10
  fmt.Println(s.age)
}
```

这里会运行错误，发生 panic。执行命令`go build -gcflags="-l -S -N" main.go`得到汇编代码如下：

```asm
"".getStudent STEXT size=86 args=0x8 locals=0x20
    // ... 省略部分
    // 给返回值赋初值为 0（变量+偏移量表示方法是伪寄存器）
    0x001d 00029 (/main.go:9)  MOVQ    $0, "".~r0+40(SP)    
    // 将变量类型放入 AX 寄存器
    0x0026 00038 (/main.go:10) LEAQ    type."".Student(SB), AX
    // 将变量类型放入栈顶
    0x002d 00045 (/main.go:10) MOVQ    AX, (SP)
    // 调用 runtime.newobject
    0x0031 00049 (/main.go:10) PCDATA  $1, $0
    0x0031 00049 (/main.go:10) CALL    runtime.newobject(SB)
    // 将返回的指针放入 AX 寄存器
    0x0036 00054 (/main.go:10) MOVQ    8(SP), AX
    // 将 AX 寄存器中指针赋给指针 s
    0x003b 00059 (/main.go:10) MOVQ    AX, "".s+16(SP)
    // AX 寄存器中指针赋值给返回值
    0x0040 00064 (/main.go:11) MOVQ    AX, "".~r0+40(SP)
    0x0045 00069 (/main.go:11) MOVQ    24(SP), BP
    0x004a 00074 (/main.go:11) ADDQ    $32, SP
    0x004e 00078 (/main.go:11) RET
```

可以看到，`new`底层调用的是`runtime.newobject`来申请内存空间：

```go
func newobject(typ *_type) unsaef.Pointer {
  return mallocgc(typ.size, typ, true)
}
```

`newobject`的底层调用的是`mallocgc`在堆上按照`typ.size`的大小申请内存。

因此，`new`只会为结构体`Student`申请一片内存哦空间，不会为结构体中的指针`age`申请内存空间（`Student`的内存空间只包括`age`字段的指针空间，不包括实际值空间）。所以上面的程序中，在第 10 行的解引用操作就会因为访问无效的内存空间而出现 panic。

![](http://cnd.qiniu.lin07ux.cn/markdown/1640754762317-998d25aaa162.jpg)

对于结构体指针，一般使用`s := &Student{age: new(int)}`的方式赋值，这样能够清晰的知道结构体中的没一个字段是什么，避免不必要的错误。

这个问题同样出现在使用`new`为 slice、map、Channel 申请内存空间中，因为这些结构体中都包含指针字段，在使用`new`的时候就不会为具体的指针申请内存空间了。

### make

`make`返回的是复合型类型本身，也就是说，`make`会为类型中的每个字段都分配对应的内存空间。`make`的函数声明如下：

```go
func make(t Type, size ...IntegerType) Type
```

使用`make`来为类型进行初始化时，编译后会分别对应不同的方法：

* slice: `runtime.makeslice`
* map: `runtime.makemap_small`
* channel: `runtime.makechan`

比如，对于如下的代码：

```go
func main() {
  nums := make([]int, 10)
  (*nums)[0] = 1
  fmt.Println(*nums)[0])
}
```

如果这里的`make`换成`new`那么就和上面一样，获取`(*nums)[0]`的时候会出现 panic，因为 slice 的底层结构中是有指针的：

```go
type slice struct {
  data unsafe.Pointer // 指向用于存储切片数据的指针
  len  int
  cap  int
}
```

使用`go build -gcflags="-l -S -N" main.go`得到上面代码的汇编代码：

```asm
0x002f 00047 (main.go:7)     LEAQ    type.int(SB), AX
0x0036 00054 (main.go:7)     MOVQ    AX, (SP)
0x003a 00058 (main.go:7)     MOVQ    $8192, 8(SP)
0x0043 00067 (main.go:7)     MOVQ    $8192, 16(SP)
0x004c 00076 (main.go:7)     PCDATA  $1, $0
0x004c 00076 (main.go:7)     CALL    runtime.makeslice(SB)
0x0051 00081 (main.go:7)     MOVQ    24(SP), AX
0x0056 00086 (main.go:7)     MOVQ    AX, "".nums+88(SP)
0x005b 00091 (main.go:7)     MOVQ    $8192, "".nums+96(SP)
0x0064 00100 (main.go:7)     MOVQ    $8192, "".nums+104(SP)
```

可以看到，`make`在申请 slice 内存时，底层调用的是`runtime.makeslice`：

```go
func makeslice(et *_type, len, cap int) unsafe.Poiter {
  mem, overflow := math.MulUintptr(et.size, uintptr(cap))
  
  // 做合法检查
  if overflow || mem > maxAlloc || len < 0 || len > cap {
    mem, over := math.MulUinptr(et.size, uintptr(len))
    if overflow || mem > maxAlloc || len < 0 {
      panicmakeslicelen()
    }
    panicmakeslicecap()
  }
  // 做内存申请
  return mallocgc(mem, et, true)
}
```

`makeslice`申请内存的底层调用也是`mallocgc`，这点和`new`一样，但是`new`调用`mallocgc`时第一个参数（申请内存大小）用的是`type.size`，而`make`调用`mallocgc`时第一个参数是`mem`，从`math.MulUintptr`源码中可以看出，`mem`是 slice 的容量`cap`乘以`type.size`。因此使用`makeslice`可以成功的为切片申请全部所需内存：

```go
func MulUinptr(a, b uintptr) (uintptr, bool) {
  if a|b < 1<<(4*sys.ptrSize) || a == 0 {
    return a * b, false
  }
  overflow := b > maxUintptr/a
  return a * b, overflow
}
```

### 3. make 和 new 的区别

相同点：

* 都是 Go 语言中用于内存申请的关键字；
* 底层都是通过`mallocgc`方法来申请内存。

不同点：

* `make`返回的是复合结构体本身的指针，而`new`返回的是指向变量内存的指针；
* `make`只能为 slice、map、channel 申请内存空间，`new`则无限制。

![](http://cnd.qiniu.lin07ux.cn/markdown/1640764482565-038c3ae07252.jpg)


