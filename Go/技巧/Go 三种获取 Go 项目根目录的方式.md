> 转摘：[三种获取Go项目根目录的方式，让你做架构，选哪种？](https://mp.weixin.qq.com/s/ws0fcHi-DzCN5PrJNDNKog)

### 1. 为何需要项目根目录路径

在项目中一般都会有确定项目根目录的绝对路径的去修，一旦有了根目录的绝对路径，就能以这个根目录为基准，确定静态文件、配置文件所在的目录。这样做的好处是无论把项目部署在哪个目录下，执行程序时都不会出现`No such file or director`的错误。

比如，一个项目有如下这样的目录结构：

```text
.
|-- config
|   |-- config.go
|   |-- config_dev.yaml
|-- main.go
|-- go.mod
|-- go.sum
```

假设在`config.go`中国使用 Viper 库把`config_dev.yaml`中的配置项加载到内存中。从目录结构中可以看到，`config.go`和`config_dev.yaml`在同一个目录中，所以一般都会如下设置配置文件的路径：

```go
vp.AddConfigPath("./")
vp.SetConfigType("yaml")
```

看起来没问题，但是运行的时候会发现并不行。这是因为，在 Go 程序中，`.`代表的是执行程序时的路径，而非当前代码所在的路径。也就是说，假如经过如下的操作来执行这段程序编译而成的程序：

```shell
cd /Code/demo
go build -o deo.app
./demo.app
```

那么代码中的`.`表示的就是`/Code/demo`这个目录。

> 并不是所有语言都是这样，比如在 Java 中，`.`就表示当前代码文件所在的目录。

所以，为了能准确的找到静态文件、配置文件目录，就需要想办法确定当前文件目录。

### 2. 

在 Go 程序中，获取项目的根目录绝对路径常见的有三种，分别依赖 Go 的三个基本库的函数：

* `os.Getwd()`
* `os.Args[0]`
* `runtime.Caller()`

虽然这三种方式都能获取到 Go 项目的根目录，但是前两种方式在某些情况下拿到的结果并不是真正想要的，只有使用第三种才能在所有执行环境都正确拿到 Go 项目的根目录路径。

#### 2.1 os.Getwd()

Go 语言中，`os.Getwd()`函数能够获取进程的当前工作目录，其名字中的`wd`表示的就是`Working directory`（工作目录）。

使用`os.Getwd()`函数改进上面的程序：

```go
wd, _ := os.Getwd()
// 输出目录，看看路径是否正确
fmt.Println("工作目录："+wd)
// 用工作目录拼接出正确的配置文件目录
vp.AddConfigPath(wd+"/config")
vp.SetConfigType("yaml")
```

编译执行：

```shell
cd /Code/demo
go build -o demo.app
./demo.app
# 工作目录： /Code/demo
```

这看起来没有什么问题，但是刚才是在项目的根目录下编译并执行的程序，如果切换到其他目录执行，就会出现问题了：

```shell
cd /Users/xxx
/Code/demo/demo.app
# 工作目录：/Users/xxx
```

可以看到，切换目录之后执行编译后的程序，得到的工作目录就变为当前执行程序的路径了，那么拼接的配置文件目录的代码自然就不对了。

所以，*`os.Getwd()`函数只能在可执行文件所在的目录下启动程序的情况下才能正确拿到 Go 项目的根目录*，不够通用，需要与运维约定项目的启动命令才行。

#### 2.2 os.Args[0]

`os.Args`这个列表中保存的是程序的启动参数，而参数 0 按照约定就是程序的可执行文件名。

下面是用`os.Args[0]`改进的程序：

```go
filePath, _ := exec.LookPath(os.Args[0])
absFilePath, _ := filepath.Abs(filePath)
rootDir := path.Dir(absFilePath)
// 输出目录，看看是否正确
fmt.Println("程序根目录："+rootDir)
// 用程序目录拼接出正确的配置文件目录
vp.AddConfigPath(rootDir+"/config")
vp.SetConfigType("yaml")
```

代码的前三行是通过`os.Args[0]`得到可执行文件所在目录的绝对路径。编译运行：

```shell
cd /Code/demo
go build -o demo.app
./demo.app
# 程序根目录：/Code/demo

cd /User/xxx
/Code/demo/demo.app
# 程序根目录：/Code/demo
```

这两种方式都能正确的拿到程序的目录路径，`os.Args[0]`在这两种情况下的值分别是`./demo.app`和`/Code/demo/demo.app`。

虽然这种方式看起来很正常，但是在研发阶段用`go run`启动程序的时候是不行的，因为`go run`会在一个临时目录中编译出可执行文件，类似如下：

```text
/var/folders/3g/f2sh8sgs5ls_z62npf80v69w0000gn/T/go-build1053443992/b001/exe
```

另外，如果是在一个脚本中进行编译和运行这段代码，得到的结果也会有所不同。

#### 2.3 runtime.Caller

如果能够拿到当前正在执行的代码的文件路径，就能够推断出程序的根目录了。而`runtime.Caller()`函数可以获取到当前函数的调用栈，其中就包含源码文件路径信息，其函数签名为：

```go
func Caller(skip int) (pc uintptr, file string, line int, ok bool)
```

所以，在`config.go`中就可以这样获取文件路径：

```go
// 获取当前文件的路径
_, filername, _, _ := runtime.Caller(0)
// 推断出项目的根目录
root := path.Dir(path.Dir(filename))
```

用这种方式改造代码之后，上面提到的几种方式都能正确的获取到项目的根目录，并能正确的获取到配置文件了。

不过，这种方式有一个缺点就是，调用`runtime.Caller()`函数的时候，需要确定好调用者的层级，如果不能传入正确的层级关系，那么得到的项目根目录可能就是错误的，也会造成找不到配置文件的问题。

### 3. 总结

这三种方案中，第三种更通用，不论是开发阶段使用`go run`还是使用单元测试，都能正常的执行。如果是在生产环境中启动项目，而且能够与运维约定好启动命令，前两种方式也是能很好的工作的。

甚至可以在系统中设置`ROOTDIR`之类的环境变量，把根目录放在环境变量中，然后在程序中南使用`os.Gentenv("ROOTDIR")`来获取。


