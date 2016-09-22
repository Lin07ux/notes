## 键盘符号
* `↖︎`  Home键 对应  fn + 左方向键
* `↘︎`  End键  对应  fn + 右方向键
* `^`   Control键

## 快捷键
- `cmd + delete` 删除当前行。选中文件的时候直接删除文件。
- `ctrl + a` 光标定位到当前行的最前端。
- `ctrl + e` 光标定位到当前行的最尾端。
- `cmd + tab` 切换应用。
- ``cmd + ` `` 切换同一应用的窗口。
- `cmd + w` 关闭当前窗口。
- `cmd + q` 关闭当前应用。
- `cmd + n` 新建窗口，比如打开新的Finder窗口，配合cmd+w很实用。
- `cmd + t` 新建tab，支持tab模式的应用一般都支持这个快捷键。比如在safari，firefox，chrome下新建tab。
- `cmd + i` 显示当前文件的信息，查看文件大小，图片宽高的时候有用。
- `选中文件按空格预览` 预览图片或者pdf文件时有用。配合方向键可以快速预览多张图片。
- `短按电源键` 可以关闭屏幕，但不能太短，多按几次就有手感了。

### 剪切文件
- 首先选中文件，按`Command+C`复制文件；
- 然后按`Command＋Option＋V`就可以把你的文件剪走了！

> `Command+X`只能剪切文字文本之类的

### 更改文件默认打开程序
点击文件 - 右键 - 显示简介 - 打开方式 - 选择合适的程序之后 - 全部更改。

### Mac OS X 升级之后，在终端执行命令出现错误
从 App Store 升级或更新 Mac OS X 之后，在终端中执行某些命令的时候，会出现如下的错误：

```
xcrun: error: invalid active developer path (/Library/Developer/CommandLineTools), missing xcrun at: /Library/Developer/CommandLineTools/usr/bin/xcrun
```

这时候可以在终端中执行如下的命令即可解决：

```shell
xcode-select --install
```

参考：[xcrun: error](http://tips.tutorialhorizon.com/2015/10/01/xcrun-error-invalid-active-developer-path-library-developer-commandline-tools-missing-xcrun/)

