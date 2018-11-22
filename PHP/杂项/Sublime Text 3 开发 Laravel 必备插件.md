Sublime Text 中有很多的插件可以帮助我们更好的开发 Laravel 项目。下面就是一些比较常用的插件及其简单介绍。

需要注意：安装插件时，需要已经在 Sublime Text 中安装好了 Package Control 插件。按章好之后就可以通过`Cmd/Ctrl + Shift + P`来打开对话框，并搜索选中`Package Control: Install Package`后，即可搜索相关的插件了。

### Laravel Blade Hightlighter

[Package 主页](https://packagecontrol.io/packages/Laravel%20Blade%20Highlighter)

该插件用于为 Laravel 4 & 5 项目中的 Blade 语法提供代码高亮。支持后缀为`.blade`和`.blade.php`的文件。如果安装后，Laravel 中的 Blade 模板中的相关语法没有高亮显示，可以重启下 Sublime Text 来解决。

效果如下：

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505363991925.png" width="617"/>
</div>

### Blade Snippets

[Package 主页](https://packagecontrol.io/packages/Blade%20Snippets)

该插件提供了 Laravel 中 Blade 语法的一些语法片段，内置了很多的代码片段，具体的可以查看其文档。

安装之后，在编辑 Blade 模板文件的时候，输入对应的短语，就会有相关的提示信息出现，选择要使用的语法后，按下`Enter`键就会自动完成语法。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/blade.gif"/>
</div>

### Laravel 5 Artisan

[Package 主页](https://packagecontrol.io/packages/Laravel%205%20Artisan)

这个插件使得我们可以直接在 Sublime Text 的界面中执行 Laravel 的 Artisan 命令。安装好之后，就可以通过`Cmd/Ctrl + Shift + P`快捷键调出对话框，输入 Laravel Artisan 相关的命令后就可以回车执行了。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505373414980.png" width="600"/>
</div>

#### Laravel 5 Snippets

[Package 主页](https://packagecontrol.io/packages/Laravel%205%20Snippets)

该插件可以使我们能更方便快捷的写 Laravel 代码，包含了很多的 Laravel 代码片段。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505373785896.png" width="755"/>
</div>

### EditorConfig

[Package 主页](https://packagecontrol.io/packages/EditorConfig)

该插件提供了通过`.editorconfig`文件来定义编辑器的编码支持。最常见的就是针对不同类别的文件使用不同的缩进，比如：Python 使用四个空格进行缩进，JavaScript 使用`Tab`缩进等。这样能够使得项目在不同的编辑器中都有相同的阅读体验。

在`.editorconfig`文件中，可以使用的指令有：

* root | 指明该文件为对顶层的 EditorConfig 文件，值为`true`。
* indent_style | 缩进格式，值可以为`tab`、`space`。
* indent_size | 缩进的空格数，值可以为正整数。
* end_of_line | 行尾结束符，可以为`lf`、`crlf`、`cr`。
* charset | 文件编码，常见的一般设置为`utf-8`。
* trim_trailing_whitespace | 是否删除行尾多余的空格，值可以为`true`、`false`。
* insert_final_newline | 是否在文件末尾添加一个空行，值可以为`true`、`false`。

具体的使用方式，可以查看 [EditorConfig 官网](http://editorconfig.org/)

### Editor Config Snippets

[Package 主页](https://packagecontrol.io/packages/EditorConfigSnippets)

该插件可以用于生成常用的一些 EditorConfig 配置代码。支持如下的指令：

* editor-base
* editor-bash
* editor-c
* editor-cpp
* editor-frontend
* editor-go
* editor-javascript
* editor-md
* editor-php
* editor-perl
* editor-python
* editor-ruby
* editor-txt

当在一个`.editorconfig`文件中输入这些指令中的某一个时，将会有一个提示窗口，选中后按`Enter`键就会自动生成对应的配置。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505367074443.png" width="358"/>
</div>


### Alignment

提供了一个自动对齐快捷键，主要是用于美化代码的作用。快捷键是`command + control + a`。示例如下：

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/eIBowAw.gif"/>
</div>

