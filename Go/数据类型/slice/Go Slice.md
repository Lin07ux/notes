> 转摘：[Go切片与技巧（附图解）](https://mp.weixin.qq.com/s/IQRHWNUnxiaCDleayNVRVg)

Go 中切片是基于数组的，但是由于数组需要固定长度，很多时候并不是很灵活，而切片则无此限制，更为灵活、更强大。

## 一、声明

创建切片有多种方式：变量声明、切片字面量、make 创建、new 创建、从切片/数组截取。

### 1.1 变量声明

Slice 的变量声明方式和其他变量声明类似：

```go
var s []byte
```

这种声明的切片变量其默认值为 nil，容量和长度默认都是 0：

```go
var x []byte
fmt.Println(cap(x)) // 0
fmt.Println(len(x)) // 0
fmt.Println(x == nil) // true
```

### 1.2 切片字面量

当使用字面量来声明切片时，其语法与使用字面量声明数组非常类似，只是不需要指定任何长度信息：

```go
a := []string{"johnny", "太白技术"}  // 是切片声明，少了长度的指定
a := [2]string{"johnny", "太白技术"} // 是数组声明，多了长度的指定
```

### 1.3 make

使用内置函数`make`可以指定长度和容量来创建切片，语法为`func make([]T, len, cap) []T`。其中：

* `T` 代表要创建的切片的元素类型。
* `len` 表示要创建的切片的长度。
* `cap` 表示要创建的切片的容量。该参数是可选的，如果不设置则会将设默认为`len`参数的值。

`make`会分配一个数组，并返回一个引用了该数组的切片。

比如，创建一个空切片：

```go
y := make([]int, 0)
fmt.Println(cap(y), len(y), y == nil) // 0 0 false
```

创建一个长度为 5、容量为 5 的切片，其每项的值都默认初始化为零值：

```go
s := make([]byte, 5, 5)
fmt.Println(s) // [0 0 0 0 0]

// 省略 cap 参数时，cap 和 len 一样
o := make([]byte, 5)
fmt.Println(o) // [0 0 0 0 0]
fmt.Println(len(o), cap(o)) // 5 5
```

### 1.4 new

使用 new 创建切片，返回的是一个值为 nil 的空切片：

```go
n := *new([]int)
fmt.Println(n == nil) // true
```

### 1.5 从切片、数组截取

还可以基于切片和数组来创建一个新的切片，新的切片与源切片/数组是共享底层空间的。也就是说，如果修改新切片的值，就可能会影响到源切片和数组（新切片未发生扩容时）：

```go
n := [5]int{1, 2, 3, 4, 5}
n1 := n[1:]
fmt.Println(n1) // [2 3 4 5]

n2 := n1[1:]
fmt.Println(n2) // [3 4 5]

n2[1] = 6
fmt.Println(n2) // [3 6 5]
fmt.Println(n1) // [2 3 6 5]
fmt.Println(n)  // [1 2 3 6 5]
```

## 二、操作

### 2.1 赋值

对切片中的项进行和数组项赋值一样，直接指定对应的位置即可：

```go
s := make([]int, 3)
s[1] = 1
fmt.Println(s) // [0 1 0]
```

### 2.2 追加

可以使用`append()`方法向切片中追加元素，而且调用返回的结果是一个新的切片，所以需要将返回值赋值后保存：

```go
n := make([]int, 0)
n = append(n, 1)                 // 添加一个元素
n = append(n, 2, 3, 4)           // 添加多个元素
n = append(n, []int{5, 6, 7}...) // 添加一个切片
fmt.Println(n)                   // [1 2 3 4 5 6 7]
```

当`append`操作追加数据时，切片容量如果不够，就会触发扩容。接上面的例子：

```go
fmt.Println(cap(n)) // 8
n = append(n, 8, 9, 10)
fmt.Println(cap(n)) // 16
```

一开始切片容量是 8，增加数据之后，容量变成了 16，也就是说发生了扩容。

### 2.3 截取

Go 语言提供了两种切片的截取表达式：简单表达式、扩展表达式。

#### 2.3.1 简单表达式

**简单切片表达式的格式为：`slice[low:high]`，表示的是左闭右开`[low, high)`的区间**，街区长度为`high - low`。

比如，从一个切片中切出一个新的切片，就可以像下面的操作一样：

```go
n := []int{1, 2, 3, 4, 5, 6}
fmt.Println(n[1:4]) // [2 3 4]
```

这里使用的`n[1:4]`来截取，表示从原切片中的第 2 项开始，截取到第 4 项，截取的长度为 3。

切片表达式的开始 low 和结束索引 high 是可选的，分别默认为零和切片的长度：

```go
n := []int{1, 2, 3, 4, 5, 6}
fmt.Println(n[:4]) // [1 2 3 4]
fmt.Println(n[1:]) // [2 3 4 5 6]
fmt.Println(n[:])  // [1 2 3 4 5 6]
```

**边界问题**：

    `n[low:high]`中 low 和 high 的取值要满足一定的条件，否则会发生越界 panic。

    * 当 n 为数组或字符串表达式时，取值关系为：`0 <= low <= high <= len(n)`；
    * 当 n 为切片时，取值关系为：`0 <= low <= high <= cap(n)`。

**字符串截取**

    从字符串中进行截取，得到的是一个新的字符串：
    
    ```go
    s := "hello world"
    s1  := s[6:]
    fmt.Println(s1)                 // world
    fmt.Println(reflect.TypeOf(s1)) // string
    ```

#### 2.3.2 扩展表达式

**扩展表达式**还支持在截取的时候设置新切片的容量，**格式为：`slice[low:high:max]`**。这里`low`和`high`跟简单表达式中的含义一样，**`max`则表示新生成的切片的最大容量**。

扩展表达式和简单表达式之间的区别就在于新切片的容量的计算上的区别：

* 使用简单表达式截取时，新切片的容量与截取的起始位置和原切片的容量/数组的长度有关：`cap(newSlice) = cap(slice) - low`。
* 使用扩展表达式时，由于指定了`max`，所以新切片的容量计算方式就是：`max - low`。

比如：

```go
n := []int{1, 2, 3, 4, 5, 6}
n1 := n[1:4]
n2 := n[1:4:5]
fmt.Println(cap(n), cap(n1), cap(n2)) // 6 5 4
```

需要注意的是，**`max`的值不能大于原切片的容量(数组的长度)**，否则会出现越界 panic。

## 三、技巧

Go 在 Github 的官方 WiKi 上介绍了切片的技巧 [SliceTricks](https://github.com/golang/go/wiki/SliceTricks)，而 [Go Slice Tricks Cheat Sheet](https://ueokande.github.io/go-slice-tricks/) 项目则基于 SliceTricks 做了一系列的图，比较直观。

### 3.1 AppendVector

追加一个切片，就直接使用`append`即可：

```go
a = append(a, b...)
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645523520287-b8ebe4034d3f.jpg)

### 3.2 Copy

有三种 copy 方法，不过一般使用前两种方式即可：

```go
// 方法一
b := make([]T, len(a))
copy(b, a)

// 方法二：效率一般比上面的写法慢，但是如果有更多的 copy 项，那其效率更好
b = append([]T(nil), a...)
b = append(a[:0:0], a...)

// 方法三：等价于 make+copy，但其实会更慢
b = append(make([]T, 0, len(a)), a...)
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645524187319-3f1535d30f7f.jpg)

### 3.3 Cut

裁掉切片中`[i, j)`之间的元素：

```go
a = append(a[:i], a[j:]...)
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645524537399-e2d9092c01fd.jpg)

### 3.4 Cut (GC)

如果切片中是指针的话，上面`Cut`的实现会存在内存泄露的问题，需要对删除的元素设置为 nil，等待 GC：

```go
copy(a[i:], a[j:])
for k, n := len(a)-j+i, len(a); k < n; k++ {
  a[k] = nil // or the zero value of T
}
a = a[:len(a)-j+i]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645524705345-045fa5148df4.jpg)

