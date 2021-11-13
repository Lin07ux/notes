> 转摘：[图解 Linux | 管道通信的原理？](https://mp.weixin.qq.com/s/Fy8XH-I8A1L1JWHQKPkOag?forceh5=1)

## 一、基础

### 1.1 背景

Linux 中，出于安全考虑，不同进程之间的内存空间是相互隔离的。如果不同进程间能够相互访问和修改对方的内存，那么当前进程的内存就有可能被其他进程非法修改，从而导致安全隐患。

但在某些场景下，不同进程间需要相互通信，比如：进程 A 负责处理用户的请求，而进程 B 负责保存处理后的数据，那么当进程 A 处理完请求之后，就需要把处理后的数据提交给进程 B 进行存储。此时，进程 A 和进程 B 就需要进行通信。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636689679121-67e28d910552.jpg)

由于进程间的内存存在隔离，而内核空间却是共用，所以，进程间通信必须由内核提供服务：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636689745907-1d2b4bf7ec6a.jpg)

内核提供多种进程间通信的方式，如：共享内存、信号、消息队列、管道(pipe)等。

### 1.2 使用

**管道一般用于父子进程之间相互通信**，一般用法如下：

1. 父进程使用 pipe 系统调用创建一个管道；
2. 然后父进程使用 fork 系统调用创建一个子进程；
3. 由于子进程会继承父进程打开的文件句柄，所以父子进程可以通过新创建的管道进行通信。

原理如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636694455684-83992d28f905.jpg)

由于管道分为读端和写端，所以需要两个文件描述符来操作管理：`fd[0]`为读端、`fd[1]`为写端。

下面 diam 介绍了怎么使用 pipe 系统调用来创建一个管道：

```c
#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>
#include <stdlib.h>
#include <string.h>

int main()
{
    int ret = -1;
    int fd[2];  // 用于管理管道的文件描述符
    pid_t pid;
    char buf[512] = {0};
    char *msg = "hello world";

    // 创建一个管理
    ret = pipe(fd);
    if (-1 == ret) {
        printf("failed to create pipe\n");
        return -1;
    }
  
    pid = fork();     // 创建子进程

    if (0 == pid) {   // 子进程
        close(fd[0]); // 关闭管道的读端
        ret = write(fd[1], msg, strlen(msg)); // 向管道写端写入数据
        exit(0);
    } else {          // 父进程
        close(fd[1]); // 关闭管道的写端
        ret = read(fd[0], buf, sizeof(buf)); // 从管道的读端读取数据
        printf("parent read %d bytes data: %s\n", ret, buf);
    }

    return 0;
}
```

## 二、实现

### 2.1 环形缓冲区 Ring Buffer

在内核中，管道使用了缓存缓冲区来存储数据。

环形缓冲区的原理是：把一个缓冲区当成是首尾相连的环，其中通过读指针和写指针来记录读操作和写操作位置。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636694780213-a7b4efa5a8f2.jpg)

在 Linux 内核中，使用了 16 个内存页作为环形缓冲区，每个内存页为 4KB，所以这个环形缓冲区的大小为 64KB。

当向管道写数据时，从写指针指向的位置开始写入，并且将写指针向前移动。而从管道读取数据时，从读指针开始读取，并且将读指针向前移动。

对没有数据刻度的管道进行读操作，将会阻塞当前进程；对没有空闲空间的管道进行写操作，也会阻塞当前进程。

> 注意：可以将管道文件描述符设置为非阻塞，这样对管道进行读写操作时，就不会阻塞当前进程。

### 2.2 管道对象 pipe_inode_info

在 Linux 内核中，管道使用`pipe_inode_info`对象来进行管理：

```c
struct pipe_inode_info {
    wait_queue_head_t wait; // 等待队列，用于存储正在等待管道可读或者可写的进程
    unsigned int nrbufs,    // 魏都区数据已经占用了缓存缓冲区的内存页数
    unsigned int curbuf;    // 当前正在读取环形缓冲区的哪个内存页中的数据
    ...
    unsigned int readers;   // 表示正在读取管道的进程数
    unsigned int writers;   // 表示正在写入管道的进程数
    unsigned int waiting_writers; // 表示等待管道可写的进程数
    ...
    struct inode *inode;    // 与管道关联的 inode 对象
    struct pipe_buffer bufs[16]; // 环形缓冲区，由 16 个 pipe_buffer 对象组成，每个表示一个内存页
};
```

