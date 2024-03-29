Go 自带的标准库`text/template`可以用来进行文本模块的解析和输出，适用于通过相同的模板配合不同的数据来生成不同的内容。`html/template`标准库和`text/template`的用法很相似，实现了很多相同的方法接口，了解其一即可。

## 一、基本用法

### 1.1 基础语法

Go 语言的模板语法与其他语言的模板有类似之处，但并不完全相同。其基本语法如下：

* `{{}}` 为模板的数据标记符，也就是遇到被这个标记包围的内容就是需要进行数据解析替换的地方；

* `{{- -}}` 对于字符串的输出，如果要去除两端的空格，可以通过这个标记符包裹（相当于其他语言的`trim()`函数）；

* `.` 表示的是所处作用域的当前对象，并不仅仅只表示渲染模板时传入的数据参数。

    - 在模板顶级作用域下，`{{.}}`代表的就是渲染模板时传入的数据；
    - 如果在模板中使用了`with`命令，则会将其作用域中的`.`改为命令指定的数据；
    - 如果`.`是一个复合数据（如 struct），则可以使用`{{.FieldName}}`的方式来使用复合数据中对应名称的字段；

* `<command>` Go 模板内置了多个命令，可以实现实现条件判断、循环等逻辑处理，还能够进行模板定义、模板命名等操作。

    - `|` 管道符号 Pipeline，表示将其前面的数据传递给其后面的命令，并作为后面命令的最后一个参数；
    - `$` 该符号后紧跟着一个变量名，表示在模板中定义一个局部变量（变量名包含`$`符号），在作用域中可以使用该变量进行渲染；
    - `if/else if/else` 条件渲染；
    - `range/else` 遍历渲染，`range`的使用和 Go 语言的`range`语法相同，不过循环产生的局部变量要用`$`来定义；如果数据长度为 0 则可以使用`else`来进行渲染；
    - `with/else` 临时修改`.`指代的数据，其作用域内的`.`即表示`with`命令指定的数据；如果数据为空可以使用`else`进行渲染；
    - `and, or, not, eq, ne, lt, le, gt, ge` 逻辑和比较运算函数；
    - `template` 嵌套引入其他模板，引入的模板名称通过该命令的参数给出；
    - `define` 定义模板，也就是将区块内的模板内容作为一个独立的模板，并指定模板名称；
    - `block` 嵌入模板占位，其效果相当于`define`和`template`的综合效果，典型的用法是定义一个基础模板，在其中使用`block`命令定义一个引入模板的占位模块，其他使用该模板的模板文件中可以通过重新定义模块进行自定义。

Go 语言的模板文件通常以`.tmpl`和`.tpl`为后缀，且必须使用 UTF-8 编码。另外，除了`|`、`$`和`template`外，其他的命令都要跟着一个`{{end}}`命令作为命令结束标识。

### 1.2 渲染流程

在 Go 语言中渲染一个模板，是有固定的流程的：

1. 创建模板对象

    ```go
    template.New(name string) *Template
    ```

2. 解析模板内容

    ```go
    func (t *Template) Parse(src string) (*Template, error)
    func ParseFiles(filenames ...string) (*Template, error)
    func ParseGlob(pattern string) (*Template, error)
    ```

3. 渲染模板数据

    ```go
    func (t *Template) Execute(wr io.Writer, data interface{}) error
    func (t *Template) ExecuteTemplate(wr io.Writer, name string, data interface{}) error
    ```
    
其中，创建模板对象和解析模板内容也可以直接使用`template.ParseFiles()`或`template.ParseGlob()`函数同时完成。而且对于`*Template`对象，可以通过调用其`Delims(left, right string)`和`Funcs(funcMap FuncMap)`方法来修改数据标记符和增加模板内函数定义。

渲染模板数据的时候，对于单一模板可以直接使用`Execute()`方法来实现渲染，如果解析了多个模板，则可以通过`ExecuteTemplate()`方法通过模板名称来指定要渲染的模板。

### 1.3 示例

下面是一个简单的示例：

