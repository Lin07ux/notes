> 转摘：[go test 的一个小技巧：禁用缓存](https://mp.weixin.qq.com/s/4DljJ8FlbmaEvdFi4V1oTw)

### 1. 问题

在执行单元测试时，如果代码没有任何变动（包括可能读取的配置文件），那么后续的测试执行会直接读取缓存中的测试结果，同时会有一个`cached`标记：

```shell
> go test -run ^TestPrint$ test/hello
ok   test/hello 0.113s

> go test -run ^TestPrint$ test/hello
ok   test/hello (cached)
```

有时候可能希望执行实际的测试，比如看日志输出。此时需要禁用缓存，需要怎么做呢？

### 2. 解决

查看 go test 的文档，里面有如下的一段说明：

```
When 'go test' runs in package list mode, 'go test' caches successful
package test results to avoid unnecessary repeated running of tests. To
disable test caching, use any test flag or argument other than the 
cacheable flags. The idiomatic way to disable test caching explicitly
is to use -count=1.
```

也就是说，在`go test`命令中加上`-count=1`选项即可禁用缓存。

在 [issues #24573](https://github.com/golang/go/issues/24573) 中有人提到，在 Go 1.10 及以前，可以通过`GOCACHE=off`的方式来禁用测试缓存；在 Go 1.11 及以上版本中则使用`-count=1`来禁用。这是因为 Go 1.11 开始，`GOCACHE=off`会影响`go.mod`。

另外，在 VSCode 中，可以加上如下的配置来禁用缓存：

```
"go.testFlags": ["-count=1"]
```


