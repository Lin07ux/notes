### DIRECTORY_SEPARATOR

DIRECTORY_SEPARATOR 是一个返回跟操作系统相关的路径分隔符的 php 内置命令，在 windows 上返回`\`，而在 linux 或者类 unix 上返回`/`，就是这么个区别，通常在定义包含文件路径或者上传保存目录的时候会用到。

虽然在 Window 中也能够将`/`识别为路径分隔符，但是有时候也会出现问题。所以建议使用该常量。

### 其他

* `__FILE__` 当前 PHP 文件的相对路径
* `__LINE__` 当前 PHP 文件中所在的行号
* `__FUNCTION__` 当前函数名，只对函数内调用起作用
* `__CLASS__` 当前类名，只对类起作用
* `__METHOD__` 表示类方法名，比如 B::test
* `PHP_VERSION` 当前使用的 PHP 版本号
* `PHP_OS` 当前 PHP 环境的运行操作系统
* `E_ERROR` 最近的错误之处
* `E_WARNING` 最近的警告之处
* `E_PARSE` 剖析语法有潜在问题之处
* `$_SERVER` 返回服务器相关信息，返回一个数组
* `$_GET` 所有 GET 请求过来的参数
* `$_POST` 所有 POST 过来的参数
* `$_COOKIE` 所有 HTTP 提交过来的 cookie
* `$_FILES` 所有 HTTP 提交过来的文件
* `$_ENV` 当前的执行环境信息
* `$_REQUEST` 相当于`$_POST`、`$_GET`、`$_COOKIE`提交过来的数据，因此这个变量不值得信任
* `$_SESSION` session 会话变量

