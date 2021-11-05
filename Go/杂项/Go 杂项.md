## 一、常规事项

### 1.1 package 不支持循环导入

Go 中包是不支持循环导入的，这会对 Go 的构建性能和依赖关系造成非常不利的影响。而且在 Go2 中官方也明确拒绝了循环导入的提案。

Go 语言之父 Rob Pike 对该问题有专门的回答：

* 不支持循环引用，目的是迫使 Go 程序员更多地考虑程序的依赖关系。

    - 保持依赖关系图的简洁，是一个 DAG(Directed acyclic graph，有向无环图)；
    - 快速的程序构建。

* 如果支持循环引用，容易造成懒惰、不良的依赖性管理和缓慢的构建。

    - 混乱的依赖关系；
    - 缓慢的程序构建。

## Go 调试工具

### gdb/cgdb

```shell
go build -gcflags "-N -l" -o main
gdb main
```

常用 [gdb](https://linuxtools-rst.readthedocs.io/zh_CN/latest/tool/gdb.html) 的调试命令：

* run
* continue
* break
* backtrace / frame
* info break / locals
* list
* print / ptype
* disass

[cgdb](https://cgdb.github.io/) 是 gdb 的增强调试命令，其视窗分为两个部分，上面显示源代码，下面是具体的命令行调试界面（跟 gdb 一样）。

### delve

[delve](https://github.com/go-delve/delve) 是使用 go 语言开发的，功能是十分强大，打印结果可以显示 gdb 支持不了的东西。

[dlv](https://github.com/aarzilli/gdlv) 则带图形化界面。


