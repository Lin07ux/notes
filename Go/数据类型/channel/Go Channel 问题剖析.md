> 转摘：[从鹅厂实例出发！分析Go Channel底层原理](https://mp.weixin.qq.com/s/nQ2SxT8dtRWjbDQccBaY1Q)

### 1. 无缓冲 Channel 导致内存泄露

**问题**

一个线上服务，其内存使用量成锯齿状增长，达到服务设置的内存上限后就会发生容器重启，看现象是出现了内存泄露。

![](https://cnd.qiniu.lin07ux.cn/markdown/1670138194)

**代码**

线上服务的代码经过简化，基本逻辑如下：

```go
package main

import (
  "errors"
  "fmt"
)

func accessMultiService() (data string, err error) {
  respAChan := make(chan string) // 无缓冲 Channel
  go func() {
    serviceAResp, _ := accessServiceA()
    respAChan <- serviceAResp
  }()
  
  _, serviceBErr := accessServiceB()
  if serviceBErr != nil {
    return "", errors.New("service B response error")
  }
  
  respA := <- respAChan
  fmt.Printf("Service A response is: %s\n", respA)
  return "success", nil
}

func accessServiceA() (string, error) {
  return "service A result", nil
}

func accessServiceB() (string, error) {
  return "service B result", errors.New("service B error")
}
```

**解析**

这段代码是希望通过协程的方式并发的访问服务 A 和 B，而且服务 A 的结果会通过一个无缓冲的 Channel 提供给当前的协程。

当前协程在发起 A 请求之后，会继续执行 B 请求。而如果 B 请求发生错误，那么当前协程就直接返回，不再等待服务 A 的响应了。

也就是说，如果请求 B 服务发生错误，那么 A 服务的子协程里的无缓冲 Channel respAChan 就会一直没有数据接收方，导致该子协程在发送数据的时候会一直被阻塞住，内存资源无法释放。随着请求数量的增多，直到达到内存上限是容器崩溃重启。

**解决**

解决办法可以是：将无缓冲 Channel 改成有缓冲 Channel，并且在写入数据后关闭它。这样就不会发生 goroutine 一直被阻塞无法释放资源的问题了。

```go
respAChan := make(chan string, 1) // 改为有缓冲 Channel
go func() {
  serviceAResp, _ := accessServiceA()
  respAChan <- serviceAResp
  close(respAChan) // 写入数据后关闭 Channel
}()
```