### 3.5 Delete

删除指定索引位置的元素：

```go
a = append(a[:i], a[i+1:]...)
// or
a = a[:i+copy(a[i:], a[i+1:])]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645524792483-bad09f6c3de7.jpg)

### 3.6 Delete (GC)

上面的方式删除之后，最后一个位置还引用着之前的元素，可能会存在内存泄露问题，需要将其设置为零值：

```go
copy(a[:i], a[i+1:])
a[len(a)-1] = nil // or the zero value of T
a = a[:len(a)-1]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645524900057-a3f82ee52bba.jpg)

### 3.7 Delete without preserving order

删除索引位置 i 的元素，然后把最后一位放到索引位置 i 上，并把最后一个元素删除掉。下面的实现方式底层并没有发生切片复制操作：

```go
a[i] = a[len(a)-1]
a = a[:len(a)-1]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645525011714-57b1e965a6e0.jpg)

同样，这样做也有可能造成内存泄露，需要明确的将最后一项设置为零值之后再删除：

```go
a[i] = a[len(a)-1]
a[len(a)-1] = nil // or the zero value of T
a = a[:len(a)-1]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645525021089-2aff793eec35.jpg)

### 3.8 Filter (in place)

原地删除切片中的元素：

```go
n := 0
for _, x := range a {
  if keep(x) {
    a[n] = x
    n++
  }
}
a = a[:n]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645525542718-9258b5243a29.jpg)

### 3.9 Insert

有两种方式，后者可以减少一次 copy：

```go
// 方法一：
a = append(a[:i], append([]T{x}, a[i:]...)...)

