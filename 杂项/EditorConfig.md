## EditorConfig 简介

EditorConfig 插件可以帮助开发者定义和维护统一的编码风格，并在不同的开发者和编辑器之间进行共用。

EditorConfig 项目是由一个定义编码风格的文件和一系列的文本编辑器插件组成。这些编辑器插件会读取定义文件中定义的编码样式，并以此来格式化代码。

EditorConfig 的定义文件中的内容是非常易读的，并且能够很好的被版本控制工具进行维护。

本文翻译自[EditorConfig 官网](http://editorconfig.org/)，并做了适当的节选。

## EditorConfig 定义文件的格式

### 示例

下面是一个简单的配置文件示例，每一行都是一个配置，其中配置了一些常用文件的缩进样式。

```ini
# EditorConfig is awesome: http://EditorConfig.org

# top-most EditorConfig file
root = true

# Unix-style newlines with a newline ending every file
[*]
end_of_line = lf
insert_final_newline = true

# Matches multiple files with brace expansion notation
# Set default charset
[*.{js,py}]
charset = utf-8

# 4 space indentation
[*.py]
indent_style = space
indent_size = 4

# Tab indentation (no size specified)
[Makefile]
indent_style = tab

# Indentation override for all JS under lib directory
[lib/**.js]
indent_style = space
indent_size = 2

# Matches the exact files either package.json or .travis.yml
[{package.json,.travis.yml}]
indent_style = space
indent_size = 2
```

另外，还可以查看一些真实的 [使用 EditorConfig 配置文件的项目](https://github.com/editorconfig/editorconfig/wiki/Projects-Using-EditorConfig)

### 配置文件的优先级

EditorConfig 定义的文件一般命名为`.editorconfig`，并可以存放在项目的任何目录中。当打开一个文件时，EditorConfig 的插件会自动在该目录以及该目录的所有祖先目录中查找`.editorconfig`文件。在查找过程中，如果找到的`.editorconfig`文件中有`root = true`配置，或者已经查找到了根目录中，则会停止查找了。

EditorConfig 会从顶部开始读取`.editorconfig`文件，所以在文件底部的配置的优先级高于文件顶部的配置。另外，在目录层次中，最靠近打开文件的`.editorconfig`具有最高的优先级，其配置会覆盖掉低优先级的配置。

> 对于 Windows 用户，在 Windows 的资源管理器中创建`.editorconfig`文件的时候，需要将文件的名称设置为`.editorconfig.`，然后资源管理器就会自动的将其重命名为`.editorconfig`了。

### 配置文件的规则

EditorConfig 的配置文件使用是 INI 文件格式，但是`[`和`]`符号能够出现在每个节(`section`)的名称中。该文件是使用 [Python ConfigParser Library](https://docs.python.org/2/library/configparser.html) 来进行解析的。

配置文件需要使用`utf-8`编码。

#### section 名称

配置文件中，节`section`的名称用于表示路径的规则，类似于 [gitignore](http://manpages.ubuntu.com/manpages/intrepid/man5/gitignore.5.html#contenttoc2)所能够接受的值。其中：

* `/` 用于表示路径的分隔符
* `#`和`;` 用于注释。注释需要单独放在一行中
* `CRLF`和`LF`表示行结束。

路径的通配符规则有如下几种：

| 通配符          | 规则                          |
|----------------|-------------------------------|
| `*`            | 匹配任何除了`/`的基本字符         |
| `**`           | 匹配任何由基本字符组成的字符串     |
| `?`            | 匹配一个单个的基本字符            |
| `[name]`       | 匹配任意一个属于`name`中的基本字符 |
| `[!name]`      | 匹配任意一个不属于`name`的基本字符 |
| `{s1,s2,s3}`   | 匹配`{}`中的任意一个字符串，每个字符串使用`,`分隔，从 EditorConfig Core 0.11.0 开始被支持 |
| `{num1..num2}` | 匹配处于`num1`和`num2`区间中的任一整数。这两个参数均可为正数或负数。 |

> 通配符中的特殊字符可以使用反斜线`\`来进行转义，使其成为普通字符串，而不再表示特殊含义。

#### 规则指令

EditorConfig 提供了多个指令用于配置代码样式，但是并非每个指令都能被所有的编辑器插件支持，具体的情况可以查看[complete list of properties](https://github.com/editorconfig/editorconfig/wiki/EditorConfig-Properties)。

* `indent_style` 	可选值有`tab`和`space`，用于设置缩进样式是制表符，还是用空格代替的制表符。 

* `indent_size` 可以设置为一个正整数，用于表示缩进的距离相当于几个空格。

* `tab_width` 可以设置为一个正整数，表示一个制表符有几个空格宽。默认为`indent_size`的值，所以一般并不需要单独的进行设置。

* `end_of_line` 可选值有`lf`、`cr`、`crlf`。用于设置换行符。

* `charset` 可选值有`latin1`、`utf-8`、`utf-8-bom`、`utf-16be`或者`utf-16le`。用于设置文件的编码方式。其中，`utf-8-bom`是不建议使用的。

* `trim_trailing_whitespace` 可选值为`true`和`false`，其中`true`表示移除行尾的空白字符，而`false`表示保留行尾的空白字符串。

* `insert_final_newline` 可选值为`true`和`false`，其中`true`表示自动在行尾增加一个空行，而`false`表示不添加。

* `root` 这是一个特殊的指令，应该出现在`.editorconfig`文件的最顶部，而且不能属于任何的 section 中。设置为`true`的时候，会使得 EditorConfig 不会继续向上搜索`.editorconfig`文件了。

目前所有的指令和其值都是大小写不敏感的，在处理的时候都会设置成小写。一般的，如果某个指令没有设置，则当前这个编辑器的设置就会起作用。

不设置某些 EditorConfig 指令是可接受的，而且常常是建议的。比如说，`tab_width`一般就不需要设置，除非它的值和`indent_size`是不同的。同样的，当`indent_style`设置为`tab`的时候，`indent_size`就不需要再设置了。另外，在某个项目中，如果某个指令不需要设置一个统一的标准，则应该将其值设置成空。

**注意**：对于任一指令，如果设置成`unset`，将会去除该指令的效果，即便在低优先级的`.editorconfig`文件中已经定义，或者在前面已经设置过。比如说，`indent_size = unset`会将`indent_size`设置成`undefined`，这样就会使用编辑器的设置了。


## IDE 的支持情况

下面的这些编辑器默认都能支持 EditorConfig，不需要进行额外的插件安装：

<div align="center">
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505453923495.png" width="645"/>
</div>

而下面的这些编辑器则需要单独安装插件之后才能支持：

<div align="center">
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505454046924.png" width="645"/>
</div>


