> 转摘：[你有考虑过 defer Close() 的风险吗](https://mp.weixin.qq.com/s/xWgJESdeECH96JP20yfUJw)

### 1. 惯例

作为一名 Gopher，我们很容易形成一个编程惯例：每当得到一个实现了`io.Closer`接口的对象`x`时，在检查错误之后，会立即使用`defer x.Close()`以保证函数返回时`x`对象的关闭。

以下给出两个惯用写法例子：

* HTTP 请求

    ```go
    resp, err := http.Get("https://golang.google.cn/")
    if err != nil {
        return err
    }
    defer resp.Body.Close()
    // The following code: handle resp
    ```

* 访问文件

    ```go
    f, err := os.Open("/home/golangshare/gopher.txt")
    if err != nil {
        return err
    }
    defer f.Close()
    // The follwowing code: handle f
    ```

### 2. 存在问题

实际上，这种写法是存在潜在问题的：`defer x.Close()`会忽略`Close()`的返回值，但在执行`Close()`时，并不能保证`x`一定能正常关闭。

如果在关闭的时候返回了错误应该怎么办？这种写法会让程序有时可能出现非常难以排查的错误。

`Close()`方法会返回什么错误呢？在 POSIX 类操作系统（Linux、MacOS）中，关闭文件的`Close()`函数最终是调用了系统方法`vlose()`。通过`man close`可以看到`close()`可能返回的错误如下：

```
ERRORS
     The close() system call will fail if:

     [EBADF]            fildes is not a valid, active file descriptor.

     [EINTR]            Its execution was interrupted by a signal.

     [EIO]              A previously-uncommitted write(2) encountered an
                        input/output error.
```

错误`EBADF`表示无效的文件描述符，`EINTR`指的是 Unix 信号打断，`EIO`表示未提交写。这里只有`EIO`错误与本文相关。

`EIO`错误是指文件的`write()`的写入还未提交时就调用了`close()`方法。

![](http://cnd.qiniu.lin07ux.cn/markdown/1631503925568-53900ce7a79e.jpg)

上图是一个经典的计算机存储器层级结构，在这个层级结构中，从上至下，设备的访问速度越来越慢，容量越来越大。存储器层级结构的主要思想是上一层的存储器作为低一层存储器的高速缓存。CPU 访问寄存器会非常快，相比之下，访问 RAM 就会很慢，而访问磁盘或者网络，那就意味蹉跎光阴。

如果每个`write()`调用都将数据同步地提交到磁盘，那么系统的整体性能能将会极度降低，所以计算机是不会这样工作的。当调用`write()`时，数据并没有立即被写到目标载体上，计算机存储器每层再提都在缓存数据，在合适的时机下，将数据刷到下一层载体，这将写入调用的同步、缓慢、阻塞转为了快速、异步的过程。

这样看来，`EIO`错误的确是需要提防的错误。这意味着如果尝试将数据保存到磁盘，在`defer x.Close()`执行时，操作系统还并未将数据刷到磁盘，这是我们应该获取到该错误提示（只要数据还未落盘，那数据就没持久化成功，它就是有可能丢失的，例如出现停电事故，这部分数据就永久丢失了，且我们会毫不知情）。

但是按照上文的惯例写法，程序得到的会是`nil`错误。

### 3. 解决方案

针对关闭文件的情况，来探讨几种可行的改造方案：

**第一种方案：不使用 defer**

```go
func solution01() error {
    f, err := os.Open("/home/golangshare/gopher.txt")
    if err != nil {
        return err
    }
    
    if _, err = io.WriteString(f, "hello, gopher"); err != nil {
        f.Close()
        return err
    }
    
    return f.Close()
}
```

这种方案需要在每个发生错误的地方都要加上关闭语句`f.Cose()`，如果对`f`的写操作 case 较多，容易存在遗漏关闭文件的风险。

**第二种方案：通过闭包和命名返回 err 来处理**

```go
func solution02() (err error) {
    f, err := os.Open("/home/golangshare/gopher.txt")
    if err != nil {
        return err
    }
    
    defer func() {
        closeErr := f.Close()
        if err == nil {
            err = closeErr
        }
    }()
    
    _, err = io.WriteString(f, "hello gopher")
    return
}
```

这种方案解决了方案一中忘记关闭文件的风险，如果有更多`if err != nil`的分支条件，这种模式可以有效降低代码行数。

**第三种方案：在函数嘴周 return 语句之前，显式调用一次 f.Close()**

```go
func solution03() error {
    f, err := os.Open("/home/golangshare/gopher.txt")
    if err != nil {
        return err
    }
    
    defer f.Close()
    
    if _, err := io.WriteString(f, "hello gopher"); err != nil {
        return err
    }
    
    if err != f.Close(); err != nil {
        return err
    }
    
    return nil
}
```

这种解决方案能在`io.WriteString()`发生错误时，由于`defer f.Close()`的存在能得到`close`的调用；也能在`io.WriteString()`未发生错误时、但缓存未刷新到磁盘时，得到`err := f.Close()`的错误。而且由于`defer f.Close()`并不会返回错误，所以并不用担心两次`f.Close()`调用会将错误覆盖。

**第四种方案：函数 return 时执行 f.Sync()**

```go
func solution04() error {
    f, err := os.Open("/home/golangshare/gopher.txt")
    if err != nil {
        return err
    }
    
    defer f.Close()
    
    if _, err := io.WriteString(f, "hello gopher"); err != nil {
        return err
    }
    
    return f.Sync()
}
```

在调用`defer f.Close()`之前，通过`f.Sync()`（其内部调用系统函数`fsync`）强制性让内核将缓存持久到磁盘中，这样就能很好的避免`close`出现`EIO`。可以预见的是，由于强制性刷盘，这种方案虽然能很好地保证数据安全性，但是在执行效率上却会大打折扣。


