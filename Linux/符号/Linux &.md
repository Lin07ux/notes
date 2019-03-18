> 转摘：[Linux 中的 &](https://linux.cn/article-10587-1.html)

### 1. 后台运行

使用`&`号可以将命令放到后台运行：

```shell
cp -R original/dir/ backup/dir/ &
```

将`original/dir/`的内容递归地复制到`backup/dir/`中时，如果原目录里面的文件太大，在执行过程中终端就会一直被卡住。在命令的末尾加上一个`&`号，将这个任务放到后台去执行，从而可以立即继续在同一个终端上工作了，甚至关闭终端也不影响这个任务的正常执行。

> 需要注意的是，如果要求这个任务输出内容到标准输出中（例如`echo`或`ls`），即使使用了`&`，也会等待这些输出任务在前台运行完毕。

当使用`&`将一个进程放置到后台运行的时候，Bash 会提示这个进程的进程 ID。在 Linux 系统中运行的每一个进程都有一个唯一的进程 ID，可以使用进程 ID 来暂停、恢复或者终止对应的进程，因此进程 ID 是非常重要的。



```shell
mkdir test_dir 2>/dev/null || touch images.txt && find . -iname "*jpg" > backup/dir/images.txt &
```