```go
package main

import (
	"os"
	"text/template"
)

type Friend struct {
	Fname string
}
type Person struct {
	UserName string
	Emails   []string
	Friends  []*Friend
}

func main() {
	f1 := Friend{Fname: "xiaofang"}
	f2 := Friend{Fname: "wugui"}
	t := template.New("test")
	t = template.Must(t.Parse(
`hello {{.UserName}}!
{{ range .Emails }}
an email {{ . }}
{{- end }}
{{ with .Friends }}
{{- range . }}
my friend name is {{.Fname}}
{{- end }}
{{ end }}`))
	p := Person{UserName: "longshuai",
		Emails:  []string{"a1@qq.com", "a2@gmail.com"},
		Friends: []*Friend{&f1, &f2}}
	t.Execute(os.Stdout, p)
}
```

输出结果如下：

```text
hello longshuai!

an email a1@qq.com
an email a2@gmail.com

my friend name is xiaofang
my friend name is wugui

```

## 二、高级特性

Go 的 template 在解析和渲染的时候，支持很多配置、选项，可以通过合理的配置和模板语法，基本能实现想要的功能。

### 2.1 去除空白

template 引擎在进行数据替换渲染的时候，是完全按照文本格式进行替换的。除了需要替换的地方，所有的行分隔符、空格等空白都会原样保留。这就要求在写模板内容的时候，不要随意缩进、随意换行。

如果有需要对内容前后的空白进行去除，可以通过在 template 的数据标识符中增加`-`来声明：

* `{{- ` 去除该渲染结果前面的空白；
* ` -}}` 去除该渲染结果后面的空白。

需要注意的是：声明要去除空白时，`{{-`的后面或者`-}}`的前面需要有一个或多个空格。而会被去除的空白包括换行符、制表符、空格等。

比如：

```template
{{23}} < {{45}}     // --> 23 < 45
{{23}} < {{- 45}}   // --> 23 <45
{{23 -}} < {{45}}   // --> 23< 45
{{23 -}} < {{- 45}} // --> 23<45
```

对于前面的示例：

```go
t.Parse(
`hello {{.UserName}}!
{{ range .Emails }}
an email {{ . }}
{{- end }}
{{ with .Friends }}
{{- range . }}
my friend name is {{.Fname}}
{{- end }}
{{ end }}`
```

在渲染的时候：

* 渲染结果的第一行和第二行之间会有空行，这是因为`{{ range .Emails }}`自身也占一行，但是它不对应具体的内容，会被渲染成一个空行；
* range 区块的结束标识`{{- end }}`添加了去除前面空白的声明，所以每一行 Email 的输出之间没有空行。如果将其改为`{{ end }}`则每个迭代的元素之间输出的时候都会有空行，这个空行就是`{{ end }}`这一行被替换造成的；
* `{{ with }}`及其对应的`{{ end }}`没有声明去除空白，所以这两行会被保留为一个空行；
* 最后一个`range-end`的输出和前面的类似。

### 2.2 注释

在模板中也能写注释，不过注释的内容也要由模板标识符包裹：`{{/* a comment */}}`

需要注意的是：**注释行的内容虽然不会输出，但其依旧会占用一行**，所以应该去除前缀或后缀空白，否则会多一行空白行。而且前缀和后缀空白应该只去除一个，不要同时都去除，否则会破坏原有的格式。

例如：

```go
t.Parse(
`hello {{.UserName}}!
{{- /* this line is a comment */}}
{{ range .Emails }}
an email {{ . }}
{{- end }}`)
```

输出为：

```text
hello longshuai!

an email a1@qq.com
an email a2@gmail.com
```

### 2.3 Pipeline

Pipeline 是指产生数据的操作，Go template 中可以使用管道符号`|`连接多个命令，用法和 Unix 下的管道类似：`|`前面的命令将运算结果（或返回值）传递给后一个命令的最后一个参数位置。

例如：

```template
{{ . | printf "%s - %s\n" "abcd" }}
```

这里就会将`.`的值传递给`printf`方法，并且处于`"abcd"`参数后面，也就是说，输出结果为`abcd - Lin07ux`。

需要注意的是：并非只有使用了`|`的才是 Pipeline。Go template 中，Pipeline 的概念是传递数据，只要能产生数据的就都是 Pipeline。这使得某些操作可以作为另一些操作内部的表达式先运行得到结果。

例如，下面的`(len "output")`就是一个 Pipeline，它会先运行，然后将结果作为参数传递给`println()`方法：

```template
{{println (len "output")}}
```

下面是 Pipeline 的几种常见使用方式，它们的输出都是`"output"`：

```template
{{ `"output"` }}
{{ printf "%q" "output" }}
{{ "output" | printf "%q" }}
{{ printf "%q" (print "out" "put") }}
{{ "put" | printf "%s%s" "out" | printf "%q" }}
{{ "output" | printf "%s" | printf "%q" }}
```

