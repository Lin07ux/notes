## 快捷键：

- `Shift + Command + A` 全局设置搜索，比如可以搜索 keymap 快速进入到快捷键设置。
- `Command + E` 查看最近查看的文件，多次按这个快捷键可以依次选中更前面的查看的文件。
- `Command + Shift + O` 跳转到任意文件。
- `Ctrl + Command + M` 查看一个类中的方法，并且支持根据输入的内容来过滤方法(原本的快捷键是`Command + F12`)。
- `Command + B` 查看一个类的源码定义，需要光标在类的名称上，和按住`Command`后点击类名的效果一样。
- `Command + D` 快速选中当前文件中与当前选中的单词相同的下一个单词。(原始快捷键是`Ctrl + G`)
- `Ctrl + Command + D` 快速选中当前文件中与当前选中的单词相同的所有单词。(原始快捷键是`Ctrl + Command + G`)


### Tips

- 快捷键设置中，可以点击搜索边上按钮，然后输入快捷键来搜索对应的设置。


## 开发
### 预览 PHP 文件
PHPStorm 可以直接运行一个 PHP 文件，并在浏览器中打开页面，显示输出的内容。不过要运行 PHP 文件需要先设置一下 PHP Interpreter(PHP 解析器)。

> 如果未设置 PHP Interpreter，在打开的 PHP 文件后，直接点击 PHPStorm 中的浏览器图标的后，会提示错误，让你设置 PHP Interpreter。

设置 PHP Interpreter 只需要在设置中，Language & Frameworks 分组中找到 PHP，点击即可显示出设置的地方。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1481686076788.png)

默认没有设置，可以点击图中的红框中的按钮，进入设置页面。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1481686914470.png)

虽然 MacOS 中默认是安装了 PHP 的，不过由于 PHPStorm 需要使用 CGI 的方式解析 PHP，直接使用 MacOS 自带的 PHP 解析器的时候，打开预览页面会出现 502 错误。所以我们需要自行安装 PHP 和 PHP-fpm，并将自己安装的解析器设置为 PHPStorm 默认的解析器。

