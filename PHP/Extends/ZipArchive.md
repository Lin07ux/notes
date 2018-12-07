PHP 支持多中压缩文件的读写操作，如`.bz2`、`.rar`、`.zip`、`.gz`，都有其各自的扩展包来实现相应的功能，使用前只需要在 php.ini 中打开对应的扩展即可。

ZipArchive 是 PHP 中对`.zip`文件操作的扩展类，点击可以查看[该类的相关文档](http://php.net/manual/zh/class.ziparchive.php)。

## 主要属性和方法

### 公有属性

   属性     |    说明
-----------|---------------
status     | ZipArchive 的状态
statusSys  | ZipArchive 的系统状态
numFiles   | 压缩包里的文件和文件夹总数
filename   | 在文件系统里的文件名
comment    | 压缩包的说明

### 公有方法：

     方法   	                   |    功能
---------------------------------|---------------------------
open                             | 打开一个 zip 文件(可以用于新建)
addEmptyDir                      | 添加一个新的目录
addFile                          | 添加一个文件
addFromString                    | 使用文件内容添加文件
addGlob                          | 使用 glob 模式添加文件
addPattern                       | 使用 PCRE 模式添加文件
close                            | 关闭 zip 文件(文件是打开的或新建的)
count                            | 获取压缩包中文件的数量(php > 7.2，老版本可以直接使用 numFiles 属性)
deleteIndex                      | 使用索引删除一个文件或目录
deleteName                       | 使用名称删除一个文件或目录
extractTo                        | 解压 Zip 文件
getArchiveComment                | 返回 Zip 文件的说明
getCommentIndex                  | 使用索引返回一个文件或目录的说明
getCommentName                   | 使用名称返回一个文件或目录的说明
getExternalAttributesIndex       | 使用索引检索一个文件或目录的外部属性
getExternalAttributesName        | 使用名称检索一个文件或目录的外部属性
getFromIndex                     | 使用索引返回文件的内容
getFromName                      | 使用文件名称返回文件的内容
getNameIndex                     | 使用索引返回一个文件或目录的名称
getStatusString                  | 返回错误状态消息
getStream                        | 得到一个文件处理程序中的文件或目录名称(只读)。
locateName                       | 返回文件或目录的索引
renameIndex                      | 通过索引重命名一个文件或目录
renameName                       | 通过文件名重命名一个文件或目录
setArchiveComment                | 设置 ZIP 归档的说明
setCommentIndex                  | 通过索引设置一个文件或目录的说明
setCommentName                   | 通过文件名设置一个文件或目录的说明
setCompressionIndex              | 通过索引设置一个文件或目录的压缩方法
setCompressionName               | 通过文件名设置一个文件或目录的压缩方法
setEncryptionIndex               | 通过索引设置一个文件或目录的加密方法
setEncryptionName                | 通过文件名设置一个文件或目录的加密方法
setExternalAttributesIndex       | 通过索引设置一个条目的外部属性
setExternalAttributesName        | 通过文件名设置一个条目的外部属性
setPassword                      | 为 ZIP 文件设置密码
statIndex                        | 通过索引得到一个文件或目录的详细信息
statName                         | 通过文件名得到一个文件或目录的详细信息
unchangeAll                      | 撤销所有更改
unchangeArchive                  | 恢复所有更改
unchangeIndex                    | 通过索引恢复一个文件或目录的所有更改
unchangeName                     | 通过文件名恢复一个文件或目录的所有更改

### open() 方法

`open()`方法用于打开一个新的 zip 压缩包用于读、写或修改。该方法的签名为：

```php
mixed ZipArchive::open ( string $filename [, int $flags ] )
```

参数`$filename` 为要打开的压缩包的文件名称，参数`$flags` 为打开的模式。模式有如下几种：

* `ZIPARCHIVE::CREATE (integer)` 如果不存在则创建一个 zip 压缩包。
* `ZIPARCHIVE::OVERWRITE (integer)` 总是以一个新的压缩包开始，此模式下如果已经存在则会被覆盖。
* `ZIPARCHIVE::EXCL (integer)` 如果压缩包已经存在，则出错。
* `ZIPARCHIVE::CHECKCONS (integer)` 对压缩包执行额外的一致性检查，如果失败则显示错误。

### addFile() 方法

`addFile()`方法用于添加文件到压缩包中。其签名为：

```php
bool ZipArchive::addFile ( string $filename [, string $localname = NULL [, int $start = 0 [, int $length = 0 ]]] )
```

参数说明如下：

* `filename` 要添加的文件(文件路径)
* `localname` 文件添加到压缩包后的名称，也就是在压缩包中这个文件的名称，不提供时，默认使用`filename`的文件名
* `start` 暂时未使用，但是在扩展 ZipArchive 时需使用
* `length` 暂时未使用，但是在扩展 ZipArchive 时需使用

很多时候，在添加文件的时需要使用文件的真实路径名，这样添加到压缩包时，会将整个路径都添加进去，比如，`$zip->addFile('/Users/Lin07ux/Downloads/Aliases.pdf')`，那么最终压缩包中会存在`/Users/Lin07ux/Downloads/Aliases.pdf`这样的全路径文件，为了避免这种情况，可以使用第二个参数，将路径去除，只保留文件名。

## 使用实例

### 压缩文件

创建 Zip 文件时，首先需要先实例化一个 ZipArchive 类，并通过新建方式打开一个新的压缩包，然后向其中增加文件或目录，最后直接关闭这个类，就可以得到相应的 Zip 文件了。

具体代码如下：

```php
$zip = new ZipArchive();

// 新建一个新的压缩包
$zip->open('file.zip', ZipArchive::CREATE);

// 添加空文件夹
$zip->addEmptyDir ('dir');

// 添加文件
$zip->addFile ('file1.txt');
$zip->addFromString ('output.txt', 'hello world!');

// 把 class 目录中后缀为'.php'的文件添加到了 Zip 文件中 phpclass 目录中。
$zip->addGlob('class/*.php', 0, array('add_path' => 'phpclass/', 'remove_path' => 'class'));

// 使用 PCRE 模式向 Zip 文件中添加文件
$zip->addPattern('/\.(?:php)$/', 'class', array('add_path' => 'phpclass/', 'remove_path' => 'class'));

// 关闭以保存
$zip->close();
```

### 解压缩

解压缩文件时，也需要先创建一个 ZipArchive 实例，并打开文件，然后将其解压到文件夹中。

具体代码如下：

```php
$zip = new ZipArchive();
$zip->open('file.zip');
$zip->extractTo('dir');
```

## 参考

[使用PHP压缩文件和解压文件 （ZipArchive类的使用）](https://blog.csdn.net/luoluozlb/article/details/72853885)


