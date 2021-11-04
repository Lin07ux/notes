> 转摘：[Go 看源码必会知识之 unsafe 包](https://mp.weixin.qq.com/s/wdFdPv3Bdnhy5pc8KL6w6w?forceh5=1)

### 1. 什么是 unsafe

Go 语言被设计成一门强类型的静态语言，那么一个变量在定义之后其类型就不能改变了，静态也是意味着类型检查在运行前就做了，所以 Go 语言中不允许两个指针类型进行转换。

强制类型转换会引起各种各样的麻烦，有时这些麻烦很容易被察觉，有时候却又隐藏极深，难以察觉。**Go 出于安全考虑就禁止了这种操作**。

比如，C 语言中是支持类型强制转换的，任何非`void`类型的指针都可以和`void`类型的指针相互指派，也就是可以通过`void`类型指针作为中介，实现不同类型的指针间的相互转换。下面是一个示例：

```c
int main() {
  double pi = 3.1415926;
  double *pv = &pi;
  void *temp = pv;
  int *p = temp;
}
```

这个例子中，变量`pv`指向的空间本是一个双精度的数据，占 8 个字节。但是经过转换后，`p`变量指向的是一个 4 字节的 int 类型。这种发生了内存阶段的设计缺陷会在转换后进行内存方式时存在安全隐患。

虽然类型转换是不安全的，但是在一些特殊场景下，使用了它可以打破 Go 的类型和内存安全机制，绕过类型系统的抵消，提高运行效率。所以，Go 标准库中提供了一个`unsafe`包。之所以叫这个名字，就是不推荐大家使用，当并非不能用，只是需要掌握的特别好。

### 2. unsafe 提供的方法

`unsafe`源码中，向外部提供了 3 个方法：

* `func Sizeof(x ArbitraryType) uintptr`

    该方法的主要用作是返回类型 x 所占据的的字节数，但并不包含 x 所指向的具体内容的大小。与 C 语言标准库中的`Sizeof()`方法的功能一样。
    
    比如，在 32 位机器上，一个指针返回的大小就是 4 字节。

* `func Offsetof(x ArbitraryType) uintptr`

    返回结构体成员在内存中的位置离结构体其实处的字节数，即偏移量。结构体的第一个字段的偏移量总是 0。
    
    注意：其入参必须是一个结构体，其返回值总是一个常量。

* `func Alignof(x ArbitraryType) uintptr`

    返回一个类型的对齐值，也可以叫做对齐系数或对齐倍数。
    
    对齐值是一个和内存对其有关的值，合理的内存对齐可以提高内存的读写性能。一般对齐值是`2^n`，最大不会超过 8（手内存对齐影响），最小值为 1：
    
    - 对于 struct 结构体类型的变量，对齐值为其内的每一个字段的对齐值中的最大值；
    - 对于 array 数组类型的变量，等于构成数组的元素类型的对齐倍数；
    - 没有任何字段的空`struct{}`和没有任何元素的 array 占据的内存空间大小为 0，但是根据其所处结构体中的位置，可能会为其分配对齐空间。
    
    获取对齐值还可以使用反射包中的函数，也就是说：`unsafe.Alignof(x)`等价于`reflect.TypeOf(x).Align()`。

### 3. unsafe 的类型

`unsafe`包中还定义了两个类型：

```go
type ArbitraryType int
type Pointer *ArbitraryType
```

其中，`ArbitraryType`代表着任意类型的意思，而`Pointer`作为一个指针类型，是作为一个类似`void *`一样的通用指针类型来使用的。

`unsafe`包的三个方法接收的都是`ArbitraryType`类型，而返回的是则是`uintptr`内建类型。`uintptr`类型是可计算特性的，而且能够与`unsafe.Pointer`类型相互转换。这样就能通过计算和转换，来访问特定的内存空间，达到对不同的内存读写的目的。

这三者的特点如下：

* `*T`：普通类型的指针类型，用于传递对象的地址，不能进行指针运算；
* `unsafe.Pointer`：通用指针类型，用于转换不同类型的指针，不能进行指针运算，不能读取内部存储的值（需要转换为某一具体类型的普通指针）；
* `uintptr`：用于指针运算。

需要注意的是，`uintptr`是一种简单的内建类型，GC 不会把它当做指针，所以它无法持有对象，`uintptr`类型的目标会被回收掉。

三者的关系就是：`unsafe.Pointer`是桥梁，可以让任意类型的指针实现相互转换，也可以将任意类型的指针转换为`uintptr`进行指针运算。也就是说，`uintptr`是用来与`unsafe.Pointer`打配合，实现指针运算功能的。图示如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635857754240-20decb970886.jpg)

