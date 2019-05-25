Python 是一门解释型语言，无需编译和链接即可执行。Python 解释器可以交互的使用，这使得试验语言的特性、编写临时程序或在自底向上的程序开发中测试方法非常容易。甚至还可以把它当做一个桌面计算器。

> 可以参考对比 PHP、JavaScript 语言。
> 
> Python 解释器和 PHP 命令行解释器，或 Node 命令行类似。

## 一、注释

注释是用于对代码做备注的，是用来给开发人员查看的，注释的内容不会被解析执行。

### 1.1 单行注释

Python 中单行注释使用`#`，从该符号开始，直至该行(不是编辑器的自动换行)结尾，都表示注释内容。

需要注意的是，单行注释可以从行首开始，也可以在空白或代码之后，但是字符串中的`#`符号不表示注释，仅仅表示是一个`#`符号。

如下示例：

```python
# this is the first comment
spam = 1  # and this is the second comment
          # ... and now a third!
text = "# This is not a comment because it's inside quotes."
```

### 1.2 多行注释

除了单行注释，还可以使用多行注释来书写一块的注释内容。多行注释使用三个双引号开始`"""`，到下一次出现三个双引号结束。

```python
"""
这里是多行注释
用来说明代码的作用
就是打印 Hello, World.
"""

print('Hello, world.')
```

## 二、语法

Python 的基本语法书写方式为：

* 语句组使用缩进代替开始和结束大括号来组织；
* 变量或参数无需声明；
* 语句结尾不需要分号

### 2.1 基础格式

Python 的代码可以写在一个单独的文件中，文件的扩展名为`.py`，然后可以通过在命令行中输入`python filename.py`来进行运行该程序，也可以直接执行这个文件，但是需要在文件的第一行指定运行环境。

下面就是一个简单的模板(假定文件名为`python-test.py`)：

```python
#!/usr/bin/env python
# coding: utf-8

"""
计算 19+2*4-8/2 的值
"""

a = 19 + 2 * 4 - 8 / 2
print(a)
```

然后就可以在命令行中通过下面的命令执行：`python python-test.py`。如果给该文件赋予了可执行权限，还可以直接通过`./python-test.py`这个命令来执行。

开头的两句需要注意：

1. `#!/usr/bin/env python`

    如果使用`python filename.py`方式执行，这一行可以不需要，即使写了也会忽略；如果是使用`./filename.py`方式执行，则这一行是必须写的，它能够引导程序找到 Python 的解析器。
    
    > 需要注意的是，如果需要使用 Python3，这一行就需要写成`#!/usr/bin/env/ python3`。

2. `#coding: utf-8`

    这一行是告诉 Python，本程序采用的编码格式是 utf-8。也可以指定为其他的编码。

### 2.2 运算符优先级

下面的表格将 Python 中用到的与运算符有关的都列出来了，是按照**从低到高**的顺序列出的。要注意的是运算中的绝杀：括号。只要有括号，就先计算括号里面的。

