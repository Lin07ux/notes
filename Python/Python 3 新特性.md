### 1. 类型提示 Type hinting

> 类型提示最低需要 Python 3.5。

```Python
def sentence_has_animal(sentence: str) -> bool:
  return "animal" in sentence

sentence_has_animal("Donald had a farm without animals")
# True
```

### 2. 枚举

> 枚举最低要求 Python 3.4。

Python 3 支持通过`Enum`类编写枚举的简单方法。枚举是一种封装常量列表的便捷方法，因此这些列表不会在结构性不强的情况下随机分布在代码中。

```Python
from enum import Enum, auto

class Monster(Enum):
    ZOMBIE = auto()
    WARRIOR = auto()
    BEAR = auto()

print(Monster.ZOMBIE)
# Monster.ZOMBIE
```

枚举是符号名称（成员）的集合，这些符号名称与唯一的常量值绑定在一起。在枚举中，可以通过标识对成员进行比较操作，枚举本身也可以被遍历。

```Python
for monster in Monster:
    print(monster)

# Monster.ZOMBIE
# Monster.WARRIOR
# Monster.BEAR
```

### 3. 扩展的可迭代对象解包

```Python
head, *body, tail = range(5)
print(head, body, tail)
# 0 [1, 2, 3] 4

py, filename, *cmds = "python3.7 script.py -n 5 -l 15".split()
print(py)
print(filename)
print(cmds)
# python3.7
# script.py
# ['-n', '5', '-l', '15']

first, _, third, *_ = range(10)
print(first, third)
# 0 2
```