### 2.4 变量

在 template 中也可以定义变量，有三点需要注意的地方：

1. 变量有作用域，只要出现包裹变量的`{{end}}`，则当前层次的作用域结束。内层可以访问外层的变量，但是外层不能访问内层的变量；
2. 变量定义和使用的时候需要加上前缀`$`，且为变量设置值时需要区分其是否已定义过，分别使用`:=`和`=`符号；
3. 存在一个特殊变量`$`，代表当前模板的最顶级作用域对象（也就是以当前模板为全局作用域的全局变量）。它的值在执行`Execute()`的时候进行赋值，且一直不变。每个模板中的`$`的值可能不同；
4. 变量不可在模板之间继承，包括`.`和`$`这种特殊的变量。

在 template 中定义变量时需要区分是否已经定义过：

```template
// 未定义过的变量赋值
$var := pipeline

// 已定义过的变量赋值
$var = pipeline
```

template 的变量场景会用在`range`循环中，例如：

```template
tx := template.Must(template.New("hh").Parse(
`{{range $x := . -}}
{{$y := 333}}
{{- if (gt $x 33)}}{{println $x $y ($z := 444)}}{{- end}}
{{- end}}
`))
s := []int{11, 22, 33, 44, 55}
_ = tx.Execute(os.Stdout, s)
```

其输出如下：

```
44 333 444
55 333 444


```

关于变量不可在模板之间继承，可以看下面的示例：

```template
func main() {
	t1 := template.New("test1")
	tmpl, _ := t1.Parse(
`
{{- define "T1"}}ONE {{println .}}{{end}}
{{- define "T2"}}{{template "T1" $}}{{end}}
{{- template "T2" . -}}
`)
	_ = tmpl.Execute(os.Stdout, "hello world")
}
```

这里`{{- template "T2" . -}}`中的`.`表示的就是`Execute()`执行时传入的字符串`hello world`，它表示使用`T2`模板，并设置`T2`模板的全局变量`$`的值为`.`的值（也就是`hello world`）。

在`T2`模板中，通过`{{template "T1" $}}`来引用`T1`模板，并且将`T1`模板的全局变量`$`的设置为`T2`模板的全局变量`$`（也就是`hello world`）。

`template`命令可以看做一个特殊的函数，执行过程就是`template(tmplName, data)`。所以如果在使用`template`命令的时候没有传入数据，则被调用的模板的全局变量`$`的值就是 nil。

### 2.5 if 条件判断

Go template 中的条件判断语法和 Go 语法基本相同：

```template
{{if pipeline}} T1 {{end}}
{{if pipeline}} T1 {{else}} T0 {{end}}
{{if pipeline}} T1 {{else if pipeline}} T0 {{end}}
{{if pipeline}} T1 {{else if pipeline}} T0 {{else}} T {{end}}
{{if pipeline}} T1 {{else}} {{if pipeline}} T0 {{end}}
```

### 2.6 range 循环迭代

Go template 有两种迭代方式：

```template
{{range pipeline}} T1 {{end}}
{{range pipeline}} T1 {{else}} T0 {{end}}
```

第二个表示方式中的`else`部分会在 pipeline 为零值的时候执行，此时`range`部分就会被跳过。

range 可以迭代 slice、array、map 和 channel。迭代的时候会设置`.`变量为当前正在迭代的元素。也可以在迭代的过程中进行赋值，和 Go 的 range 语法类似，有两种赋值方式：

```template
{{range $value := pipeline}}
{{range $key, $value := pipeline}}
```

* 如果 range 中只给一个变量赋值，则这个变量是当前正在迭代的元素的值，和当前区块中的`.`变量一样；
* 如果 range 中给两个变量赋值，则第一个变量是索引值（slice/array 是数值，map 为 key），第二个变量是当前正在迭代的元素的值。

下面是一个在 HTML 中使用 range 的示例：

```template
<ul>
	{{ range . }}
		<li>{{ . }}</li>
	{{ else }}
		<li> Nothing to show </li>
	{{ end}}
</ul>
```

需要注意的是，`{{ range . }}`和`<li>{{ . }}</li>`两行中的`.`变量的值是不同的：前者表示模板的全局变量，后者则表示当前正在迭代的元素。

### 2.7 with 作用域区块

