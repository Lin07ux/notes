> [Go 每日一库之 testify](https://mp.weixin.qq.com/s/P4Bt7I3_Zlsbzy4eNyp3GA)

[`testify`](github.com/stretchr/testify) 是目前非常流行 Go 语言测试库，提供了很多方便的函数帮助进行 asset 和错误信息输出。相对于标准库中的`testing`，`testify`可以避免手动编写各种条件判断，以及根据条件决定输出对应的信息。

`testify`核心有三部分内容：

* `assert` 断言
* `mock` 测试替身
* `suite` 测试套件

`testify`可以使用 Go Modules 进行安装：

```go
go get -u github.com/stretchr/testify
```

使用`testify`编写的测试代码与标准库`testing`一样，写在文件名格式为`xxx_test.go`的文件中，测试函数名称格式为`TestXxx`，并且使用`go test`命令运行测试。

不同的是，`testify`对`*testing.T`做了一个简单的包装，提供了`TestingT`接口：

```go
type TestingT interface {
  Errorf(format string, args ...interface{})
}
```

### 一、assert

`testify/assert`子库提供了便捷的断言函数，可以大大简化测试代码的编写。

总的来说，它将之前需要**判断 + 信息输出的模式**简化为一行**断言代码**：

```go
if got != expected {
  t.Errorf("xxx failed expect: %d got: %d", expected, got)
}

// 使用 testify/assert
assert.Equal(t, got, expected, "they should be equal")
```

这样不仅结构更清晰可读，而且熟悉其他语言测试框架的开发者对此也会更熟悉。

此外，`testify/assert`库中的函数会自动生成比较清晰的错误描述信息：

```go
func TestEqual(t *testing.T) {
  var a = 100
  var b = 200
  assert.Equal(t, a, b, "")
}
```

运行时输出的结果如下：

```
> go test
--- FAIL: TestEqual (0.00s)
    assert_test.go:12:
                Error Trace:
                Error:          Not equal:
                                expected: 100
                                actual  : 200
                Test:           TestEqual
FAIL
exit status 1
FAIL    github.com/darjun/go-daily-lib/testify/assert   0.107s
```

另外，`testify`提供的`assert`类函数众多，每个函数都有两个版本：一个版本是函数名不带`f`的，一个版本是带`f`的。区别就在于带`f`的函数需要多指定至少两个参数，一个格式化字符串`msg`，以及若干个参数`args`：

```go
func Equal(t TestingT, expected, actual interface{}, msgAndArgs ...interface{})
func Equalf(t TestingT, expected, actual interface{}, msg string, args ...interface{})
```

实际上，带有`f`的方法在内部其实也是调用了不带`f`的方法。比如，在`Equalf`内部就调用了`Equal`函数：

```go
func Equalf(t TestingT, expected interface{}, actual interface{}, msg string, args ...interface{}) {
  if h, ok := t.(tHelper); ok {
    h.Helper()
  }
  return Equal(t, expected, actual, append([]interface{}{msg}, args...)...)
}
```

所以只需要关注不带`f`的版本的函数即可。

### 1.1 正断言

正断言就是使用表示肯定意思的断言，比如`Equal`、`EqualValues`等。

#### 1.1.1 Contains

函数签名：

```go
func Contains(t TestingT, s, contains interface{}, msgAndArgs ...interface{}) bool
```

`assert.Contains`断言`s`包含`contains`。其中`s`可以是字符串、数组、切片、map。相应的`contains`为子串，数组、切片元素、map 的键。

#### 1.1.2 DirExists

函数签名：

```go
func DirExists(t TestingT, path string, msgAndArgs ...interface{}) bool
```

`assert.DirExists`断言路径`path`是一个目录，如果`path`不存在或者不是一个目录，则断言失败。

#### 1.1.3 ElementsMatch

函数签名：

```go
func ElementsMatch(t TestingT, listA, listB interface{}, masAndArgs ...interface{}) bool
```

`assert.ElementsMatch`断言`listA`和`listB`包含相同的元素，忽略元素出现的顺序。`listA/listB`必须是数组或切片。如果有重复元素，那么重复元素在`listA`和`listB`中出现的次数必须相等。

#### 1.1.4 Empty

函数签名：

```go
func Empty(t TestingT, object interface{}, msgAndArgs ...interface{}) bool
```

`assert.Empty`断言`object`是空的。根据`object`中存储的实际类型，空的含义不同：

* 指针：`nil`
* 整数：`0`
* 浮点数：`0.0`
* 字符串：`""`
* 布尔：`false`
* 切片或 Channel：长度为 0

#### 1.1.5 EqualError

函数签名：

```go
func EqualError(t TestingT, theError error, errString string, msgAndArgs ...interface{}) bool
```

`assert.EqualError`断言`theError.Error()`的返回值与`errString`相等。

#### 1.1.6 EqualValues

函数签名：

```go
func EqualValues(t TestingT, expected, actual interface{}, msgAndArgs ...interface{}) bool
```

`assert.EqualValues`断言`expected`与`actual`相等，或者可以转换为相同的类型并且相等。

这个断言条件比`assert.Equal`更宽：`assert.Equal`返回 true 则`assert.EqualValues`肯定也返回 true，反之则不然。

例如，基于`int`类型定义了一个新类型`MyInt`，它们的值都是 100，`assert.Equal`调用将返回 false，`assert.EqualValues`则会返回 true:

```go
type MyInt int

func TestEqual(t *testing.T) {
  var a = 100
  var b MyInt = 100
  assert.Equal(t, a, b, "") // false
  assert.EqualValues(t, a, b, "") // true
}
```

这两个断言函数实现的核心是下面的两个函数，使用了`reflect.DeepEqual`函数：

```go
func ObjectsAreEqual(expected, actual interface{}) bool {
  if expected == nil || actual == nil {
    return expected == actual
  }
  
  exp, ok := expected.([]byte)
  if !ok {
    return reflect.DeepEqual(expected, actual)
  }
  
  act, ok := actual.([]byte)
  if !ok {
    return false
  }
  
  if exp == nil || act == nil {
    return exp == nil && act == nil
  }
  
  return bytes.Equal(exp, act)
}

func ObjectsAreEqualValues(expected, actual interface{}) bool {
  // 如果 ObjectsAreEqual 返回 true，就直接返回
  if ObjectsAreEqual(expected, actual) {
    return true
  }
  
  actualType := reflect.TypeOf(actual)
  if actualType == nil {
    return false
  }
  
  expectedValue := reflect.ValueOf(expected)
  if expectedValue.IsValid() && expectedValue.Type().ConvertibleTo(actualType) {
    // 尝试类型转换
    return relfect.DeepEqual(expectedValue.Convert(actualType).Interface{}, actual)
  }
  
  return false
}
```

#### 1.1.7 Error

函数签名：

```go
func Error(t TestingT, err error, msgAndArgs ...interface{}) bool
```

`assert.Error`断言`err`不为`nil`。

#### 1.1.8 ErrorAs

函数签名：

```go
func ErrorAs(t TestingT, err error, target interface{}, msgAndArgs ...interface{}) bool
```

`assert.ErrorAs`断言`err`表示的 error 链中至少有一个和`target`匹配。这个函数是对标准库中`error.As`的包装。

#### 1.1.9 ErrorIs

函数签名：

```go
func ErrorIs(t TestingT, err, target error, msgAndArgs ...interface{}) boo
```

`assert.ErrorIs`断言`err`的 error 链中有`target`。

### 1.2 逆断言

逆断言就是否定意思的断言，比如`NotEqual`、`NotEqualValues`等。

上面的正断言都有对应的逆断言，其方法名称前面均带有`Not`。

### 1.3 Assertions 对象

上面的断言都是以`TestingT`为第一个参数，需要大量使用时比较麻烦。

`testify`提供了一种方便的方式：先以`*testing.T`创建一个`*Assertions`对象，`Assertions`定义了前面所有的断言方法，只是不需要再传入`TestingT`参数了：

```go
func TestEqual(t *testing.T) {
  assertions := assert.New(t)
  assertions.Equal(a, b, "")
  // ...
}
```

### 1.4 require

`testify/require`子库提供了和`testify/assert`同样的接口，但是遇到错误时，`require`直接终止测试，而`assert`返回了 false。

## 二、mock

`testify`提供了对 Mock 的简单支持。

Mock 简单来说就是构造一个**仿对象**，其提供和原对象一样的接口，在测试中使用仿对象来替换原对象。这样在原对象很难构造、特别是涉及外部资源（数据库、访问网络等）时，提供一个简单的方法继续测试。

### 2.1 基本示例

例如，现在要编写一个从一个站点拉取用户列表信息的程序，拉取完成之后程序显示和分析。如果每次都去访问网会带来极大的不确定性，甚至每次返回不同的列表，这就给测试带来了极大的困难。此时就可以使用 Mock 技术：

```go
package main

import (
  "encoding/json"
  "fmt"
  "io/ioutil"
  "net/http"
)

type User struct {
  Name string
  Age  int
}

type ICrawler interface {
  GetUserList() ([]*User, error)
}

type MyCrawler struct {
  url string
}

func (c *MyCrawler) GetUserList() ([]*User, error) {
  resp, err := http.Get(c.url)
  if err != nil {
    return nil, err
  }
  
  defer resp.Body.Close()
  data, err := ioutil.ReadAll(resp.Body)
  if err != nil {
    return nil, err
  }
  
  var userList []*User
  err = json.Unmarshal(data, &userList)
  if err != nil {
    return nil, err
  }
  
  return userList, nil
}

func GetAndPrintUsers(crawler ICrawler) {
  users, err := crawler.GetUserList()
  if err != nil {
    return
  }
  
  for _, u := range users {
    fmt.Println(u)
  }
}
```

`Crawler.GetUserList()`方法完成爬取和解析操作，返回用户列表。为了方便 Mock，`Crawler.GetUserList()`函数接受一个`ICrawler`接口。然后来定义 Mock 对象，实现`ICrawler`接口：

```go
package main

import (
  "github.com/stretchr/testify/mock"
  "testing"
)

type MockCrawler struct {
  mock.Mock
}

func (m *MockCrawler) GetUserList() ([]*User, error) {
  args := m.Called()
  return args.Get(0).([]*User), args.Error(1)
}

var MockUsers []*User

func init() {
  MockUsers = append(MockUsers, &User{"dj", 18})
  MockUsers = append(MockUsers, &User{"zhangsan", 20})
}

func TestGetUserList(t *testing.T) {
  crawler := new(MockCrawler)
  crawler.On("GetUserList").Return(MockUsers, nil)
  
  GetANdPrintUsers(crawler)
  
  crawler.AssertExpections(t)
}
```

实现`GetUserList()`方法时，需要调用`Mock.Called()`方法，传入参数（示例中无参数）。`Mock.Called()`会返回一个`mock.Arguments`对象，该对象中保存着返回的值。它提供了对基本类型和`error`的获取方法：`Int()/String()/Bool()/Error()`，和通用的获取方法`Get()`。通用方法返回的是`interface{}`，需要类型断言为具体类型，都接受一个表示索引的参数。

`crawler.On("GetUserList").Return(MockUsers, nil)`是 Mock 发挥魔法的地方，这里指示调用`GetUserList()`方法的返回值分别为`MockUsers`和`nil`，返回值在上面的`GetUserList()`方法中被`Arguments.Get(0)`和`Arguments.Error(1)`获取。

最后`crawler.AssertExpectations(t)`对 Mock 对象做断言。

运行结果如下：

```
> go test
&{dj 18}
&{zhangsan 20}
PASS
ok      github.com/darjun/testify       0.258s
```

### 2.2 调用次数

使用`Mock.Times(n int)`可以精确断言某方法以特定参数的调用次数。有两个便捷的函数`Once()/Twice()`。

比如，要求函数`Hello(n int)`要以参数 1 调用 1 次，参数 2 调用两次，参数 3 调用 3 次：

```go
type IExample interface {
  Hello(n int) int
}

type Example struct {
}

func (e *Example) Hello(n int) int {
  fmt.Printf("Hello with %d\n", n)
  return n
}

func ExampleFunc(e IExamle) {
  for n := 1; n <= 3; n++ {
    for i := 0; i< n; i++ {
      e.Hello(n)
    }
  }
}
```

编写 Mock 对象：

```go
type MockExample struct {
  mock.Mock
}

func (e *MockExample) Hello(n int) int {
  args := e.Mock.Called(n)
  return args.Int(0)
}

func TestExample(t *testing.T) {
  e := new(MockExample)
  
  e.On("Hello", 1).Return(1).Times(1)
  e.On("Hello", 2).Return(2).Times(2)
  e.On("Hello", 3).Return(3).Times(3)
  
  ExampleFunc(e)
  
  e.AssertExpectations(t)
}
```

运行：

```
> go test
PASS
ok      github.com/darjun/testify       0.236s
```

还可以设置以指定参数调用会导致 panic，测试程序的健壮性：

```go
e.On("Hello", 100).Panic("Out of range")
```

## 三、suite

`testify`提供了测试套件的功能：`TestSuite`，该测试套件只是一个结构体，内嵌一个匿名的`suite.Suite`结构。测试套件中可以包含多个测试，可以共享状态，还可以定义钩子方法执行初始化和清理操作。钩子都是通过接口来定义的，实现了这些接口的测试套件结构在运行到执行节点时会调用对应的方法。

### 3.1 测试前钩子

```go
type SetupAllSuite interface {
  SetupSuite()
}
```

如果定义了`TearDownSuite()`方法（即实现了`TearDownAllSuite`接口），在套件中**所有测试运行前**调用这个方法。

### 3.2 测试后钩子

```go
type TearDownAllSuite interface {
  TearDownSuite()
}
```

如果定义了`TearDownSuite()`方法（即实现了`TearDownSuite`接口），在套件中**所有测试运行完成后**调用这个方法。

### 3.3 每个测试执行前钩子

```go
type SetupTestSuite interface {
  SetupTest()
}
```

如果定义了`SetupTest()`方法（即实现了`SetupTestSuite`接口），在套件中**每个测试执行前**都会调用这个方法。

### 3.4 每个测试执行后钩子

```go
type TearDownTestSuite interface {
  TearDownTest()
}
```

如果定义了`TearDownTest()`方法（即实现了`TearDownTest`接口），在套件中**每个测试执行后**都会调用这个方法。

### 3.5 BeforeTest/AfterTest

另外还有一对接口`BeforeTest/AfterTest`，它们分别在每个测试运行前/后调用，接受套件名和测试名作为参数。

### 3.6 示例

```go
type MyTestSuit struct {
  suite.Suite
  testCount uint32
}

func (s *MyTestSuit) SetupSuite() {
  fmt.Println("SetupSuite")
}

func (s *MyTestSuit) TearDownSuite() {
  fmt.Println("TearDownSuite")
}

func (s *MyTestSuit) SetupTest() {
  fmt.Printf("SetupTest test count:%d\n", s.testCount)
}

func (s *MyTestSuit) TearDownTest() {
  s.testCount++
  fmt.Printf("TearDownTest test count:%d\n", s.testCount)
}

func (s *MyTestSuit) BeforeTest(suiteName, testName string) {
  fmt.Printf("BeforeTest suite:%s test:%s\n", suiteName, testName)
}

func (s *MyTestSuit) AfterTest(suiteName, testName string) {
  fmt.Printf("AfterTest suite:%s test:%s\n", suiteName, testName)
}

func (s *MyTestSuit) TestExample() {
  fmt.Println("TestExample")
}
```

这里只是简单的在各个钩子函数中打印信息，统计执行完成的测试数量。由于要借助`go test`运行，所以需要编写一个`TestXxx`函数，在该函数中调用`suite.Run()`运行测试套件：

```go
func TestExample(t *testing.T) {
  suite.Run(t, new(MyTestSuit))
}
```

这样将会运行`MyTestSuit`中所有名为`TestXxx`的方法。

运行结果如下：

```shell
$ go test
SetupSuite
SetupTest test count:0
BeforeTest suite:MyTestSuit test:TestExample
TestExample
AfterTest suite:MyTestSuit test:TestExample
TearDownTest test count:1
TearDownSuite
PASS
ok      github.com/darjun/testify       0.375s
```




