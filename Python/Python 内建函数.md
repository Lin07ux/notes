### 一、 常用函数

#### 1. id 获取对象内存地址

每个对象在内存中都有自己的一个地址，这个就是它的身份。可以通过`id()`函数来获取对象的内存地址。

> `id()`得到的结果是只读的，不可修改。

```Shell
>>> id(3)
140574872
>>> id(3.222222)
140612356
>>> id(3.0)
140612356
>>>
```

#### 2. type 获取对象类型

Python 中每个对象都是某种类型的一个实例，可以通过`type()`函数来获取对象的类型。`type()`得到的结果也是只读的。

> Python3 中`type()`的到的结果是以`class`开头的，而 Python2 中则是以`type`开头的。

```shell
>>> type(3)
<class 'int'>
>>> type(3.0)
<class 'float'>
>>> type('a')
<class 'str'>
```

#### 3. dir 获取模块的功能或对象的属性

`dir(module)`是一个非常有用的指令，可以通过它查看任何模块中所包含的工具。比如，下面的命令就可以看出，在 Math 模块中，存在可以计算`sin(a)`、`cos(a)`、`sqrt(a)`的方法：

```shell
>>> import Math
>>> dir(math)
['__doc__', '__name__', '__package__', 'acos', 'acosh', 'asin', 'asinh', 'atan', 'atan2', 'atanh', 'ceil', 'copysign', 'cos', 'cosh', 'degrees', 'e', 'erf', 'erfc', 'exp', 'expm1', 'fabs', 'factorial', 'floor', 'fmod', 'frexp', 'fsum', 'gamma', 'hypot', 'isinf', 'isnan', 'ldexp', 'lgamma', 'log', 'log10', 'log1p', 'modf', 'pi', 'pow', 'radians', 'sin', 'sinh', 'sqrt', 'tan', 'tanh', 'trunc']
```

#### 4. print 输出字符串

> 在 Python 2.x 中，`print`是一个关键词，表示输出，而在 Python 3 中则是一个函数，也是输出字符串。

`print` 进行输出时，默认会在末尾加上一个换行符，在 Python 2 中可以通过在语句末尾加上逗号的形式规避(`print 'Hello ', 'world',`)，在 Python 3 中则可以通过传入参数`end`一个值来规避(`print('Hello ', 'world', end='')`)。

如下，表示输出一个斐波那契数列：

```python
>>> a, b = 0, 1
>>> while b < 1000:
...     print(b, end=',')
...     a, b = b, a+b
...
1,1,2,3,5,8,13,21,34,55,89,144,233,377,610,987,
```

#### 5. help 查看相关函数的帮助文档

`help()`方法能够获取并展示出指定方法的使用文档。比如，下面就是查看 Math 模块中的`pow()`函数的使用方法和相关说明：

```shell
>>> import math
>>> help(math.pow)
Help on built-in function pow in module math:

pow(...)
    pow(x, y)

    Return x**y (x to the power of y).
```

1.	第一行意思是说这里是 math 模块的内建函数`pow`帮助信息（所谓 built-in，称之为内建函数，是说这个函数是 Python 默认就有的)；
2.	第三行，表示这个函数的参数，有两个，也是函数的调用方式；
3.	第四行，是对函数的说明，返回`x**y`的结果，并且在后面解释了`x**y`的含义。
4.	最后，按`q`键返回到 Python 交互模式。


### 二、数学函数

#### 1. divmod 获取除法的商和余数

使用`divmod(num1, num2)`可以计算 num1 除以 num2 后得到的商和余数。

```shell
>>> divmod(5, 2)  #表示5除以2，返回了商和余数
(2, 1)
>>> divmod(9, 2)
(4, 1)
>>> divmod(5.0, 2)
(2.0, 1.0)
```

#### 2. round 四舍五入

`round(num, length)`可以使用四舍五入法则保留参数 num 的指定位数的小数。

```shell
>>> round(1.234567, 2)
1.23
>>> round(1.234567, 3)
1.235
>>> round(10.0/3, 4)
3.3333
>>> round(1.234)
1.0
>>> round(1.2345, 3)
1.234 # 应该是：1.235
>>> round(2.235, 2)
2.23  # 应该是：2.24
```

可以看到，最后的两个四舍五入并不符合预期，这是由于二进制与十进制之间的转换造成的。

#### 3. abs 绝对值

`abs()`获取数值的绝对值。

```shell
>>> abs(10)
10
>>> abs(-10)
10
>>> abs(-1.2)
1.2
```

### 三、字符串函数

#### 1. len 字符串长度

该函数用于返回参数字符串的长度：

```python
>>> s = 'supercalifragilisticexpialidocious'
>>> len(s)
34
```

> 该函数还可以用于返回列表的长度。

