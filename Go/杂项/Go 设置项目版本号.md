> 转摘：
> 
> 1. [Go 这样设置版本号：我们的项目也可以](https://mp.weixin.qq.com/s/NK_MBMAOXONT8wUcS8ihhA)
> 2. [一个不一样的 Go 项目版本号管理方案](https://mp.weixin.qq.com/s/3TihdywvXA9l6Ru5nfoUwA)

项目中，特别是开源项目，版本号是很重要的一个标识。如果将版本号写入源码中，那么每次升级都需要修改源码中的版本号，这并不是一种特别好的方式。

Go 语言版本号是通过如下方式注入到源码中的：

1. 正式版本号：通过项目根目录的一个文件(`$GOROOT/VERSION`)得到；
2. 非正式版本号：通过 Git 获得版本信息；为了避免编译时重复执行 Git 相关操作，可以生成缓存(`$GOROOT/VERSION.cache`)；
3. 体验版本号：通过环境变量控制版本信息。

最后，通过一个统一的 API 接口将版本新出公开给用户。

也可以参考这个方式来为自己的项目设置版本信息：通过 Shell 脚本获取版本号，然后在编译 Go 项目时将版本信息通过`-X`传递进去：

> 这种方式的工作原理在于：能够在构建前更改 Go 程序的符号表中的符号对应的值，具体可以参见 [Go 符号表](../知识点/Go%20符号表.md)。

### 1. Go 代码

在 Go 代码中先定义一个变量`Version`，用于后续的注入：

```go
// main.go
package main

import "fmt"

var Version string

func main() {
  fmt.Println("Version:", Version)
}
```

### 2. Shell 脚本

```sh
#!/bin/sh

version = ""

if [ -f "VERSION" ]; then
    version = `cat VERSION`
fi

if [[ -z $version ]]; then
  if [ -d ".git" ]; then
      version = `git symbolic-ref HEAD | cut -b 12-`-`git rev-parse HEAD`
  else
      version = "unknown"
  fi
fi

go build -ldflags "-X man.Version=$version" main.go
```

这段脚本的逻辑为：

1. 如果有`VERSION`文件，则读取该文件的值作为版本信息；
2. 如果版本号为空，判断当前项目是否是 Git 项目：

    * 如果是(有`.git`文件夹)，则通过 Git 命令获取版本信息
    * 否则，版本信息设置为`unknown`

3. 通过`go build`的`ldflags`选项传递版本信息给`main.Version`。

这样项目的 Version 就设置上正确的值了。




