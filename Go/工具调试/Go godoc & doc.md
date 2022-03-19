> 转摘：[go doc与godoc](https://wiki.jikexueyuan.com/project/go-command-tutorial/0.5.html)

## 一、godoc

Go 程序的文档说明可以通过`godoc`命令直接从代码注释中进行提取，而且在发布之后也能够在[Go doc](https://godoc.org)官网上看到。

### 1. 什么是 godoc

Go 开发工具安装之后，就自动安装了`godoc`命令工具。使用 godoc 命令可以在本地建立一个 godoc 网站服务（官方的 godoc 其实也基本上是用同一个工具建立起来的）。

自建的 godoc 有两个作用：解决 godoc.org 网站难以访问的问题，而且可以在本地调试自建的文档。

可以使用下面的命令在本地启动 godoc 服务，并且开放 playground 功能：

```shell
godoc --http=127.0.0.1:8080 -play
```

然后就可以在浏览器中通过[http://127.0.0.1:6060](http://127.0.0.1:6060)查看 Go 文档了：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637139080879-cf84ac43eb36.jpg)

原理上，godoc 读取的包路径来自于`$GOROOT`，因此，如果要让本地的 godoc 认识并解析正在开发的包，就需要在`$GOROOT`目录下按照路径结构放好自己的工程代码——软连接也是支持的。

### 2. godoc 总览

在 godoc 中，文档包含如下几部分：

* Overview：总览，包含包的 import 语句和概要说明；
* Index：目录，包含包中可见性为 public 的常量、类型、方法、函数的总目录及说明；
* Examples：示例，包含文档中所有示例的快速跳转；
* Files：文件，列出了包中所有代码文件的超链接。

godoc 的文档都是从源码中的注释中提取的，所以源码注释的格式就会影响文档的展示样式：

* 注释块可以有多行，但必须是连续的`//`或`/* ... */`开头。
* 注释中的空行对应着文档中的换行。
* 注释中的 tab 缩进会将这些统一显示为代码块。

比如，对于如下的注释：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637140945668-e1be018c7a4f.jpg)

会被展示为一个代码块：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637140963134-a408f18dac41.jpg)

### 3. Overview 文档

Overview 部分主要由三种内容组成：import 语句、文字说明、代码部分。

import 语句是 godoc 自动按 URL 生成的，文字说明和代码部分都是从源码文件中提取出来的。提取的原则是：

* 在代码中所有`package`语句上方紧贴着的、以包名开头的注释。比如，对于 jsonvalue 包，就会提取所有源文件中`package jsonvalue`语句上方的`// jsonvalue XXX`或`/* jsonvalue XXX */`注释块。
* 如果找到多个符合条件的注释（比如多个源文件的`package`上都有注释），那么就会按照文件名字母的顺序进行展示。

对于比较多的 Overview 注释，建议都放在一个注释块中，统一放在`doc.go`文件中，而且该文件中只有一个`package`语句。

### 4. 代码文档

godoc 工具会搜寻代码中所有源码文件（测试文件除外），提取相关的注释展示到页面上。提取的依据如下：

* 搜寻对象是 diam 中所有的公共部分，包括常量、变量、接口、类型、函数等。
* 与 Overview 类似，在公共元素之上、以该元素开头的注释会被视为该元素的注释。

由于在源码说明中，会更多的采用代码示例来说明逻辑，所有在代码文档中一般较少会出现代码块注释。

比如，对于如下函数：

```go
![](http://cnd.qiniu.lin07ux.cn/markdown/1637140963134-a408f18dac41.jpg)
 // At completes the following operation of Set(). It defines posttion of value in Set() and return the new value set.
//
// The usage of At() is perhaps the most important. This function will recursivly search for child value, and set the new value specified by Set() or SetXxx() series functions. Please unfold and read the following examples, they are important.
func (s *Set) At(firstParam interface{}, otherParams ...interface{}) (*V, error) {
    ......
}
```
godoc 解析并格式化效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637146881046-16af461cc071.jpg)

### 5. 代码示例

除了对代码进行文档描述，还有一个更好的方法可以用来展示如何使用该元素，这就是代码示例。

