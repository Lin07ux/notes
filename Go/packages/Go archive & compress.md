> 转摘：[Go 如何打包与压缩文件](https://mp.weixin.qq.com/s/XSxQL4VYYAP6LBTHMQuhwQ)

文件的打包、压缩和解压缩是经常会使用到的功能，一般情况下，可以借助 tar、gzip 等工具来完成这些操作。

在 Go 中，标准库`archive`与`compress`提供了打包/解包、压缩/解压的能力，而且以 Go 编程的方式生成和处理压缩打包文件也非常的简单。

## 一、概念

在开始编写代码之前，需要明确打包和压缩的概念：

* 打包，又称为归档，指的是将一个文件或目录的集合使用一个文件进行存储。
* 压缩，指穿是利用算法将文件进行处理，以达到保留最大文件信息且使文件体积变小的目的。

以打包工具 tar 为例，通过其打包出来的文件通常称为 tar 包，其文件命名通常以`.tar`结尾。再通过其他的压缩工具对 tar 包进行压缩，如 gzip 压缩，则得到通常以`.tar.gz`结尾命名的压缩文件（在 tar 中可使用`-z`参数来调用 gzip）。

tar 包是文件的集合，其结果也是由数据段组成的，每块数据段包含了文件头（描述文件的元信息）和文件内容，如：

```
+----------------------------------------+
| Header                                 |
| [name][mode][owner][group][size]  ...  |
+----------------------------------------+
| Content                                |
| XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|
| XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|
+----------------------------------------+
| Header                                 |
| [name][mode][owner][group][size]  ...  |
+----------------------------------------+
| Content                                |
| XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|
| XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|
+----------------------------------------+
| ...                                    |
```

## 二、archive 打包和解包

archive 库的中文含义是档案，起作用就是归档（打包）与拆档（解包）。其提供两种方案：tar 与 zip，调用路径分别为`archive/tar`和`archive/zip`。

以 tar 为例，来展示如何实现文件的打包与解包。

### 2.1 打包

打包的过程主要就是分为四步：

1. 构建打包器；
2. 针对每个要归档的文件/文件夹构造对应的头信息并写入；
3. 跟着每个头信息，写入对应的文件/文件夹信息；
4. 关闭相关文件。

首先，新建目标打包文件为`out.tar`，然后构造一些文件数据`readme.txt`、`gopher.txt`、`todo.txt`用于归档。

```go
package main

import (
    "archive/tar"
    "log"
    "os"
)

func main() {
    tarPath := "out.tar"
    tarFile, err := os.Create(tarPath)
    if err != nil {
        log.Fatal(err)
    }
    defer tarFile.Close()
    
    tw := tar.NewWriter(tarFile)
    defer tw.Close()
    
    files := []struct{
        Name, Body string
    }{
        {"readme.txt", "This archive contains some text files."},
        {"gopher.txt", "Gopher names:\nGorge\nGeoffrey\nGonzo"},
        {"todo.txt", "Get animal handling license."},
    }
    // ...
}
```

然后就针对这些分别构建文件头信息（指定了文件名、权限、大小，也可以指定更多的头字段），再通过`*tar.Writer`类型的写入器依次写入每个文件的头信息和内容即可：

```go
func main() {
    // ...
    for _, file := range files {
        hdr := &tar.Header{
            Name: file.Name,
            Mode: 066,
            Size: int64(len(file.Body)),
        }
        if err := tw.WriteHeader(hdr); err != nil {
            log.Fatal(err)
        }
        if _, err := tw.Write([]byte(file.Body)); err != nil {
            log.Fatal(err)
        }
    }
}
```

执行上述代码后，就能得到打包后的`out.tar`文件，而且能够通过 tar 工具指定`-tvf`参数查看归档信息：

```shell
$ tar -tvf out.tar
-rw-------  0 0      0          38 Jan  1  1970 readme.txt
-rw-------  0 0      0          35 Jan  1  1970 gopher.txt
-rw-------  0 0      0          28 Jan  1  1970 todo.txt
```

可以看到，指定的文件信息（文件名、权限、大小）符合预期，但是其他未指定的原信息则使用的是默认值，例如日期。

### 2.2 解包

得到的这个归档文件也可以使用 tar 工具进行解压，提取出打包进去的文件：

```shell
$ tar -xvf out.tar
x readme.txt
x gopher.txt
x todo.txt
```

使用 Go 程序进行拆包则可以通过创建 tar 读取器的方式，依次读取出对应的文件和文件内容，并进行相关处理即可。其中的关键是循环通过 tar 读取器的`Next()`方法获取每一个文件，直到遇到错误（IO EOF 或其他错误）。

示例如下：

```go
package main

import (
    "archive/tar"
    "fmt"
    "log"
    "io"
    "os"
)

func main() {
    tarPath := "out.tar"
    tarFile, err := os.Open(tarPath)
    if err != nil {
        log.Fatal(err)
    }
    defer tarFile.Close()
    
    tr := tar.NewReader(tarFile)
    for {
        hdr, err := tr.Next()
        // End of archive
        if err == io.EOF {
            break
        }
        if err != nil {
            log.Fatal(err)
        }
        
        fmt.Printf("Contents of %s: ", hdr.Name)
        if _, err := io.Copy(os.Stdout, tr); err != nil {
            log.Fatal(err)
        }
        fmt.Println()
    }
}
```

输出信息如下：

```
Contents of readme.txt: This archive contains some text files.
Contents of gopher.txt: Gopher names:
George
Geoffrey
Gonzo
Contents of todo.txt: Get animal handling license.
```

## 三、compress 压缩和解压缩

`compress`库支持多中压缩方案，包括：`bzip2`、`flate`、`gzip`、`lzw`和`zlib`。调用路径为`compress/xxx`。

以常用的 gzip 压缩方式为例，来展示压缩和解压缩代码。

### 3.1 压缩

想在 tar 归档之后进行压缩，以得到被压缩了的`out.tar.gz`文件，非常简单，就是在创建 tar 写入器的时候，不是以文件为参数，而是以 gzip 写入器为参数。而 gzip 写入器则以文件为参数。也就是说，tar 写入器的参数是经过 gzip 包装之后的参数。

比如，同样是上文的文件数据`readme.txt`、`gopher.txt`、`todo.txt`，进行打包和压缩，示例如下：

```go
package main

import (
    "archive/tar"
    "compress/gzip"
    "log"
    "os"
)

func main() {
    tarPath := "out.tar.gz"
    tarFile, err := os.Create(tarPath)
    if err != nil {
        log.Fatal(err)
    }
    defer tarFile.Close()
    
    gw := gzip.NewWriter(tarFile)
    defer gw.Close()
    
    tw := tar.NewWriter(gw)
    defer tw.Close()
    
    files := []struct{
        Name, Body string
    }{
        {"readme.txt", "This archive contains some text files."},
        {"gopher.txt", "Gopher names:\nGorge\nGeoffrey\nGonzo"},
        {"todo.txt", "Get animal handling license."},
    }
    for _, file := range files {
        hdr := &tar.Header{
            Name: file.Name,
            Mode: 066,
            Size: int64(len(file.Body)),
        }
        if err := tw.WriteHeader(hdr); err != nil {
            log.Fatal(err)
        }
        if _, err := tw.Write([]byte(file.Body)); err != nil {
            log.Fatal(err)
        }
    }
}
```

可以看到，这跟前面的打包的代码非常类似，只是将`tar.NewWriter(tarFile)`改为了`tar.NewWriter(gw)`，其中`gw`是由`gzip.NewWriter(tarFile)`得到的。

经过压缩之后，打包文件的体积从 4.0K 减小到了 224B：

```shell
$ ls -alh out.tar out.tar.gz
-rw-r--r--  1 slp  staff   4.0K Jul  3 21:52 out.tar
-rw-r--r--  1 slp  staff   224B Jul  3 21:53 out.tar.gz
```

### 3.2 解压缩

同样的，对压缩后的打包文件进行解压缩和解包操作，也只需替换 tar 读取器的参数即可：

```go
package main

import (
    "archive/tar"
    "compress/gzip"
    "log"
    "io"
    "os"
)

func main() {
    tarPath := "out.tar.gz"
    tarFile, err := os.Open(tarPath)
    if err != nil {
        log.Fatal(err)
    }
    defer tarFile.Close()
    
    gr, err := gzip.NewReader(tarFile)
    if err != nil {
        log.Fatal(err)
    }
    defer gr.Close()
    
    tr := tar.NewReader(gr)
    for {
        hdr, err := tr.Next()
        // End of archive
        if err == io.EOF {
            break
        }
        if err != nil {
            log.Fatal(err)
        }
        
        fmt.Printf("Contents of %s: ", hdr.Name)
        if _, err := io.Copy(os.Stdout, tr); err != nil {
            log.Fatal(err)
        }
        fmt.Println()
    }
}
```

输出结果和前面的解包结果是一样的。

可以看到，这里唯一发生变化的地方就是将`tar.NewReader(tarFile)`改为了`tar.NewReader(gr)`，而`gr`是由`gzip.NewReader(tarFile)`得到的。

## 四、总结

通过`archive`库实现文件的打包和解包操作，通过`compress`库可以实现文件的压缩和解压缩操作。而`archive`库和`compress`库可以结合使用，实现在打包的同时进行压缩的处理。如果想切换打包/解包、压缩/解压缩策略，只需要替换对应的 Writer/Reader 即可。这种便利源于 Go 中优秀的流式 IO 设计。

