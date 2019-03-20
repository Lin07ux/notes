## 快捷键：

- `Shift + Command + A` 全局设置搜索，比如可以搜索 keymap 快速进入到快捷键设置。
- `Command + E` 查看最近查看的文件，多次按这个快捷键可以依次选中更前面的查看的文件。
- `Command + Shift + O` 跳转到任意文件。
- `Ctrl + Command + M` 查看一个类中的方法，并且支持根据输入的内容来过滤方法(原本的快捷键是`Command + F12`)。
- `Command + B` 查看一个类的源码定义，需要光标在类的名称上，和按住`Command`后点击类名的效果一样。
- `Command + D` 快速选中当前文件中与当前选中的单词相同的下一个单词。(原始快捷键是`Ctrl + G`)
- `Ctrl + Command + D` 快速选中当前文件中与当前选中的单词相同的所有单词。(原始快捷键是`Ctrl + Command + G`)
- `Command + Shift + -` 折叠全部代码
- `Command + Shift + +` 打开全部代码，取消折叠

### Tips

- 快捷键设置中，可以点击搜索边上按钮，然后输入快捷键来搜索对应的设置。

### 关闭参数提示

PHPStorm 2017.1 增加了很多新功能，有个默认开启的参数名和类型提示功能，虽然功能挺强大的，不过一般用不着，还是关掉的好。可以如下进行关闭：

1. 打开全局设置搜索快捷键：`Shift + Command + A`；
2. 搜索`parameter name hints`；
3. 在搜索的结果中，一般第一行就可以直接切换参数名提示的状态，选中该行之后按`Enter`键即可关闭了。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505355668866.png" width="277" />
</div>

## 开发
### 预览 PHP 文件

PHPStorm 可以直接运行一个 PHP 文件，并在浏览器中打开页面，显示输出的内容。不过要运行 PHP 文件需要先设置一下 PHP Interpreter(PHP 解析器)。

> 如果未设置 PHP Interpreter，在打开的 PHP 文件后，直接点击 PHPStorm 中的浏览器图标的后，会提示错误，让你设置 PHP Interpreter。

设置 PHP Interpreter 只需要在设置中，Language & Frameworks 分组中找到 PHP，点击即可显示出设置的地方。

![](http://cnd.qiniu.lin07ux.cn/markdown/1481686076788.png)

默认没有设置，可以点击图中的红框中的按钮，进入设置页面。

![](http://cnd.qiniu.lin07ux.cn/markdown/1481686914470.png)

虽然 MacOS 中默认是安装了 PHP 的，不过由于 PHPStorm 需要使用 CGI 的方式解析 PHP，直接使用 MacOS 自带的 PHP 解析器的时候，打开预览页面会出现 502 错误。所以我们需要自行安装 PHP 和 PHP-fpm，并将自己安装的解析器设置为 PHPStorm 默认的解析器。

## 问题

### 无法对类、方法等进行提示

PHPStorm 突然对类名、方法、变量等不再提示，可能是由于缓存导致的，可以尝试清除缓存并重启：`File --> Invalidate Caches/Restart`。

还有可能是开启了省电模式，可以查看是否开启：`File --> Power Save Mode`，如果开启了(前面有勾号)则关闭即可。

