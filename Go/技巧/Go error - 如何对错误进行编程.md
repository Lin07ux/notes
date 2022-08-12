> 转摘：[Go 创始人诠释：如何对错误进行编程？](https://mp.weixin.qq.com/s/_7VHPSekCGZZH2ikDIwZEA)

在 Go 的官方谚语中，有一条是“Errors are values”，其基本意思就是说 Go 中的错误其实就是一个普通的值。说这句话的 Rob Pike 通过文章《[Errors are values](https://go.dev/blog/errors-are-values)》诠释了这句谚语的意思。

### 1. 背景

Go 程序员，尤其是刚接触该语言的程序员，经常讨论的一个问题是如何处理错误。对于以下代码片段的出现的次数，谈话经常变成哀叹：

```go
if err != nil {
    return err
}
```

通过扫描能找到的所有 Go 开源项目，发现这个代码片段只在每一两页出现一次，比一些人认为的要少。

尽管如此，如果人们任然认为必须经常输入如下代码：

```go
if err != nil
```

那么一定有什么地方出了问题，而明显的目标就是 Go 语言本身。

### 2. 错误的理解

显然这是不幸的、误导的，而且很容易纠正。也许现在的情况是，刚接触 Go 的程序员会问：“如何处理错误？”。学习这种`if err != nil`的模式，然后就此打住。

在其他语言中，人们可能会使用`try-catch`块或其他类似机制来处理错误。因此，很多程序员认为，当在以前的语言中会使用`try-catch`时，在 Go 中只需要输入`if err != nil`。

于是，随着时间的推移，Go 代码中收集了许多这样`if err != nil`的片段，结果感觉很笨拙。

不管这种解释是否合适，很明显，这些 Go 程序员错过了关于错误的一个基本点：**错误是值（Errors are values）**。

**值可以被编程，既然错误是值，那么错误也可以被编程**。

当然，设计错误值的常见语句是测试它是否为 nil，但是还有无数其他事情可以用错误值做，并且应用其中一些其他事情可以使程序更好，消除很多`if err != nil`的样板。

如果使用死记硬背的`if`语句检查每个错误，就会出现上述提到的情况。

### 3. bufio 例子

下面是一个来自 bufio 包的 Scanner 类型的简单例子。它的`Scan`方法执行了底层的 I/O，这当然可能会导致一个错误。然后，`Scan`方法根本没有暴露出错误，相反，它返回一个布尔值，并在扫描结束时运行一个单独的方法，报告是否发生错误。

客户端代码看起来像这样：

```go
scanner := bufio.NewScanner(input)
for scanner.Scan() {
    token := scanner.Text()
    // process token
}
if err := scanner.Err(); err != nil {
    // process the error
}
```

当然，有一个 nil 检查错误，但它只出现并执行一次。Scan 方法可以改为定义为：

```go
func (s *Scanner) Scan() (token []byte, error)
```

然后，用户代码的例子可能是（取决于如何检索令牌）：

```go
scanner := bufio.NewScanner(input)
for {
    token, err := scanner.Scan()
    if err != nil {
        return err // or maybe break
    }
    // process token
}
```

这并没有太大的不同，但有一个重要的区别：在这段代码中，客户端必须在每次迭代时检查错误，但在真正的 Scanner API 中，错误处理是从关键 API 元素中抽象出来的，它正在迭代令牌。

使用真正的 API，客户端的代码因此感觉更自然：循环直到完成，然后担心错误，错误处理不会掩盖控制流程。

当然，在幕后发生的事情是，一旦`Scan`遇到 I/O 错误，它就会记录它并返回 false。当客户端询问时，一个单独的方法`Err`会报告错误值。

虽然这很微不足道，但它与在每个`if err != nil`后到处放或要求客户端检查错误是不一样的。这是用错误值变成，简单的编程，仍然是变成。

值的强调的是，无论设计如何，程序检查错误是至关重要的，无论它们暴露在哪里。这里的讨论不是关于如何避免检查错误，而是关于使用语言优雅地处理错误。

### 4. 实战探讨

在东京矩形的 2014 年秋季 GoCon 时，出现了重复错误检查代码的话题。以为热心的 Gopher 回应了熟悉的关于错误检查的哀叹。

他有一些代码，从结构上看是这样的：

```go
_, err := fd.Write(p0[a:b])
if err != nil {
    return err
}

_, err = fd.Write(p1[c:d])
if err != nil {
    return err
}

_, err = fd.Write(p2[e:f])
if err != nil {
    return err
}

// and so on
```

它是非常重复的。在真正的代码中，这段代码比较长，有更多的事情要做，所以不容易只是用一个辅助函数来重构这段代码。但在这种理想化的形式中，一个函数字面的关闭对错误变量会有帮助：

```go
var err error
write := func(buf []byte) {
    if err != nil {
        return
    }
    _, err = w.Write(buf)
}

write(p0[a:b])
write(p1[c:d])
write(p2[e:f])
// and so on

if err != nil {
    return err
}
```

这种模式效果很好，但需要在每个执行写入的函数中关闭；单独的辅助函数使用起来比较笨拙，因为需要在调用之前维护 err 变量。

可以通过借用上面的扫描方法的思路，使之更简洁、更通用、更可重复使用。

定义一个名为`errWriter`的对象，并为其添加一个`write`方法，如下所示：

```go
type errWriter struct {
    w   io.Writer
    err error
}

func (ew *errWriter) write(buf []byte) {
    if ew.err != nil {
        return
    }
    _, ew.err = ew.w.Write(buf)
}
```

`write`方法并不需要具有标准的`Write`签名，其底层`Writer`的`Write`方法，并记录第一个错误以备参考。一旦发生错误，`write`方法就会变成无用功，但错误值会被保存。

鉴于 errWriter 类型和它的`write`方法，上面的代码可以被重构为如下代码：

```go
ew := &errWriter{w: fd}
ew.write(p0[a:b])
ew.write(p1[c:d])
ew.write(p2[e:f])
// and so on
if ew.err != nil {
    return ew.err
}
```

这更干净，甚至与使用闭包相比，也使实际的写入顺序更容易在页面上看到。使用错误值（和接口）进行编程，会使代码更好，不再有混乱。

很可能同一个包中的其他一些代码可以基于这个想法，甚至直接使用 errWriter。

另外，一旦 errWriter 存在，它可以做更多的事情来帮助简化代码，特别是在不太人性化的例子中：它可以积累字节数、可以将写内容凝聚成一个缓冲区然后以原子方式传输，还有更多。

事实上，这种模式经常出现在标准库中：archive/zip 和 net/http 包使用到了这种方式。在这个讨论中更突出的是：bufio 包的 Writer 实际上是 errWriter 思想的一个实现，尽管`bufio.Writer.Write`方法返回错误，但这主要是为了尊重 io.Writer 接口。

bufio.Writer 的`Write`订单的行为就像上面的 errWriter.write 方法一样，Flush 会报错，所以可以这样写：

```go
b := bufio.NewWriter(fd)
b.Write(p0[a:b])
b.Write(p1[c:d])
b.Write(p2[e:f])
// and so on
if b.Flush() != nil {
    return b.Flush()
}
```

这种方法有一个明显的缺点，至少对于某些应用程序而言：没有办法知道在错误发生之前完成了多少处理。如果该信息很重要，则需要更细粒度的方法。不过，通常情况下，最后进行全有或全无检查就足够了。

### 5. 总结

在本文中，只研究了一种避免重复错误处理代码的结束。但是使用`errWriter`或`bufio.Writer`并不是简化错误处理的唯一方法，而且这种方法并不适用于所有情况。

不过，关键点在于：**错误是值，Go 编程语言的全部功能都可用于处理它们**。

而且，无论做什么，都要检查错误。


