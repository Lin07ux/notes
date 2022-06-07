> 转摘：[面试官：go中for-range使用过吗？这几个问题你能解释一下原因吗？](https://mp.weixin.qq.com/s/UAna6mFSxybdCexrj_VYfg)

## 一、map 的使用使用

在迭代中是可以对 map 进行增删改元素的。

官方对此的解释如下：

> The iteration order over maps is not specified and is not guaranteed to be the same from one iteration to the next. If map entries that have not reached are removed during iteration, the corresponding iteration values will not be produced. If map entries are created during iteration, that entry may be produced during the iteration or may be skipped. The choice may vary for each entry created and from one iteration to the next. If the map is nil, the number of iterations is 0.

也就是说：

> map 的迭代顺序是不确定的，并且不能保证每次迭代的顺序都相同。
> 
> 如果在迭代过程中删除了尚未遍历到的映射条目，则不会为其生成相应的迭代值（也就是跳过这个被删除的条目）。
> 
> 如果在迭代过程中，添加了新的条目，则该条目可能会在迭代过程中生成对应的迭代值，也有可能不会生成。对于每个新增的条目，以及从一个迭代到下一个迭代，选择可能都会不同。
> 
> 如果 map 的值为 nil，则迭代次数为 0。

### 1.1 迭代中删除 delete

在`for-range`循环过程中，是可以删除 map 中的数据的：

```go
for key := range m {
  if key.expired() {
    delete(m, key)
  }
}
```

### 1.2 迭代中添加元素 add

同样的，在迭代中也可以添加元素。

在上面的官方文档中也说明了，由于 map 迭代的顺序的不确定性，所以新添加的元素有可能会出现在迭代中，也有可能会被跳过。

比如：

```go
func addTomap() {
  t := map[string]string{
    "asong": "太帅",
    "song": "好帅",
    "asong1": "非常帅",
  }
  for k := range t {
    t["song2020"] = "真帅"
    fmt.Printf("%s%s", k, t[k])
  }
}

func main() {
  for i := 0; i < 10; i++ {
    addTomap()
    fmt.Println()
  }
}
```

这里在迭代中向 map 中增加了元素，并且循环执行这个方法 10 次，得到的结果类似如下：

```
asong太帅 song好帅 asong1非常帅 song2020真帅 
asong太帅 song好帅 asong1非常帅 
asong太帅 song好帅 asong1非常帅 song2020真帅 
asong1非常帅 song2020真帅 asong太帅 song好帅 
asong太帅 song好帅 asong1非常帅 song2020真帅 
asong太帅 song好帅 asong1非常帅 song2020真帅 
asong太帅 song好帅 asong1非常帅 
asong1非常帅 song2020真帅 asong太帅 song好帅 
asong太帅 song好帅 asong1非常帅 song2020真帅 
asong太帅 song好帅 asong1非常帅 song2020真帅
```

可以看到，有些情况下新增加的元素确实被跳过了，而有些出现在迭代中，但是出现的顺序并不确定。

## 二、问题

`for-range`语法在进行遍历循环时，其代表`value`的变量只会被声明一次，每次迭代的值都是直接赋值给`value`变量的，在实际使用中，常会因为对该值变量`value`的理解不透彻而造成问题。

### 2.1 指针数据坑

对于下面的代码：

```go
package main

import "fmt"

type user struct {
  name string
  age  uint64
}

func main() {
  u := []user{
    {"asong", 23},
    {"song", 19},
    {"asong2020", 18},
  }
  n := make([]*user, 0, len(u))
  for _, v := range u {
    n = append(n, &v)
  }
  fmt.Println(n)
  for _, v := range n {
    fmt.Println(v)
  }
}
```

这个例子的目的是想通过`u`这个 slice 构造出一个新的 slice。预期的输出结果应该是显示出`u`中各个元素的地址和内容，而实际输出的结果如下：

```
[0xc0000a6040 0xc0000a6040 0xc0000a6040]
&{asong2020 18}
&{asong2020 18}
&{asong2020 18}
```

可以看到，`n`这个新的 slice 中的每个元素都指向了同一个地址，而且其数据都和`u`这个 slice 中的最后一个元素相同。

而要得到预期的结果也很简单，就是将第一个`for-range`中为`n`变量添加元素的逻辑改为如下方式即可：

```go
for _, v := range u {
  t := v
  n = append(n, &t)
}
```

也就是说，在添加元素到`n`之前，创建一个新的临时变量，并接收循环中的`v`变量的值。由于 Go 语法的特性，`t`变量在每次循环中都是重新声明并被赋值的。

之所以这样就可以得到预期结果，是因为：

在`for-range`中，变量`v`是被 Go 解释器自动声明的，而且在整个`for-range`循环中都只会声明一次。这就导致：

1. 变量`v`在每次循环中都是具有相同的地址和的一个变量，而每次迭代为为其赋值其实都是在修改同一个地址；
2. 在执行`append(n, &v)`的时候，其实都是取同一个地址的指针；
3. 当`for-range`遍历结束时，`v`变量的数据和`u`中的最后一个元素的数据相同，但是地址不同；
4. 所以`n`中的数据都相同，而且都指向同一个地址——`v`变量的地址。

理解了这个，就能发现还有一种更好的方法来解决这个问题：直接用索引来引用切片元素，这样还能避免开辟新的内存空间。代码如下：

```go
for k := range u {
  n = append(n, &u[k])
}
```

### 2.2 迭代修改变量的问题

依旧针对上面的代码，做一些变动：对切片`u`中保存的每个用户的年龄进行修改，都修改为 18。代码如下：


```go
package main

import "fmt"

type user struct {
  name string
  age  uint64
}

func main()  {
  u := []user{
    {"asong",23},
    {"song",19},
    {"asong2020",18},
  }
  for _, v := range u {
    if v.age != 18 {
      v.age = 18
    }
  }
  fmt.Println(u)
}
```

运行结果如下：

```
[{asong 23} {song 19} {asong2020 18}]
```

很明显，在迭代中修改年龄的操作并没有起作用。

之所以会这样，依旧是因为`for-range`中的`v`变量是 Go 编译器自动声明的，且在真个迭代中都只会声明一次。所以每次迭代时都是在用`u`中的元素为`v`变量赋值。而赋值之后，修改`v`变量就相当于修改一个独立的新变量，自然不会反应到`u`中了。

解决方法也很简单，就是使用索引来引用切片元素：

```go
for k, v := range u {
  if v.age != 18 {
    u[key].age = 18
  }
}
```

### 3. 迭代中添加元素是否会造成死循环

在`for-range`迭代过程中向切片中添加元素，是否会造成死循环？比如下面的代码：

```go
func main() {
  v := []int{1, 2, 3}
  for i := range v {
    v = append(v, i)
  }
}
```

这当然不会造成死循环的。因为`for-range`在迭代开始前，其实是会对切片做一次拷贝的，然后使用拷贝的副本进行循环迭代。所以在循环中新增的数据并不会出现在副本中，也就不会发生死循环。