运算符                 | 描述
--------------------- | -------
`lambda`              | Lambda 表达式
`or`                  | 逻辑或
`and`                 | 逻辑与
`not x`               | 逻辑非
`in` `not in`         | 成员测试
`is` `is not`         | 同一性测试
`<` `<=` `>` `>=` `!=` `==` | 比较
`\`                   | 按位或
`^`                   | 按位异或
`&`                   | 按位与
`<<` `>>`             | 移位
`+` `-`               | 加法与减法
`*` `/` `%`           | 乘法、除法与取余
`+x` `-x`             | 正负号
`~x`                  | 按位翻转
`**`                  | 指数
`x.attribute`         | 属性参考
`x[index]`            | 下标
`x[index:index]`      | 寻址段
`f(arguments...)`     | 函数调用
`(experession, ...)`  | 绑定或元组显示
`[expression, ...]`   | 列表显示
`{key:datum, ...}`    | 字典显示
`'expression, ...'`   | 字符串转换

操作符的优先级很重要，不小心就会造成奇怪的结果。比如，因为`**`的优先级高于`-`，所以`-3**2`将解释为`-(3**2)`且结果为`-9`。为了避免这点并得到`9`，可以使用`(-3)**2`。

## 三、数值

Python 中的数值可以分为有符号数、无符号数；整数、长整型、浮点数。

除了`int`和`float`，Python 还支持其它数字类型，例如 Decimal 和 Fraction。Python 还内建支持**复数**，使用后缀`j`或`J`表示虚数部分（例如`3+5j`）。

Python 中除了常见的运算符操作和内建函数外，标准库中有一个模块 Math，该模块提供了很多数学操作函数和变量，如`math.pi`、`math.pow()`等。使用的时候，不需要安装，只需要使用`import Math`引入该模块即可。

### 3.1 数学运算符

数学运算有多种，常见的是四则运算：`+`、`-`、`*`、`/`，分别表示加减乘除。

两个数值进行四则运算时，如果有浮点数存在，那么结果就是浮点数；如果是除法运算，在 Python3 中，结果总是浮点数，但在 Python2 中，如果除数和被除数都是整数，结果则会被取整(直接去掉小数，而不是四舍五入)，如`2 / 5`在 Python2 中结果就是 0。

除了常见的四则运算，还有其他的一些算术运算：

* `%` 取余数
* `//` 除法运算后取整数部分，如`8 // 5 = 1`
* `**` 计算幂值，如`5 ** 3 = 125`

当然，还可以通过 Python 的 Math 模块来实现更多运算。

### 3.2 浮点数的不准确现象

由于计算机中二进制与十进制转换的特性，会出现有些浮点数不能被准确的表示的现象，这并非是 bug，如：

```python
>>> 10 / 3
3.3333333333333335
>>> 0.1 + 0.2
0.30000000000000004
>>> 0.1 + 0.1 - 0.2
0.0
>>> 0.1 + 0.1 + 0.1 - 0.3
5.551115123125783e-17
>>> 0.1 + 0.1 + 0.1 - 0.2
0.10000000000000003
```

上面的例子中，输入的是十进制，计算机就要把十进制的数转化为二进制，然后再计算。但是，在转化中，浮点数转化为二进制，就出问题了。例如十进制的 0.1，转化为二进制是：`0.0001100110011001100110011001100110011001100110011...`。

也就是说，转化为二进制后，不会精确等于十进制的 0.1。同时，计算机存储的位数是有限制的，所以就出现上述现象了。

对于需要非常精确的情况，可以使用 decimal 模块，它实现的十进制运算适合会计方面的应用和高精度要求的应用。另外 fractions 模块支持另外一种形式的运算，它实现的运算基于有理数（因此像1/3这样的数字可以精确地表示）。最高要求则可是使用由 SciPy 提供的 Numerical Python 包和其它用于数学和统计学的包。

### 3.3 溢出

在计算机中，数值是不能无限大的，这是由于计算机的特性决定的。但是在 Python 中，我们并不需要特别在意溢出问题，因为 Python 都已经处理好了：

```shell
>>> 123456789870987654321122343445567678890098876 * 1233455667789990099876543332387665443345566
152278477193527562870044352587576277277562328362032444339019158937017801601677976183816L
```


## 四、字符串

Python 中字符串使用单引号(`'...'`)或双引号(`"..."`)标识，并用`\`来表示转义。这和其他语言中的规则基本一致。

```python
>>> '"Isn\'t," she said.'
'"Isn\'t," she said.'
>>> print('"Isn\'t," she said.')
"Isn't," she said.
>>> s = 'First line.\nSecond line.'  # \n means newline
>>> s  # without print(), \n is included in the output
'First line.\nSecond line.'
>>> print(s)  # with print(), \n produces a new line
First line.
Second line.
```

Python 字符串不可以被更改 — 它们是**不可变的**。因此，赋值给字符串索引的位置会导致错误：

```python
>>> word[0] = 'J'
  ...
TypeError: 'str' object does not support item assignment
>>> word[2:] = 'py'
  ...
