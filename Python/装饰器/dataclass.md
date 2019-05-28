Python 3.7 引入了`dataclass`装饰器，用于装饰类，可以用来减少对样板代码的使用，因为该装饰器会自动生成诸如`__init__()`和`__repr__()`这样的特殊方法。在官方的文档中，它们被描述为「带有缺省值的可变命名元组」。

> `dataclass`装饰器属于`dataclasses`标准库，使用前需要先引入。

对于一般的类：

```Python
class Armor:

    def __init__(self, armor: float, description: str, level: int = 1):
        self.armor = armor
        self.level = level
        self.description = description

    def power(self) -> float:
        return self.armor * self.level

armor = Armor(5.2, "Common armor.", 2)
armor.power()
# 10.4

print(armor)
# <__main__.Armor object at 0x7fc4800e2cf8>
```

使用`dataclass`装饰器修饰`Armor`类之后，可以简写成如下方式：

```Python
from dataclasses import dataclass

@dataclass
class Armor:
  armor: float
  description: str
  level: int = 1
  
  def power(self) -> float:
    return self.armor * self.level

armor = Armor(5.2, "Common armor.", 2)
armor.power()
# 10.4

print(armor)
# Armor(armor=5.2, description='Common armor.', level=2)
```


