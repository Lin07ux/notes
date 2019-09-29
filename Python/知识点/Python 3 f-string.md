f-string 出现在 Python 3.6，是当前最佳的拼接字符串的形式，它允许直接在字符串中使用变量、表达式，方便了字符串的拼接操作。

语法格式如下：

```
f'<text> { <expression> <optional !s, !r, or !a> <optional : format specifier> } <text> ... '
```

### 1. expression

在 f-string 中，`<expression>`可以是任意的变量名、表达式。

比如：

```Python
first_name = 'Lin07ux'
second_name = 'Lin'

print(f"My name is: {first_name} {second_name}")
# My name is: Lin07ux Lin
```

### 2. modifier

在 f-string 中，还可以对 expression 使用修饰符。

修饰符有如下三个：

* `!r` 表示对表达式调用`repr()`函数
* `!a` 表示对表达式调用`ascii()`函数
* `!s` 表示对表达式调用`str()`函数

默认情况下，f-string 将使用`str()`来转换表达式，但如果包含转换标志，则可以确保它们使用`repr()`来进行转换。

比如：

```Python
class Comedian:
    def __init__(self, first_name, last_name, age):
        self.first_name = first_name
        self.last_name = last_name
        self.age = age
        
    def __str__(self):
        return f"{self.first_name} {self.last_name} is {self.age}."
    
    def __repr__(self):
        return f"{self.first_name} {self.last_name} is {self.age}. Surprise!"

new_comedian = Comedian("Eric", "Idle", "74")
print(f"{new_comedian}")   # Eric Idle is 74.
print(f"{new_comedian!s}") # Eric Idle is 74.
print(f"{new_comedian!r}") # Eric Idle is 74. Surprise!
print(f"{new_comedian!a}") # Eric Idle is 74. Surprise!
```

### 3. =

PyCon2019 有人提出的一个展望`!d`的功能，在 Python 3.8 中已经实现，但不是使用`!d`作为修饰符，而是使用`f"{a=}"`的形式。

比如：

```Python
a = 5
print(f"a={a}")  # a=5
print(f"{a=}")   # a=5
```

可以发现，这个符号可以很方便的实现同时输出变量名和变量值。

### 4. format specifier