### 4. unsafe.Pointer 的使用

`unsafe.Pointer`是一个通用指针类型，要想对其指向的数据进行使用，一般需要对其指向的数据进行强制类型转换。

下面是一个简单的使用示例：

```go
func main() {
  number := 5
  pointer := &number
  fmt.Printf("number: addr: %p, value: %d\n", pointer, *pointer)
  
  float32Number := (*float32)(unsafe.Pointer(pointer))
  *float32Number = *float32Number + 3
  fmt.Printf("float32: addr: %p, value: %f\n", float32Number, *float32Number)
}
```

输出结果类似如下：

```
number: addr: 0xc000122008, value: 5
float32: addr: 0xc000122008, value: 0.000000
float32: addr: 0xc000122008, value: 3.000000
```

由这个结果可以看到：使用`unsafe.Pointer`强制类型转换后，指针指向的地址是不会发生改变的，只是类型发生了改变。

总结一下基本的使用方法：**先把`*T`类型转换成`unsafe.Pointer`类型，然后再进行强制转换成需要的指针类型即可。**

### 5. unsafe.Sizeof() 的使用

`unsafe.Sizeof()`方法用来计算任意类型变量的字节数。需要注意的是，其返回值仅仅表示类型的大小，而非实际存储的数据的大小。

```go
type User struct {
	Name string
	Age uint32
	Gender bool
}

func main() {
	fmt.Println(unsafe.Sizeof(true))  // 1
	fmt.Println(unsafe.Sizeof(int8(0)))  // 1
	fmt.Println(unsafe.Sizeof(int16(10)))  // 2
	fmt.Println(unsafe.Sizeof(int32(190)))  // 4
	fmt.Println(unsafe.Sizeof(int(10)))  // 8（64 位 CPU）
	fmt.Println(unsafe.Sizeof("asong"))  // 16
	fmt.Println(unsafe.Sizeof([]int{1,3,4}))  // 24（data 指针、len、cap）
	fmt.Println(unsafe.Sizeof(User{"lin07ux", 28, true})) // 24
}
```

这里，`int`类型与 CPU 位数相关：如果 CPU 是 32 位的，那么`int`就占 4 字节；如果 CPU 是 64 位的，那么`int`就占 8 字节。

另外，自定义的`User`结构体占用了 24 字节，是因为最后的`Gender`属性被自动做了对齐导致的。

### 6. unsafe.Alignof() 的使用

该函数用来返回类型的对齐值。

```go
type User struct {
	Name string
	Age uint32
	Gender bool
}

func main() {
	var b bool
	var i8 int8
	var i16 int16
	var i64 int64
	var f32 float32
	var s string
	var m map[string]string
	var p *int32
	var u User

	fmt.Println(unsafe.Alignof(b))   // 1
	fmt.Println(unsafe.Alignof(i8))  // 1
	fmt.Println(unsafe.Alignof(i16)) // 2
	fmt.Println(unsafe.Alignof(i64)) // 8
	fmt.Println(unsafe.Alignof(f32)) // 4
	fmt.Println(unsafe.Alignof(s))   // 8
	fmt.Println(unsafe.Alignof(m))   // 8
	fmt.Println(unsafe.Alignof(p))   // 8
	fmt.Println(unsafe.Alignof(u))   // 8
}
```

除了`int`、`uintptr`这些依赖于 CPU 位数的基本类型，其他基本类型的对齐值都是固定的。而结构体的对齐值为其成员对齐值中的最大值，而且结构体的对齐值还涉及到内存对齐。

### 7. unsafe.Offsetof() 的使用

该函数是用来获取字段相对结构体起始处的偏移量的，而且结构体的第一个字段的偏移量总是 0。

对于下面的示例：

```go
type User struct {
	Name string
	Age uint32
	Gender bool
}

func main() {
	user := User{
		Name:   "Lin07ux",
		Age:    31,
		Gender: true,
	}

	userPointer := unsafe.Pointer(&user)

	//namePointer := (*string)(unsafe.Pointer(uintptr(userPointer) + unsafe.Offsetof(user.Name)))
	namePointer := (*string)(userPointer)
	*namePointer = "Lin07ux Lin"

	agePointer := (*uint32)(unsafe.Pointer(uintptr(userPointer) + unsafe.Offsetof(user.Age)))
	*agePointer = 20

	genderPointer := (*bool)(unsafe.Pointer(uintptr(userPointer) + unsafe.Offsetof(user.Gender)))
	*genderPointer = false

	fmt.Printf("user: %v\n", user)
}
```

