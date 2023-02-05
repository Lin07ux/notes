> 转摘：[为什么 Go 不支持 []T 转换为 []interface](https://mp.weixin.qq.com/s/jsdGV31yT5AR07BzRovWVw)

### 1. interface{} 参数

在 Go 中，如果用`interface{}`作为函数参数的话，就可以传任意类型的数据，然后通过类型断言来进行转换。

例如：

```go
package main

import "fmt"

func foo(v interface{}) {
  if v1, ok1 := v.(string); ok1 {
    fmt.Println(v1)
  } else if v2, ok2 := v.(it); ok2 {
    fmt.Println(v2)
  }
}

func main() {
  foo(233)
  foo("666")
}
```

运行这段程序可以看到不论是 int 类型还是 string 类型，都能正常输出结果。

### 2. []T 能否转换为 []interface{}

既然 interface 类型的参数表示可以传入任意类型，那么能否将`[]T`转换为`[]interface{}`类型呢？

比如下面的这段代码：

```go
func foo([]interface{}) {
  /* do something */
}

func main() {
  var a []string = []string{"hello", "world"}
  foo(a)
}
```

编译时即发生错误，而且如果直接通过`b := []interface{}(a)`的方式进行转换，也是会报错：

```text
cannot use a (type []string) as type []interface {} in function argument
```

正确的转换方式是将`[]string`中的每个元素都转换为`interface{}`类型：

```go
b := make([]interface{}, len(a), len(a))
for i := range a {
  b[i] = a[i]
}
```

### 3. 官方解释

为何不能将`[]T`类型的数据直接作为`[]interface{}`类型使用呢？官方 Wiki 中有如下的说明：

> The first is that a variable with type `[]interface{}` is not an interface! It is a slice whose element type happens to be interface{}. But even given this, one might say that the meaning is clear. Well, is it? A variable with type `[]interface{}` has a specific memory layout, known at compile time. Each interface{} takes up two words (one word for the type of what is contained, the other word for either the contained data or a pointer to it). As consequence, a slice with length  and with type `[]interface{}` is backed by a chunk of data that is N*2 words long. This is different than the chunk of data backing a slice with type `[]MyType` and the same length. Its chunk of data will be `N*sizeof(MyType)` words long. The result is that you cannot quickly assign something of type `[]MyType` to something of type `[]interface{}`; the data behind them just look different.

大概意思就是说，之所以`[]T`不能作为`[]interface{}`类型，主要有两方面的原因：

1. `[]interface{}`类型并不是`interface{}`，它依旧是一个切片，只不过碰巧其元素是`interface{}`；
2. `[]interface{}`是有特殊的内存布局的，它与`interface{}`不一样，与`[]T`的内存布局也不一样。

下面对`[]interface{}`的内存布局进行详细说明。

#### 3.1 内存布局

首先看下 slice 在内存中是如何存储的。在`src/runtime/slice.go`中，slice 的定义如下：

```go
type slice struct {
  data unsafe.Pointer // 底层数组指针
  len  int // 切片长度
  cap  int // 切片容量
}
```

比如，对于如下的切片：

```go
is := []int64{0x55, 0x22, 0xab, 0x9}
```

其布局如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1675523688)

在 64 位机器中，图中的每个正方形所占用的空间是 8 个字节，所以`ptr`所指向的底层数组占用空间就是 4 个正方形，也就是 32 字节。

由于`interface{}`在 Go 中对应两种类型：

```go
type iface struct {
  tab  *itab
  data unsafe.Pointer
}

type eface struct {
  _type *_type
  data  unsafe.Pointer
}
```

每个`interface{}`都包含两个指针，也就是会占用两个正方形：第一个指针指向`itab`或`_type`，第二个指针指向实际的数据。

所以对于`[]interface{}`类型来说，其内存布局如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1675524347)

所以，不能直接将`[]int64`类型的数据直接传给`[]interface{}`。

#### 3.2 运行时内存布局

下面从程序实际运行过程中看看内存的分布情况。

对于如下的代码：

```go
package main

var sum int64

func addUpDirect(s []int64) {
  for i := 0; i < len(s); i++ {
    sum += s[i]
  }
}

func addUpViaInterface(s []interface{}) {
  for i := 0; i < len(s); i++ {
    sum += s[i].(int64)
  }
}

func main() {
  is := []int64{0x55, 0x22, 0xab, 0x9}
  
  addUpDirect(is)
  
  iis := make([]interface{}, len(is))
  for i := 0; i < len(is); i++ {
    iis[i] = is[i]
  }
  
  addUpViaInterface(iis)
}
```

使用 Delve 来进行调试：

