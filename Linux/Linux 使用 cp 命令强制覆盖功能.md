我们平常在 Linux 中使用`cp`命令时,会发现将一个目录中文件复制到另一个目录具有相同文件名称时，即使添加了`-rf`参数强制覆盖复制时，系统仍然会提示让你一个个的手工输入`y`确认复制，所添加的`rf`参数是不起作用的。

### 原因 
`cp`命令被系统设置了别名，相当于`cp='cp -i'`。

查询 alias 命令：

```
[root@localhost sonarqube]# alias 
alias cp='cp -i'
alias egrep='egrep --color=auto'
alias fgrep='fgrep --color=auto'
alias grep='grep --color=auto'
alias l.='ls -d .* --color=auto'
alias ll='ls -l --color=auto'
alias ls='ls --color=auto'
alias mv='mv -i'
alias rm='rm -i'
alias which='alias | /usr/bin/which --tty-only --read-alias --show-dot --show-tilde'
```

通过上述输出，可以看出，我们平时使用`cp`命令，虽然没有添加任何参数，但系统默认会在我们使用`cp`命令时自动添加`-i`参数：

```
-i, --interactive
      prompt before overwrite
```

`-i`即交互的缩写方式，也就是在使用`cp`命令作文件覆盖操作之前，系统会要求确认提示。这个本来是系统的一个保险措施。

### 解决

如果有很多文件要复制，觉得一个一个输入`y`确认麻烦的话，可以使用如下方法解决：

> 建议大家使用方式一，因为取消别名的话很容易造成风险，万一又忘记恢复别名，以后的复制都不会有提示信息。  

1. 方式一：使用原生的`cp`命令

    ```shell
    /bin/cp -rf xxxx
    ```

2. 方式二：取消`cp`命令别名

    ```shell
    unalias cp
    ```

    去掉`cp`命令的别名，这时再用`cp -rf`复制文件时，就不会要求确认了。
    
    复制完成后恢复别名：

    ```shell
    alias cp='cp -i'
    ```

### 转摘

[Linux 使用 cp 命令强制覆盖功能](http://blog.csdn.net/xinluke/article/details/52229431)


