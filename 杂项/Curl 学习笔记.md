> 练习使用 Curl 的时候，可以使用 [httpbin.org](httpbin.org) 作为请求对象，该网站提供客户端测试 http 请求的服务，返回 json 数据，具体可以查看他的网站。

### GET
直接以 GET 方式请求一个 url，会返回该 url 返回的原始内容，类似于在浏览器中查看到的源码：

```shell
curl http://httpbin.org
```

返回类似如下信息：

```
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv='content-type' value='text/html;charset=utf8'>
  <meta name='generator' value='Ronn/v0.7.3 (http://github.com/rtomayko/ronn/tree/0.7.3)'>
  <title>httpbin(1): HTTP Client Testing Service</title>
  <style type='text/css' media='all'>
  /* style: man */
  body#manpage {margin:0}
  ...
```

### 其他请求类型
使用`--request`可以指定请求类型，--data 指定数据，例如：

```shell
curl http://httpbin.org/post --request POST
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474261155375.png" width="643"/>


### 附加请求数据
如果在发送 POST、PUT 等请求的时候，需要提供一些数据给服务器，可以使用`--data`选项来添加数据：

```shell
# 在命令行中输入 url 的时候，?、=、& 会被转义
curl http://httpbin.org/get?a=1&b=2
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474261384702.png" width="676"/>

> 对于 GET 请求不可使用`--data`选项附加数据，只能使用 URL 编码方式附加在 URL 后面。
> <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474261178171.png" width="638"/>


### form 表单提交
form 表单提交使用`--form`选项，并可使用`@`指定本地文件。

例如我们提交一个表单，有字段 name 和文件 f：

```shell
curl http://httpbin.org/post --form "name=Lin07ux" --form "f=@/Users/Lin07ux/Downloads/8132527.jpeg"
```

显示结果类似如下：

```
{
  "args": {},
  "data": "",
  "files": {
    "f": "data:image/jpeg;base64,........"
  },
  "form": {
    "name": "Lin07ux"
  }, 
  "headers": {
    "Accept": "*/*", 
    "Content-Length": "29207", 
    "Content-Type": "multipart/form-data; boundary=------------------------f1dbcd4ea5f23944", 
    "Host": "httpbin.org", 
    "User-Agent": "curl/7.43.0"
  }, 
  "json": null, 
  "origin": "202.107.200.100", 
  "url": "http://httpbin.org/post"
}
```

### 显示头信息
使用`--include`选项在输出中包含头信息，使用`--head`选项只返回头信息，例如：

```shell
curl httpbin.org/post --include --request POST --data 'name=Lin07ux'
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474262006810.png" width="568"/>

或者，只显示头信息：

```shell
curl http://httpbin.org --head
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474262265252.png" width="674"/>

> `--head` 选项貌似没有办法用在其他类型的请求上，否则会报错。


### 设置头信息
使用`--header`选项设置头信息，`httpbin.org/headers`会显示请求的头信息：

```shell
curl http://httpbin.org/headers --header 'nickname: Lin07ux' --header 'user-agent: spdb'
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474262771939.png" width="735"/>

> 设置的 header 是以键值对的方式传入的，header 名称和其值之间使用`:`分割，`:`后面可以有空格，会被自动过滤掉，但是前面不能有空格。另外，传入的 header 名称会自动转成首字母大写的情况。


### 设置 Referer 字段
设置 Referer 字段很简单，使用`--referer`，例如：

```shell
curl http://httpbin.org/headers --referer http://a.b.com
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474262873039.png" width="637"/>


### 包含 cookie
使用`--cookie`选项来设置请求的 cookie，例如：

```shell
curl http://httpbin.org/headers --cookie "name=Lin07ux;website=http://a.b.com"
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474262971121.png" width="645"/>


### http 认证
当页面需要认证时，可以使用`--user`选项来添加认证信息：账号和密码，例如：

```shell
curl http://lin07ux.org/basic-auth/lin07ux/123456 --user lin07ux:123456
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474263113473.png" width="670"/>


### 自动跳转
使用`--location`选项的时候，如果访问的页面发生跳转，则其会跟随链接的跳转，例如：

```shell
curl http://httpbin.org/redirect/1 --location
```

由于`http://httpbin.org/redirect/1`会 302 跳转到`http://httpbin.org/get`。所以，如果加上`--location`会返回`http://httpbin.org/get`的内容，而不加的话，就不会自动跳转了：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474263298927.png" width="863"/>


### 显示详细的通信过程
使用`--verbose`选项可以显示详细的通信过程。

```shell
curl http://httpbin.org/post --verbose --request POST --data "name=Lin07ux"
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474262427174.png" width="641"/>


### 转摘
1. [httpbin(1): HTTP Request & Response Service](http://httpbin.org/)
2. [告别Postman&ARC&DHC，拥抱curl](http://keenwon.com/1576.html)

