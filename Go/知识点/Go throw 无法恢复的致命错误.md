> [Go 有哪几种无法恢复的致命场景？](https://mp.weixin.qq.com/s/gSfzrSKYbZTP8COz4lZKHQ)

### 1. 错误类型

Go 中的错误主要有如下三种：

1. error

    error 是 Go 中的标准错误，其本质是一个`interface{}`，任何一个实现了`func Error() string`方法的类型都可以认为是 error 错误类型。

2. panic

    panic 是 Go 中的异常，能够产生异常错误。可以通过`panic + recover`来恢复程序的执行进程。如果没有使用`recover`捕获 panic，就会导致程序中断。
    
3. throw

    throw 表示的是致命错误，这个错误类型在用户侧是没法主动调用的，均为 Go 底层自行调用。常见的 map 并发读写竞争时抛出的错误就是由此触发的。

### 2. throw 源码

`throw`方法很简单，其源码如下：

```go
func throw(s string) {
  systemstack(func() {
    print("fatal error:", s, "\n")
  })
  
  gp := getg()
  if gp.m.throwing == 0 {
    gp.m.throwing = 1
  }
  fatalthrow()
  *(*int)(nil) = 0 // not reached
}
```

`throw`方法会获取当前 G 的实例，并设置其 M 的`throwing`状态为 1。然后再调用`fatalthrow`方法进行真正的 Crash 相关操作：

```go
func fatalthrow() {
  pc := getcallerpc()
  sp := getcallersp()
  gp := getg()
  
  systemstack(func() {
    startpanic_m()
    if dopanic_m(gp, pc, sp) {
      crash()
    }
    
    exit(2)
  })
  
  *(*int)(nil) = 0 // not reached
}
```

`fataltrhow`的主体逻辑就是发送`_SIGABRT`信号量，最后调用`exit`方法退出。所以被`throw`抛出的致命错误是无法拦住的。

### 3. 致命场景

#### 3.1 并发读写 map

```go
func foo() {
  m := map[string]int[]
  
  go func() {
    for {
      m["煎鱼"] = 1
    }
  }()
  
  for {
    _ = m["煎鱼 2"]
  }
}
```

输出结果：

```
fatal error: concurrent map read and map write

goroutine 1 [running]:
runtime.throw(0x1078103, 0x21)
...
```

#### 3.2 堆栈内存耗尽

```go
func foo() {
  var f func(a [1000]int64)
  f = func(a [1000]int64) {
    f(a)
  }
  f([1000]int64)
}
```

输出结果：

```
runtime: goroutine stack exceeds 1000000000-byte limit
runtime: sp=0xc0200e1bf0 stack=[0xc0200e0000, 0xc0400e0000]
fatal error: stack overflow

runtime stack:
runtime.throw(0x1074ba3, 0xe)
        /usr/local/Cellar/go/1.16.6/libexec/src/runtime/panic.go:1117 +0x72
runtime.newstack()
...
```

#### 3.3 将 nil 函数作为 goroutine 启动

```go
func foo() {
  var f func()
  go f()
}
```

输出结果：

```go
fatal error: go of nil func value

goroutine 1 [running]:
main.foo()
...
```

#### 3.4 goroutines 死锁

```go
func foo() {
  select{}
}
```

输出结果：

```
fatal error: all goroutines are asleep - deadlock!

goroutine 1 [select (no cases)]:
main.foo()
...
```

#### 3.5 线程限制耗尽

如果 goroutine 被 IO 操作阻塞了，新的线程就可能会被启动来执行其他的 goroutines。

Go 的最大线程数是有限制的，如果达到了这个限制，应用程序就会崩溃，会出现如下的错误结果：

```
fatal error: thread exhaustion
...
```

#### 3.6 超出可用内存

如果执行的操作（如下载大文件等）导致应用程序占用的内存过大，就可能会造成 OOM，此时出现如下的错误：

```
fatal error: runtime: out of memory
...
```