```shell
dlv debug slice-layout.go
Type 'help' for list of commands.
(dlv) break slice-layout.go:27
Breakpoint 1 set at 0x105a3fe for main.main() ./slice-layout.go:27
(dlv) c
> main.main() ./slice-layout.go:27 (hits goroutine(1):1 total:1) (PC: 0x105a3fe)
    22:  iis := make([]interface{}, len(is))
    23:  for i := 0; i < len(is); i++ {
    24:   iis[i] = is[i]
    25:  }
    26:
=>  27:  addUpViaInterface(iis)
    28: }
```

在进入`addUpViaInterface()`函数之前设置断点，然后打印`is`变量的地址：

```shell
(dlv) p &is
(*[]int64)(0xc00003a740)
```

然后使用这个地址看下`is`这个 slice 在内存中包含了哪些内容：

```shell
(dlv) x -fmt hex -len 24 0xc00003a740
0xc00003a740:   0x10   0xa7   0x03   0x00   0xc0   0x00   0x00   0x00
0xc00003a748:   0x04   0x00   0x00   0x00   0x00   0x00   0x00   0x00
0xc00003a750:   0x04   0x00   0x00   0x00   0x00   0x00   0x00   0x00
```

输出中，每行表示 8 个字节，也就是前面图片中的一个正方形：

* 第一行是指向数据的地址，值为`0xc00003a710`；
* 第二行的值为 4，表示切片长度；
* 第三行的值也是 4，表示切片容量。

使用 slice 的指向数据的地址看下其数据有什么（已知其包含 4 个 int64 类型数据，占用 32 字节）：

```shell
(dlv) x -fmt hex -len 32 0xc00003a710
0xc00003a710:   0x55   0x00   0x00   0x00   0x00   0x00   0x00   0x00
0xc00003a718:   0x22   0x00   0x00   0x00   0x00   0x00   0x00   0x00
0xc00003a720:   0xab   0x00   0x00   0x00   0x00   0x00   0x00   0x00
0xc00003a728:   0x09   0x00   0x00   0x00   0x00   0x00   0x00   0x00
```

这就是一片连续的存储空间，保存着实际的数据，每一行对应着`is`切片中存储的一个数据。

使用同样的方式查看下`iis`的内存布局：

```shell
(dlv) p &iis
(*[]interface {})(0xc00003a758)
(dlv) x -fmt hex -len 24 0xc00003a758
0xc00003a758:   0x00   0x00   0x09   0x00   0xc0   0x00   0x00   0x00
0xc00003a760:   0x04   0x00   0x00   0x00   0x00   0x00   0x00   0x00
0xc00003a768:   0x04   0x00   0x00   0x00   0x00   0x00   0x00   0x00
```

可以看到，`iis`切片的布局和`is`是一样的，主要的不同是其所指向的数据地址和数据的不同：

```shell
(dlv) x -fmt hex -len 64 0xc000090000
0xc000090000:   0x00   0xe4   0x05   0x01   0x00   0x00   0x00   0x00
0xc000090008:   0xa8   0xee   0x0a   0x01   0x00   0x00   0x00   0x00
0xc000090010:   0x00   0xe4   0x05   0x01   0x00   0x00   0x00   0x00
0xc000090018:   0x10   0xed   0x0a   0x01   0x00   0x00   0x00   0x00
0xc000090020:   0x00   0xe4   0x05   0x01   0x00   0x00   0x00   0x00
0xc000090028:   0x58   0xf1   0x0a   0x01   0x00   0x00   0x00   0x00
0xc000090030:   0x00   0xe4   0x05   0x01   0x00   0x00   0x00   0x00
0xc000090038:   0x48   0xec   0x0a   0x01   0x00   0x00   0x00   0x00
```

由于`iis`切片中的数据是`interface{}`类型，所以每个元素需要 16 字节（也就是两行）来存储：前面一行存储的是`itab`的地址，后面一行存储的是指向实际的数据的地址。

从上面的数据中可以看出，`iis`中每个元素指向的`itab`都是相同的，表示它们是相同的类型；而每个元素的实际数据的地址并不相同，打印数据地址内容：

```shell
(dlv) x -fmt hex -len 8 0x010aeea8
0x10aeea8:   0x55   0x00   0x00   0x00   0x00   0x00   0x00   0x00
(dlv) x -fmt hex -len 8 0x010aed10
0x10aed10:   0x22   0x00   0x00   0x00   0x00   0x00   0x00   0x00
(dlv) x -fmt hex -len 8 0x010af158
0x10af158:   0xab   0x00   0x00   0x00   0x00   0x00   0x00   0x00
(dlv) x -fmt hex -len 8 0x010aec48
0x10aec48:   0x09   0x00   0x00   0x00   0x00   0x00   0x00   0x00
```

可以看到，`iis`中存储的实际数据即为`is`切片中每个元素的值，这和前面的分析是一致的。


