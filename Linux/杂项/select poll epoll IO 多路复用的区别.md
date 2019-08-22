一般来说以下场合需要使用 I/O 多路复用：

* 当客户处理多个描述字时（一般是交互式输入和网络套接口）
* 如果一个服务器既要处理 TCP，又要处理 UDP，一般要使用 I/O 复用
* 如果一个 TCP 服务器既要处理监听套接口，又要处理已连接套接口

`select`、`poll`、`epoll`都是 IO 多路复用的机制，可以监视多个描述符的 I/O 事件，一旦某个描述符就绪（一般是读或者写事件发生了），就能够将发生的事件通知给关心的应用程序去处理该事件。这三者之间也有一些区别，在性能上，一般来说是`epoll`最优，`poll`次之，`select`最差，但在某些时候并非这样。

### 1. select (1983)

I/O 多路复用这个概念被提出来以后，select 是第一个实现，一个 select 的调用过程图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1566436882443.png)

对应的头文件和函数原型为：

```c
#include <sys/select.h>
#include <sys/time.h>

int select (int nfds, fd_set *readfds, fd_set *writefds, fd_set *exceptfds, struct timeval *timeout);
/* Returns: positive count of ready descriptors, 0 on timeout, –1 on error */
```

其缺点为：

* 每次调用 select，都需要把 fd 集合从用户态拷贝到内核态，这个开销在 fd 很多时会很大
* 同时每次调用 select 都需要在内核遍历传递进来的所有 fd，这个开销在 fd 很多时也很大
* select 支持的文件描述符数量只有 1024，非常小 
> `fd`表示文件描述符。

如果系统支持的文件描述符数量不够，在 Linux 上一般就会表现为：

```
Too many open files (24)
```

此时就需要通过类似`ulimit -n 2048`的命令的方式来临时提升。

### 2. poll (1997)

poll 和 select 原理一样，不过相比较 select 而言，poll 可以支持大于 1024 个文件描述符。所以 poll 也会存在 select 一样的需要在用户态和内核态之间进行数据拷贝的问题。

对应的头文件和函数原型为：

```c
#include <sys/poll.h>

int poll (struct pollfd *fdarray, unsigned long nfds, int timeout);

/* Returns: count of ready descriptors, 0 on timeout, –1 on error */
```

### 3. epoll (2002)

epoll 和 select/poll 的实现方式不同，通过事件驱动方式提升了性能。

对应的头文件和函数原型为：

```c
#include <sys/epoll.h>

int epoll_create(int size);
int epoll_ctl(int epfd, int op, int fd, struct epoll_event *event);
int epoll_wait(int epfd, struct epoll_event * events, int maxevents, int timeout);
```

epoll 的最大特点是：

* epoll 是线程安全的，而 select 和 poll 不是。
* epoll 内部使用了 mmap 共享了用户和内核的部分空间，避免了数据的来回拷贝。
* epoll 基于事件驱动，`epoll_ctl`注册事件并注册`callback`回调函数，`epoll_wait`只返回发生的事件，避免了像 select 和 poll 对事件的整个轮寻操作。 

