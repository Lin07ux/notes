> 转摘：[内存逃逸，真令人头大！](https://mp.weixin.qq.com/s/ltev_KYH5C-F8aOoZiAqSw)

### 1. 概念

Go 程序会被编译器分为代码区、数据区、全局变量区、堆、栈五个区。其中：

* 栈：朱啊哟存储函数的入参、局部变量、出参中的资源，由编译器控制申请和释放。
* 堆：内存由程序员控制申请和释放，往往用于存放一些占用大块内存空间的变量，或是存在于函数局部但需供其他作用域使用的变量。

**内存逃逸是指：原本应该被存储在栈上的变量，因为一些原因被存储到了堆上。**

### 2. new 的情况

即使是使用`new`申请的内存，如果编译器发现`new`出来的内存在函数结束后就没有使用了，而且申请内存空间不是很大，那么`new`申请的内存空间还是会被分配在栈上，毕竟栈访问速度更快且易于管理。

比如，下面的这段代码：

```go
package main

// 函数内部 new 一块空间，外部无引用
func testNew() {
  t := new(int)
  *t = 1
}

func main() {
  testNew()
}
```

使用逃逸分析命令：

```shell
go build -gcflags="-m" main.go
```

得到结果如下：

```
./main.go:4:10: new(int) does not escape
./main.go:9:9: new(int) does not escape
```

可以看到通过`new`申请的内存空间被分配在了栈上，而不是堆上，未发生逃逸。

### 3. 常见场景

一般情况下，程序运行时会优先将变量放在栈上，但是出现一下情况时，会将变量放到堆上，也就是会出现内存逃逸现象。

#### 3.1 变量在函数外部存在引用

比如，对于下面的代码：

```go
func showPoint() *int {
  num := 1
  point := &num
  return point
}

func main() {
  var point *int
  point = showPoint()
  fmt.Println(*point)
}
```

执行逃逸分析命令，得到如下结果：

```go
./main.go:6:2: moved to heap: num
./main.go:14:14: *point escapes to heap
```

可以看到，局部变量`num`从栈逃逸到了堆上。原因也很简单，因为在`main`函数中对返回的指针`point`做了解引用操作，而`point`指向的变量`num`如果存储在栈上会因为函数`showPoint`的结束而被释放，那么后续就无法正常的使用`point`变量了。所以变量`num`也必须放在堆上。

#### 3.2 变量内存超过 64kb

在 Go 1.3 之后用连续栈取代了分段栈，Go 1.4 中连续栈的初始大小为 2kb。由于频繁的栈扩缩容会导致性能下降，所以在变量的大小达到阈值的 64kb 时会在堆上申请内存，而不是栈上。

比如，下面的例子：

```go
package main

func testLarge() {
  nums1 := make([]int, 8191)
  nums2 := make([]int, 8192)
}

func main() {
  testLarge()
}
```

在`testLarge`中 make 了两个切片，长度分别为 8191 和 8192，申请的内存空间分别为`8191*8/1024 < 64kb`和`8192*8/1024 = 64kb`。

通过如下命令查看逃逸的详细信息(参数多一个`-m`查看更详细的逃逸信息)：

```
go build -gcflags="-m -m" main.go
```

对应的逃逸分析结果如下：

```
./main.go:5:16: make([]int, 8192) escapes to heap:
./main.go:5:16:   flow: {heap} = &{storage for make([]int, 8192)}:
./main.go:5:16:     from make([]int, 8192) (too large for stack) at ./main.go:5:16
./main.go:4:16: make([]int, 8191) does not escape
```

可以看到，大小为 64kb 的`nums2`变量逃逸到了堆上，而小于 64kb 的变量`nums1`未发生逃逸。

#### 3.3 make 创建的切片的值为指针

当创建的切片中的元素类型为指针时，也会发生逃逸，此时和切片占用的内存大小无关：

```go
package main

func testSlice() {
  nums := make([]*int, 0)
  a := 1
  nums[0] = &a
}

func main() {
  testSlice()
}
```

执行逃逸分析，得到结果如下：

```
./main.go:5:2: moved to heap: a
./main.go:4:14: make([]*int, 0) does not escape
```

这里之所以 make 的变量占用内存比较小也会发生逃逸，就是因为切片的元素为指针类型。

假设这里创建的切片存储了大量的指针，那么对于当中的每一个指针都需要做变量在外部是否被引用的验证。这样含有大量元素的切片取指针验证操作会带来性能的损耗。所以，**当切片中存储的是指针时，切片中的指针元素指向的栈上的变量全部都会放在堆上。**

