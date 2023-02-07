Linux 中提供了 select、poll、epoll 三类系统调用，支持批量等待数据可读写信号，从而实现 IO 多路复用。

这三种方式使用上有如下的一些通用逻辑：

1. 需要定义一组要监听的套接字和要监听的事件；
2. 定义等待超时时间 timeout，0 表示不等待；
3. 返回值是有事件发生的 socket 数量；
4. 错误码被重置（同时返回值是 -1）；
5. 函数返回后，调用时传入的参数均会被修改。

它们的不同之处则在于：

* 对要监听的套接字和事件的定义方式不一样；
* 对参数的改动方式也不一样。

### 1. select

select 系统函数的声明如下：

```c
int select(
  int nfds,
  fd_set *readfds,
  fd_set *rwitefds,
  fd_set *errorfds,
  struct timeval *timeout);
```

其中：

* `nfds`指定了监听的套接字数；
* `readfs/writefds/errorfds`分别指定了要监听的读/写/异常的套接字集合；
* `timeout`指定了最长等待时间。

套接字集合所使用的`fd_set`类型的定义如下：

```c
typedef struct {
  uint32_t fd32[(FD_SETSIZE + 31) / 32];
} fd_set
```

`fd_set`在逻辑上是一个长度为 1024 的 bit 数组，在实现中可以使用长度为 32 的 int32 数组或者长度为 16 的 int64 数组表示。由于 Big Endian 和 Little Endian 的影响，每个操作系统在不同的硬件架构下采用的表示方式也不完全相同。

由于`FD_SETSIZE`的值通常为 1024，所以每个`fd_set`可以支持监听 1024 个已连接套接字。

**Select 特点**：

* select 最多支持 1024 个套接字监听；
* * select 只会监听信号，不会对套接字上的数据进行 Accept、Read 或 Write；
* 每次调用都必须把三个`fd_set`从用户态拷贝到内核态进行处理，然后内核态将更新结果同步到用户态对应的`fd_set`中；
* 调用完成后，用户态需要遍历`fd_set`才能知道哪些套接字发生了改变。

### 2. poll

poll 系统函数的声明如下：

```c
int poll(
  struct pollfd *fds,
  int nfds,
  int timeout);

struct pollfd {
  int fd;
  short int events;
  short int revents;
};
```

其中：

* `fds`表示一个 pollfd 数组，用来指定要监听的套接字列表；
* `nfds`制定了监听的套接字数量；
* `timeout`指定了最长等待时间。

poll 系统函数参数中，没有按照监听的事件类型对要监听的套接字列表进行拆分，而是针对每个要监听的套接字分别设置监听事件。而且，每个套接字收到的事件也是通过一个 pollfd 中的新字段`revents`来判断，而不是修改传入的字段。

**poll 特点**：

* 能监听的套接字数量不受限于 1024；
* 支持监听的事件类型增多，不受限于`read/write/error`；
* 支持消息的优先级；
* 每次 polling 的时候，仍然需要把监听的所有套接字和事件信息从用户态传入到内核态，内核态处理完成后再将结果同步到用户态；
* 调用完成后，用户态需要遍历 pollfd 数组才能知道哪些套接字发生了变化。

### 3. epoll

epoll 针对 select 和 poll 模式中每次调用都需要传入所有要监听的套接字的问题进行了优化，每次只需要传入一个要监听的套接字。实现上包含三个系统调用：

```c
// 创建一个 epoll fd
int epoll_create(int flags);

// 增删改监听的套接字
int epoll_ctl(
  int epfd, // epoll fd
  int op,   // 操作：add/del/update
  int fd,   // 监听的套接字
  struct epoll_event *event); // 监听的事件

// 等待套接字发生指定事件
int epoll_wait(
  int epfd,      // epoll fd
  struct epoll_event *events,
  int maxevents, // events 的长度
  int timeout);  // 超时时间 -1 表示一直 block
```

epoll 模式下，内核承担了维护套接字状态的任务，使用红黑树去实现`O(logN)`复杂度的查找、插入、删除和更新操作。

在用户态层面上，epoll 拆分出三个系统调用，大大减少了`epoll_wait`时用户态和内核态之间的数据拷贝。

**epoll 特点**：

* 不再需要在用户态和内核态之间批量的拷贝套接字数据。

