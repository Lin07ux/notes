> 转摘：[zsh使用scp命令时*通配符无法使用](https://blog.webfsd.com/post_zshscp.html)

Mac 中，使用 zsh 终端时，scp 命令无法使用通配符来匹配所有的相关文件，如：

```shell
scp host:~/*.conf .
# 提示错误如下：
# zsh: no matches found: host:~/*.conf
```

但是这条命令在 bash 中是可以正常工作的，出现这个问题是 zsh 的解析错误：zsh 试图将`*`通配符展开，在本地查找文件，由于本地没有对应的文件，所以就出现了 no matches 的错误。

有两种方式可以解决这个问题：

1. 不要让 zsh 将通配符展开：将路径用引号(单引号和双引号都可以)包裹起来，或者使用转义。比如，可以将上面的命令改写成下面的方式：

    ```shell
    scp host:~/\*.conf .
    scp host:"~/*.conf" .
    scp "host:~/*.conf" .
    ```

2. 设置`nonomatch`选项，让 zsh 在匹配失败时不报错，并使用原本的内容：

    ```shell
    setopt nonomatch
    ```
    
    也可以将这个设置写入到`.zshrc`配置文件，让以后使用时自动生效。

