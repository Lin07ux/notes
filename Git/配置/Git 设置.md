
### Git config 配置文件

git config 文件可以位于三个地方，这三个地方的配置，后一个会覆盖前一个的设置，比如 ~/.gitconfig 会覆盖 /etc/config 的配置：

- /etc/gitconfig 文件：包含了适用于系统所有用户和所有库的值。如果你传递参数选项’--system’ 给 git config，它将明确的读和写这个文件。

- ~/.gitconfig 文件：这个是对当前登陆用户起作用的配置文件，可以通过传递 --global 选项使 Git 读写这个特定的文件。

- git 安装目录中的 config 文件。

> 在 Windows 系统中，/etc/gitconfig 会被设置为相对 Git 的安装根目录进行查找。

其他相关介绍：[Git config 配置文件](http://www.cnblogs.com/wanqieddy/archive/2012/08/03/2621027.html)

### Git 不能显示中文(或其他非英文)

当使用`git status`或者其他的 git 指令的时候，如果文件或目录中包含 utf-8 字符(如中文)，那么就会显示形如 \347\273\223.png 的乱码。

这种情况下，可以将 core.quotepath 设置为 false，就不会对 0x80 以上的字符进行 quote 了：
	`$ git config --global core.quotepath false`


