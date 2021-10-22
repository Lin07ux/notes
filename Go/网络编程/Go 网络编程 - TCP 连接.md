> 转摘：
> 
> 1. [Go 网络编程和 TCP 抓包实操](https://mp.weixin.qq.com/s/k4rtZoEzZ_SZQW5RfrGe0A)
> 2. [Go 中如何强制关闭 TCP 连接](https://mp.weixin.qq.com/s/Lbv4myGcvH6fIfNoQfyqQA)

### 1. Go 网络编程模型

网络编程属于 IO 的范畴，其发展可以简单概括为：多进程 --> 多线程 --> non-block + I/O 多路复用。

Go 语言的网络编程模型是*同步网络编程*，基于*协程 + I/O 多路复用*，结合网络轮询器与调度器实现。

> Go 网络编程中的 I/O 多路复用：Linux 下是 epoll，Darwin 下是 kqueue，Windows 下是 iocp。它们通过网络轮询器 netpoller 进行封装。

用户层 goroutine 中的 block socket，实际上是通过 netpoller *模拟*出来的。runtime 拦截了底层 socket 系统调用的错误码，并通过 netpooler 和 goroutine 调度，让 goroutine *阻塞*在用户层得到的 socket fd 上。

Go 将网络编程的复杂性隐藏与 runtime 中：开发者不用关注 socket 是否是 non-block 的，也不用处理回调，只需在每个连接对应的 goroutine 中以 *block I/O* 的方式对待 socket 即可。

![Go 网络编程流程示意图](http://cnd.qiniu.lin07ux.cn/markdown/1634831822784-e66abe1607e0.jpg)

例如：当用户层针对某个 socket fd 发起 read 操作时，如果该 socket fd 中尚无数据，那么 runtime 会将该 socket fd 加入到 netpoller 中监听，同时对应的 goroutine 被挂起。直到 runtime 收到 socket fd 数据 ready 的通知，runtime 才会重新唤醒等待在该 socket fd 上准备 read 的那个 goroutine。而*这个过程从 goroutine 的视角来看，就像是 read 操作一直 block 在那个 socket fd 上似的*。

一句话总结：**Go 将复杂的网络模型进行封装，放在用户面前的只是阻塞式 I/O 的 goroutine，这让用户可以非常轻松地实现高性能网络编程。**

### 2. TCP Server

在 Go 中，网络编程非常容易。下面通过 Go 中的`net`包，可以轻松的实现一个 TCP 服务器：

```go
package main

import (
	"log"
	"net"
)

func main() {
	// Part 1: Create a listener
	l, err := net.Listen("tcp", ":8000")
	if err != nil {
		log.Fatalf("Error listener returned: %s", err)
	}
	defer l.Close()

	for {
		// Part 2: accept new connection
		c, err := l.Accept()
		if err != nil {
			log.Fatalf("Error to accept new connection: %s", err)
		}

		// Part 3: create a goroutine that reads and write back data
		go func() {
			log.Printf("TCP session open")
			defer c.Close()

			for {
				d := make([]byte, 100)

				// Read from TCP buffer
				if _, err := c.Read(d); err != nil {
					log.Printf("Error reading TCP session: %s", err)
					break
				}
				log.Printf("Reading data from client: %s\n", string(d))

				// Write back data to TCP client
				if _, err = c.Write(d); err != nil {
					log.Printf("Error writting TCP session: %s", err)
					break
				}
			}
		}()
	}
}
```

根据逻辑，代码分为三个部分：

* *第一部分：端口监听*。通过使用`net.Listen("tcp", ":8000")`在端口 8000 上开启了 TCP 连接监听。

* *第二部分：建立连接*。开启监听之后，调用`net.Listener.Accept()`方法等待 TCP 连接。`Accept`方法将阻塞式地等待新的连接到达，并将该链接作为`net.Conn`接口类型返回。

* *第三部分：数据传输*。当连接建立成功之后，启动一个新的 goroutine 来处理连接上的读取和写入。示例中服务器的数据处理逻辑是：服务器端将客户端写入该连接中的所有内容循环读取并记录到日志中，然后原封不动的发送给客户端。

### 3. TCP 客户端

同样，通过`net`包也能快速实现一个 TCP 客户端：

```go
package main

import (
	"log"
	"net"
	"time"
)

func main() {
	// Part 1: open a TCP session to server
	c, err := net.Dial("tcp", "localhost:8000")
	if err != nil {
		log.Fatalf("Error to open TCP connection: %s", err)
	}
	defer c.Close()

	// Part 2: write some data to server
	log.Printf("TCP session open")
	b := []byte("Hi, gopher?")
	if _, err = c.Write(b); err != nil {
		log.Fatalf("Error writting TCP session: %s", err)
	}

	// Part 3: create a goroutine that closes TCP session after 10 seconds
	go func() {
		<- time.After(time.Duration(10) * time.Second)
		defer c.Close()
	}()

	// Part 4: read any responses until get an error
	for {
		d := make([]byte, 100)

		if _, err := c.Read(d); err != nil {
			log.Fatalf("Error reading TCP session: %s", err)
		}
		log.Printf("Reading data from server: %s\n", string(d))
	}
}
```

客户端的代码分为 4 个部分：

* *第一部分：建立连接*。使用`net.Dial("tcp", "localhost:8000")`可以建立一个 TCP 连接到`localhost:8000`的服务器，也就是前面创建的 TCP Server。

* *第二部分：写入数据*。当连接建立成功后，通过`c.Write()`方法就可以向连接中写入数据到服务器了。示例中写入的是`Hi, gopher?`。

* *第三部分：关闭连接*。默认情况下，TCP 连接是不会自动关闭的。所以启动一个新的 goroutine，在 10s 之后主动调用`c.Close()`方法关闭 TCP 连接。

* *第四部分：读取数据*。除非发生 error，否则客户端可以通过`c.Read()`方法阻塞式的循环读取 TCP 连接上的数据。

### 4. 建立连接

实现代码之后，分别启动服务器端和客户端代码：

```shell
# 服务器端
$ go run main.go
2021/10/22 09:47:17 TCP session open
2021/10/22 09:47:17 Reading data from client: Hi, gopher?
2021/10/22 09:47:27 Error reading TCP session: EOF

# 客户端
2021/10/22 09:47:17 TCP session open
2021/10/22 09:47:17 Reading data from server: Hi, gopher?
2021/10/22 09:47:27 Error reading TCP session: read tcp [::1]:56424->[::1]:8000: use of closed network connection
exit status 1
```

可以看到，服务器断和客户端之间可以正常的收发数据，而且在 10s 之后客户端会自动的断开 TCP 连接，导致客户端再从连接中读取数据时触发错误。

### 4. 抓包分析

在抓包前，可以先通过下图熟悉一下上面的 TCP 通信过程：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634872270908-015f35a6c5e2.jpg)

下面通过 tcpdump 命令来抓取前面 TCP 客户端与服务器端通信全过程数据：

```shell
tcpdump -S -nn -vvv -i lo0 port 8000
```

> 这里：`-S` 显示序列号绝对值；`-nn`不解析主机名和端口，直接用端口号显示；`-vvv`显示更多的详细描述信息；`-i lo0`指定补货环回接口 localhost；`port 8000`将网络捕获过滤为仅与端口 8000 通信或来自端口 8000 的流量。

运行 tcpdump 之后，然后再分别执行服务器端和客户端代码，可以得到如下的数据表日志：

```log
tcpdump: listening on lo0, link-type NULL (BSD loopback), capture size 262144 bytes
09:49:35.199547 IP6 (flowlabel 0x70c00, hlim 64, next-header TCP (6) payload length: 44) ::1.56487 > ::1.8000: Flags [S], cksum 0x0034 (incorrect -> 0x9a6d), seq 3648971496, win 65535, options [mss 16324,nop,wscale 6,nop,nop,TS val 3255691295 ecr 0,sackOK,eol], length 0
09:49:35.199624 IP6 (flowlabel 0x80900, hlim 64, next-header TCP (6) payload length: 44) ::1.8000 > ::1.56487: Flags [S.], cksum 0x0034 (incorrect -> 0xebef), seq 284168922, ack 3648971497, win 65535, options [mss 16324,nop,wscale 6,nop,nop,TS val 2692278825 ecr 3255691295,sackOK,eol], length 0
09:49:35.199637 IP6 (flowlabel 0x70c00, hlim 64, next-header TCP (6) payload length: 32) ::1.56487 > ::1.8000: Flags [.], cksum 0x0028 (incorrect -> 0x4ced), seq 3648971497, ack 284168923, win 6371, options [nop,nop,TS val 3255691295 ecr 2692278825], length 0
09:49:35.199646 IP6 (flowlabel 0x80900, hlim 64, next-header TCP (6) payload length: 32) ::1.8000 > ::1.56487: Flags [.], cksum 0x0028 (incorrect -> 0x4ced), seq 284168923, ack 3648971497, win 6371, options [nop,nop,TS val 2692278825 ecr 3255691295], length 0
09:49:35.199820 IP6 (flowlabel 0x70c00, hlim 64, next-header TCP (6) payload length: 43) ::1.56487 > ::1.8000: Flags [P.], cksum 0x0033 (incorrect -> 0x5c06), seq 3648971497:3648971508, ack 284168923, win 6371, options [nop,nop,TS val 3255691295 ecr 2692278825], length 11
09:49:35.199832 IP6 (flowlabel 0x80900, hlim 64, next-header TCP (6) payload length: 32) ::1.8000 > ::1.56487: Flags [.], cksum 0x0028 (incorrect -> 0x4ce2), seq 284168923, ack 3648971508, win 6371, options [nop,nop,TS val 2692278825 ecr 3255691295], length 0
09:49:35.200014 IP6 (flowlabel 0x80900, hlim 64, next-header TCP (6) payload length: 132) ::1.8000 > ::1.56487: Flags [P.], cksum 0x008c (incorrect -> 0x5ba2), seq 284168923:284169023, ack 3648971508, win 6371, options [nop,nop,TS val 2692278825 ecr 3255691295], length 100
09:49:35.200026 IP6 (flowlabel 0x70c00, hlim 64, next-header TCP (6) payload length: 32) ::1.56487 > ::1.8000: Flags [.], cksum 0x0028 (incorrect -> 0x4c7f), seq 3648971508, ack 284169023, win 6370, options [nop,nop,TS val 3255691295 ecr 2692278825], length 0
09:49:45.205304 IP6 (flowlabel 0x70c00, hlim 64, next-header TCP (6) payload length: 32) ::1.56487 > ::1.8000: Flags [F.], cksum 0x0028 (incorrect -> 0x2580), seq 3648971508, ack 284169023, win 6370, options [nop,nop,TS val 3255701277 ecr 2692278825], length 0
09:49:45.205361 IP6 (flowlabel 0x80900, hlim 64, next-header TCP (6) payload length: 32) ::1.8000 > ::1.56487: Flags [.], cksum 0x0028 (incorrect -> 0xfe80), seq 284169023, ack 3648971509, win 6371, options [nop,nop,TS val 2692288807 ecr 3255701277], length 0
09:49:45.205522 IP6 (flowlabel 0x80900, hlim 64, next-header TCP (6) payload length: 32) ::1.8000 > ::1.56487: Flags [F.], cksum 0x0028 (incorrect -> 0xfe7f), seq 284169023, ack 3648971509, win 6371, options [nop,nop,TS val 2692288807 ecr 3255701277], length 0
09:49:45.205588 IP6 (flowlabel 0x70c00, hlim 64, next-header TCP (6) payload length: 32) ::1.56487 > ::1.8000: Flags [.], cksum 0x0028 (incorrect -> 0xfe80), seq 3648971509, ack 284169024, win 6370, options [nop,nop,TS val 3255701277 ecr 2692288807], length 0
```

可以重点关注下每条日志中的`FLAGS []`部分，其中：`[S]`表示 SYN（开始连接），`[.]`表示 ACK（数据应答），`[F]`表示 FIN（结束连接），`[P]`表示 PSH（推送数据），`[R]`表示 RST（重置链接）。

tcpdump 的日志序列完整的表达了整个 TCP 通信的过程：建立连接、收发数据、结束连接：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634873023748-95ee1705e487.jpg)

* 前面三条记录表示双方建立 TCP 连接的三次握手；
* 中间的四条记录分别表示客户端向服务器端发送数据，和服务器端向客户端发送数据；
* 最后的四条记录表示客户端发起连接关闭的四次挥手过程。

### 5. 强制关闭 TCP 连接

在上面的代码中，通过`Conn.Close()`方法可以进行四次挥手来完成 TCP 连接的关闭。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634873683299-69a3943935f8.jpg)