输出结果为：

```
user: {Lin07ux Lin 20 false}
```

可以看到，通过使用结构体的地址和其字段的偏移量，可以直接引用该字段的地址，并直接可以直接修改字段的值。当前，这样做的前提是能够很好的还原字段的类型，否则就有可能造成内存截断的安全隐患。

### 8. 经典应用：string 和 byte[] 的相互转换

正常情况下，`string`和`byte[]`类型之间的转换会使用如下的方式：

```go
// string to []byte
str1 := "Golang梦工厂"
by := []byte(s1)

// []byte to string
str2 := string(by)
```

使用这种方式进行转换都会涉及到底层的数据拷贝，要想实现零拷贝，可以使用`unsafe.Pointer`进行强制类型转换来实现。使用`unsafe.Pointer`可以使得`string`和`byte[]`指向同一个底层数据。

在`reflect`包中，`string`和`slice`对应的结构体定义如下：

```go
type StringHeader struct {
  Data uintptr
  Len  int
}

type SliceHeader struct {
  Data uintptr
  Len  int
  Cap  int
}
```

`StringHeader`代表的是 string 类型的运行时表现形式，`SliceHeader`是 slice 类型的运行时表现形式。对比两者可以发现，它们之间只有一个`Cap`字段的不同，所以它们的内存布局是对齐的，所以可以通过`unsafe.Pointer`进行转换：

```go
func stringToBytes(s string) []byte {
  header := (*reflect.StringHeader)(unsafe.Pointer(&s))
  
  newHeader := reflect.SliceHeader{
    Data: header.Data,
    Len:  header.Len,
    Cap:  header.Len,
  }
  
  return *([]byte)(unsafe.Pointer(&newHeader))
}

func bytesToString(b []byte) string {
  header := (*reflect.SliceHeader)(unsafe.Pointer(&b))
  
  newHeader := reflect.StringHeader{
    Data: header.Data,
    Len:  header.Len,
  }
  
  return *(*string)(unsafe.Pointer(&newHeader))
}
```

上面的代码通过重新构造`reflect.SliceHeader`和`reflect.StringHeader`完成了类型的转换，其实`[]byte`转换成`string`可以省略掉自己构造`StringHeader`的方式，直接使用强转就可以，因为`string`的底层也是`[]byte`，强转会自动构造。省略后的代码如下：

```go
func bytesToString(b []byte) string {
  return *(*string)(unsafe.Pointer(&b))
}
```

虽然这种方式更高效，但是也是不安全的，所以不推荐使用。使用不当的情况下，将会出现极大的隐患，一些严重的情况 recover 也不能捕获。

### 9. 内存对齐

#### 9.1 对齐的作用和原因

现代计算机中，内存空间都是按照 byte 划分的，从理论上讲，似乎对任何类型的变量的访问都可以从任一地址开始，但是实际情况是在访问特定类型变量的时候经常在特定的内存地址访问，这就需要各种类型数据按照一定的规则在空间上排列，而不是顺序的一个接一个的排放，这就叫做**对齐**。

CPU 访问内存时，并不是逐个字节访问，而是以字长(word size)单位访问。比如，32 位的 CPU，字长为 4 字节，那么 CPU 访问内存的单位也是 4 字节。这样设计可以**减少 CPU 访问内存的次数，加大 CPU 访问内存的吞吐量**。假设需要读取 8 个字节的数据，一次读取 4 个字节那么就只需要读取 2 次就可以。内存对齐实现变量的原子性操作也是有好处的，每次内存访问都是原子的，如果变量的大小不超过字长，那么内存对齐后，对该变量的访问就是原子的，这个特性在并发场景下至关重要。

#### 9.2 对齐实例

对于下面的示例：

