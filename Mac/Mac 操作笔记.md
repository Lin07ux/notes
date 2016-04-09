
### 粘贴板
命令行中粘贴板可以使用`pbcopy`来调用，比如将文件内容拷贝到粘贴板中：
`pbcopy > ~/.ssh/id_rsa.pub`

### 命令行清屏
`Ctrl + L`
`clear`

### 切换终端
使用 chsh 命令可以更改默认的终端，比如切换到 zsh 终端：
`chsh -s /bin/zsh`

### 命令行打开应用
* `open /path/to/some.app`   打开指定路径中的指定应用
* `open "path/to/file.ext"`  使用默认的应用打开指定的文件
* `open /path/`              在 Finder 中打开指定路径
* `open -a /path/to/some.app "/path/to/file.ext"`   使用指定应用打开指定文件
* `open -e "/path/to/file.ext"`  使用 TextEdit 打开指定文件
* `open http://www.apple.com/`   使用默认浏览器打开网址

### 设置 host
Mac 系统的 host 文件位于：`/etc/hosts`。

### 修改 hostname($HOST)
在终端中，一般会显示当前电脑的电脑名或者 $HOST。

修改 $HOST 的方法为：`sudo scutil --set hostname [ newname | newname.local ]`。

> 如果出现使用 newname 方式重命名之后，不能访问网络，可以设置为 newname.local 方式试试。

### vim
删除所有内容：`:%d`

## 键盘符号
* `↖︎`  Home键 对应  fn + 左方向键
* `↘︎`  End键  对应  fn + 右方向键
* `^`   Control键