Go template 中可以使用`with`命令来定义一个作用域区块，并将当前区块中的`.`变量的值修改为`with`命令指定的值。

`with`有两种格式：

```go
{{with pipeline}} T1 {{end}}
{{with pipeline}} T1 {{else}} T0 {{end}}
```

和`range`一样，当`with`的 pipeline 的值为零值时，会跳过`with`块，而执行对应的`else`块。

例如：

```template
{{with "xx"}}{{println .}}{{end}}
```

这行模板中，通过`with`将其区块中的`.`变量的值设置为了`"xx"`，所以渲染输出的结果就是`xx`。

### 2.8 funcs 函数

Go template 中定义了一些内置函数，主要有如下：

* `and` 返回第一个为空的参数，或者最后一个参数，可以有任意多个参数。比如，`and x y`等价于`if x then y else x`。和 JavaScript 中的`&&`符号的逻辑相同；

* `or` 返回第一个不为空的参数，或最后一个参数，可以有任意多个参数。比如，`or x y`等价于`if x then x else y`。和 JavaScript 中的`||`符号的逻辑相同；

* `not` 布尔取反，只能有一个参数；

* `print/printf/println` 分别等价于 fmt 表中的`Sprint/Sprintf/Sprintln`；

* `len` 返回参数的 length；

* `index` 对可索引对象进行索引取值。第一个参数是索引对象，后面的参数是索引位。可索引对象包括 map、slice、array。比如，`index x 1 2 3`代表的是`x[1][2][3]`；

* `call` 显式调用函数。第一个参数必须是函数类型，且不是 template 中的函数，而是外部函数。而且，这个函数必须只能有一个或两个返回值。如果是两个返回值，第二个返回值必须是 error 类型。比如，一个 struct 中的某个字段`Y`是 func 类型，那么`call .X.Y 1 2`表示调用`.X.Y(1, 2)`。

另外还有一些用于比较的函数：

* `eq arg1 arg2` 相等判断，在`arg1 == arg2`时返回 true；
* `ne arg1 arg2` 不等判断，在`arg1 != arg2`时返回 true；
* `lt arg1 arg2` 小于判断，在`arg1 < arg2`时返回 true；
* `le arg1 arg2` 小于等于判断，在`arg1 <= arg2`时返回 true；
* `gt arg1 arg2` 大于判断，在`arg1 > arg2`时返回 true；
* `ge arg1 arg2` 大于等于判断，在`arg1 >= arg2`时返回 true。

对于`eq`函数，它支持多个参数，表示后面的参数有任意一个和第一个参数相等，则返回 true，等价于：

```template
arg1==arg2 || arg1==arg3 || arg1==arg4
```

Go template 也支持自定义函数，不过自定义函数需要在执行模板解析（`Parse`）之前进行设置。

### 2.9 define & template 模板嵌套

Go template 支持模板嵌套，也就是说在一个模板中能够通过模板名称引入其他的模板，实现模板复用。

模板嵌套需要用到两个命令：

* `define` 定义一个模板，并关联到一个名称上。这个模板可以在待解析内容中进行定义，也可以在单独的文件中定义；
* `template` 通过模板名称引入模板内容并进行渲染执行。

由于模板渲染的时候可能需要数据，而 Go template 中变量的作用域是不能跨模板继承的，所以`template`命令有两种使用方式：

```template
{{template "name"}}
{{template "name" pipeline}}
```

第一种方式是直接渲染指定的子模板，且渲染时设置子模板中的`.`变量为 nil；第二个方式则是设置子模板的`.`变量的值为 pipeline 的值。所以可以将`template`看做是一个函数调用，其第一个参数为子模板名称，第二个参数为子模板渲染时传入的数据，可以为 nil：

```go
template("name")
template("name", pipeline)
```

示例如下：

```go
func main() {
	t1 := template.New("test1")
	tmpl, _ := t1.Parse(
`{{- define "T1"}}ONE {{println .}}{{end}}
{{- define "T2"}}TWO {{println .}}{{end}}
{{- define "T3"}}{{template "T1"}}{{template "T2" "haha"}}{{end}}
{{- template "T3" -}}
`)
	_ = tmpl.Execute(os.Stdout, "hello world")
}
```

这里：

