## 一、基础

字符串是 Python 中的基本数据类型。所谓字符串，就是由零个或多个字符组成的有限序列。

### 1.1 定义

Python 中，字符串可以使用单引号、双引号和三引号(三个单引号或三个双引号)来定义：

```python
# 单引号
s1 = 'hello, world!'

# 双引号
s2 = "hello, world!"

# 三引号：三引号定义的字符串可以折行
s3 = """
hello,
world!
"""

print(s1, s2, s3, end='')
```

输出效果类似如下：

```
hello, world! hello, world!
hello,
world!
```

### 1.2 转义

可以在字符串中使用`\`(反斜杠)来表示转义，也就是说`\`后面的字符不再是它原来的意义，而是代表一些特殊的表示。比如：

* `\n`表示换行符
* `\t`表示制表符
* `\\`表示`\`
* `\'`表示`'`字符，而不是字符串的起始和结束定义符
* `\"`同`\'`

```python
s1 = '\'hello, world!\''
s2 = '\n\\hello, world!\\\n'

print(s1, s2, end='')
```

输出如下：

```
'hello, world!'
\hello, world!\
```

### 1.3 八进制、十六进制和 Unicode 字符

字符串中在`\`后面跟随一个八进制或者十六进制数值可以用来表示对应编码的字符，还可以使用`u`开头的 Uincode 字符编码来表示字符。

比如`\141`和`\x61`分别表示用八进制和十六进制表示的小写字母`a`，`\u9a86\u660a`表示的中文字符`骆昊`。

```Python
s1 = '\141\142\143\x61\x62\x63'
s2 = '\u9a86\u660a'

print(s1, s2)
```

输出如下：

```
abcabc 骆昊
```

### 1.4 原始字符串

可以通过在字符串的定义前面添加`r`来表示这个字符串是原始字符串，不需要进行转义。此时，`\`就表示`\`字符，而没有转义功能了。

```python
s1 = r'\'hello, world!\''
s2 = r'\n\\hello, world!\\\n'
s3 = r'\u9a86\u660a'

print(s1, s2, s3, end='')
```

输出结果如下：

```
\'hello, world!\' \n\\hello, world!\\\n \u9a86\u660a
```

可以看到其中转义字符(如`\n`)和 Unicode 字符(如`\u9a86`)都没有被转义和解析，而且原样输出了。

## 二、操作处理

### 2.1 运算符

Python 为字符串类型提供了非常丰富的运算符：

* `+`运算符实现字符串的拼接
* `*`运算符重复一个字符串的内容指定次数
* `in`运算符来判断一个字符串是否存在于另一个字符串中
* `not in`运算符来判断一个字符串是否不存在与另一个字符串中
* `[]`和`[:]`运算符对字符串进行切片

```Python
s1 = 'hello ' * 3  # hello hello hello
s1 += 'world'      # hello hello hello world

'll' in s1   # True
'good' in s1 # False

s2= 'abc123456'
s2[2]     # c
s2[2:5]   # c12
s2[2:]    # c123456
s2[2::2]  # c246
s2[::2]   # ac246
s2[::-1]  # 654321cba
s2[-3:-1] # 45
```

### 2.2 方法

在 Python 中，还可以通过一系列的方法来完成对字符串的处理：

```Python
str1 = 'hello, world!'

# 计算字符串的长度
len(str1) # 13

# 获得字符串首字母大写的拷贝
str1.capitalize() # Hello, world!

# 获得字符串每个单词首字母大写的拷贝
str1.title() # Hello, World!

# 获得字符串中每个字符都变成大写之后的拷贝
str1.upper() # HELLO, WORLD!

# 从字符串中查找子串所在位置
str1.find('or')   # 8
str1.find('shit') # -1

# 与 find 类似但找不到子串时会引发异常
str1.index('or')   # 8
str1.index('shit') # ValueError: substring not found

# 检查字符串是否以指定的字符串开头(大小写敏感)
str1.startswith('He')  # False
str1.startswith('hel') # True

# 检查字符串是否以指定的字符串结尾(大小写敏感)
str1.endswith('!') # True

# 将字符串以指定的宽度居中并在两侧填充指定的字符
str1.center(20, '*') # ***hello, world!****

# 将字符串以指定的宽度靠右放置，并在左侧填充指定的字符
str1.rjust(20, '*')  # *******hello, world!

# 将字符串以指定的宽度靠左放置，并在右侧填充指定的字符
str1.ljust(20, '*')# hello, world!*******

str2 = 'abc123456'

# 检查字符串是否由数字构成
str2.isdigit() # False

# 检查字符串是否由字母构成
str2.isalpha() # False

# 检查字符串是否由字母和数字构成
str2.isalnum() # True

str3 = '  jackfrued@126.com '

# 获得字符串修剪左右两侧空格之后的拷贝
str3.strip() # jackfrued@126.com
```

### 2.3 格式化

字符串中可以设置一些标记，并通过格式化替换这些标记，生成一个新的字符串。比如：

```Python
a, b = 5, 10

# 使用内置的标记符，%d 表示整数，%s 表示字符串
'%d * %d = %d' % (a, b, a * b)        # 5 * 10 = 50

# 使用字符串内置的 format 方法
'{0} * {1} = {2}'.format(a, b, a * b) # 5 * 10 = 50

# Python 3.6 新增的格式化方法，在字符串前加上字母`f`即可，相对来说更简单
f'{a} * {b} = {a * b}'                # 5 * 10 = 50
```


