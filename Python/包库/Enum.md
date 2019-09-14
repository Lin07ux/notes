> 转摘：[Python 的枚举类型](https://segmentfault.com/a/1190000017327003)

枚举类型可以看作是一种标签或是一系列常量的集合，通常用于表示某些特定的有限集合，例如星期、月份、状态等。[PEP 435](https://www.python.org/dev/peps/pep-0435/) 在 Python 3.4 版本中添加了 Enum 标准库，以实现枚举数据。

## 一、定义枚举类型

### 1.1 替代方式

Python 的原生类型中并不包含枚举类型，在未提供专门的枚举类型的时候，一般就通过字典或类来实现。

比如：

```Python
# 字典
Color = {
    'RED': 1,
    'GREEN': 2,
    'BLUE': 3,
}

# 类
class Color:
    RED = 1
    GREEN = 2
    BLUE = 3
```

这种枚举实现方式虽然可以满足相关需要，但是需要小心的使用，因为这些“枚举”数据是可以被修改的。

### 1.2 Enum 定义

Enum 是一个类，用户自定义的类可以通过集成该类来实现枚举类型数据。**枚举类型不可实例化，值也不可更改。**

比如：

```Python
from enum import Enum

class Color(Enum):
    RED = 1
    GREEN = 2
    BLUE = 3
```

上面定义了一个枚举的 Color 类，可以通过如下的方式使用其数据：

```Python
print(Color.RED) # Color.RED
print(repr(Color.RED)) # <Color.red: 1>
type(Color.red) # <Enum 'Color'>
isinstance(Color.RED, Color) # True
```

## 二、Enum 特点

### 2.1 成员名不允许重复

定义枚举类型时，成员名不能重复：

```Python
from enum import Enum

class Color(Enum):
    RED = 1
    GREEN = 2
    RED = 3 # TypeError: Attempted to reuse key: 'RED'
```

### 2.2 成员值可相同

虽然成员名称不能相同，但是成员的值是允许一样的，此时后面定义的同名成员是前面定义的成员的别名。

比如：

```Python
from enum import Enum

class Color(Enum):
    RED = 1
    GREEN = 2
    BLUE = 1

print(Color.RED)               # Color.RED
print(Color.blue)              # Color.RED
print(Color.red is Color.blue) # True
print(Color(1))                # Color.RED  在通过值获取枚举成员时，只能获取到第一个成员
```

### 2.3 @unique 装饰

如果要让成员不能存在相同的值，可以为枚举类型使用`@unique`装饰。

比如：

```Python
from enum import Enum, unique

@unique
class Color(Enum):
    RED = 1
    GREEN = 2
    BLUE = 1 # ValueError: duplicate values found in <enum 'Color'>: blue -> red
```

## 三、枚举使用

### 3.1 取值

枚举类型可以通过成员名或成员值来获取成员。

比如，对于上面定义的 Color 类型，可以有如下的使用：

```Python
Color.RED     # Color.RED
Color['RED']  # Color.RED

Color(1)      # Color.RED
```

而获取到的每个成员都具有名称和值两个属性：

```Python
red = Color.RED
red.name  # 'RED'
red.value # 1
```

### 3.2 迭代

枚举类型还可以通过迭代方式遍历其成员。迭代的顺序是定义时的顺序，如果有值相同的成员，则只获取重复的第一个成员。

比如：

```Python
for color in Color:
    print(color)

# Color.RED
# Color.GREEN
# Color.BLUE
```

枚举类型的特殊属性`__members__`是一个将名称映射到成员的有序字典，也可以通过它来完成遍历：

```Python
for color in Color.__members__items():
    print(color)

# ('RED', <Color.RED: 1>)
# ('GREEN', <Color.GREEN: 2>)
# ('BLUE', <Color.BLUE: 3>)
```

### 3.3 比较

一般情况下，枚举类型的成员只支持`is`同一性比较，和`==`等值比较，而不能进行大小比较。

比如：

```Python
Color.RED is Color.RED      # True
Color.RED is not Color.BLUE # True

Color.BLUE == Color.RED # False
Color.BLUE != Color.RED # True

Color.RED < Color.BLUE  # TypeError: unorderable types: Color() < Color()
```

### 3.4 扩展枚举 IntEnum

`IntEnum`是`Enum`的扩展，使得不同类型的整数枚举也可以相互比较：

```Python
from enum import IntEnum

class Shape(IntEnum):
    circle = 1
    square = 2

class Request(IntEnum):
    post = 1
    get = 2

print(Shape.circle == 1)            # True
print(Shape.circle < 3)             # True
print(Shape.circle == Request.post) # True
print(Shape.circle >= Request.post) # True
```

