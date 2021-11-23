Go 自带有测试组件，可以很方便的实现测试用例开发。

## 一、单元测试

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

## 四、模糊测试

> 转摘：[新功能！Go 模糊测试](https://mp.weixin.qq.com/s/cKNMtEkJoRZ_lC9yEIocJw)

模糊测试是一种自动化测试技术，其依赖于随机函数生成测试用例，并将这些用例不断地输入程序进行测试，以发现代码可能受到影响的问题。

模糊测试的用例是不重复、随机的，这能够得到意想不到的输入和结果，达到人工设置可能会遗漏的边缘情况。

> Go 语言的模糊测试将在 1.18 版本中引入，目前可以使用 gotip 来实验：
> 
> ```shell
> go install golang.org/dl/gotip@latest
> gotip download
> gotip test -fuzz=Fuzz
> ```

### 4.1 用例要求

与单元测试一样，模糊测试代码也有一定的规范：

* 必须写在`*_test.go`文件中；
* 测试函数命名形如`FuzzXxx`；
* 该函数必须接收一个`*testing.F`类型的参数；
* 该函数无返回值。

模糊测试模板函数如下：

```go
func FuzzXXX(f *testing.F) {
  f.Fuzz(func(t *testing.T, m string, n int) {
    err := doSomething(m, n)
    // check for errors in err here
  })
}
```

在这个函数中的第二行，通过`f.Fuzz()`函数填充一个常规的测试函数，测试函数包括了`*testing.T`类型的`t`，功能函数`doSomething`所需要的`string`类型的`m`和`int`类型的`n`，都会由 Go 自动生成并传递到单元测试函数中。

### 4.2 命令参数

模糊测试会大量消耗机器资源。在默认情况下，会启用 GOMAXPROCS 个 worker 并行运行模糊测试，且永不停止。

可以在执行模糊测试的时候传递适当的参数来进行控制：

* `-fuzz name` 对给定的正则表达式运行模糊测试，必须匹配最多一个模糊测试。

* `-fuzztime` 指定模糊测试运行的时间或次数：
    - 如果传入的值是`Nx`类型（如`100 x`），那么就表示要运行多少次（100 次）；
    - 否则传入的值会被作为`time.Duration`类型被解析，如`1h30s`，表示运行指定的时长；
    - 默认情况下会一直运行。

* `-keepfuzzing` 如果测试运行时遇到了崩溃，将会继续执行模糊测试。默认情况下会停止的。

* `-parallel` 允许并行的通过`t.Parallel`进行模糊测试。
    - 当指定`-fuzz`参数进行模糊测试时，`-parallel`参数的值会被作为同时运行的 worker 的最大数量。
    - 默认情况下，这个参数的值为 GOMAXPROCS。
    - 该参数只能用于单个 test 二进制文件。

* `-race` 允许在进行模糊测试时检查是否存在数据竞争存在。默认情况下不允许检测。

* `-run` 指定进行单元测试、示例测试、模糊测试的集合匹配表达式。在指定模糊测试的集合时，表达式可以是`$test/$name`格式化的，这里`$test`是模糊测试的名称，`$name`是模糊测试所在的文件名（忽略文件扩展符）。

### 4.3 示例

下面是一个 Go 官网博客给出的测试`net/url/query`包函数的模糊测试示例：

```go
//go:build go1.18
// +build go1.18

package fuzz

import (
  "net/url"
  "reflect"
  "testing"
)

func FuzzParseQuery(f *testing.F) {
  f.Add("x=1&y=2")
  f.Fuzz(func(t *testing.T, queryStr string) {
    query, err = url.ParseQuery(queryStr)
    if err != nil {
      t.Skip()
    }
    queryStr2 := query.Encode()
    query2, err := url.ParseQuery(queryStr2)
    if err != nil {
      t.Fatalf("ParseQuery failed to decode a valid encoded query %s: %v", queryStr2, err)
    }
    if !reflect.DeepEqual(query, query2) {
      t.Errorf("ParseQuery gave different query after being encoded\nbefore: %v\nafter: %v", query, query2)
    }
  }
}
```

测试结果类似如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637548895055-edde1196ab72.jpg)



