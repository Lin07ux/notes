> 转摘：
> 
> 1. [Go 每日一库之文件类型鉴别利器 filetype](https://mp.weixin.qq.com/s/MIhk4jGAYSxkJnOSH1Upbg)
> 2. [Go 标准库的神秘功能：如何轻松识别任何文件类型](https://mp.weixin.qq.com/s/sjtXU_MXuPNMXWMYxoyx-g)

### 1. net/http.DetectContentType 标准库的文件类型识别

Go 语言中的`net/http`包中提供了一个简单的判断文件（内容）类型的方法：`DetectContentType()`。该方法使用文件（内容）的前 512 个字节的数据进行判断，返回一个 MIME 类型字符串，如：`image/jpeg`。

该方法内部使用了`mimesniff`算法，根据一组预定义的规则来匹配文件内容的特征，得到对应的 MIME 类型。这个方法不依赖于文件扩展名，而且不需要完整的读取文件内容，准确又快速。

不过，由于内置的匹配规则只是针对常见类型的文件，对于稍微偏一点的文件类型就会被识别为字节流类型了。

如下，是使用 Go net/http 中的`DetectContentType()`方法来识别 MP3 文件格式的示例：

```go
package main

import (
  "fmt"
  "mime"
  "net/http"
  "os"
)

func main() {
  // 打开文件
  file, err := os.Open("example.mp3")
  if err != nil {
    fmt.Println(err)
    return
  }
  defer file.Close()
  
  // 读取文件前 512 字节内容
  buffer := make([]byte, 512)
  n, err := file.Read(buffer)
  if err != nil {
    fmt.Println(err)
    return
  }
  
  // 调用 http.DetectContentType() 获取文件类型
  contentType := http.DetectContentType(buffer[:n])
  fmt.Println(contentType) // audio/mpeg
  
  // 获取扩展名
  ext, _ := mime.ExtensionsByType(contentType)
  fmt.Println(ext) // [".mp3"]
}
```

### 2. h2non/filetype 第三方文件类型识别库

[h2non/filetype](https://github.com/h2non/filetype) 是一个第三方 Go 语言文件类型识别库，可以根据文件的魔数(Magic Numbers)签名来推断文件的类型和 MIME 类型。支持多种文件类型，如图片、视频、音频、文档、压缩包等，比 net/http 标准库的方法支持的更广泛。而且提供了一些便捷的函数和类型匹配器，可以方便地对文件进行分类和筛选。

**特点**：

* 支持多种文件类型，提供文件扩展名和正确的 MIME 类型
* 可以根据扩展名或 MIME 类型来发现文件类型
* 可以根据类别（图片、视频、音频等）来发现文件类型
* 可以添加自定义的新类型和匹配器
* 简单且语义化的 API
* 只需要文件前 262 字节即可进行判断
* 无依赖、跨平台
* 性能比`net/http.DetectContentType()`更高

**原理**：

filetype 识别文件类型是基于文件的魔数签名来实现的。文件的魔数签名是一种特定的字节序列，通常出现在文件的开头，用来标识文件的格式或内容。

不同的文件类型有不同的魔数，比如 JPEG 文件的魔数为`FF D8 FF`，PNG 文件的魔数为`89 50 4E 47`，ZIP 文件的魔数为`50 4B 03 04`等。所以通过读取文件的前几个字节就可以判断出文件的类型。而目前文件魔数的最大字节数为 262 字节，所以可以传入文件的前 262 字节的切片即可。

该库没有直接定义`Matcher`接口来限定匹配方式，而是定义了一个函数类型`type Matcher func([]byte) bool`，并且为每种支持的文件类型定义了一个 Matcher 类型函数，并注册到一个全局的`matchers.Map`中。

当用户调用`filetype.Match(buf)`函数的时候，这个函数会遍历所有注册的 Matcher 函数，逐个进行调用，直到找到一个匹配的文件类型就返回对应的`Type`结构体和空错误。如果没有找到匹配的文件类型，就会返回`Unknown`类型和一个错误信息。

而且，该库允许用户自定义新的文件类型和匹配器，并将它们添加到全局的`Types`和`matchers.Map`中，这样就能实现自定义文件的识别了。

**辅助方法**：

filetype 库中还提供了一些辅助方法，可以方便的进行文件类型的判断等，如下：

* `IsImage([]byte) bool` 是否为图片
* `IsVideo([]byte) bool` 是否为视频
* `IsAudio([]byte) bool` 是否为音频
* `IsSupported(ext string)`
* `IsMIMESupported(mime string)`
* `GetType(ext string)`
* `GetMIME(ext string)`

**使用示例**：

```go
package main

import (
  "fmt"
  "io/ioutil"

  "github.com/h2non/filetype"
)

func main() {
  // 读取文件
  buf, _ := ioutil.ReadFile("sample.jpg")
  
  // 匹配文件类型
  kind, _ := filetype.Match(buf)
  if kind == filetype.Unknown {
    fmt.Println("Unknown file type")
  } else {
    // 输出：File type: jpg. MIME: image/jpeg
    fmt.Printf("File type: %s. MIME: %s\n", kind.Extension, kind.MIME.Value)
  }
  
  // 判断是否为图片
  // 输出：File is an image
  if filetype.IsImage(buf) {
    fmt.Println("File is an image")
  } else {
    fmt.Println("Not an image")
  }
  
  // 检查是否支持某个扩展名
  // 输出：Extension supported
  if filetype.IsSupported("jpg") {
    fmt.Println("Extension supported")
  } else {
    fmt.Println("Extension not supported")
  }
  
  // 检查是否支持某个 MIME 类型
  // 输出：MIME type supported
  if filetype.IsMIMESupported("image/jpeg") {
    fmt.Println("MIME type supported")
  } else {
    fmt.Println(MIME type not supported)
  }
}
```

**自定义类型和匹配器示例**：

```go
package main

import (
  "fmt"
  
  "github.com/h2non/filetype"
)

// 定义一个新的类型
var fooType = fileType.NewType("foo", "foo/foo")

// 定义对应的匹配器
func fooMatcher(buf []byte) bool {
  return len(buf) > 1 && buf[0] == 0x01 && buf[1] == 0x02
}

func main() {
  // 注册匹配器和类型
  filetype.AddMatcher(fooType, fooMatcher)
  
  // 检查是否支持新的扩展名
  if filetype.IsSupported("foo") {
    fmt.Println("New supported type: foo")
  }
  
  // 检查是否支持新的 MIME 类型
  if filetype.IsMIMESupported("foo/foo") {
    fmt.Println("New supported MIME type: foo/foo")
  }
  
  // 新类型的匹配
  fooFile := []byte{0x01, 0x02}
  kind, _ := filetype.Match(fooFile)
  if kind == filetype.Unknown {
    fmt.Println("Unknown file type")
  } else {
    fmt.Println("File type matched: %s\n", kind.Extension)
  }
}
```