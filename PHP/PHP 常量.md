### DIRECTORY_SEPARATOR
DIRECTORY_SEPARATOR 是一个返回跟操作系统相关的路径分隔符的 php 内置命令，在 windows 上返回`\`，而在 linux 或者类 unix 上返回`/`，就是这么个区别，通常在定义包含文件路径或者上传保存目录的时候会用到。

虽然在 Window 中也能够将`/`识别为路径分隔符，但是有时候也会出现问题。所以建议使用该常量。

