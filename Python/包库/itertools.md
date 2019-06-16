itertools 是一个包含很多面向可迭代对象的工具函数集。

[官方文档](https://docs.python.org/zh-cn/3.7/library/itertools.html)

## 方法

### product

`product()`方法返回可迭代对象的笛卡尔乘积，大致相当于生成器表达式中的嵌套循环。例如`product(A, B)`和`((x,y) for x in A for y in B)`返回结果一样。

该方法可以用于简化循环的嵌套，比如，从 3 个数字列表中，寻找是否存在和为 12 的 3 个数，用一般的循环方式如下：

```Python
def find_twelve(num_list1, num_list2, num_list3):
    """从 3 个数字列表中，寻找是否存在和为 12 的 3 个数"""
    for num1 in num_list1:
        for num2 in num_list2:
            for num3 in num_list3:
                if num1 + num2 + num3 == 12:
                    return num1, num2, num3
```

而改用`product()`方法之后，就可以简化成一个循环了：

```Python
from itertools import product


def find_twelve_v2(num_list1, num_list2, num_list3):
    for num1, num2, num3 in product(num_list1, num_list2, num_list3):
        if num1 + num2 + num3 == 12:
            return num1, num2, num3
```

### islice

从迭代器中按照规则挑选部分数据组成新的迭代器并返回，可用来从内部数据结构被压平的数据中提取相关字段（例如一个多行报告，它的名称字段出现在每三行上）。

语法如下：

```Python
itertools.islice(iterable, start, stop[, step])
```

该方法有四个参数：

* `iterable` 被处理的迭代器。
* `start` 默认为 0，如果传入 None 则使用默认值。如果该参数不为 0，则跳过 iterable 中的元素，直到到达 start 这个位置。
* `stop` 默认为迭代器的长度，如果传入 None 则会耗光迭代器的元素为止，否则，在该参数指定的位置停止。
* `step` 每次处理之后跳过的元素。如果没有传入，或者为 None，则默认为 1。

与普通的切片不同，`islice()`不支持将`start`、`stop`、`step`设为负值。

比如，有一个文件的内容如下：

```
python-guide: Python best practices guidebook, written for humans.
---
Python 2 Death Clock
---
Run any Python Script with an Alexa Voice Command
---
<... ...>
```

在这份文件里的每两个标题之间，都有一个`---`分隔符。现在需要获取文件里所有的标题列表，所以在遍历文件内容的过程中，必须跳过这些无意义的分隔符。可以通过在迭代时获取当前下标，并对下标进行比对的方式进行处理，也可以使用`islice`方法先处理内容，再进行迭代：

```Python
from itertools import islice

def parse_titles(filename):
    with open(filename, 'r') as fp:
        # 设置 step=2，跳过无意义的 '---' 分隔符
        for line in islice(fp, 0, None, 2):
            yield line.strip()
```

### takewhile 在指定条件下返回迭代器元素

`takewhile`方法可以设置一个条件，该条件会对迭代器中的每个元素进行执行，如果直接结果为真，则会返回该元素，否则就会跳过该元素，语法如下：

```Python
itertools.takewhile(predicate, iterable)
```

其实现大致相当于：

```Python
def takewhile(predicate, iterable):
    for x in iterable:
        if predicate(x):
            yield x
        else:
            break
```

所以，`takewhile`可以简化遍历迭代器时的逻辑，避免判断条件逻辑杂糅在循环中。比如：

```Python
from itertools import takewhile

for user in takewhile(is_qualified, users):
    # 当 user 符合 is_qualified 中的判断逻辑，则进行处理 ... ...
```


