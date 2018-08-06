由于未设置好编码或其他问题，导致写入到文件中的内容是一些 utf-8 编码，而不是正常的文字（中文），这时就需要想办法将其中的编码转换成文字。

可以考虑使用如下的方法：

1. 先从字符串中截取对应的编码；
2. 将其构造成类似 bytes 格式的字符串；
3. 将该字符串解析成 bytes 格式的内容；
4. 从 bytes 中获取到真实的字符。

具体操作如下：

```py
import ast

file = open('/path/to/file', 'r')

# 从文件中获取一行，并截取其中的 utf-8 编码字符串
string = file.readline()[:20]
# 将编码字符串构造成类似 bytes 格式的字符串
string = r"b'" + string + "'"
# 使用 ast 将字符串解析成 bytes 对象
bytes = ast.literal_eval(string)
# 将 bytes 对象解码成对应的文字
word = bytes.decode('utf-8')
print(word)
```

上面就是对应的实现方式，虽然可以使用 Python 自带的`eval()`函数来完成字符串到 bytes 对象的解析，但是其并不安全，建议使用 ast 库。

另外，对于直接将 utf-8 编码(或其他编码也行)的字符串进行解析的情况下，可以直接使用如下的方式：

```py
string = '\xe4\xb8\xad\xe6\x96\x87'
word = string.encode('raw_unicode_escape').decode()
```

这是因为直接赋值和从文件中读取时，Python 对其的处理不同(具体是什么样的我也不清楚)，直接赋值可以直接被当做编码过的 unicode 字符串来处理。

> 参考：[python3.x 如何从str中提取bytes？](https://www.zhihu.com/question/43161731)