发起关闭连接乙方会经历`FIN_WAIT_1 --> FIN_WAIT_2 --> TIME_WAIT --> CLOSE`的状态变化。这些变化是需要得到被关闭方的反馈才会更新的（最后一个状态变化是在 2MSL 时间内服务器端无数据响应则会自动发生变更）。

这是一个中正常情况的关闭 TCP 连接的方式，TCP 还支持另外一种*强制*的关闭模式：发送 RST 数据包。这种方式使发起关闭方不关心对方是否同意就直接结束掉连接。

在 Go 中可以通过`net.TCPCon.SetLinger()`方法来实现 RST 强制关闭，该方法的声明如下：

```go
// SetLinger sets the behavior of Close on a connection which still
// has data waiting to be sent or to be acknowledged.
//
// If sec < 0 (the default), the operating system finishes sending the
// data in the background.
//
// If sec == 0, the operating system discards any unset or
// unacknowledged data.
//
// If sec > 0, the data is sent in the background as with sec < 0. On
// some operating systems after sec seconds have elapsed any remaining
// unsent data may be discarded.
func (c *TCPConn) SetLinger(sec int) error {}
```

将前面 TCP 客户端中 Part 3 部分的代码，在关闭连接前，增加`SetLinger()`方法的调用，如下：

```go
// Part 3: create a goroutine that closes TCP session after 10 seconds
go func() {
	<- time.After(time.Duration(10) * time.Second)

	// SetLinger(0) to force close the connection
	if err = c.(*net.TCPConn).SetLinger(0); err != nil {
		log.Printf("Error when setting linger: %s", err)
	}

	defer c.Close()
}()
```

