> 转摘：[快速上手](https://2.python-requests.org//zh_CN/latest/user/quickstart.html)
> 
> 更多用法详见 [高级用法](https://2.python-requests.org//zh_CN/latest/user/advanced.html) 和 [身份认证](https://2.python-requests.org//zh_CN/latest/user/authentication.html)

Requests 是 Python 中用于 HTTP 网络请求的类库，用 Python 编写，比 urllib2 模块更简洁，使用 Apache2 licensed 许可证。

Requests 支持 HTTP 连接保持和连接池，支持使用 cookie 保持会话，支持文件上传，支持自动响应内容的编码，支持国际化的 URL 和 POST 数据自动编码。Requests 在 Python 内置模块的基础上进行了高度的封装，从而使得 Python 进行网络请求时更人性化，可以轻而易举的完成浏览器可有的任何操作。

官方文档：[Requests: 让 HTTP 服务人类](https://2.python-requests.org#zh_CN/latest/index.html)

## 一、安装与引入

Requests 可以使用 pip 进行安装：

```shell
pip install requests
```

使用 Requests 时，主需要引入 requests 即可：

```python
import requests

r = requests.get('https://api.github.com/events')
```

## 二、请求

Requests 支持全部的 HTTP 请求类别，使用方式也很简单：

```python[贵州阿里云](media/%E8%B4%B5%E5%B7%9E%E9%98%BF%E9%87%8C%E4%BA%91.txt)
requests.head(url, **kwargs)
requests.options(url, **kwargs)
requests.get(url, params=None, **kwargs)
requests.post(url, data=None, json=None, **kwargs)
requests.put(url, data=None, **kwargs)
requests.patch(url, data=None, **kwargs)
requests.delete(url, **kwargs)
```

每种请求方式都必须提供一个 url，不同的方法还可以有相应的其他参数。

另外，除了必须的参数外，每个请求都还支持更多其他的可选参数，以最大化的配置请求：

* `headers` 设置请求头
* `cookies` 会话 cookie
* `files` 上传文件
* `auth` 认证方式
* `timeout` 超时时间
* `allow_redirects` 是否允许自动跳转，默认情况下，除了 HEAD 之外的请求，Requests 都会自动处理所有重定向
* `proxies` 请求代理
* `verify` 是否验证服务器的证书
* `stream` 是否将响应生成数据流，如果是 false，那么响应内容会被立即完全下载
* `cert` 客户端证书

### 2.1 请求头

如果想为请求添加 HTTP 头部，只要简单地传递一个 dict 给请求方法的 headers 参数就可以了。

比如，添加一个 UserAgent 请求头：

```python
url = 'https://api.github.com/some/endpoint'
headers = {'user-agent': 'my-app/0.0.1'}

r = requests.get(url, headers=headers)
```

**注意: 定制 header 的优先级低于某些特定的信息源**。例如：

* 	如果在`.netrc`中设置了用户认证信息，使用 headers 设置的授权就不会生效。而如果设置了`auth`参数，`.netrc`的设置就无效了。
* 如果被重定向到别的主机，授权 header 就会被删除。
* 代理授权 header 会被 URL 中提供的代理身份覆盖掉。
* 在能判断内容长度的情况下，header 的`Content-Length`会被改写。

更进一步讲，Requests 不会基于定制 header 的具体情况改变自己的行为。只不过在最后的请求中，所有的 header 信息都会被传递进去。

> 所有的 header 值必须是 string、bytestring 或者 unicode。尽管传递 unicode header 也是允许的，但不建议这样做。

### 2.2 Cookie

要想发送 cookies 到服务器，可以使用请求方法的 cookies 参数：

```python
url = 'http://httpbin.org/cookies'
cookies = dict(cookies_are='working')

r = requests.get(url, cookies=cookies)
r.text
# '{"cookies": {"cookies_are": "working"}}'
```

还可以把 Cookie Jar 传到 Requests 中。RequestsCookieJar 对象和字典类似，但接口更为完整，适合跨域名跨路径使用。

```python
jar = requests.cookies.RequestsCookieJar()
jar.set('tasty_cookie', 'yum', domain='httpbin.org', path='/cookies')
jar.set('gross_cookie', 'blech', domain='httpbin.org', path='/elsewhere')

r = requests.get('http://httpbin.org/cookies', cookies=jar)
r.text
# '{"cookies": {"tasty_cookie": "yum"}}'
```

### 2.3 超时

可以设置请求的`timeout`参数，使 Requests 在经过 timeout 秒之后停止等待响应。基本上所有的生产代码都应该使用这一参数。如果不使用，程序可能会永远失去响应。

请求超时时，会抛出`requests.exceptions.Timeout`异常：

```python
requests.get('http://github.com', timeout=0.001)
# Traceback (most recent call last):
#   File "<stdin>", line 1, in <module>
# requests.exceptions.Timeout: HTTPConnectionPool(host='github.com', port=80): Request timed out. (timeout=0.001)
```

> 注意：
> timeout 仅对连接过程有效，与响应体的下载无关。timeout 并不是整个下载响应的时间限制，而是如果服务器在 timeout 秒内没有应答，将会引发一个异常（更精确地说，是在 timeout 秒内没有从基础套接字上接收到任何字节的数据时）。

### 2.4 GET 请求参数

GET 请求中，可以添加一个字典参数作为请求参数，字典中的数据会被自动编码，并附加到 url 中，如下：

```python
payload = {'key1': 'value1', 'key2': 'value2'}
r = requests.get("http://httpbin.org/get", params=payload)

print(r.url)
# http://httpbin.org/get?key2=value2&key1=value1
```

还可以将一个列表作为值传入：

```python
payload = {'key1': 'value1', 'key2': ['value2', 'value3']}
r = requests.get('http://httpbin.org/get', params=payload)

print(r.url)
# http://httpbin.org/get?key1=value1&key2=value2&key2=value3
```

**注意：字典里值为 None 的键都不会被添加到 URL 的查询字符串里。**

### 2.5 POST 请求

HTTP POST 请求允许在请求体中提供数据作为请求内容，而请求内容可以有多种格式。Requests 的 post 方法也支持使用不同的格式提交数据。

#### 2.5.1 表单提交 x-www-form-data

如果要使用 HTML 表单提交的方式提交 POST 数据，只需要传递一个字典给 post 方法的 data 参数即可：

```python
payload = {'key1': 'value1', 'key2': 'value2'}
r = requests.post("http://httpbin.org/post", data=payload)

print(r.text)
# {
#   ...
#   "form": {
#     "key2": "value2",
#     "key1": "value1"
#   },
#   ...
# }
```

还可以为 data 参数传入一个元组列表。在表单中多个元素使用同一 key 的时候，这种方式尤其有效：

```python
payload = (('key1', 'value1'), ('key1', 'value2'))
r = requests.post('http://httpbin.org/post', data=payload)

print(r.text)
# {
#   ...
#   "form": {
#     "key1": [
#       "value1",
#       "value2"
#     ]
#   },
#   ...
# }
```

#### 2.5.2 JSON

如果要使用 JSON 格式的数据进行请求，那么可以给 post 方法的 json 参数提供一个字典：

```python
url = 'https://api.github.com/some/endpoint'
payload = {'some': 'data'}
r = requests.post(url, json=payload)
```

或者，如果给 post 方法的 data 参数传递的是一个 string，那么也会被当做 json 进行请求：

```python
r = requests.post(url, data=json.dumps(payload))
```

#### 2.5.3 Multipart-Encoded

在需要上传文件的时候，就需要采用这种格式传递数据了。此时，只需要设置 file 参数即可：

```python
url = 'http://httpbin.org/post'
files = {'file': open('report.xls', 'rb')}

r = requests.post(url, files=files)
r.text
# {
#   ...
#   "files": {
#     "file": "<censored...binary...data>"
#   },
#   ...
# }
```

> 强烈建议用二进制模式(binary mode)打开文件。这是因为 Requests 可能会试图提供 Content-Length header，在它这样做的时候，这个值会被设为文件的字节数（bytes）。如果用文本模式(text mode)打开文件，就可能会发生错误。

也可以显式地设置文件名，文件类型和请求头：

```python
url = 'http://httpbin.org/post'
files = {'file': ('report.xls', open('report.xls', 'rb'), 'application/vnd.ms-excel', {'Expires': '0'})}

r = requests.post(url, files=files)
r.text
# {
#   ...
#   "files": {
#     "file": "<censored...binary...data>"
#   },
#   ...
#  }
```

也可以发送作为文件内容的字符串：

```python
url = 'http://httpbin.org/post'
files = {'file': ('report.csv', 'some,data,to,send\nanother,row,to,send\n')}

r = requests.post(url, files=files)
r.text
# {
#   ...
#   "files": {
#     "file": "some,data,to,send\\nanother,row,to,send\\n"
#   },
#   ...
# }
```

如果要发送一个非常大的文件作为`multipart/form-data`请求，可能希望将请求做成数据流。默认下 Requests 不支持, 但有个第三方包 requests-toolbelt 是支持的，可以阅读 [toolbelt 文档](https://toolbelt.rtfd.org/) 来了解使用方法。

## 三、响应

任何时候进行了类似`requests.get()`的调用，都在做两件主要的事情：

1. 构建一个 Request 对象，该对象将被发送到某个服务器请求或查询一些资源。
2. 一旦 requests 得到一个从服务器返回的响应就会产生一个 Response 对象。该响应对象包含服务器返回的所有信息，也包含前面创建的 Request 对象。

### 3.1 解码

Requests 会自动解码来自服务器的内容。大多数 unicode 字符集都能被无缝地解码。请求发出后，Requests 会基于 HTTP 头部对响应的编码作出有根据的推测。当访问响应对象的`text`属性时，Requests 会使用其推测的文本编码。可以找出 Requests 使用了什么编码，并且能够使用响应对象的`encoding`属性来改变它：

```python
import requests

r = requests.get('https://api.github.com/events')
r.text
# u'[{"repository":{"open_issues":0,"url":"https://github.com/...

r.encoding
'utf-8'
r.encoding = 'ISO-8859-1'
```

如果手工改变了编码，每当你访问响应的`text`属性时，Request 都将会使用`encoding`属性的新值来解析数据。

如果希望在使用特殊逻辑计算出文本的编码的情况下来修改编码，比如 HTTP 和 XML 自身可以指定编码。这样的话，应该使用响应的`content`属性获取内容来找到编码，然后设置`encoding`属性为相应的编码。这样就能使用正确的编码解析响应的文本`text`了。

在需要的情况下，Requests 也可以使用定制的编码。如果创建了自己的编码，并使用`codecs`模块进行注册，就可以轻松地使用这个解码器名称作为响应的`encoding`属性的值，然后由 Requests 来处理编码。

### 3.2 二进制响应

也可以使用二进制方式访问请求响应体：

```python
import requests

r = requests.get('https://api.github.com/events')
r.content
# b'[{"repository":{"open_issues":0,"url":"https://github.com/...
```

Requests 还会自动解码 gzip 和 deflate 传输编码的响应数据。例如，以请求返回的二进制数据创建一张图片，可以使用如下代码：

```python
from PIL import Image
from io import BytesIO

i = Image.open(BytesIO(r.content))
```

### 3.3 JSON 响应

Requests 中也有一个内置的 JSON 解码器，以处理 JSON 数据：

```python
import requests

r = requests.get('https://api.github.com/events')
r.json()
# [{u'repository': {u'open_issues': 0, u'url': 'https://github.com/...
```

如果 JSON 解码失败，`r.json()`就会抛出一个异常。例如，响应内容是 401 (Unauthorized)时，尝试访问`r.json()`将会抛出`ValueError: No JSON object could be decoded`异常。

需要注意的是，成功调用`r.json()`并**不**意味着响应的成功。有的服务器会在失败的响应中包含一个 JSON 对象（比如 HTTP 500 的错误细节）。这种 JSON 会被解码返回。要检查请求是否成功，请使用`r.raise_for_status()`或者检查`r.status_code`是否和期望相同。

### 3.4 原始响应

如果想要获取来自服务器的原始套接字响应，那么可以访问响应的`raw`属性。

> 请确保在初始请求中设置了`stream=True`。

```python
import requests

r = requests.get('https://api.github.com/events', stream=True)
r.raw
# <requests.packages.urllib3.response.HTTPResponse object at 0x101194810>
r.raw.read(10)
# '\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\x03'
```

一般情况下，应该以下面的模式将文本流保存到文件：

```python
with open(filename, 'wb') as fd:
    for chunk in r.iter_content(chunk_size):
        fd.write(chunk)
```

使用`Response.iter_content`将会处理大量直接使用`Response.raw`不得不处理的。当流下载时，上面是优先推荐的获取内容方式。`chunk_size`参数则可以是任何适合的值，没有特定的要求。

### 3.5 响应状态码

可以使用`Response.status_code`属性方便的获取响应状态码。为方便引用，Requests 还附带了一个内置的状态码查询对象：

```python
r = requests.get('http://httpbin.org/get')
r.status_code
# 200

r.status_code == requests.codes.ok
# True
```

如果请求的响应失败(一个 4XX 客户端错误，或者 5XX 服务器错误响应)，可以通过`Response.raise_for_status()`来抛出异常：

```python
bad_r = requests.get('http://httpbin.org/status/404')
bad_r.status_code
# 404

bad_r.raise_for_status()
# Traceback (most recent call last):
#  File "requests/models.py", line 832, in raise_for_status
#    raise http_error
# requests.exceptions.HTTPError: 404 Client Error
```

当`status_code = 200`的时候，调用这个方法则会返回 None：

```python
r = requests.get('http://httpbin.org/get')
r.raise_for_status()
# None
```

### 3.6 响应头

`Response.headers`是一个响应头字典，包含全部的响应头信息，但是这个字典比较特殊：它是仅为 HTTP 头部而生的。根据 RFC 2616， HTTP 头部是大小写不敏感的。因此，可以使用任意大写形式来访问这些响应头字段：

```python
r.headers
# {
#     'content-encoding': 'gzip',
#     'transfer-encoding': 'chunked',
#     'connection': 'close',
#     'server': 'nginx/1.0.4',
#     'x-runtime': '148ms',
#     'etag': '"e1ca502697e5c9317743dc078f67693f"',
#     'content-type': 'application/json'
# }

r.headers['Content-Type']
# 'application/json'

r.headers.get('content-type')
# 'application/json'
```

### 3.7 响应 Cookie

可以使用`Response.cookies`属性访问响应设置的 Cookie。

```python
url = 'http://example.com/some/cookie/setting/url'
r = requests.get(url)
r.cookies['example_cookie_name']
# 'example_cookie_value'
```

### 3.8 请求历史

`Response.history`是一个 Response 对象的列表，包含请求过称中的重定向响应。这个对象列表按照从最老到最近的请求进行排序。

如果在初始化请求时，不允许重定向(`allow_redirects=False`)，那么`Response.history`属性就是一个空列表。

例如，Github 将所有的 HTTP 请求重定向到 HTTPS：

```python
r = requests.get('http://github.com')
r.url
# 'https://github.com/'

r.status_code
# 200

r.history
# [<Response [301]>]
```

## 四、错误与异常

* `ConnectionError` 遇到网络问题（如：DNS 查询失败、拒绝连接等）。
* `HTTPError` 如果 HTTP 请求返回了不成功的状态码， Response.raise_for_status() 会抛出一个该异常。
* `Timeout` 请求超时。
* `TooManyRedirects` 请求超过了设定的最大重定向次数。

所有 Requests 显式抛出的异常都继承自`requests.exceptions.RequestException`。