* 通过`template`引入子模板`T3`的时候未传入数据，所以`T3`模板的`.`变量为 nil；
* `T3`模板中引入`T1`模板时未传入数据，所以`T1`模板的`.`变量为 nil；
* `T3`模板中引入`T2`模板时传入的数据为`"haha"`，所以`T2`模板的`.`变量为`"haha"`。

所以，最终的渲染结果为：

```text
ONE <nil>
TWO haha
```

### 2.10 block 模板占位

根据官方文档的解释：`block`等价于使用`define`定义一个指定名称的模板，并在有需要的地方执行这个模板，执行时将子模板的`.`变量设置为 pipeline 的值。

也即是说，`block`命令相当于有两个动作：

1. **如果指定名称的模板不存在，则通过`define`定义该名称的模板**；
2. **通过`template`命令引入指定名称的模板，并传入 pipeline 数据**。

换句话说，`block`相当于是引入指定名称的模板，如果该名称的模板不存在，那么就有`block`设置一个兜底的模板。

比如：

```template
{{block "T1" .}} one {{end}}
```

它首先会判断`T1`模板是否存在，如果不存在的话则临时通过`{{define "T1"}} one {{end}}`定义一个`T1`模板；然后再通过`{{template "T1" .}}`方式来引入并渲染这个模板。

`block`命令常用在 HTML 中的基础布局模板中，通过它可以在基础模板中为不同页面的具体内容提供占位符，然后其他页面通过继承该基础模板并重定义`block`指定的名称的模板，从而实现复用。

例如，`home.html`中有如下内容：

```template
{{ define "home.html" }}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Go Web Programming</title>
    </head>
    <body>
        {{ block "content" . }}
            <h1 style="color: blue;">Hello World!</h1>
        {{ end }}
    </body>
</html>
{{ end }}
```

其他页面要使用这个模板的时候，可以使用如下的内容：

```template
{{ template "home.html" . }}

{{ define "content" }}
	<h1 style="color: red;">Hello World!</h1>
{{ end }}
```

从而实现对`home.html`模板的复用和对`content`内容的自定义。

### 2.11 html 上下文感知

对于`html/template`包来说，存在上下文感知的处理，而`text/template`没有该功能。

上下文感知是指根据渲染数据所处的环境（css/js/html/url-path/url-query）自动进行不同格式的转义。

例如，对于如下的模板：

```template
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Go Web Programming</title>
	</head>
	<body>
		<div>{{ . }}</div>
		<div><a href="/{{ . }}">Path</a></div>
		<div><a href="/?q={{ . }}">Query</a></div>
		<div><a onclick="f('{{ . }}')">Onclick</a></div>
	</body>
</html>
```

模板中有 4 个不同的环境：html、url 的 path、url 的 query 以及 js 环境。虽然都是使用`{{.}}`数据，但是渲染时可能会有不同的处理。

在渲染时传入的数据为`I asked: <i>"What's up?"</i>`时，渲染结果如下：

```html
<html>
  <head>
	  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	  <title>Go Web Programming</title>
  </head>
  <body>
	  <div>I asked: &lt;i&gt;&#34;What&#39;s up?&#34;&lt;/i&gt;</div>
	  <div><a href="/I%20asked:%20%3ci%3e%22What%27s%20up?%22%3c/i%3e">Path</a></div>
	  <div><a href="/?q=I%20asked%3a%20%3ci%3e%22What%27s%20up%3f%22%3c%2fi%3e">Query</a></div>
	  <div><a onclick="f('I asked: \x3ci\x3e\x22What\x27s up?\x22\x3c\/i\x3e')">Onclick</a></div>
  </body>
</html>
```

上下文感知的自动转义能让程序更加安全，比如可以防止 XSS 攻击等。如果不想进行转义，那么可以将数据进行类型转换，转为 html/template 包中定义的如下类型：

```go
type HTML
type CSS
type JS
type URL
```

转换为这些类型后，字符都将是字面意义：

```go
t, _ := template.ParseFiles("tmpl.html")
t.Execute(w, template.HTML(r.FormValue("comment")))
```

## 三、实现

text/template 和 html/template 包在整体的结构和实现逻辑上都是相似的，只是有些属性值的使用和处理不同。

### 3.1 结构体

**Template**

template 中最主要的是 Template 结构体，包中的大部分导出的方法和函数的返回值都是该结构体的实例：

```go
type Template struct {
  name string       // 模板名称
  *parse.Tree       // 解析树
  *common           // 模板集合
  leftDelim  string // 左分隔符，默认为 {{
  rightDelim string // 右分隔符，默认为 }}
}
```