然后开启抓包，并重新运行客户端、服务器端。抓取到的日志与前面的抓包结果基本相同，只有最后一部分出现了差异：不再是由`[F]`和`[.]`组成的四次挥手交互，而是直接的`[R]`报文关闭了连接。

```log
tcpdump: listening on lo0, link-type NULL (BSD loopback), capture size 262144 bytes
11:47:39.199754 IP6 (flowlabel 0xf0500, hlim 64, next-header TCP (6) payload length: 44) ::1.57839 > ::1.8000: Flags [S], cksum 0x0034 (incorrect -> 0x1710), seq 2523541752, win 65535, options [mss 16324,nop,wscale 6,nop,nop,TS val 3625076021 ecr 0,sackOK,eol], length 0
11:47:39.199820 IP6 (flowlabel 0x40000, hlim 64, next-header TCP (6) payload length: 44) ::1.8000 > ::1.57839: Flags [S.], cksum 0x0034 (incorrect -> 0x2ff9), seq 3381839912, ack 2523541753, win 65535, options [mss 16324,nop,wscale 6,nop,nop,TS val 3350171034 ecr 3625076021,sackOK,eol], length 0
11:47:39.199833 IP6 (flowlabel 0xf0500, hlim 64, next-header TCP (6) payload length: 32) ::1.57839 > ::1.8000: Flags [.], cksum 0x0028 (incorrect -> 0x90f6), seq 2523541753, ack 3381839913, win 6371, options [nop,nop,TS val 3625076021 ecr 3350171034], length 0
11:47:39.199845 IP6 (flowlabel 0x40000, hlim 64, next-header TCP (6) payload length: 32) ::1.8000 > ::1.57839: Flags [.], cksum 0x0028 (incorrect -> 0x90f6), seq 3381839913, ack 2523541753, win 6371, options [nop,nop,TS val 3350171034 ecr 3625076021], length 0
11:47:39.200092 IP6 (flowlabel 0xf0500, hlim 64, next-header TCP (6) payload length: 43) ::1.57839 > ::1.8000: Flags [P.], cksum 0x0033 (incorrect -> 0xa00f), seq 2523541753:2523541764, ack 3381839913, win 6371, options [nop,nop,TS val 3625076021 ecr 3350171034], length 11
11:47:39.200108 IP6 (flowlabel 0x40000, hlim 64, next-header TCP (6) payload length: 32) ::1.8000 > ::1.57839: Flags [.], cksum 0x0028 (incorrect -> 0x90eb), seq 3381839913, ack 2523541764, win 6371, options [nop,nop,TS val 3350171034 ecr 3625076021], length 0
11:47:39.200303 IP6 (flowlabel 0x40000, hlim 64, next-header TCP (6) payload length: 132) ::1.8000 > ::1.57839: Flags [P.], cksum 0x008c (incorrect -> 0x9fab), seq 3381839913:3381840013, ack 2523541764, win 6371, options [nop,nop,TS val 3350171034 ecr 3625076021], length 100
11:47:39.200321 IP6 (flowlabel 0xf0500, hlim 64, next-header TCP (6) payload length: 32) ::1.57839 > ::1.8000: Flags [.], cksum 0x0028 (incorrect -> 0x9088), seq 2523541764, ack 3381840013, win 6370, options [nop,nop,TS val 3625076021 ecr 3350171034], length 0
11:47:49.201049 IP6 (flowlabel 0xf0500, hlim 64, next-header TCP (6) payload length: 20) ::1.57839 > ::1.8000: Flags [R.], cksum 0x001c (incorrect -> 0x3c2e), seq 2523541764, ack 3381840013, win 6370, length 0
```

最后一个记录就是由客户端主动发送的`[R]`报文记录：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634874735488-28b4d93c872f.jpg)