// 方法二：
a = append(a, 0) // 先添加一个 0 值
copy(a[i+1:], s[i:])
s[i] = x
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645525692070-7df9c3ed624c.jpg)

### 3.10 InsertVector

将一个动态大小的数组/切片顺序的插入到切片中的某个位置上，有两种方式：

简单方式：

```go
a = append(a[:i], append(b, a[i:]...)...)
```

复杂方式：

```go
func InsertVector(s []int, k int, vs ...int) []int {
   if n := len(s) + len(vs); n <= cap(s) {
     s2 := s[:n]
     copy(s2[k:], vs)
     copy(s2[k+len(vs):], s[k:])
     return s2
   }
   s2 := make([]int, len(s) + len(vs))
   copy(s2, s[:k])
   copy(s2[l:], vs)
   copy(s2[k+len(vs):], s[k:])
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528439948-61aa14ffa389.jpg)

### 3.11 Push & Pop

将元素压入切片直接用 append 即可：

```go
a = append(a, x)
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528493468-1efd2b347bdd.jpg)

将切片末尾的元素弹出则需要进行切片截取：

```go
x, a := a[len(a)-1], a[:len(a)-1]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528541018-be2e3064eab9.jpg)

### 3.12 Push Front/Unshift & Pop Front/Shift

将元素压入到切片首部，需要进行一次拷贝追加：

```go
a = append([]T{x}, a...)
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528606796-ba3e8ad5accc.jpg)

将切片首部的元素弹出，则直接截取即可：

```go
x, a := a[0], a[1:]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528642161-a7818ff91263.jpg)

### 3.13 Filtering without allocating

下面的方式在进行切片数据过滤的时候，b 基于原来的 a 存储空间来操作，没有重新生成新的存储空间（前面的`Filter (in place)`已经足够）：

```go
b := a[:0]
for _, x := range a {
  if f(x) {
    b = append(b, x)
  }
}
```

为了让截取之后没有使用的存储被 GC 掉，需要设置成 nil：

```go
for i := len(b); i < len(a); i++ {
  a[i] = nil // or the zero value of T
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528826362-6704337bad0d.jpg)

### 3.14 Reversing

反转操作：

```go
for i := len(a)/2-1; i >= 0; i-- {
  opp := len(a)-1-i
  a[i], a[opp] = a[opp], a[i]
}
```

或者更明确的定义中间变量：

```go
for left, right := 0, len(a)-1; left < right; left, right = left+1, right-1 {
  a[left], a[right] = a[right], a[left]
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645528960070-de6bde473145.jpg)

### 3.15 Shuffling

洗牌算法。思想就是从原始数组中随机抽取一个新的数字插入到对应的位置上：

```go
for i := len(a) - 1; i > 0; i-- {
  j := rand.Intn(i + 1)
  a[i], a[j] = a[j], a[i]
}
```

Go 1.10 之后还有内置的函数 [Shuffle](https://pkg.go.dev/math/rand#Shuffle)。

![](http://cnd.qiniu.lin07ux.cn/markdown/1645529102106-1cdd7ccbc9bc.jpg)

### 3.16 Batching with minimal allocation

做批处理大的切片的时候，可以使用这个技巧：

```go
actions := []int{0, 1, 2, 3, 4, 5, 6, 7, 8, 9}
batchSize := 3
batches := make([][]int, 0, (len(actions) + batchSize - 1) / batchSize)