该结构体中需要主要关注的是`name`和`common`两个字段，前者可以被用来查找、引用该模板，后者则是与当前 Template 实例关联的一个模板组。

**common**

`common`结构体的定义如下：

```go
type common struct {
  tmpl       map[string]*Template // Map from name to defined templates.
  option     option
  muFuncs    sync.RWMutes // protects parseFuncs and execFuncs
  parseFuncs FuncMap
  execFuncs  map[string]reflect.Value
}
```

这个结构体的`tmpl`是一个 map 结构，key 为模板实例的名称，value 为模板实例指针，所以一个 common 中可以包含多个 Template。而每个 Template 中又通过`common`字段指向了 common 实例，这就构成了一个闭环。

也就是说，Template 在解析的时候，会将遇到的每个模板定义都生成一个 Template 实例，这些 Template 实例会共用同一个 common。而且这个 common 实例中会包含全部的这些 Template 实例。

可以将 common 看做为一个模板组，它与 Template 实例之间最终会组成类似如下的结构：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676647620)

**FuncMap**

common 中`parseFuncs`的类型是一个很简单的函数 map 类型，定义如下：

```go
type FuncMap map[string]any
```

虽然从定义上看其值可以是 any（也就是`interface{}`），但其实是要求其需要为一个函数，并且只能返回一个或者两个值，而且如果返回两个值则需要第二个值是 error 类型。如果不符合这些条件，就会出现 panic。

common 中的 parseFuncs 和 execFuncs 共同构成模板渲染时的自定义函数组。

### 3.2 New

template 包中，提供了一个`New()`函数，来创建一个空的、无解析数据的模板，同时还会初始化 common 结构体。对应的源码如下：

```go
func New(name string) *Template {
  t := &Template{
    name: name,
  }
  t.init()
  return t
}

func (t *Template) init() {
  if t.common == nil {
    c := new(common)
    c.tmpl = make(map[string]*Template)
    c.parseFuncs = make(FuncMap)
    c.execFuncs = make(map[string]reflect.Value)
    t.common = c
  }
}
```

可以看到，`New()`函数的功能很简单，就是创建一个 Template 实例，并初始化其 common 结构体。而且初始化 common 结构体的方法`t.init()`在整个包中会有多次用到，所以它就做了 common 是否为空的判断，为空的时候才会进行初始化，这样可以避免多次设置 common。

另外，需要注意的是，`New()`函数得到的 Template 实例虽然初始了 common 实例，但是 common 实例中并没有包含该 Template 实例，它们之间还只是单向指向关系。在调用了 Template 实例的`Parse()`方法之后才会将当前的 Template 实例添加到它的 common 中。所以，**common 是一个已经被解析了的模板组**。

在 Template 结构体上也定义了一个`New()`方法，它相当于对当前实例的拷贝，不过名字是单独设置的，而且这个新的实例还处于未解析状态：

```go
// New allocates a new, undefined template associates with the given one and with the same
// delimiters. The association, which is transitive, allows one template to
// invoke another with a {{template}} action.
func (t *Template) New(name string) *Template {
  t.init()
  nt := &Template {
    name:       name,
    common:     t.common,
    leftDelim:  t.leftDelim,
    rightDelim: t.rightDelim,
  }
  return nt
}
```

### 3.3 Parse/ParseGlob/ParseFiles/ParseFs

template 提供了四个方法来实现模板的解析，常用的是前三个：

* Parse 解析给定的文本内容；
* ParseGlob 读取并解析与给定的正则表达式匹配的文件；
* ParseFiles 解析给定的文件，支持多个文件路径参数；
* ParseFs 在指定的文件系统中读取并解析文件。

需要注意：**注意在调用 `Parse()` 方法之后才会将相关的 Template 实例放进 common 中，表示这个模板已经可用了，或者称为已经定义了(defined)，可以被 `Execute()` 或 `ExecuteTemplate()` 进行渲染了，也表示可以使用 `Lookup()` 和 `DefinedTemplates()` 来检索模板了。**

另外，调用了`Parse()`方法解析模板之后，会将给定的 FuncMap 中的函数添加到 common 中。而只有存在于 common 中的函数才可以在模板中使用。