代码示例的编写也有一定的规则：

**首先，一般应该新建至少一个文件，专门用来存放示例代码**，比如`example_test.go`（使用`_test`作为文件名结尾可以避免该文件被当做源码文件而被编译）。而且这个**文件的`package`名也不得与当前包名相同**，一般设置为包名+`_test`。

**其次，示例代码中的方法名应该按照一定的格式进行命名**，示例代码函数名的组成部分包含以下这些：

* `Example` 示例代码函数的固有开头
* `Set` 表示这是设置类型相关方法的示例（获取类型值的方法示例不需要`Get`），可选的
* `<Name>` 表示这是哪个类型的示例
* `_` 分隔符，用来分隔`Set`、`<Name>`和附加内容

比如，对于方法`At()`，可以为其添加如下的示例方法：

```go
func ExampleSet_At() {
    ......
}
```

如果一个元素包含多个示例，那么 godoc 会按照字母顺序对示例及其相应的说明排序。为了自定义顺序，可以为示例方法名添加附加信息后缀，比如：

```go
func ExampleSet_At_1() {
    ......
}
```

**最后，为示例添加标准输出内容**，这样便于读者了解示例代码执行的情况和结果。标准输出内容在示例函数内的最后，采用`// Output:`单独起一行开头，剩下的每一行标准输出写一行注释。

比如：

```go
func ExampleBuffer() {
	var b bytes.Buffer // A Buffer needs no initialization.
	b.Write([]byte("Hello "))
	fmt.Fprintf(&b, "world!")
	b.WriteTo(os.Stdout)
	// Output: Hello world!
}
```

### 6. 在官网发布

开发完成自己的包并添加相应的注释之后，就可以将其推送到公开仓库（如 GitHub）中，然后在浏览器中通过`https://godoc.org/<pkg_path>`路径进行访问。

比如，对于`github.com/chromedp/chromedp`包，就可以使用[https://pkg.go.dev/github.com/chromedp/chromedp](https://pkg.go.dev/github.com/chromedp/chromedp)地址进行访问。

如果该页面是第一次被访问，那么 godoc 网站就会先去获取、解析和更新代码仓库中的文档，并且在格式化之后展示出来。在页面的底部，会列出该 godoc 的更新时间。

## 二、doc

### 2.1 什么是 doc

`doc`是 go 命令下的一个子命令，`go doc`命令可以打印附于 Go 语言程序实体上的文档，可以通过把程序实体的标识符作为该命令的参数来达到查看其文档的目的、

所谓 Go 语言的程序实体，就是指变量、常量、函数、结构体、接口等。而程序实体的标识符即是代表它们的名称。标识符又分为非限定标识符和限定标识符。其中，限定标识符一般用于表示某个代码包中的程序实体或某个结构体类型中的方法或字段。

例如，标准库代码包`io`中的名为`EOF`的变量用限定标识符表示即`io.EOF`。

### 2.2 选项

`go doc`命令有如下几个常用的选项：

 选项    | 描述
------- | -------------------------------
 `-c`   | 使该命令区分参数中字母的大小写。默认情况下，命令是大小写不敏感的。
 `-cmd` | 使该命令同时打印出`main`包中的可导出的程序实体（其名称的首字母大写）的文档。默认情况下，这部分文档是不会被打印出来的。
 `-u`   | 使该命令同时打印出不可导出的程序实体（其名称是首字母小写）的文档。默认情况下，这部分文档是不会被打印出来的。

### 2.3 参数

`go doc`命令可以跟一个或两个参数，也可以不附加任何参数。

* 无参数：打印出当前目录所代表的的代码包的文档及其中的包级程序实体的列表。如：`go doc`。
* 一个参数：这个参数表示实体名称，这个实体可以是一个包名，也可以是一个包名中的具体实体、方法的名称。如：`go doc -u loadgen.myGenerator.init`。
* 两个参数：这两个参数就分别表示包路径（导入路径）和包内实体名。比如：`go doc net/http Request`表示打印`net/http`包内的`Request`的文档，而不能使用`go doc http Request`，因为 Go 找不到`http`包。