for batchSize < len(actions) {
  actions, batches = actions[batchSize:], append(batches, actions[:batchSize:batchSize])
}
batches = append(batches, actions)

// 结果：
// [[0 1 2] [3 4 5] [6 7 8] [9]]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645529310744-3a409f4bb830.jpg)

### 3.17 In-place deduplicate (comparable)

删除有序数组中的重复项：

```go
in := []int{3, 2, 1, 4, 3, 2, 1, 4, 1} // any item can be sorted
sort.Ints(in)
j := 0
for i := 1; i < len(in); i++ {
  if in[j] == in[i] {
    continue
  }
  j++
  // preserve the original data
  // in[i], in[j] = in[j], in[i]
  // only set what is required
  in[j] = in[i]
}
result := in[:j+1]
fmt.Println(result) // [1 2 3 4]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645529507227-ffe2e12f87b1.jpg)

### 3.18 Move to front, or prepend if not present, in place if possible

移动指定元素到头部，其思想就是先将第一个元素设置为要移动的元素，然后逐个将后续的元素向后移位。如果某个元素与要移动的元素相同，则直接返回：

```go
// moveToFront moves needle to the front of haystack, in place if possible.
func moveToFront(needle string, haystack []string) []string {
  if len(haystack) != 0 && haystack[0] == needle {
    return haystack
  }
  
  prev := needle
  for i, elem := range haystack {
    switch {
    case i == 0:
      haystack[0] = needle
      prev = elem
    case elem == needle:
      haystack[i] = prev
      return haystack
    default:
      haystack[i] = prev
      prev = elem
    }
  }
  return append(haystack, prev)
}
```

使用示例：

```go
haystack := []string{"a", "b", "c", "d", "e"}
haystack = moveToFront("c", haystack) // [c a b d e]
haystack = moveToFront("f", haystack) // [f c a b d e]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645529975622-1cf133768050.jpg)

![](http://cnd.qiniu.lin07ux.cn/markdown/1645529979872-34b2e4df4a8d.jpg)

### 3.19 Sliding Window

根据 size 的滑动窗口输出：

```go
func slidingWindow(size int, input []int) [][]int {
  // returns the input slice as the first elemnt
  if len(input) <= size {
    return [][]int{input}
  }
  
  // allocate slice at the precise size we need
  r := make([][]int, 0, len(input)-size+1)
  
  for i, j := 0, size; j <= len(input); i, j = i+1, j+1 {
    r = append(r, input[i:j])
  }
  
  return r
}
```

测试效果如下：

```go
result := slidingWindow(2, []int{1, 2, 3, 4, 5})
fmt.Println(result) // [[1 2] [2 3] [3 4] [4 5]]
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1645530237324-7f219db121a3.jpg)

## 四、总结

### 4.1 特点

Go 切片本质上是一个结构体，保存了长度、底层数组的容量、底层数组的指针信息。

因为**切片包含对底层数组的引用**，所以如果将一个切片分配给另一个切片，则两者都引用同一个数组。

切片底层的数据结构如下(`src/runtime/slice.go`)：

```go
type slice struct {
  data unsafe.Pointer // 指向底层数组
  len  int // 切片长度
  cap  int // 底层数组容量
}
```

切片的类型规范是`[]T`，其中 T 是切片元素的类型。**与数组类型不同，切片类型没有指定长度**。


### 4.2 性能提升

1. 适当选用数组

    数组是基本数据类型，而切片是包装类型，通过`slice.array`指针进行寻址，多了一个二次寻址的过程。因此在明确数列的长度不会变化时，会优先选用数组而不是切片。

2. 设置合适的容量

    切片在扩容时如果需要重新申z请内存空间做值拷贝，将会非常耗时。这也是容量`cap`存在的意义。所以在声明切片时，尽可能的预见所需的大小并赋值给`cap`，避免切片的扩容。
    
3. 切片的拷贝

    在 Go 中，当内存块还存在外部引用时，该内存将无法被释放。因此在做切片的拷贝时，如果只是从大切片上获取很小的一部分使用，应该使用`copy`进行拷贝，而不是用等号进行赋值。这样可以避免大切片由于这一块小内存的引用而一直得不到释放，从而导致内存泄露。