TypeError: 'str' object does not support item assignment
```

内置函数`len()`返回字符串长度：

```python
>>> s = 'supercalifragilisticexpialidocious'
>>> len(s)
34
```

### 4.1 原始字符串

在前面的示例中中，`\`会将其他字符进行转义从而出现特殊的结果，那么如果想要该符号不进行转义，可以在字符串前面加上字符`r`来表示原始字符串。如下：

```python
>>> print('C:\some\name')  # here \n means newline!
C:\some
ame
>>> print(r'C:\some\name')  # note the r before the quote
C:\some\name
```

### 4.2 多行字符串

字符串文本能够分成多行。一种方法是使用三引号：`"""..."""`或者`'''...'''`。每行行尾换行符会被自动包含到字符串中，但是可以在行尾加上`\`来避免这个行为。下面的示例，可以使用反斜杠为行结尾的连续字符串，它表示下一行在逻辑上是本行的后续内容：

```python
print("""\
Usage: thingy [OPTIONS]
     -h                        Display this usage message
     -H hostname               Hostname to connect to
""")
```

将生成以下输出（注意，没有开始的第一行，但最后有一个空行）：

```python
Usage: thingy [OPTIONS]
     -h                        Display this usage message
     -H hostname               Hostname to connect to

```

### 4.3 拼接字符串

字符串可以由`+`操作符连接(粘到一起)，可以由`*`表示重复：

```python
>>> 3 * 'un' + 'ium'  # 3 times 'un', followed by 'ium'
'unununium'
```

相邻的两个字符串文本自动连接在一起，但是该特性只用于两个字符串文本，不能用于字符串表达式。如果你想连接多个变量或者连接一个变量和一个字符串文本，使用`+`：

```python
>>> 'Py' 'thon'
'Python'
>>> prefix = 'Py'
>>> prefix 'thon'  # can't concatenate a variable and a string literal
  ...
SyntaxError: invalid syntax
>>> ('un' * 3) 'ium'
  ...
SyntaxError: invalid syntax
```

### 4.4 索引

字符串可以像列表一样被索引，字符串的第一个字符索引为 0 。Python没有单独的字符类型；一个字符就是一个简单的长度为1的字符串。

```python
>>> word = 'Python'
>>> word[0]  # character in position 0
'P'
>>> word[5]  # character in position 5
'n'
```

索引也可以是负数，这将导致从右边开始计算。例如：

```python
>>> word[-1]  # last character
'n'
>>> word[-2]  # second-last character
'o'
>>> word[-6]
'P'
```

> 请注意 -0 实际上就是 0，所以它不会导致从右边开始计算。

试图使用太大的索引会导致错误：

```python
>>> word[42]  # the word only has 6 characters
Traceback (most recent call last):
  File "<stdin>", line 1, in <module>
