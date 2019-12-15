> 转摘：[面试官：如果 http 响应头中 ETag 值改变了，是否意味着文件内容一定已经更改](https://segmentfault.com/a/1190000021273854)

### 1. 解答

Nginx 响应中的 HTTP Etag 头发生了变化时，并不一定意味着对应的文件也发生了变化。

Nginx 的 Etag 的值是由文件的`last_modified`和响应的`content_length`组成，而`last_modified`又由`mtime`组成。当编辑文件却未更改文件内容时，其`mtime`也会改变，此时文件内容未发生变化而 Etag 的值却改变了。

### 2. 为什么使用`mtime`而不是`ctime`

文件系统中，一个文件有`mtime`和`ctime`两个时间。

在 Linux 系统中：

* `mtime`表示`modified time`，也就是文件内容改变的时间戳。
* `ctime`表示`change time`，也就是指文件属性改变的时间戳，这些属性包括`mtime`。

在 Windows 系统中：

* `mtime`和 Linux 系统中的`mtime`一样
* `ctime`表示的则是`creation time`，也就是文件的创建时间。

所以文件的`mtime`属性才能正确的反应文件内容是否发生了变化。由此属性生成的 Etag 才能更好的反应文件是否需要更新。



