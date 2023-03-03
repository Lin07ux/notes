Linux 可以通过在配置文件中使用 alias 来配置命令的别名，从而实现简化命令参数输入的目的。比如：

```shell
alias ohmyzsh="mate ~/.oh-my-zsh"
```

不过 alias 默认不支持参数，如果要使用参数则可以定义一个方法，然后在 alias 中执行这个方法，例如：

```shell
rdbLogin() {
    redis-cli -h rdb.example.com -p 63791 -n $1
}
alias rdb="rdbLogin"
```

> 注意：shell 中的参数要从`$1`开始取，因为`$0`表示的时脚本/函数名。

如果想要使用默认值参数，可以使用 shell 脚本中的`:-`语法，如：

```shell
rdbLogin() {
    redis-cli -h rdb.example.com -p 63791 -n ${1:-10}
}
alias rdb="rdbLogin"
```

这里的`${1:-10}`表示如果没有传入参数，则使用 10 作为`$1`参数的值，所以这里默认是连接 10 号库。