> 转摘：[一文告诉你如何判断Go接口变量是否相等](https://mp.weixin.qq.com/s/2Pw8fsnjvu9-uTzf4Imojg)

### 1. 比较逻辑

Go interface 与 interface 的比较与 interface 和非 interface 的比较类似，可以认为后者是前者的一个特殊情况，所以它们的比较逻辑也是相通的：

1. 比较两个 interface 的值类型信息是否相等，不相等则返回 false；
2. 比较两个 interface 的动态值是否相等，不相等则返回 false。

而对 interface 动态值的比较的时候，需要区分值是否为指针类型：

* 对于动态类型为指针的（direct interface type），直接比较两个 interface 类型变量的类型指针是否相等；
* 若为其他非指针类型，则调用类型(_typ)信息中的`equal`方法，最终是对 data 解引用后的相等性判断。

### 2. 问题代码

```go
type T struct {
  name string
}

func (t T) Error() string {
  return "bad error"
}

func main() {
  var err1 error    // 非空接口类型
  var err1ptr error // 非空接口类型
  var err2 error    // 非空接口类型
  var err2ptr error // 非空接口类型
  
  err1 = T{"eden"}
  err1ptr = &T{"eden"}
  
  err2 = T{"eden"}
  err2ptr = &T{"eden"}
  
  println("err1:", err1)
  println("err2:", err2)
  println("err1 = err2:", err1 == err2)             // true
  println("err1ptr:", err1ptr)
  println("err2ptr:", err2ptr)
  println("err1ptr = err2ptr:", err1ptr == err2ptr) // false
}
```

上述代码中，两个非指针的 interface 是相等的，但是两个指针类型的 interface 却不相等。这是因为：

* err1 和 err2 两个接口变量的动态类型都是 T（非指针类型），因此比较的就是 data 指向的内存块的值。虽然 err1 和 err2 的 data 字段指向的是两个内存块，但是这两个内存块中对应的 T 对象的值相同（实质上就是一个 string）。因此 err1 和 err2 判断相等。
* err1ptr 和 err2ptr 两个 interface 的动态类型都是`*T`，因此比较的就是 data 的值，也就是两个`*T`的地址。显然 err1ptr 和 err2ptr 所指向的地址不同，因此 err1ptr 和 err2ptr 不相等。

### 3. 源码说明

Go 中对 interface 的比较，最终会由`runtime.ifaceeq()/runtime.efaceeq()`函数来实现，对应的源码如下：

```go
// $GOROOT/src/runtime/alg.go
func efaceeq(t *_type, x, y unsafe.Pointer) bool {
  if t == nil {
    return true
  }
  eq := t.equal
  if eq == nil {
    panic(errorString("comparing uncomparable type " + t.string()))
  }
  if isDirectIface(t) {
    // Direct interface types are ptr, chan, map, funcs and single-element struct/arrays thereof.
    // Maps and funcs are not comparable, so they can't reach here.
    // Ptrs, chans, and single-element items can be compared directly using ==.
    return x == y
  }
  return eq(x, y)
}

func ifaceeq(tab *itab, x, y unsafe.Pointer) bool {
  if tab == nil {
    return true
  }
  t := tab._type
  eq := t.equal
  if eq == nil {
    panic(errorString("comparing uncomparable type " + t.string()))
  }
  if isDirectIface(t) {
    // See comment in efaceeq
    return x == y
  }
  return eq(x, y)
}
```