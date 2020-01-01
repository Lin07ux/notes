> 转摘：[一个简单的 HTTP 调用，为什么时延这么大？](https://mp.weixin.qq.com/s/qsIiEZCsasE8lXPOSTN6wQ)

TCP 中 Nagle 算法是用来提升网络传输效率的，但是由于会和 TCP Delayed ACK 规则“冲突”，从而会造成延时增加的问题。而使用 TCP_NODELAY 则可以避免这种延迟。

### 1. Nagle 算法是什么

Nagle 算法是一种通过减少网络发送的数据包的数量来提高 TCP/IP 网络效率的方法。它使用发明人 John Nagle 的名字来命名，John Nagle 在 1984 年首次用这个算法来尝试解决福特汽车公司的网络拥塞问题。

如果应用程序每次产生 1 个字节的数据，然后这 1 字节的数据就以一个网络数据包的形式发送到远端服务器，那么就很容易导致网络由于图爱多的数据包而过载。在这种典型情况下，传送一个只有 1 个字节有效数据的数据包，却要话费 40 个字节的包头(即 IP 头部 20 字节 + TCP 头部 20 字节)的额外开销，这种有效载荷(payload)的利用率极其低下。

Nagle 算法的内容比较简单，以下是伪代码：

```shell
if there is new data to send
    if the window size >= MSS and available data is >= MSS
        send complete MSS segment now
    else
        if there is unconfirmed data still in the pipe
            enqueue data in the buffer until an acknowledge is received
        else
            send data immediately
        end if
    end if
end if
```

也就是：

1. 如果发送内容大于等于 1 个 MSS，则立即发送；
2. 如果之前没有包未被 ACK，立即发送；
3. 如果之前有包未被 ACK，缓存发送内容；
4. 如果收到 ACK，立即发送缓存的内容。

> MSS 为 TCP 数据表每次能够传输的最大数据分段。

### 2. Delayed ACK 是什么

TCP 协议为了保证传输的可靠性，规定在接受到数据包时需要向对方发送一个确认。只是单纯的发送一个确认，代价会比较高(IP 头部 20 字节 + TCP 头部 20 字节)。

TCP Delayed ACK(延迟确认)就是为了改善网络性能来解决这个问题的。它将几个 ACK 响应组合在一起成为单个响应，或者将 ACK 响应与响应数据一起发送给对方，从而减少协议开销。

具体做法是：

* 当有响应数据要发送时，ACK 会随响应数据立即发送给对方；
* 如果没有相应数据，ACK 将会延迟发送，以等待看是否有相应数据可以一起发送，在 Linux 系统中，这个延迟时间默认是 40ms；
* 如果在等待发送 ACK 期间，对方第二个数据包又到达了，这时要立即发送 ACK。但是如果对方的三个数据包相继到达，第三个数据段到达时是否立即发送 ACK，则取决于以上两条。

### 3. Nagle 与 Delayed ACK 一起工作时会出现的问题

Nagle 与 Delayed AVK 都能够提高网络传输的效率，但在一起会好心办坏事。

例如：A 和 B 进行数据传输，A 运行 Nagle 算法，B 运行 Delayed ACK 算法。如果 A 向 B 发送一个数据包，B 由于 Delayed ACK 不会立即响应。而 A 使用 Nagle 算法，A 就会一直等 B 的 ACK，ACK 不来就不会继续发送第二个数据包。如果这两个数据包是应对同一个请求，那这个请求就会被一直延迟到 40ms 超时再发送。

### 4. TCP_NODELAY

在 Socket 编程中，`TCP_NODELAY`选项是用来控制是否开启 Nagle 算法。在 Java 中，`TCP_NODELAY`选项的值为 true 表示关闭 Nagle 算法，为 false 表示打开 Nagle 算法。

当使用`TCP_NODELAY`关闭了 Nagle 算法，即使上个数据包的 ACK 没有到达，也会发送下个数据包，进而打破 Delayed ACK 造成的影响。

当然也可以通过修改 Delayed ACK 的相关系统配置来解决问题，但由于需要修改机器配置，很不方便，因此这种方式不太推荐。

一般在网络编程中，强烈建议开启`TCP_NODELAY`。


