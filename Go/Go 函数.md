函数通过`func`关键字来声明，可以直接用来定义普通函数，也可以定义匿名函数：

```go
// 普通函数
func Hello() {
    fmt.Println("Hello")
}

// 匿名函数
foo := func() {
    fmt.Println("foo")
}
```

在 Go 中，公共函数以大写字母开始，私有函数以小写字母开始。

### 1. 参数类型

可以为函数的每个参数都指定类型，类型在参数名称后面，并由空格分隔。

如果有多个相同类型的参数，可以只为最后一个参数指定类型即可。

```go
func Add(x, y int) int {
    return x + y
}

// 等同于
func Add(x int, y int) int {
    return x + y
}
```

### 2. 命名返回值

在函数定义时，可以使用命名返回值，这将会自动在函数内创建对应的命名变量，且为其赋予相应类型的零值：如果是 int 类型，则值为 0，如果是 string 类型则值为`""`。

而且命名返回值函数中，可以只使用`return`关键词，而无需`return varName`的方式来返回变量。

同时，这种方式会显示在 Go Doc 中，使代码更加清晰。

比如：

```go
func greetingPrefix(language string) (prefix string) {
    switch language {
    case "French":
        prefix = "Banjour, "
    case "Spanish":
        prefix = "Hola, "
    default:
        prefix = "Hello, "
    }
    return
}
```