环形缓冲区中存储数据的地方就在由`bufs`属性指定的 16 个内存页中，每个内存页对应一个`pipe_buffer`对象：

```c
struct pipe_buffer {
    struct page *page;   // 指向 pipe_buffer 对象占用的内存页
    unsigned int offset; // 指向正在读取当前内存页的偏移量
    unsigned int len;    // 当前内存页拥有未读取数据的长度
    ...
};
```

下图展示了`pipe_inode_info`对象与`pipe_buffer`对象的关系：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636695435777-9df2925527bc.jpg)

管道的环形缓冲区实现方式与经典的环形缓冲区有点区别：

* 经典环形缓冲区一般先申请一块地址连续的内存块，然后通过读指针与写指针对读操作与写操作进行定位；
* **管道的环形缓冲区**为了减少对内存的占用，不会在创建的时候就申请 64KB 的内存快，而是**在写入数据时按需申请**。而且，当内存页的数据被**读取完后，内核会将此内存页回收**。

### 2.3 读操作

从经典的环形缓冲区中读取数据的流程如下：

1. 通过读指针来定位到读取数据的起始地址；
2. 判断环形缓冲区中是否有数据可读；
3. 有数据的话，从环形缓冲区中读取数据到用户空间的缓冲区中。

如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636695667198-e265cb15f978.jpg)

而管道的环形缓冲区稍有不同，管道的环形缓冲区其读指针是由`pipe_inode_info.curbuf`字段与`pipe_buffer.offset`字段组合而成：

1. `pipe_inode_info.curbuf`字段表示读操作要从`pipe_inode_info.bufs`数组中的哪个`pipe_buffer`中读取数据；
2. `pipe_buffer.offset`字段表示读操作要从内存页的哪个位置开始读取数据。

读取过程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636695832687-3267b4d806ef.jpg)

从缓冲区读取到 n 个字节的数据后，就会相应的移动读指针 n 个字节的位置（也就是增加`pipe_buffer.offset`字段 n），并减少 n 个字节的可读取数据长度（也就是减少`pipe_buffer.len`）。

当`pipe_buffer.len`字段变为 0 时，表示当前`pipe_buffer`没有可读数据，将会对`pipe_inode_info.curbuf`移动一个位置，并将`pipe_inode_info.nrbufs`字段进行减一操作。

Linux 管道读操作由`pipe_read`函数实现，下面列出了关键代码：

```c
static ssize_t
pipe_read(struct kiocb *iocb, const struct iovec *_iov, unsigned long nr_segs,
          loff_t pos)
{
    ...
    struct pipe_inode_info *pipe;

    // 1. 获取管道对象
    pipe = inode->i_pipe;

    for (;;) {
        // 2. 获取管道未读数据占有多少个内存页
        int bufs = pipe->nrbufs;

        if (bufs) {
            // 3. 获取读操作应该从环形缓冲区的哪个内存页处读取数据
            int curbuf = pipe->curbuf;  
            struct pipe_buffer *buf = pipe->bufs + curbuf;
            ...

            /* 4. 通过 pipe_buffer 的 offset 字段获取真正的读指针,
             *    并且从管道中读取数据到用户缓冲区.
             */
            error = pipe_iov_copy_to_user(iov, addr + buf->offset, chars, atomic);
            ...

            ret += chars;
            buf->offset += chars; // 增加 pipe_buffer 对象的 offset 字段的值
            buf->len -= chars;    // 减少 pipe_buffer 对象的 len 字段的值

            /* 5. 如果当前内存页的数据已经被读取完毕 */
            if (!buf->len) {
                ...
                curbuf = (curbuf + 1) & (PIPE_BUFFERS - 1);
                pipe->curbuf = curbuf; // 移动 pipe_inode_info 对象的 curbuf 指针
                pipe->nrbufs = --bufs; // 减少 pipe_inode_info 对象的 nrbufs 字段
                do_wakeup = 1;
            }

            total_len -= chars;

            // 6. 如果读取到用户期望的数据长度, 退出循环
            if (!total_len)
                break;
        }
        ...
    }

    ...
    return ret;
}
```

上面代码总结来说，分为以下几个步骤：

