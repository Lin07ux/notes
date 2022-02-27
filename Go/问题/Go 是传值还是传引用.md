> 转摘：[又吵起来了，Go 是传值还是传引用？](https://mp.weixin.qq.com/s/qsxvfiyZfRCtgTymO9LBZQ)

### 1. 问题

Go 语言是传值（值传递），还是传引用（引用传递）？

### 2. 官方定义

在 Go 官方 FAQ 的“When are function parameters passed by value?”中有如下内容：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645873697560-d35d27eac58b.jpg)

这里明确说明了，如同 C 系列的所有语言一样，**Go 语言中的所有东西都是以值传递的**。也就是说，一个函数总是得到一个被传递的东西的副本，就像有一个赋值语句将值赋值给参数一样。

例如：

* 向一个函数传递一个 int 值，就会得到这个 int 值的拷贝副本；而传递一个指针值就会得到指针的副本，该副本会包含源指针指向的位置，但是不会的得到源指针指向的具体数据；
* map 和 slice 的行为类似于指针：它们是包含指向底层 map 或 slice 数据的指针的描述符。所以，复制一个 map 或 slice 值并不会复制它所指向的数据。
* 复制一个接口值会复制存储在接口值中的东西：

    - 如果接口值持有一个结构，复制接口值就会复制该结构。
    - 如果接口值持有一个指针，复制接口值就会复制该指针，但同样不会复制它所指向的数据。

### 3. 传值和传引用

#### 3.1 传值

传值，也叫做值传递（pass by value），指的是**在调用函数时将实际参数复制一份传递到函数中**，这样在函数中如果对参数进行修改，将不会影响到源数据。

简单来说，值传递，所传递的是该参数的副本，是复制了一份的，本质上不能认为是一个东西，指向的不是一个内存地址。

案例一如下：

```go
func main() {
  s := "脑子进煎鱼了"
  fmt.Printf("main 内存地址：%p\n", &s)
  hello(&s)
}

func hello(s *string) {
  fmt.Printf("hello 内存地址：%p\n", &s)
}
```

输出结果：

```
main 内存地址：0xc000116220
hello 内存地址：0xc000132020
```

可以看到在 main 函数中的变量 s 所指向的内存地址和 hello 函数中参数传递后的内存地址是不同的，也就是说，这两者不是同一个变量了，即便这两个地址中存储的数据都是一样的。

这也说明，在 Go 语言中确实都是值传递的。但是这并不能说明函数内修改参数的值，就一定不会影响到源数据：

```go
func main() {
  s := "脑子进煎鱼了"
  fmt.Printf("main 内存地址：%p\n", &s)
  hello(&s)
  fmt.Println(s)
}

func hello(s *string) {
  fmt.Printf("hello 内存地址：%p\n", &s)
  *s = "煎鱼进脑子了"
}
```

在 hello 函数中，修改了参数 s 的值，最终在 main 函数输出的 s 的值也发生了变化：

```
main 内存地址：0xc000010240
hello 内存地址：0xc00000e030
煎鱼进脑子了
```

Go 是值传递，之所以函数内部修改参数的值，也可能会影响到源数据，是因为对于指针参数，值传递时复制的是源指针的值，也就是指针指向的地址。而当传递过去的值是指向内存空间的地址时，是可以对这块内存空间做修改的。

#### 3.2 传引用

传引用，也叫做引用传递（pass by reference），**指在调用函数时，将实际参数的地址直接传递到函数中**，那么在函数中对参数的修改，将影响到实际参数。

在 Go 语言中，官方已经明确了没有传引用，也就是没有引用传递这一情况。

### 4. map 和 slice

Go 中的 map 和 slice 类型，传给函数之后，也是能直接修改的，这是因为：map 和 slice 的行为类似于指针，它们是一种包含指向底层 map 或 slice 数据的指针的描述符。

#### 4.1 map