`ParseGlob/ParseFiles/ParseFs`三个方法（在 html/template 包中也提供了三个同名的函数）会在解析文件的时候创建新的 Template 实例，这些新的实例都属于同一个 common 模板组。但是需要注意的是：调用这三个方法之后，当前的模板实例依旧没有被添加到 common 中。也就是说，当前模板实例依旧不可用、未定义。

使用文件来解析时，默认会使用文件的 basename 作为对应的 Template 实例的名称。

### 3.4 Execute/ExecuteTemplate

这两个方法是用来渲染已经解析好的模板，并将结果输出到一个 io.Writer 中。它俩的区别在于：前者使用整个 common 中已定义好的模板对象进行渲染，后者可以指定使用 common 中的某个已定义的模板进行渲染。

```go
func (t *Template) Execute(wr io.Writer, data interface{}) error
func (t *Template) ExecuteTemplate(wr io.Writer, name string, data interface{}) error
```

### 3.5 Lookup/DefinedTemplates/Templates

这三个方法都用于检索已经定义的模板：

* Lookup 根据模板名称来检索并返回对应的 Template 实例，对应名称的模板不存在在返回 nil；
* DefinedTemplates 返回所有已经定义的模板名称组成的字符串，没有已定义的模板时返回空字符串；
* Templates 返回所有已定义的模板实例的 slice，没有已定义的模板时返回空的 slice。

### 3.6 Clone

`Clone()`方法用于克隆一个完全一样的模板，包括 common 结构体也会完全克隆。它与`New()`方法的不同在于：

* `New()`方法复用当前模板实例的内容，创建一个新的模板实例；
* `Clone()`方法将当前模板实例的全部数据都做一份深拷贝，得到一个与当前模板实例没有关联的新实例。

也就是说，调用`Clone()`方法得到的实例和当前实例完全一样，但是修改克隆的新实例将不会对原实例造成影响。

例如：

```go
func main() {
	t1 := template.New("test1")
	t2 := t1.New("test2")
	t1, _ = t1.Parse(
		`{{define "T1"}}ONE{{end}}
		{{define "T2"}}TWO{{end}}
		{{define "T3"}}{{template "T1"}} {{template "T2"}}{{end}}
		{{template "T3"}}`)
	t2, _ = t2.Parse(
		`{{define "T4"}}ONE{{end}}
		{{define "T2"}}TWOO{{end}}
		{{define "T3"}}{{template "T4"}} {{template "T2"}}{{end}}
		{{template "T3"}}`)

	t3, err := t1.Clone()
	if err != nil {
		panic(err)
	}

	// 两者的解析树位置相同，但 common 不同
	fmt.Println(t1.Lookup("T4"))  // &{T4 0xc000070240 0xc000100050  }
	fmt.Println(t3.Lookup("T4"))  // &{T4 0xc000070240 0xc000078000  }

	// 修改 t3
	t3, _ = t3.Parse(`{{define "T4"}}one{{end}}`)

	// t1 中的 T4 未发生变化，但 t3 中的 T4 已经不同了
	fmt.Println(t1.Lookup("T4"))  // &{T4 0xc000070240 0xc000100050  }
	fmt.Println(t3.Lookup("T4"))  // &{T4 0xc0000706c0 0xc000078000  }
}
```

### 3.7 Must

template 中的函数和方法一般返回的都是两个值：一个是 Template，一个是 error。

但是有时候，只有第一个返回值是想要的，而 error 出现时只想简单的 panic 即可。所以 template 包中提供了`Must()`函数来进行封装简化，只返回需要的 Template 值：

```go
func Must(t *Template, err error) *Template {
  if err != nil {
    panic(err)
  }
  return t
}
```

利用这个函数就能够简化很多调用的写法：

```go
t := template.Must(template.New("name").Parse("text"))
```

### 3.8 Funcs

`Funcs()`方法可以为模板组指定自定义函数，而且自定义函数的优先级高于内置函数的优先级。所以如果自定义函数的名称和内置函数名称相同，则内置函数将失效。

`Funcs()`方法接收一个 FuncMap 类型的参数，所以理论上是可以设置任何类型的自定义函数的。但是如果自定义函数的返回值不是 1 个或 2 个，而且第二个返回值不是 error 类型时，模板渲染会自动停止。

自定义的函数可以有零个或多个参数，如果需要多个参数，需要在调用函数时自行决定好传入参数的顺序。

注意：**必须在解析之前调用`Funcs()`方法，这样才能在解析的时候将自定义函数放进 common 结构中，否则自定义函数不生效。**

