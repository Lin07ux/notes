> [Mac 键盘快捷键](https://support.apple.com/zh-cn/HT201236)

## 一、基础

### 1.1 键盘符号

* `↖︎` Home 键 对应 fn + 左方向键
* `↘︎` End 键  对应 fn + 右方向键
* `^`  Control 键

### 1.2 更改文件默认打开程序

点击文件 - 右键 - 显示简介 - 打开方式 - 选择合适的程序之后 - 全部更改。

## 二、快捷键

### 2.1 一般

- `选中文件按空格预览` 预览图片或者pdf文件时有用。配合方向键可以快速预览多张图片。
- `短按电源键` 可以关闭屏幕，但不能太短，多按几次就有手感了。

### 2.2 删除快捷键

* `Delete + Fn` 逐个删除光标右侧的字符；
* `Delete + Option` 删除光标左侧的整个单词；
* `Delete + Fn + Option` 删除光标右侧的整个单词；
* `Command + delete` 删除当前行。选中文件的时候直接删除文件。

### 2.3 截图快捷键

- `cmd + shift + 3` 截取整个屏幕。
- `cmd + shift + 4` 截取部分区域。会出现十字供选取，若此时按空格键，会选取当前应用的窗口，再按空格会取消选中当前窗口。

上面快捷键是截图后以文件形式保存在桌面(默认是桌面，当然你也可以自己修改保存位置)。在上面快捷键基础上再同时按 ctrl 就会把图片保存在内存/剪贴板中，直接去相应窗口粘贴即可。

当然，也可以在`设置 - 键盘 - 快捷键 - 屏幕快照`中设置新的截图快捷键。

在开启区域模式并选中一个区域，松开鼠标按键前，还可以调整区域的大小：
 
* 按住 "空格" 并移动鼠标，就可以保持区域大小不变，同时移动区域
* 按住 "Shift" 并移动鼠标，就可以保持区域的其他三个边不变，移动一个边的位置
* 按住 "Alt" 并移动鼠标，就可以对称地调整区域大小

**禁用提示音**

截图提示音是可以关闭的，在`设置 - 声音 - 声音效果`中，将 "播放用户界面操作声音" 关闭掉即可。

### cmd

- `cmd + ,` 设置当前 active 的程序。
- `cmd + -/+` 缩小/放大。
- `cmd + 0` 恢复当前窗口到初始大小(无放大和缩小)。
- `cmd + w` 关闭当前 active 窗口。
- `cmd + t` 新建tab，支持tab模式的应用一般都支持这个快捷键。比如在 safari，firefox，chrome 下新建tab。
- `cmd + n` 新建窗口，比如打开新的 Finder 窗口，配合`cmd + w`很实用。
- `cmd + q` 退出当前应用(不能退出 Finder)。
- `cmd + i` 显示当前文件的信息，查看文件大小，图片宽高的时候有用。
- ``cmd + ` `` 切换同一应用的窗口。
- `cmd + tab` 切换应用。

### ctrl

- `ctrl + a` 光标定位到当前行的最前端。
- `ctrl + e` 光标定位到当前行的最尾端。
- `ctrl + u` 删除到行首。(在 zsh 中是删除整行)
- `ctrl + k` 删除到行尾。
- `ctrl + p/n` 上/下移动一行或者前/后一个命令
- `ctrl + b/f` 光标前/后移 char
- `esc + b/f` 光标前/后移 word(蛋疼不能连续执行，必须松开全部按键重新按)
- `ctrl + a/e` 到行首/行尾
- `ctrl + h/d` 删前/后字符
- `ctrl + y` 粘贴
- `ctrl + w` 删除前一个单词
- `esc + d` 删后一个单词
- `ctrl + _` undo
- `ctrl + r` bck-i-search/reverse-i-search, 输入关键字搜索历史命令

> 上面的这些快捷键特别是在敲命令时还是很有用的(可能有的确实是在命令行中才生效)。

### 剪切文件

- 首先选中文件，按`Command + C`复制文件；
- 然后按`Command + Option + V`就可以把你的文件剪走了！

> `Command + X`只能剪切文字文本之类的

### 延时截图及 Grab 应用

有时候我们需要延时截图，macOS 为我们提供了这个功能，只是藏的比较深。可以直接用 macOS 自带的 Spotlight 搜索到，直接输入 Grab 即可：

![](http://cnd.qiniu.lin07ux.cn/markdown/1487773429516.png)

回车之后没有打开任何窗口，你可能觉得刚刚是打开了假的 App。但其实，这个 App 本身就是没有 UI 的，只会在 Menu Bar 上面显示一条菜单。我们可以在这里找到延时截图的选项：

![](http://cnd.qiniu.lin07ux.cn/markdown/1487773476125.png)

点击之后，按照提示操作就好，默认的延时时间是 10 秒。要注意的是，延时截图会截取整个屏幕，不能截区域。我们可以先把图片保存下来，然后再打开 Preview（预览）App 裁剪就好了.

默认的延时时间和保存路径都是可以更改的。只要打开 Terminal（终端），然后输入这个命令并执行：

```shell
screencapture -T 10 screenshot1.jpg
```

其中，10 就表示延时十秒，"screenshot1" 就是默认的文件名，你可以把它改成其他文件名，也可以给它加上一个文件夹路径用于设置默认的保存位置