```go
// 64位平台，对齐参数是8
type User1 struct {
  A int32 // 4 
  B []int32 // 24 
  C string // 16 
  D bool // 1 
}

type User2 struct {
 B []int32
 A int32
 D bool
 C string
}

type User3 struct {
 D bool
 B []int32
 A int32
 C string
}
func main()  {
 var u1 User1
 var u2 User2
 var u3 User3

 // 运行结果 MAC: 64位
 fmt.Println("u1 size is ",unsafe.Sizeof(u1)) // u1 size is  56
 fmt.Println("u2 size is ",unsafe.Sizeof(u2)) // u2 size is  48
 fmt.Println("u3 size is ",unsafe.Sizeof(u3)) // u3 size is  56
}
```

从结果可以看出，字段放置的顺序的不同，结构体占用的内存大小也不同，这就是因为内存对齐导致的。所以合理的字段和字段顺序是能够减少内存开销的。

Go 内存对齐的规则如下（与 C 语言的对齐规则相同）：

1. 对于结构体的各个成员，第一个成员位于偏移量为 0 的位置，之后的每个成员相对于结构体首地址的偏移量都是该成员大小与有效对齐值中较小的那个值的整数倍。如有需要，编译器会在成员之间加上填充字节。
2. 除了结构体成员需要对齐，结构体本身也需要对齐。结构体的长度必须是编译器默认的对齐长度和成员中最长类型中的最小数据大小的倍数对齐。

64 位 CPU 中，对其参数最大为 8，`int32`、`[]int32`、`string`、`bool`对齐值分别是 4、8、8、1，占用内存大小分别是 4、24、16、1。

先根据规则一来分析`User1`结构体：

* 第一个字段类型是`int32`，对齐值是 4，大小为 4，放在内存布局中的第一位；
* 第二个字段类型是`[]int32`，对齐值是 8，大小为 24，它的内存偏移值必须是 8 的倍数。所以在`user1`中，它的起始位置就不能从第一个字段后的第 4 位开始了，必须要从第 8 位开始，也就是偏移量为 8。第 4、5、6、7 位由编译器进行填充，一般为 0 值，也称之为空洞。 第 9 位到第 31 位为的第二个字段 B；
* 第三个字段类型是 string，对齐值是 8，大小为 16，所以它的内存偏移值必须是 8 的倍数。因为`User1`的前两个字段已经排到了第 32 位，所以下一位的偏移量正好是 32，正好是字段 C 的对齐的倍数，不需要填充，可以直接排在第二个字段之后，也就是从第 32 位到第 47 位存储的是第三个字段 C。
* 第四个字段类型是 bool，对齐值是 1，大小是 1，所以它的内存偏移值必须是 1 的倍数。因为`User1`前三个字段已经排到了第 48 位，所以下一位的偏移量正好是 48，正好是字段 D 的对齐值的倍数，不用填充，可以直接排在第四个字段之后，也就是第 48 位存储的是第四个字段 D。

再根据规则二继续对`User1`进行分析：

* 64 位 CPU 上，编译器默认的对齐长度为 8，`User1`结构体中字段类型的最大长度为 24，取最小的哪一个，所以求出结构体的对齐值为 8；
* 前面分析得到的结构体的内存大小为 49，不是 8 的倍数，所以需要进行补齐，至少需要填补 7 位，最终长度就是 56 了。

图示如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635904236616-61f3a4880a2e.jpg)

对于其他两个 struct，分析方式类似。

#### 9.3 空 struct 的对齐

空结构体 struct 是不占用任何存储空间的，也就是说，`struct{}`的大小为 0。

空结构体内嵌到其他结构体中时，一般不需要内存对齐，但是有一种情况例外：**当`struct{}`作为结构体最后一个字段时，需要内存对齐。**因为，如果有指针指向该字段，返回的地址将在结构体之外，如果此指针一直存活不释放对应的内存，就会有内存泄露的问题（该内存将不会因结构体释放而释放）。

例如：

```go
func main() {
  fmt.Println(unsafe.Sizeof(test1{})) // 8
  fmt.Println(unsafe.Sizeof(test2{})) // 4
}

type test1 struct {
  a int32
  b struct{}
}

type test2 struct {
  a struct{}
  b int32
}
```

简单来说：**对于任何占用 0 字节大小空间的类型**，像`struct{}`或者`[0]byte`这些，如果该类型**出现在结构体末尾，那么就假设它占用 1 字节的大小**。因此，对于`test1`结构体，看起来就是这样的：

```go
type test1 struct {
  a int32
  // b struct{}
  b [1]byte
}
```

这样，在进行内存对齐之后，最后结构体占用的字节数就是 8 了。

所以，要注意：**不要在结构体定义的最后添加零大小的类型**，否则会占用更多的空间。