IndexError: string index out of range
```

### 4.5 切片

除了索引，还支持*切片*。索引用于获得单个字符，切片用于获得一个子字符串：

```python
>>> word[0:2]  # characters from position 0 (included) to 2 (excluded)
'Py'
>>> word[2:5]  # characters from position 2 (included) to 5 (excluded)
'tho'
```

> 切片时，包含切片的起始位置，不包括切片的结束位置。这使得`s[:i] + s[i:]`永远等于`s`。

切片的索引有非常有用的默认值：省略的第一个索引默认为零，省略的第二个索引默认为切片的字符串的大小。

一个过大的索引值(即下标值大于字符串实际长度)将被字符串实际长度所代替，当上边界比下边界大时(即切片左值大于右值)就返回空字符串

```python
>>> word[:2]  # character from the beginning to position 2 (excluded)
'Py'
>>> word[4:]  # characters from position 4 (included) to the end
'on'
>>> word[-2:] # characters from the second-last (included) to the end
'on'
>>> word[4:42]
'on'
>>> word[42:]
''
```

### 4.6 方法

由于 Python 中所有值都是对象，所以字符串也可以直接后面跟着`.method()`方法进行处理，这和 JavaScript 中的使用类似。不过 Python 中提供了相当多的字符串函数。

## 五、列表

Python 中列表是一个很常用的*复合*数据类型，可以写作中括号之间的一列逗号分隔的值。列表的元素不必是同一类型：

```python
>>> squares = [1, 4, 9, 16, 25]
>>> squares
[1, 4, 9, 16, 25]
```

列表是*可变的*，它允许修改元素：

```python
>>> cubes = [1, 8, 27, 65, 125]  # something's wrong here
>>> 4 ** 3  # the cube of 4 is 64, not 65!
64
>>> cubes[3] = 64  # replace the wrong value
>>> cubes
[1, 8, 27, 64, 125]
```

内置函数`len()`同样适用于列表：

```python
>>> letters = ['a', 'b', 'c', 'd']
>>> len(letters)
4
```

允许嵌套列表(创建一个包含其它列表的列表)，例如：

```python
>>> a = ['a', 'b', 'c']
>>> n = [1, 2, 3]
>>> x = [a, n]
>>> x
[['a', 'b', 'c'], [1, 2, 3]]
>>> x[0]
['a', 'b', 'c']
>>> x[0][1]
'b'
```

### 5.1 切片

就像字符串(以及其它所有内建的*序列*类型)一样，列表可以被索引和切片：

```python
>>> squares[0]  # indexing returns the item
1
>>> squares[-1]
25
>>> squares[-3:]  # slicing returns a new list
[9, 16, 25]
```

所有的切片操作都会返回一个包含请求的元素的新列表。但这是一个**浅拷贝**的子列表，也就是如果列表中的值是引用对象，那么子列表中的这个值也是引用相同的对象。

也可以对切片赋值，此操作可以改变列表的尺寸，或清空它：

```python
>>> letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g']
>>> letters
['a', 'b', 'c', 'd', 'e', 'f', 'g']
>>> # replace some values
>>> letters[2:5] = ['C', 'D', 'E']
>>> letters
['a', 'b', 'C', 'D', 'E', 'f', 'g']
>>> # now remove them
>>> letters[2:5] = []
>>> letters
['a', 'b', 'f', 'g']
>>> # clear the list by replacing all the elements with an empty list
>>> letters[:] = []
>>> letters
[]
```

### 5.2 拼接

列表也支持连接这样的拼接操作：

```Python
>>> squares + [36, 49, 64, 81, 100]
[1, 4, 9, 16, 25, 36, 49, 64, 81, 100]
```

还可以使用`append()`方法在列表的末尾添加新的元素：

```python
>>> cubes.append(216)  # add the cube of 6
>>> cubes.append(7 ** 3)  # and the cube of 7
>>> cubes
[1, 8, 27, 64, 125, 216, 343]
```

## 六、模块

Python 中有很多别人写好的模块(类似其他语言中的类库)，可以引入特定的模块来实现某种功能，或者引入某种特性。

当安装好 Python 之后，就有一些模块默认安装了，称之为“标准库”。“标准库”中的模块不需要安装，就可以直接使用。如果没有纳入标准库的模块，需要安装之后才能使用。模块的安装方法，推荐使用 pip 来安装。

引入模块的方式有两种：

* `import module_name` 直接引入整个模块；
* `from module_parent import module_child` 从父模块中引入某个子模块。

## 七、其他

### 7.1 文件编码

默认情况下，Python3 的源文件是 UTF-8 编码，而 Python2 的则是 ASCII 编码。

> Python 标准库仅使用 ASCII 字符做为标识符，这只是任何可移植代码应该遵守的约定。

也可以为源文件指定不同的字符编码。为此，在`#!`行（首行）后插入至少一行特殊的注释行来定义源文件的编码：

```Python
# -*- coding: utf-8 -*-
```

### 7.2 交互模式下的 _

交互模式中，最近一个表达式的值赋给变量`_`。这样我们就可以把它当作一个桌面计算器，很方便的用于连续计算，例如：

```shell
>>> tax = 12.5 / 100
>>> price = 100.50
>>> price * tax
12.5625
>>> price + _
113.0625
>>> round(_, 2)
113.06
>>> 'a' + 'b'
'ab'
>>> _
'ab'
```

此变量对于用户是**只读**的。不要尝试给它赋值 —— 你只会创建一个独立的同名局部变量，它屏蔽了系统内置变量的魔术效果。


