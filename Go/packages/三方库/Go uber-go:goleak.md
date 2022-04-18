> 转摘：[好物分享：快速找到 Goroutine 泄露的地方](https://mp.weixin.qq.com/s/E3Lgl9T8iQYl65T3e0vDTA)

[uber-go/goleak](https://github.com/uber-go/goleak) 能够结合单元测试去快速检测 goroutine 泄露，达到避免和排查的目的。

> 类似的还有`ysmood/gotrace`库，可以达到类似的效果。

### 1. 安装

```shell
go get -u go.uber.org/goleak
```

### 2. 泄露示例

下面的代码中 Channel 会导致一直阻塞，造成 goroutine 泄露：

```go
func leak() {
  ch := make(chan struct{})
  go func() {
    ch <- struct{}{}
  }()
}
```

测试该方法：

```go
func TestLeak(t *testing.T) {
  leak()
}
```

输出结果：

```
=== RUN   TestLeak
--- PASS: TestLeak (0.00s)
PASS
```

是可以正常通过测试的。

### 3. goleak 检测

在测试代码中引入 goleak 库，使用`goleak.VerifyNone()`方法即可检测到 goroutine 泄露：

```go
import (
  "testing"
  "go.uber.org/goleak"
)

func TestLeak(t *testing.T) {
  defer goleak.VerifyNone(t)
  leak()
}
```

再进行验证时就能检测出泄露问题了：

```
=== RUN   TestLeak
    leaks.go:78: found unexpected goroutines:
        [Goroutine 7 in state chan send, with github.com/eddycjy/awesome-project/tools.leak.func1 on top of the stack:
        goroutine 7 [chan send]:
        github.com/eddycjy/awesome-project/tools.leak.func1(0xc0000562a0)
         /Users/eddycjy/go-application/awesomeProject/tools/leak.go:6 +0x35
        created by github.com/eddycjy/awesome-project/tools.leak
         /Users/eddycjy/go-application/awesomeProject/tools/leak.go:5 +0x4e
        ]
--- FAIL: TestLeak (0.46s)
FAIL
```

可以从输出结果中看到有 goroutine，并且找到引发 goroutine 泄露的代码栈和泄露类型。