针对 map 类型，经过编译器处理之后，生成 map 的代码会变成对`runtime.makemap`方法的调用，该方法的签名如下：

```go
func makemap(t *maptype, hint int, h *hmap) *hmap{}
```

注意其返回值的类型为`*hmap`，是一个指针。也就是说，Go 语言通过对 map 类型的相关方法进行封装，达到了用户需要关注指针传递的作用。

也就是说，在调用参数为 map 类型的函数时，其实际是在传入一个指针参数，与前面值类型中的第二个例子修改字符串值类似。

示例如下：

```go
func main() {
  m := make(map[string]string)
  m["脑子进煎鱼了"] = "这次一定！"
  fmt.Printf("main 内存地址：%p\n", &m)

  hello(m)
  fmt.Printf("%v", m)
}

func hello(p map[string]string) {
  fmt.Printf("hello 内存地址：%p\n", &p)
  p["脑子进煎鱼了"] = "记得点赞！"
}
```

输出结果：

```
main 内存地址：0xc00000e028
hello 内存地址：0xc00000e038
```

这种类型称为“引用类型”，但是“引用类型”不等同于传引用，又或是引用传递，还是比较明确的区别的。

在 Go 语言中与 map 类型类似的还有 chan 类型：

```go
func makechan(t *chantype, size int) *hchan {}
```

#### 4.2 slice

slice 类型虽然不是一个引用类型，但是其内部包含了一个指向实际存储数据的地址指针。当按值传递的时候，复制的是 slice 的表层结构，副本中的指针也依旧指向源数据中的指针指向的地址。这样，当通过副本修改 slice 的数据时，源数据也一样能看到数据的变化，毕竟它俩共享了实际数据的内存空间。

例如：

```go
func main() {
 s := []string{"烤鱼", "咸鱼", "摸鱼"}
 fmt.Printf("main 内存地址：%p\n", s)
 hello(s)
 fmt.Println(s)
}

func hello(s []string) {
 fmt.Printf("hello 内存地址：%p\n", s)
 s[0] = "煎鱼"
}
```

输出结果：

```
main 内存地址：0xc000098180
hello 内存地址：0xc000098180
[煎鱼 咸鱼 摸鱼]
```

从结果来看，函数参数和源数据的内存地址是一样的，而且也能成功的通过副本改变源数据的值。但是这并非说明 slice 是引用传递。

注意两个细节：

* 没有使用`&`来取地址；
* 可以直接用`%p`来打印 slice 的地址；

之所以可以同时做到上面这两件事，是因为标准库`fmt`对 slice 做了优化：

```go
func (p *pp) fmt.Pointer(value reflect.Value, verb rune) {
  var u uintptr
  switch value.Kind() {
  case reflect.Chan, reflect.Func, reflect.Map, reflect.Ptr, reflect.Slice, reflect.UnsafePointer:
    u = value.Pointer()
  default:
    p.badVerb(verb)
    return
  }
}
```

代码中可以看到，对 slice 等一些类型，fmt 库做了特殊处理，直接通过`value.Pointer()`取对应的值的指针地址，所以就不需要手动取地址符了。

fmt 能输出 slice 类型对应的值的原因也在于此：

```go
func (v value) Pointer() uintptr {
  ...
  case Slice:
    return (*SliceHeader)(v.ptr).Data
}

type SliceHeader struct {
  Data uintptr
  Len  int
  Cap  int
}
```

其在内部转换使用的`SliceHeader`，也正是 Go 语言中 slice 类型的运行时表现类型。在调用`%p`输出的时候，就是输出`SliceHeader.Data`属性的地址。

由于`SliceHeader.Data`属性是一个指针，slice 源数据和副本的`SliceHeader.Data`的值一样，就都指向了同一个底层数组，所以修改副本的数据也会影响到源数据了。

> 如果在函数中造成了 slice 的扩容，那么有可能副本数据的变动不会影响源数据。

