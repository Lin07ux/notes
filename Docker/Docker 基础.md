## 一、简介

Docker 使用 Go 语言开发实现，基于 Linux 内核的 CGroup、Namespace 等技术，对进程进行封装隔离，属于操作系统层面的虚拟化技术。由于隔离的进程独立于宿主和其他的隔离进程，因此也称其为容器。

![Docker 架构](http://cnd.qiniu.lin07ux.cn/markdown/1609060222464.png)

Docker 在容器的基础上，进行了进一步的封装，从文件系统、网络互联到进程隔离等等，极大的简化了容器的创建和维护。使得 Docker 技术比虚拟机技术更为轻便、快捷。下面是虚拟机与 Docker 的对比图：

![Virtual Machines](http://cnd.qiniu.lin07ux.cn/markdown/1609063522791.png)

![Docker](http://cnd.qiniu.lin07ux.cn/markdown/1609063549067.png)

Docker 具有如下的一些优势：

* 更高效的利用系统资源
* 更快的启动速度
* 一致的运行环境
* 持续交互和部署
* 更轻松的迁移
* 更轻松的维护和扩展

## 二、基本概念

Docker 包括三个基本概念，理解了这三个概念，就理解了 Docker 的整个生命周期：

* 镜像（Image）
* 容器（Container）
* 仓库（Repository）

### 2.1 镜像

Docker 镜像是一个特殊的文件系统，除了提供容器运行时所需的程序、库、资源、配置等文件外，还包含了一些为运行时准备的一些配置参数（如匿名卷、环境变量、用户等）。镜像不包含任何动态数据，其内容在构建之后也不会被改变。

### 2.2 容器

镜像是静态的定义，容器是镜像运行时的实体。容器可以被创建、启动、停止、删除、暂停等。

容器的实质是进程，运行在一个隔离的环境中，好像是在一个独立于宿主的系统下操作一样。这种特性使得容器封装的应用比直接在宿主机中运行更加安全。

按照 Docker 最佳实践的要求，容器不应该向其存储层内写入任何数据，容器存储层要保持无状态化。所有的文件写入操作，都应该使用 数据卷（Volume）、或者 绑定宿主目录，在这些位置的读写会跳过容器存储层，直接对宿主（或网络存储）发生读写，其性能和稳定性更高。

数据卷的生存周期独立于容器，容器消亡，数据卷不会消亡。因此，使用数据卷后，容器删除或者重新运行之后，数据却不会丢失。

### 2.3 仓库

仓库就是一个集中的存储、分发镜像的服务，[Docker Registry](https://yeasy.gitbook.io/docker_practice/repository/registry) 就是这样的服务。

一个 Docker Registry 中可以包含多个 仓库（Repository）；每个仓库可以包含多个 标签（Tag）；每个标签对应一个镜像。



