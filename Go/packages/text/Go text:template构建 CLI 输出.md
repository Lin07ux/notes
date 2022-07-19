> 转摘：[使用 Go 内置模板构建丰富的 CLI 应用程序](https://mp.weixin.qq.com/s/-QKkvFVDJ9xqocBOduAVmw)

使用模板进行输出，虽然不会从多次执行模板输出中收益，但是它易于使用且有助于输出带颜色的文本、编码数据和表格呈现方式。而且在命令行中进行输出，不同的颜色、合理的格式能够使信息有更好的展现，更容易阅读。

### 1. text/template 基本输出

Go 自带的 [text/template](https://pkg.go.dev/text/template) 包实现了用于生成文本输出的数据驱动模板，支持通过按名称映射附加函数，可以使用更多功能扩展模板引擎。而模板中的函数可以接受来自模板引擎的输入作为参数并返回将呈现到输出中的值。

> 映射应该在调用模板`Parse()`函数功能之前完成。

示例如下：

```go
rand.Seed(time.Now().UnixNano())
tmpl := template.Must(template.
  New("").
  Funcs(map[string]interface{}{
    "rand": func() int {
      return rand.Intn(100)
    },
  }).
  Parse(`Hi {{.}}, you are number {{rand}}.`)
)
_ = tmpl.Execute(os.Stdout, "User")
```

这里，将`rand`函数隐射到一个返回 0 到 100（不包括）之间的随机数，模板引擎将会接受这个函数的返回值，并将其字符串化作为`{{rand}}`片段的结果输出。输出可能类似如下：

```
Hi User, you are number 6.
```

模板可以接受的函数应该有一个有效的名称，可以用作模板的一部分（由字母和数字、下划线组成，且不易数字开头），并返回值类型或带有 error 的值。

### 2. JSON 输出

一个常见的用例是需要打印出数据模型：配置、服务器响应或其他复杂的结构。而 JSON 数据通常用于这些场景中。

可以通过在映射中添加一个`json`函数来轻松呈现 JSON 数据输出：

```go
data := struct{
  ID int `json:"id"`
  UpdateTime time.Time `json:"update_time"`
  Path string `json:"path,omitempty"`
}{
  ID: 1,
  UpdateTime: time.Now(),
  Path: "path/to/data",
}
tmpl := template.Must(template.
  New("").
  Funcs(map[string]interface{}{
    "json": func(v interface{}) (string, error) {
      b, err := json.MarshalIndent(v, "", "    ")
      if err != nil {
        return "", err
      }
      return string(b), nil
    },
  }).
  Parse(`Record information {{ . | json }}`)
)
_ = tmpl.Execute(os.Stdout, data)
```

在模板的映射中，定义了`json`方法，其使用 Go 的 json 包将数据格式化为缩进为 4 个空格的 JSON 字符串。而且`json`方法和前面定义的映射函数不同，其返回值包含一个 string 类型的结果和一个 error 错误数据，用于指示是否发生错误。如果发生失败，那么`tmpl.Execute()`方法将会返回这个错误。

输出结果类似如下：

```
Record information {
    "id": 1,
    "update_time": "2021-10-18T21:18:25.973140953+03:00",
    "path": "path/to/data"
}
```

### 3. go-pretty/text 彩色输出

可以使用 [go-pretty](https://github.com/jedib0t/go-pretty) 包来输出彩色文本，而且它还支持禁用/启用颜色支持的选项。

示例如下：

```go
package main

import (
	"github.com/jedib0t/go-pretty/v6/text"
	"os"
	"text/template"
)

func main() {
  data := struct{
    Passed int
    Failed int
  }{
    Passed: 1,
    Failed: 5,
  }
  tmpl := template.Must(template.
    New("").
    Funcs(map[string]interface{}{
      "red": func(v interface{}) string {
        return text.FgHiRed.Sprint(v)
      },
      "green": func(v interface{}) string {
        return text.FgHiGreen.Sprint(v)
      },
      "yellow": func( v interface{}) string {
        return text.FgHiYellow.Sprint(v)
      },
    }).
    Parse(`{{ "Results" | yellow }}
Passed: {{ .Passed | green }}    
Failed: {{ .Failed | red }}
`))
  _ = template.Execute(os.Stdout, data)
}
```

示例中通过 struct 数据`data`来为模板引擎提供数据，而对不同的数据采用了不同的方法来进行处理，这三个方法分别对应红、绿、黄三种颜色的输出。经过函数处理之后，在命令行中就能得到不同颜色的输出了。类似如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1658148742609-bb3f1d462ed4.jpg)

而且，可以检查输出文件是否为终端，如果不是则可以关闭颜色输出。这可以通过 Go 的标准扩展库 [term](https://pkg.go.dev/golang.org/x/term) 来实现：

```go
if !term.IsTerminal(int(os.Stdout.Fd())) {
  text.DisableColors()
}
```

### 4. go-pretty/table 表格输出

以表格格式处理和显示数据很常见，而 go-pretty 库中提供了 table 包用来实现则个目的。

下面的示例中，提供给模板的函数使用了一个通用的数据结构来呈现表格：

```go
// type table.Row interface{} - holds any value
type Table struct {
  Headers table.Row
  Rows    []table.Row
}
```

该`Table`结构由标题和行组成，用于对任何表格信息进行建模。标题的数量反映了每行中的单元格数量（假设它是对齐的）。

就像对彩色输出所做的那样，将表格渲染到终端与将其渲染到文件不同，可以通过`term.IsTerminal()`函数的结果来渲染出对齐的表格或者 CSV 格式，以便于处理。

```go
package main

import (
	"github.com/jedib0t/go-pretty/v6/table"
	"golang.org/x/term"
	"os"
	"text/template"
)

type Table struct {
  Headers table.Row
  Rows    []table.Row
}

func main() {
  tmpl := template.Must(template.
    New("").
    Funcs(map[string]interface{}{
      "table": func(tab *Table) string {
        w := table.NewWriter()
        w.AppendHeader(tab.Headers)
        w.AppendRows(tab.Rows)
        if term.IsTerminal {
          return w.Render()
        }
        return w.RenderCSV()
      },
    }).
    Parse(`{{ . | table }}`)
  )
  tbl := &Table{
    Headers: table.Row{"id", "path"},
    Rows: []table.Row{{1, "field1"}, {2, "field2"}, {3, "field3"}},
  }
  _ = tmpl.Execute(os.Stdout, tbl)
}
```

在终端中，输出效果类似如下：

```
+----+-------+
| ID | PATH  |
+----+-------+
|  1 | file1 |
|  2 | file2 |
|  3 | file3 |
+----+-------+
```

非终端输出如下：

```
id,path
1,file1
2,file2
3,file3
```


