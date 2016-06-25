## 通过 referer 判断
防盗链主要是通过 referer 指令来过滤图片请求的。
如果请求图片的 referer 不是指定的网站域名，或者没有 referer，那么就不能访问图片，也不能下载了。

```conf
location ~* \.(gif|jpg|jpeg|png)$ {
	expires 30d;
	valid_referers *.lin07ux.dev *.baidu.com;
	if ($invalid_referer) {
		return 404;
	}
}
```

参考：[nginx实现图片防盗链(referer指令)](http://www.ttlsa.com/nginx/nginx-referer/)

延伸：[nginx secure_link下载防盗链](http://www.ttlsa.com/nginx/nginx-modules-secure_link/)

## 语法
语法：`valid_referers none | blocked | server_names | string ...;`

默认值：`—`

配置段：`server`, `location`

可能值：`none`、`blocked`、`<server_names>`、`<arbitrary string>`、`regular expression`

作用：指定合法的来源'referer', 他决定了内置变量`$invalid_referer`的值，如果 referer 头部包含在这个合法网址里面，这个变量被设置为 0，否则设置为 1。记住，不区分大小写。

参数值解释：

- `none`  Referer 来源头部为空的情况
- `blocked` Referer 来源头部不为空，但是里面的值被代理或者防火墙删除了，这些值都不以`http://`或者`https://`开头。
- `server_names` Referer 来源头部包含当前的`server_names`（当前域名）
- `arbitrary string` 任意字符串，定义服务器名或者可选的 URI 前缀。主机名可以使用 * 开头或者结尾，在检测来源头部这个过程中，来源域名中的主机端口将会被忽略掉。
- `regular expression` 正则表达式，`~`表示排除`https://`或`http://`开头的字符串。












