### 下载图片

可以使用流式下载：

```python
import requests

url = 'http://52kantu.cn/static/photos/full/5f483af86df016c855cb9af9b76c7f4271f700c2.jpg'
r = requests.get(url, stream=True)

with open('123.jpg', 'wb') as fd:
    for chunk in r.iter_content():
        fd.write(chunk)
```

也可以直接整个下载：

```python
import requests

url = 'http://52kantu.cn/static/photos/full/5f483af86df016c855cb9af9b76c7f4271f700c2.jpg'
r = requests.get(url)

if r.status_code == requests.code.ok:
    with open('logo.jpg', 'wb') as image:
        image.write(r.content)
```

