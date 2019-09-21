Python 3.8 中对可迭代对象的解包语法进行了增强，使其可以设置一个接收所有解包之后的剩下的未赋值给常规命名的值。具体可以参看：[PEP 3132 -- Extended Iterable Unpacking](https://www.python.org/dev/peps/pep-3132/)。

具体示例如下：

```Python
a, *b, c = range(5)
a # 0
c # 4
b # [1, 2, 3]

*a, = range(5)
a # [0, 1, 2, 3, 4]

for a, *b in [(1, 2, 3), (4, 5, 6, 7)]:
    print(b)
# [2, 3]
# [5, 6, 7]
```

也可以将可迭代解包语法运行的结果直接用在`return`和`yield`语句中，这在 Python 3.8 之前是不可行的：

```Python
def a():
    rest = (4, 5, 6)
    return 1, 2, 3, *rest

def b():
    rest = (4 , 5, 6)
    yield 1, 2, 3, *rest

for i in b():
    print(i)

# 1, 2, 3, 4, 5, 6
```

这段代码在 Python 3.8 之前会抛出 SyntaxError 错误，而 Python 3.8 解决了这个 bug。

