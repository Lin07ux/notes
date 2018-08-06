Python 中内置了一个`hashlib`模块，提供了很多摘要算法，如 MD5、SHA1 等。

> 摘要算法又称哈希算法、散列算法。它通过一个函数，把任意长度的数据转换为一个长度固定的数据串（通常用 16 进制的字符串表示）。

### 一般的使用方式

```py
import hashlib

data = 'This is a md5 test!'
print(hashlib.md5(data.encode('utf-8')).hexdigest())

data = 'This is a sha1 test!'
print(hashlib.sha1(data.encode('utf-8')).hexdigest())
```

这样就会输出如下内容：

```
0a2c0b988863f08471067903d8737962
63d8242da8214b3028624397be3ee20f3f8e3372
```

### 处理大数据量

如果要处理的数据量很大，那么直接使用上面的方式就会造成内存紧张了。这时就需要分块多次调用`update()`，最后计算的结果是一样的：

```py
import hashlib

md5 = hashlib.md5()
md5.update('This is a '.encode('utf-8'))
md5.update('md5 test!'.encode('utf-8'))
print(md5.hexdigest())
```

输出的内容也是`0a2c0b988863f08471067903d8737962`。

对于`sha1`也是同样的使用方式。


