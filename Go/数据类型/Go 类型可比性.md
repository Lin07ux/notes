> 转摘：[Go 语言类型可比性](https://mp.weixin.qq.com/s/_a5kd6bBMEToCPZNR7D4Pw)

### 1. 类型的可比性

在 Go 中，数据类型可以被分为可比较和不可比较两类，两者的区别很简单：类型是否可以使用运算符`==`和`!=`进行比较。

* 可比较类型：Boolean、Integer、Float、Complex、String、Interface，这些类型总是可比较的。

* 不可比较类型：Slice、Map、Function，这些类型是不可比较的，但是它们可以与`nil`相比较。

* 限定可比较类型：Struct、Array、Pointer、Channel，这些类型在满足一定条件的情况下才是可比较的。

    - Struct：如果其所有的字段都具有可比性，则 Struct 具有可比性：如果它们对应的非空字段相等，则两个结构体值相等。
    - Array：如果数组中的元素类型的值是可比较的，则数组值是可比较的：如果它们对应的元素相等，则两个数组值相等。
    - Pointer：如果两个指针指向同一个变量，或者两个指针类型相同且值都为`nil`，则它们相等。*注意：指向不同的零大小变量的指针可能相等也可能不相等*。

不可比较的类型在进行比较时会触发 panic，如果要进行比较，可以使用`reflect.DeepEqual()`函数来比较。

### 2. Interface 的比较

如果两个接口值具有相同的动态类型和相等的动态值，则它们相等。

例如，当类型 X 的值具有可比性且 X 实现 T 时，非接口类型 X 的值 x 和接口类型 T 的值 t 具有可比性：如果 t 的动态类型与 X 相同且 t 的动态值等于 x，则它们相等。

在进行 JSON 序列化和反序列化的时候，Interface 值可能会丢失其动态类型，这就导致反序列化之后的值与原始值是不相等的情况出现：

```go
type Data struct {
 UUID    string
 Content interface{}
}

var x, y Data
x = Data {
 UUID:   "856f5555806443e98b7ed04c5a9d6a9a",
 Content: 1,
}
bytes, _ := json.Marshal(x)
_ = json.Unmarshal(bytes, &y)
fmt.Println(x)  // {856f5555806443e98b7ed04c5a9d6a9a 1}
fmt.Println(y)  // {856f5555806443e98b7ed04c5a9d6a9a 1}
fmt.Println(reflect.DeepEqual(x, y)) // false
```

这里之所以反序列化之后得到的 y 与原始值 x 不相等，就是因为`Data.Content`字段是 Interface 类型的，在序列化之前是 int 类型，在反序列化后就成了 float 类型。通过调试就可以看到：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637152934082-35e7e39e45bc.jpg)

针对这个问题，可以基于`reflect.DeepEqual()`函数进行改造适配：

```go
func DeepEqual(v1, v2 interface{}) bool {
  if reflect.DeepEqual(v1, v2) {
    return true
  }
  
  bytes1, _ := json.Marshal(v1)
  bytes2, _ := json.Marshal(v2)
  return bytes.Equal(bytes1, bytes2)
}
```

### 3. Channel 的可比条件

* 如果两个通道值都是由同一个`make`调用创建的，则它们相等；
* 如果两个通道都没有被初始化（也就是它们都为`nil`），而且类型相同，则它们相等；
* 否则它们是不等的，而且如果两个通道类型不同，它们还是不可比的。

```go
c1 := make(chan int, 2)
c2 := make(chan int, 2)
c3 := c1

fmt.Println(c3 == c1) // true
fmt.Println(c2 == c1) // false

var c4 chan int
var c5 chan int
fmt.Println(c4 == c5) // true
fmt.Println(c1 == c5) // false

var c6 chan string
fmt.Println(c6 == c5) // panic: invalid operation: c6 == c5 (mismatched types chan string and chan int)
```


