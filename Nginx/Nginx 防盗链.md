
防盗链主要是通过 referer 指令来过滤图片请求的。
如果请求图片的 referer 不是指定的网站域名，或者没有 referer，那么就不能访问图片，也不能下载了。

```nginx
location ~* \.(gif|jpg|jpeg|png)$ {
	expires 30d;
	valid_referers *.lin07ux.dev, *.baidu.com;
	if ($invalid_referer) {
		return 404;
	}
}
```












