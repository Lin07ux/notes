Go 自带有测试组件，可以很方便的实现测试用例开发。

## 一、测试

编写测试和编写函数很类似，其中有一些规则：

* 程序需要在一个名为`xxx_test.go`的文件中编写；
* 测试函数的命名必须以单词`Test`开始；
* 测试函数只接受一个参数`t *testing.T`。

> 类型为`*testing.T`的参数是测试框架中的 hook（钩子），当想让测试失败时可以执行`t.Fail()`之类的操作。

### 1.1 t.Helper()

这个方法用于告知测试套件，这个方法是辅助函数(Helper)。这样，当测试失败时，所报告的行号将在函数调用中，而不是在辅助函数内部。这将帮助开发人员更容易的跟踪问题。

比如：

```go
func TestHello(t *testing.T) {

    assertCorrectMessage := func(t *testing.T, got, want string) {
        t.Helper() // 标记为辅助函数
        if got != want {
            t.Errorf("got '%q' want '%q'", got, want)
        }
    }

    t.Run("saying hello to people", func(t *testing.T) {
        got := Hello("Chris")
        want := "Hello, Chris"
        assertCorrectMessage(t, got, want)
    })

    t.Run("empty string defaults to 'world'", func(t *testing.T) {
        got := Hello("")
        want := "Hello, World"
        assertCorrectMessage(t, got, want)
    })

}
```

### 1.2 t.Errorf(format string, args ...interface{})

该方法用于打印一条消息，并使测试失败。

方法名称中的`f`表示格式化，允许用户构建一个字符串，并使用占位符（如`%q`、`%s`、`%v`等）引入替换值。这样能够清晰的展示出测试失败时的相关信息。

## 二、示例

Go 示例执行起来就跟测试一样，并且作为包的测试套件的一部分，示例会被编译（并可选择性的执行）。

与典型的测试函数一样，示例函数有如下基本规则：

* 存在于一个包的`xxx_test.go`文件中；
* 示例函数名称一般以`Example`开头。

### 2.1 可选择性的执行

在测试的时候，测试函数是会自动被执行的，但是示例的函数需要在满足条件的时候才会被执行：

**示例函数中存在`// Output: xx`类型的注释**。

而且 Output 注释中的输出的值应该与示例函数输出的值相同才能测试通过。

```go
func ExampleAdder() {
    sum := Add(1, 5)
    fmt.Println(sum)
    // Output: 6
}
```

## 三、基准测试

基准测试(benchmarks)是 Go 预约的一个一级特性，与编写典型的测试非常相似。

基准测试的运行是通过如下命令进行：`go test -bench=.`。基准测试默认是顺序执行的。

基准测试函数与典型测试函数一样，也有一些默认的规则：

* 程序需要在一个名为`xxx_test.go`的文件中编写；
* 函数名称以`Benchmark`开头；
* 函数接受唯一一个参数`b *testing.B`。

### 3.1 隐性次数 b.N

在基准测试函数中，可以指定循环`b.N`次，此时测试框架会选择一个它所认为的最佳值，以便获得更合理的结果。

```go
func BenchmarkRepeat(b *testing.B) {
    for i := 0; i < b.N; i++ {
        Repeat("a")
    }
}
```

