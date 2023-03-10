### 如何查看 Docker 镜像的构建命令

对于一个已有的镜像，可以通过`docker history`命令来查看其构建命令：

```shell
docker history <name>[:<version>] --no-trunc
```

### 如何让容器启动后一直运行

**容器不是虚拟机，而是一个进程**。

对于一个进程，其运行完成后就会自动退出。而**容器启动后，能否保持长时间就完全取决于容器启动后执行的程序**，也就是 Dockerfile 中指定的`CMD`或`ENTRYPOINT`所执行的命令。

所以，如果为一个镜像指定要执行的命令（比如`ls`）很快就执行完成，那么用它运行的容器就会在启动后很快关闭。如果想要保持容器一直运行，就要为其指定一个长久执行而不结束的的命令。

> 参考：[docker run 如何让容器启动后不会自动停止](https://jerrymei.cn/docker-container-run-not-stop-automatically/)。

### CMD 和 ENTERYPOINT 的区别

`CMD`和`ENTRYPOINT`指令都能指定容器启动后运行的命令，而且其使用格式类似。区别在于：

* `CMD`指定的命令可以在运行容器时被覆盖。

    比如对于 ubuntu 镜像制定了`CMD /bin/bash`，执行`docker run -it ubuntu`时就会直接进入 ubuntu 的 bash 中。
    
    如果执行`docker run -it ubuntu cat /etc/os-release`就会输出系统版本信息后结束运行，这里的`cat /etc/os-release`就覆盖了镜像中`CMD`指定的`/bin/bash`命令。
    
* `ENTRYPOINT`指定的命令不会再运行时被覆盖，而是将`CMD`的内容作为参数一起执行。

    不论是在镜像中定义的`CMD`指令，还是在运行时提供的其他命令（参数），在制定了`ENTRYPOINT`指令后，它们的内容都会作为`ENTRYPOINT`指定的命令的参数来执行。
    
    比如，对于 myip 这个镜像，通过`ENTRYPOINT [ "curl", "-s", "http://myip.ipip.net" ]`制定了入口命令：
    
    - 执行`docker run myip`的时候就相当于执行了`curl -s http://myip.ipip.net`；
    - 执行`docker run myip -i`的时候就相当于执行了`curl -s http://myip.ipip.net -i`。

> 参考：
> 
> 1. [CMD 容器启动命令](https://yeasy.gitbook.io/docker_practice/image/dockerfile/cmd)
> 2. [ENTRYPOINT 入口点](https://yeasy.gitbook.io/docker_practice/image/dockerfile/entrypoint)