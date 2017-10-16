### 获取当前正在执行脚本的绝对路径
在 Shell 脚本中获取当前脚本的绝对路径，常见的一种误区，是使用`pwd`命令，该命令的作用是“print name of current/working directory”，显示当前的工作目录，这里没有任何意思说明，这个目录就是脚本存放的目录。所以，这是不对的。

另一个误人子弟的答案是`$0`，这个`$0`是 Bash 环境下的特殊变量，其真实含义是：

> Expands to the name of the shell or shell script. This is set at shell initialization.  If bash is invoked with a file of commands, `$0` is set to the name of that file. If bash is started with the -c option, then `$0` is set to the first argument after the string to be executed, if one is present. Otherwise, it is set to the file name used to invoke bash, as given by argument zero.

这个`$0`有可能是好几种值，跟调用的方式有关系：

* 使用一个文件调用 bash，那`$0`的值，是那个文件的名字(没说是绝对路径噢)
* 使用`-c`选项启动 bash 的话，真正执行的命令会从一个字符串中读取，字符串后面如果还有别的参数的话，使用从`$0`开始的特殊变量引用(跟路径无关了)
* 除此以外，`$0`会被设置成调用 bash 的那个文件的名字(没说是绝对路径) 
真正的正确答案应该是：

```shell
basepath = $(cd `dirname $0`; pwd)
```

解释如下：

* `dirname $0`，取得当前执行的脚本文件的父目录
* ``cd `dirname $0` ``，进入这个目录(切换当前工作目录)
* `pwd`，显示当前工作目录(cd 执行后的)

由此，我们获得了当前正在执行的脚本的存放路径。

> 参考：[linux shell 获取当前正在执行脚本的绝对路径](http://www.cnblogs.com/FlyFive/p/3640267.html)


### 指定用户执行特定命令

有时候可能希望不切换登录用户，而仅仅指定某个命令用特定的用户执行，甚至，使用某个无权登录的用户执行命令。这时就可以考虑`su`命令的更多其他用法了。

`su`命令一般用于切换登录用户，比如使用`su root`就可以从当前用户切换到`root`用户，使用`root`账户来登录系统。

1. 不切换用户，使用指定用户执行命令：

    ```shell
    su - username -c "command"
    ```
    
    这表示，切换到用户`username`的登录状态，并执行命令`command`，执行完成之后，切换回当前的用户。


2. 不重置登录环境指定用户执行命令：
    
    ```shell
    su -m username -c 'command'
    ```
    
    这种和上面基本相同，不同的地方在于，这种方式可以使用不能登录系统的用户来执行命令。
    

> 如果切换到指定的用户需要验证，那么就需要输入密码以便完成切换。所以一般情况下，会在当前用户是`root`的时候使用。

