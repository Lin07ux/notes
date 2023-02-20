> 转摘：[深入理解 Linux 的 epoll 机制](https://mp.weixin.qq.com/s/LGMNEsWuXjDM7V9HlnxSuQ)

![](http://cnd.qiniu.lin07ux.cn/markdown/1658393747897-f187daf1245b.jpg)

在 Linux 系统中有一个核心武器：epoll 池。在高并发、高吞吐的 IO 系统中常常会见到 epoll 的身影。

## 一、IO 多路复用

在 Go 里最核心的是 Goroutine，也就是所谓的协程。协程最妙的一个实现就是异步的代码长的跟同步代码一样。

比如，在 Go 中，网络 IO 的`read`和`write`看似都是同步代码，其实底下都是异步调用。一般流程是：

```text
write (/* IO 参数*/)
    请求入队
    等待完成

  后台 loop 程序
    发送网络请求
    唤醒业务方
```

Go 配合协程在网络 IO 上实现了**异步流程的代码同步化**，核心就是用 epoll 池来管理网络 fd。

实现形式上，后台的程序只需要 1 个就可以负责管理多个 fd 句柄，其负责应对所有的业务方的 IO 请求。这种一对多的 IO 模式就叫做 IO 多路复用。其中：

* **多路**是指多个业务方（句柄）并发下来的 IO。
* **复用**是指这一个后台处理程序。

虽然也能够使用多个线程来实现 IO 的并发，但是线程数过多时会造成系统性能的下降。

### 1.1 最朴实的实现

不同任何系统调用的情况下，也是可以实现 IO 多路复用的。最简单就是写个`for`循环，每次都尝试 IO 一下，读/写请求到了就处理，不到就 sleep 一下，这样就能实现一个 IO 多路复用。

伪代码如下：

```text
while True:
    for each 句柄数组 {
        read/write(fd, /* 参数 */)
    }
    sleep(1s)
```

但是这里也有个问题：这段代码可能会被卡在`read/write`方法上，使得整个系统无法运行。这是因为，默认情况下，创建出的句柄是阻塞类型的，读数据的时候，如果数据还没有准备好，是会需要等待的；当写数据的时候，如果写目标还没有准备好默认也会卡住等待。

举个例子，现在有 11/12/13 这 3 个句柄，其中 11 读写都没有准备好，12/13 已经准备好了。此时只要`read/write(11, /* 参数 */)`整个系统就会被卡住。而遍历的句柄数组的时候，因为 11 句柄被卡住，12 和 13 就无法运行，不符合预期，因为 IO 多路复用的 loop 线程是公共服务，不能因为一个 fd 就直接瘫痪。

如何解决这个问题呢？**只需要把 fd 都设置成非阻塞模式**。这样在`read/write`的时候，如果数据没有准备好，就会返回`EAGIN`的错误，而不会卡住程序。比如，上面句柄 11 还未就绪，调用`read/write(11, /* 参数 */)`的时候就会得到一个`EAGIN`错误，这种错误需要特殊处理，然后 loop 线程就可以继续执行 12/13 的读写操作了。

以上就是最朴实的 IO 多路复用的实现了。不过生产环境并不会使用这种方式，因为这种方式还不够高级：`for`循环每次要定期的 sleep 1s，这会导致吞吐能力极差，因为很可能在刚好要 sleep 的时候，所以的 fd 都准备好了 IO 数据，而这个时候却硬生生的等待 1s。

而如果 for 循环中不进行 sleep，虽然能够及时响应，但是 CPU 估计要跑飞了：在没有 fd 需要处理的时候，这个循环依旧会不断的执行，CPU 很快就能达到 100%，也是无法接受的。

既不能 sleep，又不能不 sleep，这种情况用户态很难有所作为，只能求助内核提供机制来协助处理，因为**内核才能及时的管理这些事件的通知和调度**。

再梳理下 IO 多路复用的需求和原理：IO 多路复用就是用 1 个线程处理多个 fd 的模式，但需要这个线程处理的尽可能快，而且能避免一些无效工作，**要把所有时间都用在处理句柄的 IO 上，不能有任何空转和 sleep 时间浪费**。而这种需求只能由内核提供机制来满足。

### 1.2 内核实现

内核为这种情况的处理提供了三种方案：`select`、`poll`和`epoll`。这 3 种方案是内核在不断的改进过程中产生的，其效率是越来越高的。

这 3 中方案都能管理 fd 的可读可写事件，在所有 fd 不可读不可写的时候，可以阻塞线程，切走 CPU；在任何一个 fd 都情况的时候，都能使线程被唤醒。

这三种方案中，`epoll`的效率最高，因为它做的无用功是最少的，`select`和`poll`或多或少都要有多余的拷贝，需要遍历整个 fd 组才能找到准备好了的 fd，所以效率自然就低了。

比如，以`select`和`epoll`为例，池子中管理了 1024 个句柄，loop 线程被唤醒的时候：

* `select`方案中并不知道这 1024 个 fd 中哪个是 IO 准备好了的，这时就只能遍历这 1024 个 fd，一个个的测试哪个是 IO 就绪的。而如果只有 1 个是就绪的，那么就相当于做了 1 千多倍的无用功；
* `epoll`则不同，线程从`epoll_wait`醒来的时候就能精确的拿到已就绪的 fd 数组，不需要任何测试就能对这些 fd 进行处理了。

### 1.3 epoll 使用流程

epoll 的使用非常简单，只有下面 3 个系统调用：

* `int epoll_create(int size)` 负责创建一个池子，一个监控和管理句柄 fd 的池子；
* `int epoll_ctl(int epfd, int op, int fd, struct epoll_event *event)` 负责管理这个池子里的 fd 的增、删、改；
* `epoll_wait` 负责等待池子中的 fd 准备好，能够让线程让出 CPU，并在有准备好的 fd 的时候从这里唤醒。

下面从使用的角度来一步步的分析：

* 首先，epoll 的第一步是创建一个池子：

    ```c
    epollfd = epoll_create(1024);
    if (epollfd == -1  {
        perror("epoll_create");
        exit(EXIT_FAILURE);
    }
    ```

    这个池子可以看为一个黑盒，是用来装 fd 的。拿到的这个 epollfd 就能唯一代表这个 epoll 池。
    
    注意：**用户可以创建多个 epoll 池**。

* 然后使用`epoll_ctl`往这个 epoll 池中放 fd：

    ```c
    if (epoll__ctl(epollfd, EPOLL_CTL_ADD, 11, &ev) == -1) {
        perror("epoll_ctl: listen_sock");
        exit(EXIT_FAILURE);
    }
    ```
    
    这里把句柄 11 放到了这个池子中，op(`EPOLL_CTL_ADD`)表明是增加、修改、三处，event 结构体可以指定监听事件的类型，可选择可读、可写事件。

* 之后就可以使用`epoll_wait`来挂起当前线程让出 CPU。

## 二、epoll 池原理

epoll 高效的原理其实非常朴实：epoll 的实现几乎没有任何无效功。下面通过几个问题来说明 epoll 的高效原理。

### 2.1 添加/修改/删除池子中的 fd 是如何保证快速的？

这里涉及到 epoll 池的数据结构了。

最常见的思路是用 list，但是虽然 list 能实现功能，但是性能很拉胯，时间复杂度比较高(`O(n)`)，每次都要遍历链表才能找到合适的位置。而且随着池子的增大，性能就会更慢。

所以 Linux 内核中对于 epoll 池的内部实现就是用**红黑树**的结构体来管理这些注册的句柄 fd。红黑树是一种平衡二叉树，时间复杂度为`O(log n)`，就算这个池子不断的增删改，也能保持非常稳定的查找性能。

### 2.2 如何保证有准备就绪的 fd 就能立马感知？

快速感知的密码就是**回调的设置**。在 epoll_ctl 的内部实现中，除了把句柄结构用红黑树管理，另一个核心步骤就是设置 poll 回调。

在 Linux 中，一切皆是文件。在实现一个文件系统的时候，就要实现这个文件调用，这个结构体用`struct file_operations`来表示，这个结构体有非常多的函数，精简之后如下：

```c
struct file_operations {
    ssize_t (*read) (struct file *, char __user *, size_t, loff_t *);
    ssize_t (*write) (struct file *, const char __user *, size_t, loff_t *);
    __poll_t (*poll) (struct file *, struct poll_table_struct *);
    int (*open) (struct inode *, struct file *);
    int (*fsync) (struct file *, loff_t, loff_t, int datasync);
    // ....
}
```

可以看到，其中有`read`、`write`、`open`、`poll`等函数，这些都是对文件的定制处理操作，*对文件的操作其实都是在这个框架内实现逻辑而已*，比如 ext2 如果对`read/write`做定制话，就会是`ext2_read`、`ext2_write`，ext4 就会是`ext4_read`、`extr4_write`。在`open`具体文件的时候，就会赋值对应文件系统的`file_operations`给到 file 结构体。

那就很容易知道，`read`是文件系统定制 fd 读的行为调用，`write`是文件系统定制 fd 写的行为调用。而*`poll`方法就是定制监听事件的机制实现：通过`poll`机制让上层能够直接告诉底层，这个 fd 一单读写就绪了，请底层硬件（比如网卡）回调的时候自动把这个 fd 相关的结构体放到指定的队列中，并且唤醒操作系统。*

举个例子：网卡收发包其实是使用异步流程的，操作系统把数据丢到一个指定的地方，网卡则不断的从这个指定的地点获取数据进行处理。请求响应通过中断回调来处理，中断一般拆分为两部分：硬中断和软中断。*poll 函数就是把这个软中断回来的路上再加点料：只要读写事件触发的时候，就会立马通知到上层，采用这种事件通知的形式就能把浪费的时间窗口就完全消失了。*

这里的重点在于：**这个 poll 事件回调机制是 epoll 池高效的最核心原理**。而且，epoll 池管理的句柄只能是支持了`file_operations->poll`接口的文件 fd。换句话说，如果一个文件所在的文件系统没有实现 poll 接口，那么就用不了 epoll 机制。

在`epoll_ctl`的实现中，有一步是调用`vfs_poll`方法来判断 fd 是否实现了 poll 接口。如果 fd 所在的文件系统实现了 poll 接口，那么就会直接调用，否则会报告相应的错误码：

```c
static inline __poll_t vfs_poll(struct file *file, struct poll_table_struct *pt)
{
    if (unlikely(!file->f_op->poll))
        return DEFAULT_POIIMASK;
    return file->f_op->poll(file, pt);
}
```

### 2.3 poll 中实现了什么？

概括来说，在文件操作的`poll`接口中，主要是挂了个钩子，设置了唤醒的回调路径。

epoll 跟底层对接的回调函数是`ep_poll_callback`，这个函数很简单，做了两件事：

1. 把事件就绪的 fd 对应的结构体放到一个特定的队列（就绪队列，ready list）；
2. 唤醒 epoll。

当 fd 满足可读可写的时候就会经过层层回调，最终调用到这个回调函数，把对应 fd 的结构体放到就绪队列中，从而把 epoll 从`epoll_wait`中唤醒。

对应 fd 的结构体叫做`epitem`，每个注册到 epoll 的 fd 都会对应一个。

就绪队列很简单，就是一个最简单的双向指针链表。这是因为就绪队列中都是就绪的 epitem，都需要进行处理，没有查找的需求。

### 2.4 哪些 fd 可以用 epoll 来管理？

前面提到，只有实现了`file_operations->poll`接口的文件系统的 fd 才能被加入到 epoll 池中，而并不是所有的文件系统都实现了这个接口，所有自然并不是所有的 fd 都可以放进 epol 池。那么有哪些文件系统实现了`file_operations->poll`接口呢？

首先，类似**`ext2`、`ext4`、`xfs`这样常规的文件系统是没有实现 poll 接口**的。换句话说，Linux 中最常见的、存储真实文件的文件系统是用不了 epoll 机制的。

Linux **中支持 poll 接口的文件系统**有很多，下面是三个常被使用的文件系统，它们也常被放到 epoll 中：

* **socket fd 网络套接字**，它实现了一套`socket_file_operations`的逻辑（net/socket.c）

    ```c
    static const struct file_operations socket_file_ops = {
        .read_iter =    sock_read_iter,
        .write_iter =   sock_write_iter,
        .poll =     sock_poll,
        // ...
    };
    ```

* **eventfd** 专门用来做事件通知用的，使用系统调用`eventfd`创建。这种文件 fd 无法传输数据，只能传输事件，常常用于生成消费者模式的事件实现；

* **timerfd** 一种定时器 fd，使用`timerfd_create`创建，到指定的时间点触发可读写事件。

其实，在 Linux 模块划分中，eventfd、timerfd、epoll 池都是文件系统的一种模块实现：

![](http://cnd.qiniu.lin07ux.cn/markdown/1658403669141-587ecfa679fe.jpg)

### 2.5 总结

epoll 之所以做到了高效，最关键的有三点：

1. 内部管理 fd 使用了高效的红黑树结构，做了增删改的性能优化和平衡；
2. epoll 池添加 fd 的时候，通过`file_operations->poll`接口把这个 fd 就绪之后的回调路径安排好，通过事件通知的形式做到最高效的运行；
3. epoll 池通过另一个核心的数据结构就绪队列来保存准备就绪 fd 对应的结构，这样只需要遍历这个就绪链表就能给用户返回所有已经就绪的 fd 数组，而没有多余数据。

![](http://cnd.qiniu.lin07ux.cn/markdown/1658402872087-f76902fae36b.jpg)

## 三、总结

### 3.1 总结

通过以上的分析，可以得到如下的结论：

1. IO 多路复用的原始实现很简单，就是一个一对多的服务模式，一个 loop 对应处理多个 fd；
2. IO 多路复用想要做到真正的高效，**必须要内核机制提供**，因为 IO 的处理和完成是在内核，内核不提供协助，用户态的程序根本无法精确的抓到处理时机；
3. **fd 需要设置成非阻塞的**，否则将无法使用到异步的优势；
4. epoll 池通过高效的内部数据结构，并且结合操作系统提供的 **poll 事件注册机制**，实现了高效的 fd 事件管理，为高并发的 IO 处理提供了前提条件；
5. epoll 全名为 eventpoll，在 Linux 内核下是以一个文件系统模块的形式实现，所以说**epoll 其实本身就是文件系统**也是对的；
6. socketfd、eventfd、timerfd 这三种文件 fd 实现了 poll 接口。所以它们都可以使用`epoll_ctl`注册到池子中，常见的就是网络 socket fd 的多路复用；
7. ext2、ext4、xfs 这种真正意义上的文件系统没有实现 poll 接口，所以不能用 epoll 池来管理其句柄，是不能直接使用 epoll 机制的。但是可以通过 libaio 库来间接的让这些文件 fd 可以使用 epoll 通知事件。

### 3.2 思考

下面有一些简单有趣的知识点：

*问题：单核 CPU 能实现并行吗？*

不能。

*问题：单线程能实现高并发吗？*

可以。

*问题：并发和并行的区别是什么？*

前者看的是时间段内的执行情况，后者看的每一时刻的执行情况。

*问题：单线程如何做到高并发？*

IO 多路复用，比如 epoll 池。

*问题：单线程实现并发的实例有哪些？*

Redis、Nginx 都是非常好的例子，Go 的 runtime 实现也尽显高并发的设计思想。

