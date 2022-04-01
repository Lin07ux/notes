> 转摘：
> 
> 1. [写了 30 多个 Go 常用文件操作的示例，收藏这一篇就够了](https://mp.weixin.qq.com/s/FIRfPEYI0GK72ZtCETszjA)
> 2. [NanoDano](https://www.devdungeon.com/content/working-files-go)

Go 官方提供的文件操作标准库分散在 os、ioutil 等多个包中，里面有非常多的方法，覆盖了文件操作的所有场景。

下面对常用的文件函数进行汇总，分为四大类：基本操作、读写操作、文件压缩、其他操作。每个操作都提供相关的示例代码。

## 一、基本操作

### 1.1 创建空文件 os.Create

```go
package main

import (
  "log"
  "os"
)

func main() {
  // newFile 为 *os.File 类型
  newFile, err := os.Create("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  log.Println(newFile)
  newFile.Close()
}
```

### 1.2 裁剪文件 os.Truncate

```go
package main

import (
  "log"
  "os"
)

func main() {
  // 裁剪一个文件到 100 个字节，得到的总是精确的包含 100 个字节的文件
  // 如果文件本来就少于 100 个字节，则文件中原始内容得以保留，剩余的字节以 null 字节跳虫
  // 如果文件本来就超过 100 个字节，则超过的文件会被抛弃
  // 传入 0 则会清空文件
  err := os.Truncate("test.txt", 100)
  if err != nil {
    log.Fatal(err)
  }
}
```

### 1.3 获取文件信息 os.Stat

```go
package main

import (
  "fmt"
  "log"
  "os"
)

func main() {
  // fileInfo 为 os.FileInfo 类型
  // 如果文件不存在，则会返回错误
  fileInfo, err = os.Stat("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  fmt.Println("File name:", fileInfo.Name())
  fmt.Println("Size in bytes:", fileInfo.Size())
  fmt.Println("Permissions:", fileInfo.Mode())
  fmt.Println("Last modified:", fileInfo.ModTime())
  fmt.Println("Is Directory:", fileInfo.IsDir())
  fmt.Printf("System interface type: %T\n:", fileInfo.Sys())
  fmt.Printf("System info: %+v\n\n", fileInfo.Sys())
}
```

### 1.4 重命名和移动 os.Rename

```go
package main

import (
  "log"
  "os"
)

func main() {
  originalPath := "test.txt"
  newPath := "test2.txt"
  err := os.Rename(originalPath, newPath)
  if err != nil {
    log.Fatal(err)
  }
}
```

### 1.5 删除文件 os.Remove

```go
package mian

import (
  "log"
  "os"
)

func main() {
  err := os.Remove("test.txt")
  if err != nil {
    log.Fatal(err)
  }
}
```

### 1.6 打开和关闭文件 os.Open/os.OpenFile/os.File.Close

`os.Open`和`os.OpenFile`打开文件得到的都是一个`*os.File`类型的对象，关闭则直接调用`os.File.Close`方法即可。

```go
package mian

import (
  "log"
  "os"
)

func main() {
  // 简单地以只读方式打开，得到的是 os.*File 格式化的结果
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  file.Close()

  // OpenFile 支持更多的选项，第二个参数是打开时的属性，最后一个参数是权限模式
  file, err = os.OpenFile("test.txt", os.O_APPEND, 0666)
  if err != nil {
    log.Fatal(err)
  }
  file.Close()
}
```

`os.OpenFile`打开时的属性可以是以下这些值或者这些值的组合值（如`os.O_CREATE|os.O_APPEND`）：

* `os.O_RDONLY` 只读
* `os.O_WRONLY` 只写
* `os.O_RDWR` 读写
* `os.O_APPEND` 追加
* `os.O_CREATE` 如果文件不存在则先创建
* `os.O_TRUNC` 文件打开时裁剪文件
* `os.O_EXCL` 和`os.O_CREATE`一起使用，则文件不能存在
* `os.O_SYNC` 以同步 I/O 方式打开

### 1.7 检查文件是否存在 os.IsNotExist

检查文件是否存在可以通过获取文件状态接口来实现，如果是因文件不存在导致该函数返回错误，则该错误为文件不存在的错误：

```go
package main

import (
  "log"
  "os"
)

func mian() {
  fileInfo, err := os.Stat("test.txt")
  if err != nil {
    if os.IsNotExist(err) {
      log.Fatal("File does not exist.")
    }
  }
  log.Println("File does exist. File infomation:")
  log.Println(fileInfo)
}
```

### 1.8  检查文件读写权限 os.IsPermission

以指定方式打开文件时，如果因为权限不足则会返回无权操作的错误，可以使用`os.IsPermission`函数来判断：

```go
package main

import (
  "log"
  "os"
)

func main() {
  // 测试写权限
  file, err := os.OpenFile("test.txt", os.O_WRONLY, 0666)
  if err != nil {
    if os.IsPermission(err) {
      log.Println("Error: Write permission denied.")
    }
  }
  file.Close()

  // 测试读权限
  file, err = os.OpenFile("test.txt", os.O_RDONLY, 0666)
  if err != nil {
    if os.IsPermission(err) {
      log.Println("Error: Read permission denied.")
    }
  }
  file.Close()
}
```

### 1.9 改变权限、拥有者、时间戳 os.Chmod/os.Chown/os.Chtimes

```go
package main

import (
  "log"
  "os"
  "time"
)

func main() {
  // 使用 Linux 风格改变文件权限
  err := os.Chmod("test.txt", 0777)
  if err != nil {
    log.Println(err)
  }

  // 改变文件所有者
  err = os.Chown("test.txt", os.Getuid(), os.Getgid())
  if err != nil {
    log.Println(err)
  }

  // 改变时间戳
  twoDaysFromNow := time.Now().Add(48 * time.Hour)
  lastAccessTime := twoDaysFromNow
  lastModifyTime := twoDaysFromNow
  err = os.Chtimes("test.txt", lastAccessTime, lastModifyTime)
  if err != nil {
    log.Println(err)
  }
}
```

### 1.10 创建硬链接和软链接 os.Link/os.Symlink

一个普通的文件是一个指向硬盘的 inode 的地方。硬链接会创建一个新的指针指向同一个地方，而且只有所有的硬链接被删除后文件才会被删除。硬链接只在相同的文件系统中才工作。可以认为一个硬链接就是一个正常的文件，创建硬链接后一个文件数据会对应两个文件名，通过任何一个文件名修改文件内容，都会影响到另一个文件名看到的结果。而删除和重命名一个硬链接则不会影响另一个文件名。

Symbolic link 又叫做软链接，和硬链接不同的是，它不直接指向硬盘中的同一个地方，而是通过名字引用其他的文件。它们可以指向不同的文件系统中的不同文件，并且不是所有的操作系统都支持软链接。

```go
package main

import (
  "log"
  "os"
  "fmt"
)

func main() {
  // 创建一个硬链接
  err := os.Link("original.txt", "original_also.txt")
  if err != nil {
    log.Fatal(err)
  }

  // 创建软连接
  err = os.Symlink("original.txt", "original_sym.txt")
  if err != nil {
    log.Fatal(err)
  }

  // Lstat 返回一个文件的信息，但是当文件名是一个软链接时，返回的是软链接的信息，而不是引用的文件的信息
  // Symlink 在 Windows 中不工作
  fileInfo, err := os.Lstat("original_sym.txt")
  if err != nil {
    log.Fatal(err)
  }
  fmt.Printf("Link info: %+v", fileInfo)

  // 改变软连接的拥有者不会影响原始文件
  err = os.Lchown("original_sym.txt", os.Getuid(), os.Getgid())
  if err != nil {
    log.Fatal(err)
  }
}
```

## 二、读写操作

### 2.1 复制文件 os.Copy

```go
package main

import (
  "log"
  "os"
  "io"
)

func main() {
  // 打开原始文件
  originalFile, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  defer originalFile.Close()

  // 创建新的文件作为目标文件
  newFile, err := os.Create("test_copy.txt")
  if err != nil {
    log.Fatal(err)
  }
  defer newFile.Close()

  // 从源文件复制字节到目标文件
  bytesWritten, err := os.Copy(newFile, originalFile)
  if err != nil {
    log.Fatal(err)
  }

  // 将文件内容 flush 到硬盘中
  err = newFile.Sync()
  if err != nil {
    log.Fatal(err)
  }
}
```

### 2.2 跳转到文件指定位置 os.File.Seek

`os.File.Seek`方法可以跳转到打开的文件的指定偏移位置。最终跳转的位置和偏移距离、初始位置有关，其中：

* 偏移位置可以为正数，也可以为负数。正数表示向后跳转，负数表示向前跳转；
* 初始位置表示设定进行偏移的起点，可取值有如下几种：0（文件开始位置）、1（当前位置）、2（文件结尾处）。

最终跳转的位置就是相对初始位置偏移指定量之后的位置。

```go
package main

import (
  "log"
  "os"
  "io"
)

func main() {
  file, _ := os.Open("test.txt")
  defer file.Close()

  var offset int64 = 5

  // 从起始位置向后跳转
  newPosition, err := file.Seek(5, 0)
  if err != nil {
    log.Fatal(err)
  }
  fmt.Println("Just moved to 5:", newPosition)

  // 从当前位置向前跳转
  newPosition, err = file.Seek(-2, 1)
  if err != nil {
    log.Fatal(err)
  }
  fmt.Println("Just moved back two:", newPosition)

  // 得到当前的位置
  currentPosition, err := file.Seek(0, 1)
  fmt.Println("Current position:", currentPosition)

  // 跳转到文件的开始处
  startPosition, err := file.Seek(0, 0)
  if err != nil {
    log.Fatal(err)
  }
  fmt.Println("Position after seeking 0,0:", startPosition)
}
```

### 2.3 写文件 os.File.Write

```go
package main

import (
  "log"
  "os"
  "io"
)

func main() {
  // 使用可写方式打开文件
  file, err := os.OpenFile("test.txt", os.O_WRONLY|os.O_TRUNC|os.O_CREATE, 0666)
  if err != nil {
    log.Fatal(err)
  }
  defer file.Close()

  // 写字节到文件中
  byteSlice := []byte("Bytes!\n")
  bytesWritten, err := file.Wrte(byteSlice)
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Wrote %d bytes.\n", bytesWritten)
}
```

### 2.4 快写文件 ioutil.WriteFile

ioutil 包有一个非常有用的函数`WriteFile`，可以处理创建或者打开文件、写入字节切片和关闭文件一系列的操作，使用起来简洁快速。

```go
package main

import (
  "log"
  "io/ioutil"
)

func main() {
  err := ioutil.WriteFile("test.txt", []byte("Hi\n"), 0666)
  if err != nil {
    log.Fatal(err)
  }
}
```

### 2.5 使用缓存写 bufio.Writer

bufio 包提供了带缓存功能的 writer，可以在写字节到硬盘前使用内存缓存。者在处理很多的数据时非常有用，可以节省操作硬盘 I/O 的时间。另外，在每次写入少量数据，但是要写入多次的情况下，可以将每次的写入攒在内存缓存中，然后一次性写入到硬盘中，减少硬盘的操作，提升性能。

```go
package main

import (
  "log"
  "os"
  "bufio"
)

func main() {
  // 以只写方式打开文件
  file, err := os.OpenFile("test.txt", os.O_WRONLY, 0666)
  if err != nil {
    log.Fatal(err)
  }
  defer file.Close()

  // 为这个文件创建 buffered writer
  bufferedWriter := bufio.NewWriter(file)

  // 写字节到 buffer
  bytesWritten, err := bufferedWriter.Write([]byte{65, 66, 67})
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Bytes written: %d\n", bytesWritten)

  // 写字符串到 buffer
  bytesWritten, err = bufferedWriter.WriteString("Buffered string\n")
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Bytes written: %d\n", bytesWritten)

  // 检查缓存中的字节数
  unflushedBufferSize := bufferedWriter.Buffered()
  log.Printf("Bytes buffered: %d\n", unflushedBufferSize)

  // 还有多少字节可用（未使用的缓存大小）
  bytesAvailable := bufferedWriter.Available()
  log.Printf("Available buffer: %d\n", bytesAvailable)

  // 写内存 buffer 到硬盘
  bufferedWriter.Flush()

  // 丢弃还没有 flush 的缓存内存，清除错误并把它的输出传给参数中的 writer
  // 在将缓存传给另一个 writer 时有用
  bufferedWriter.Reset(bufferedWriter)
  bytesAvailable = bufferedWriter.Available()
  log.Printf("Available buffer: %d\n", bytesAvailable)

  // 重新设置缓存大小
  // 第一个参数是缓存应该输出到哪里，第二个参数是新的大小
  // 如果新的大小小于第一个参数 writer 的缓存大小，并不会对缓存空间进行收缩
  // 第二个参数的作用主要是为了扩容
  bufferedWriter = bufio.NewWriterSize(bufferedWriter, 8000)

  bytesAvailable = bufferedWriter.Available()
  log.Printf("Available buffer: %d\n", bytesAvailable)
}
```

### 2.6 读取最多 N 个字节 os.File.Read

```go
package main

import (
  "log"
  "os"
)

func main() {
  // 只读方式打开文件
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  defer file.Close()

  // 从文件中读取 len(b) 字节的文件
  // 返回 0 字节意味着读取到文件末尾了，并且会返回 io.EOF 的 error
  byteSlice := make([]byte, 16)
  bytesRead, err := file.Read(byteSlice)
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Number of bytes read: %d\n", bytesRead)
  log.Printf("Data read: %s\n", byteSlice)
}
```

### 2.7 正好读取 N 个字节 io.ReadFull

`io.ReadFull()`在文件的字节数小于 byte slice 字节数的时候会返回错误

```go
package main

import (
  "log"
  "os"
  "io"
)

func main() {
  // Open file for reading
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }

  byteSlice := make([]byte, 2)
  numBytesRead, err := io.ReadFull(file, byteSlice)
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Number of bytes read: %d\n", numBytesRead)
  log.Printf("Data read: %s\n", byteSlice)
}
```

### 2.8 读取至少 N 个字节 io.ReadAtLeast

`io.ReadAtLeast()`在不能得到最小的字节的时候会返回错误，但会把已读的文件保留

```go
package main

import (
  "log"
  "os"
  "io"
)

func main() {
  // Open file for reading
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  byteSlice := make([]byte, 512)
  minBytes := 8
  numBytesRead, err := io.ReadAtLeast(file, byteSlice, minBytes)
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Number of bytes read: %d\n", numBytesRead)
  log.Printf("Data read: %s\n", byteSlice)
}
```

### 2.9 读取全部字节 ioutil.ReadAll

`ioutil.ReadAll()`函数会读取 reader 中的每一个字节，然后把字节 slice 返回

```go
package main

import (
  "log"
  "os"
  "fmt"
  "io/ioutil"
)

func main() {
  // Open file for reading
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }

  data, err := ioutil.ReadAll(file)
  if err != nil {
    log.Fatal(err)
  }

  fmt.Printf("Data as hex: %x\n", data)
  fmt.Printf("Data as string: %s\n", data)
  fmt.Println("Number of bytes read:", len(data))
}
```

### 2.10 快读到内存 ioutil.ReadFile

```go
package main

import (
  "log"
  "os"
  "io"
)

func main() {
  data, err := ioutil.ReadFile("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Data read: %s\n", data)
}
```

### 2.11

```go
package main

import (
  "log"
  "os"
  "bufio"
  "fmt"
)

func main() {
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  bufferedReader := bufio.NewReader(file)

  // 读取字节，但是保持当前指针不动
  byteSlice := make([]byte, 5)
  byteSlice, err = bufferedReader.Peek(5)
  if err != nil {
    log.Fatal(err)
  }
  fmt.Printf("Peeked at 5 bytes: %s\n", byteSlice)

  // 读取字节，并移动指针
  numBytesRead, err := bufferedReader.Read(byteSlice)
  if err != nil {
    log.Fatal(err)
  }
  fmt.Printf("Read %d bytes: %s\n", numBytesRead, byteSlice)

  // 读取一个字节
  myByte, err := bufferedReader.ReadByte()
  if err != nil {
    log.Fatal(err)
  }
  fmt.Printf("Read 1 byte: %c\n", myByte)

  // 读取到分隔符，包含分隔符，返回 byte slice
  dataBytes, err := bufferedReader.ReadBytes('\n')
  if err != nil {
    log.Fatal(err)
  }
  fmt.Printf("Read bytes: %s\n", dataBytes)

  // 读取到分隔符，包含分隔符，返回字符串
  dataString, err := bufferedReader.ReadString('\n')
  if err != nil {
    log.Fatal(err)
  }
  fmt.Printf("Read string: %s\n", dataString)
}
```

### 2.12 使用 bufio.Scanner

`bufio.Scanner`类型在处理文件中以分隔符分隔的文本时很有用。通过`Scan()`方法去读取下一个分隔符，然后使用`Text()`或者`Bytes()`获取读取的数据。

`os.File`可以被包装成`bufio.Scanner`，它就像一个缓存 reader，这样就能够进行分隔读取。

通常用到的是使用换行符作为分隔符将文本分成多行，或者在 CSV 文件中使用逗号作为分隔符。

分隔符也可以不是一个简单的字符或者字节，而是一个用于获取分隔符、指针移动字节数量、返回数据的特殊的方法。自定义方法的类型如下：

```go
// To define your own split function, match this fingerprint
type SplitFunc func(data []byte, atEOF bool) (advance int, token []byte, err error)

// Returning (0, nil, nil) will tell the scanner
// to scan again, but with a bigger buffer because
// it wasn't enough data to reach the delimiter
```

缺省情况下，会使用`bufio.ScanLines()`作为分隔方法，其使用的是`bufio.newline`字符作为分隔符，其它的分隔函数还包括`bufio.ScanRunes()`、`bufio.ScanWords()`。

下面的示例中就使用逐词匹配方式进行分隔读取：

```go
package main

import (
  "log"
  "os"
  "fmt"
  "bufio"
)

func main() {
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  scanner := bufio.NewScanner(file)

  // 逐词分隔
  scanner.Split(bufio.ScanWords)

  // 读取下一个 token
  success := scanner.Scan()
  if success == false {
    err = scanner.Err()
    if err == nil {
      log.Println("Scan completed and reached EOF")
    } else {
      log.Fatal(err)
    }
  }

  // 获取读到的数据 Bytes() 或者 Text()
  fmt.Println("First word found:", scanner.Text())

  // 继续调用 scanner.Scan() 读取下一个 token
}
```

## 三、文件压缩

### 3.1 打包（zip）文件

在标准库中，已经有了对压缩文件的支持，下面的示例使用的是 archive/zip 包。

```go
package main

import (
  "archive/zip"
  "os"
  "log"
)

func main() {
  // 创建一个打包文件
  outFile, err := os.Create("test.zip")
  if err != nil {
    log.Fatal(err)
  }
  defer outFile.Close()

  // 创建 zip writer
  zipWriter := zip.NewWriter(outFile)

  // 定义要打包的文件（也可以使用遍历文件夹的方式将文件夹下的文件都写入到这个打包文件中）
  var filesToArchive = []struct{
    Name, Body string
  } {
    {"test.txt", "String contents of file"},
    {"test3.txt", "\x61\x62\x63\n"},
  }

  // 往打包文件中写文件
  for _, file := range filesToArchive {
    fileWriter, err := zipWriter.Create(file.Name)
    if err != nil {
      log.Fatal(err)
    }
    _, err = fileWriter.Write([]byte(file.Body))
    if err != nil {
      log.Fatal(err)
    }
  }

  // 完成后的清理
  err = zipWriter.Close()
  if err != nil {
    log.Fatal(err)
  }
}
```

### 3.2 抽取(uzip)文件

标准库中也支持对打包文件进行抽取，下面的示例使用的是 archive/zip 包。

```go
package main

import (
  "archive/zip"
  "log"
  "os"
  "io"
  "path/filepath"
)

func main() {
  zipReader, err := zip.OpenReader("test.zip")
  if err != nil {
    log.Fatal(err)
  }
  defer zipReader.Close()

  // 遍历打包文件中的每一个文件/文件夹
  for _, file := range zipReader.Reader.File {
    // 打包文件中的文件就像普通的一个文件对象一样
    zippedFile, err := file.Open()
    if err != nil {
      log.Fatal(err)
    }
    defer zippedFile.Close()

    // 指定抽取的文件名（可以指定全路径名或一个前缀）
    targetDir := "./"
    extractedFilePath := filepath.Join(targetDir, file.Name)

    // 抽取项目或者创建文件夹
    if file.fileInfo().IsDir() {
      // 创建文件夹并设置同样的权限
      log.Println("Creating directory:", extractedFilePath)
      os.MkdirAll(extractedFilePath, file.Mode())
    } else {
      // 抽取正常的文件
      log.Println("Extracting file:", file.Name)
      outputFile, err := os.OpenFile(extractedFilePath, os.O_WRONLY|os.O_CREATE|os.O_TRUNC, file.Mode())
      if err != nil {
        log.Fatal(err)
      }
      defer outputFile.Close()

      // 通过 io.Copy 简洁的复制文件内容
      _, err = io.Copy(outputFile, zippedFile)
      if err != nil {
        log.Fatal(err)
      }
    }
  }
}
```

### 3.3 压缩文件 compress/gzip

```go
package main

import (
  "log"
  "os"
  "compress/gzip"
)

func main() {
  outputFile, err := os.Create("test.txt.gz")
  if err != nil {
    log.Fatal(err)
  }

  gzipWriter := gzip.NewWriter(outputFile)
  defer gzipWriter.Close()

  // 写到 gzip writer 时，会依次压缩数据并写入到底层的文件中
  // 不需要关注是如何压缩的，只需要像普通的 writer 一样操作接口
  _, err = gzipWriter.Write([]byte("Gophers rule!\n"))
  if err != nil {
    log.Fatal(err)
  }

  log.Println("Compressed data written to file.")
}
```

### 3.4 解压缩文件 compress/gzip

```go
package main

import (
  "log"
  "os"
  "io"
  "compress/gzip"
)

func main() {
  // 打开一个文件（也可以打开其他 gzip 格式的数据源，比如 web 服务器返回的 gzipped 的内容）
  // 它的内容不是一个文件，而是一个内存流
  gzipFile, err := os.Open("test.txt.gz")
  if err != nil {
    log.Fatal(err)
  }

  gzipReader, err := gzip.NewReader(gzipFile)
  if err != nil {
    log.Fatal(err)
  }
  defer gzipReader.Close()

  // 解压缩到一个 writer
  outfileWriter, err := os.Create("unzipped.txt")
  if err != nil {
    log.Fatal(err)
  }
  defer outfileWriter.Close()

  // 复制内容
  _, err = io.Copy(outfileWriter, gzipReader)
  if err != nil {
    log.Fatal(err)
  }
}
```

## 四、其他操作

### 4.1 临时文件和目录 ioutil.TempDir/ioutil.TempFile

`ioutl`包中提供了两个函数`TempDir()`和`TempFile()`，它们可以创建临时文件夹和临时文件。而且如果传入的路径参数是空字符串的时候，临时文件夹和临时文件会被创建在系统的临时文件夹(Linux 下就是`temp`文件夹)中。

`os.TempDir()`方法会返回当前操作系统的临时文件夹。

```go
package main

import (
  "log"
  "os"
  "io/ioutil"
  "fmt"
)

func main() {
  // 在系统临时文件夹中创建一个临时文件
  tempDirPath, err := ioutil.TempDir("", "myTempDir")
  if err != nil {
    log.Fatal(err)
  }
  fmt.Println("Temp dir created:", tempDirPath)

  // 在临时文件夹中创建临时文件
  tempFile, err := ioutil.TempFile(tempDirPath, "myTempFile.txt")
  if err != nil {
    log.Fatal(err)
  }
  fmt.Println("Temp file created:", tempFile.Name())

  // ... 其他一些操作 ...
  
  // 关闭文件
  err = tempFile.Close()
  if err != nil {
    log.Fatal(err)
  }

  // 删除创建的资源
  err = os.Remove(tempFile.Name())
  if err != nil {
    log.Fatal(err)
  }
  err = os.Remove(tempDirPath)
  if err != nil {
    log.Fatal(err)
  }
}
```

### 4.2 通过 HTTP 下载文件 http.Get/io.Copy

通过`net/http`标准库的方法可以请求一个 url 对应的资源，然后使用`io.Copy()`函数直接将获取到的资源存储到创建的文件中，即完成了 HTTP 文件资源的下载和保存。

```go
package main

import (
  "log"
  "os"
  "io"
  "net/http"
)

func main() {
  newFile, err := os.Create("devdungeon.html")
  if err != nil {
    log.Fatal(err)
  }
  defer newFile.Close()

  url := "http://www.devdungeon.com/archive"
  response, err := http.Get(url)
  if err != nil {
    log.Fatal(err)
  }
  defer response.Body.Close()

  // 将 HTTP response Body 中的内容直接写入到文件中
  numBytesWritten, err := io.Copy(newFile, response.Body)
  if err != nil {
    log.Fatal(err)
  }
  log.Printf("Downloaded %d byte file.\n", numBytesWritten)
}
```

### 4.3 哈希和摘要 crypto

`crypto`库中有很多中哈希/摘要的算法方法，可以直接使用。

下面是将整个文件内容加载到内存中，然后传递给 hash 函数的方式：

```go
package main

import (
  "crypto/md5"
  "crypto/sha1"
  "crypto/sha256"
  "crypto/sha512"
  "log"
  "fmt"
  "io/ioutil"
)

func main() {
  data, err := ioutil.ReadFile("test.txt")
  if err != nil {
    log.Fatal(err)
  }

  // 计算文件 hash
  fmt.Printf("Md5: %x\n\n", md5.Sum(data))
  fmt.Printf("Sha1: %x\n\n", sha1.Sum(data))
  fmt.Printf("Sha256: %x\n\n", sha256.Sum(data))
  fmt.Printf("Sha512: %x\n\n", sha512.Sum(data))
}
```

为了避免大文件的加载造成内容占用问题，还可以创建一个 hash writer，然后使用`Write()`、`WriteString()`和`Copy()`方法将数据传给它：

```go
package main

import (
  "crypto/md5"
  "log"
  "fmt"
  "io"
  "os"
)

func main() {
  file, err := os.Open("test.txt")
  if err != nil {
    log.Fatal(err)
  }
  defer file.Close()

  // 创建一个新的 hasher，满足 writer 接口
  hasher := md5.New()
  _, err = io.Copy(hasher, file)
  if err != nil {
    log.Fatal(err)
  }

  // 计算 hash 并打印结果，传递 nil 作为参数，因为不需要通过参数传递数据，而且通过 writer 接口
  sum := hasher.Sum(nil)
  fmt.Printf("Md5 checksum: %x\n", sum)
}
```
