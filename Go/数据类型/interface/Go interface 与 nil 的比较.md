interface 变量定义的是一个 16 字节的结构体，其中首 8 字节是类型字段，后 8 字节是数据指针。普通的 interface 是`iface`结构，`interface{}`对应的是`eface`结构。

interface 变量创建的时候是 nil，此时这 16 个字节全都是 0。**interface 变量与 nil 的判断，汇编逻辑是判断首 8 字节是否是 0 值。**

所以，如果一个 interface 的变量指向了某个具体的类型，但是类型值为 nil，此时 interface 依旧不为 nil。

这也表明：在编译通过的情况下，**具体类型到接口的赋值一定会导致接口非零**。

### 1. 示例

示例代码如下：

```go
package main

import "fmt"

func main()  {
	var a interface{}
	var b *int

	isNil(a) // empty interface
	isNil(b) // non-empty interface

	a = b
	isNil(a) // non-empty interface
}

func isNil(x interface{}) {
	if x == nil {
		fmt.Println("empty interface")
	} else {
		fmt.Println("non-empty interface")
	}
}
```

可以看到，默认情况下，一个 interface 变量的值就是 nil。但是一旦将一个具体类型的变量赋值给它了，它就不再是 nil 了。因为，虽然它的值是 nil，但是类型必然不是 nil 了。

### 2. 解决方法

要使得能正确的判断 interface 的值是否为 nil，可以利用反射来实现。代码如下：

```go
func isNil(i interface{}) bool {
  vi := reflect.ValueOf(i)
  if vi.Kind() == reflect.Ptr {
    return vi.IsNil()
  }
  return false
}
```

因为反射中有针对 interface 类型的特殊处理，所以可以用上面的方法来判断。最终输出的结果就是`true`了。

在其他情况下，可以考虑改变原有的程序逻辑，避免出现这样的问题。例如：

* 先对值进行 nil 判断，不为 nil 时在设置成 interface 类型；
* 返回具体的值类型，而不是返回 interface 类型。

### 3. 赋值改变为非 nil interface

对于一个声明为 interface 的变量，其初始值默认为 nil，但是当将一个对应 interface 实现对象赋值给它时，它就是非 nil interface 了，即便这个实现对象是 nil。这也是因为 interface 与 nil 的判断是根据其首 8 字节的动态类型是否为 nil 造成的。

示例如下：

```go
package main

import "fmt"

type Worker interface {
    Work() error
}

type Qstruct struct{}

func (q *Qstruct) Work() error {
    return nil
}

// 返回一个 nil
func findSomething() *Qstruct {
    return nil
}

func main() {
    // 声明接口变量
    var v Worker
    
    v = findSomething()
    if v != nil {
        fmt.Printf("v(%v) != nil\n", v) // 走到这里
    } else {
        fmt.Printf("v(%v) == nil\n", v)
    }
}
```

上面的代码中，`v`变量被声明为 Worker 接口类型，`findSomething()`函数返回的是一个实现了 Worker 接口的 Qstruct 对象指针，值为 nil。

经过`v = findSomething()`的赋值之后，`v`变量的动态类型就不是 nil 了，而是`*Qstruct`，动态值则依旧是 nil。而判断 interface 变量是否为 nil 只看其动态类型是否为 nil。显然，此时`v`已经不满足这个条件了，所以`v != nil`这个条件就成立了。

如何任何地方有判断接口是否为 nil 值的逻辑，就一定不要有具体类型赋值给接口的逻辑。对于函数、方法的返回值，如果需要将其赋值给接口，就尽可能的返回接口类型，而非具体的类型，不过经过具体类型的转换。

比如，对于上面的代码，可以修改`findSomething()`函数的返回值类型，就能得到 nil 的`v`接口变量：

```go
func findSomething() Worker {
    return nil
}
```



