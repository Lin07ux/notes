哈希表是一个无序的 key/value 键值对集合，其中所有的 key 都是不同的。通过给定的 key 可以在常数时间复杂度内检索、更新或删除对应的 value。

在 Go 中，一个 map 就是一个哈希表的引用，对应的类型是`map[key]value`。map 中所有的 key 都有相同的类型，所有的 value 也都有相同的类型，但是 key 和 value 之间可以是不同的数据类型。而且要求 key 是可以比较的。

## 1. 使用方式

map 的使用方式如下：

```go
// 创建 map
var persons = map[string]int{}
ages := make(map[string]int, 2)
ages := map[string]int{
  "alice": 31,
  "charlie": 34,
}

// 添加元素
ages["bob"] = 28

// 更新元素
ages["alice"] = 32

// 删除元素
delete(ages, "bob") // remove element ages["bob"]

// 获取元素
fmt.Println(ages["alice"]) // "32"

// 不存在的 key 其 value 对应为零值
ages["bob"]++ // ages["bob"] == 1

// map 中的元素不是一个变量，因此不能对 map 中的元素进行取址操作：
_ = &ages["bob"] // compile error: cannot take address of map element

// 变量 map 中的 key/value
// map 的迭代顺序是不确定的，并且不同的哈希函数可能导致不同的遍历顺序
for name, age := range ages {
  fmt.Printf("%s\t%d\n", name, age)
}

// 判断 key 是否存在，如果存在则 ok == true，否则 ok == false
age, ok := ages["lily"]
if !ok { /* "lily" is not a key in this map; age == 0 */ }

var scores map[string]int

// map 类型的零值是 nil，也就是没有引用任何哈希表
fmt.Println(scores == nil)    // "true"

// 空 map 的长度为 0
fmt.Println(len(scores) == 0) // "true"

// map 的查找、删除、len() 和 reange 循环都可以安全工作在 nil 值的 map 上，行为和空的 map 类似
// 但是不能向一个 nil 值的 map 存入元素，否则会导致一个 panic 异常：
scores["carol"] = 21 // panic: assignment to entry in nil map

// map 之间也不能进行相等比较，但是 map 可以和 nil 进行比较
// 自定义 map 的比较函数时，需要注意 key 不存在时对应的 value 为类型零值的情况
if scores == nil { /* scores is nil */ }
```

> 禁止对 map 元素取址是原因是 map 可能随着元素数量的增长而重新分配更大的内存空间，从而可能导致之前的地址无效。

> 在实践中，遍历 map 的顺序是随机的，每一次遍历的顺序都不相同，这是故意的，可以强制要求程序不会依赖具体的哈希函数实现。如果要按顺序遍历 map，可以显式的提取并对 key 进行排序，然后再使用有序的 key 列表进行遍历。

### 1. 初始化

map 可以使用`make()`来初始化，也可以使用字面量方式初始化。字面量初始化 map 的方式有点类似于数组，不同之处是：

* 要以`map`关键字开头；
* 需要指定键的类型（在`[]`中）；
* 指定值的类型（在`[]`后）。

例如：

```go
m := make(map[int32]int64)
ids := make(map[int32]int64, 10)
dictionary := map[string]string{"test": "this is just a test"}
```

使用`make`初始化时，可以指定容量，也可以不指定容量。但是不指定容量的时候，其实并没有真正的完成 map 初始化，而是等到其第一次被更新时才完成初始化。同时，预定义的容量并不会阻止 map 的扩容，只是帮助优化对 map 的使用（因为按需扩容会有性能损耗）。

需要注意的是：**键的类型只能是一个可比较的类型**。因为如果不能判断两个键是否相等，就无法确保得到的是正确的值。而且不建议使用浮点数作为 key 的类型（最坏的情况是可能出现的 NaN 和任何浮点数都不相等）

另一方面，值的类型可以是任意类型，甚至可以是另一个 map。

### 2. 访问

```go
v := m[i]
v, ok := m[i]
```