* 通过`inode`文件对象来获取到管道的`pipe_inode_info`对象；
* 通过`pipe_inode_info.nrbufs`字段获取管道未读取数据占有多少个内存页；
* 通过`pipe_inode_info.curbuf`字段获取读操作应该从环形缓冲区的哪个内存页处读取数据；
* 通过`pipe_buffer.offset`字段获取真正的读指针，并从管道中读取数据到用户缓冲区；
* 如果当前内存页的数据已经被读取完毕，那么移动`pipe_inode_info.curbuf`指针，并减少`pipe_inode_info.nrbufs`字段的值；
* 如果读取到用户期望的数据长度，退出循环。

### 2.4 写操作

经典环形缓冲区在写入数据时：

1. 首先通过写指针进行定位，找到要写入的内存地址；
2. 判断环形缓冲区的空间是否足够，足够的话就写入数据到环形缓冲区。

如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636696457154-71aed12c6a76.jpg)

而管道环形缓冲区并没有保存写指针，而是通过读指针计算出来：`写指针 = 读指针 + 未读数据长度`。

下面是一个向管道写入 200 字节数据的过程示意图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636696532666-b9432074c814.jpg)

可以看到管道环形缓冲区的写入过程为：

1. 首先通过`pipe_inode_info.curbuf`字段和`nrbufs`字段来定位到要写入的`pipe_buffer`；
2. 通过`pipe_buffer.offset`和`pipe_buffer.offset`字段定位到要写入的内存位置。

关键代码如下：

```c
static ssize_t
pipe_write(struct kiocb *iocb, const struct iovec *_iov, unsigned long nr_segs,
           loff_t ppos)
{
    ...
    struct pipe_inode_info *pipe;
    ...
    pipe = inode->i_pipe;
    ...
    chars = total_len & (PAGE_SIZE - 1); /* size of the last buffer */

    // 1. 如果最后写入的 pipe_buffer 还有空闲的空间
    if (pipe->nrbufs && chars != 0) {
        // 获取写入数据的位置
        int lastbuf = (pipe->curbuf + pipe->nrbufs - 1) & (PIPE_BUFFERS-1);
        struct pipe_buffer *buf = pipe->bufs + lastbuf;
        const struct pipe_buf_operations *ops = buf->ops;
        int offset = buf->offset + buf->len;

        if (ops->can_merge && offset + chars <= PAGE_SIZE) {
            ...
            error = pipe_iov_copy_from_user(offset + addr, iov, chars, atomic);
            ...
            buf->len += chars;
            total_len -= chars;
            ret = chars;

            // 如果要写入的数据已经全部写入成功, 退出循环
            if (!total_len)
                goto out;
        }
    }

    // 2. 如果最后写入的 pipe_buffer 空闲空间不足, 那么申请一个新的内存页来存储数据
    for (;;) {
        int bufs;
        ...
        bufs = pipe->nrbufs;

        if (bufs < PIPE_BUFFERS) {
            int newbuf = (pipe->curbuf + bufs) & (PIPE_BUFFERS-1);
            struct pipe_buffer *buf = pipe->bufs + newbuf;
            ...

            // 申请一个新的内存页
            if (!page) {
                page = alloc_page(GFP_HIGHUSER);
                ...
            }
            ...
            error = pipe_iov_copy_from_user(src, iov, chars, atomic);
            ...
            ret += chars;

            buf->page = page;
            buf->ops = &anon_pipe_buf_ops;
            buf->offset = 0;
            buf->len = chars;

            pipe->nrbufs = ++bufs;
            pipe->tmp_page = NULL;

            // 如果要写入的数据已经全部写入成功, 退出循环
            total_len -= chars;
            if (!total_len)
                break;
        }
        ...
    }

out:
    ...
    return ret;
}
```

上面代码很长，但是逻辑比较简单：

1. 如果上次写操作写入的`pipe_buffer`还有空闲的空间，那么就将数据写入到这个`pipe_buffer`中，并增加`pipe_buffer.len`字段的值；
2. 如果上次写操作写入的`pipe_buffer`没有足够的空间，那么就申请一个内存页，并且把数据保存到新的内存页中，并增加`pipe_inode_info.nrbufs`字段的值。
3. 如果写入的数据已经全部写入成功，那么就退出写操作


