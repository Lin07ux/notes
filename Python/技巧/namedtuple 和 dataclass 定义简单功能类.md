dataclass 在 Python 3.7 中引入到标准库，用于解决具名元组(namedtuple)的属性必须预先定义的缺点。

### 1. namedtuple

namedtuple 是 Python 标准库 collections 里的一个模块，可以实现一个类似类的一个功能。具名元组类虽然定义和使用比较简单，但是所有属性都需要提前定义好才能用，如果要为其添加属性，则必须修改具名元组类的定义，否则会引发错误。

比如：

```Python
from collections import namedtuple

# 定义一个 Car 具名元组类，并设置 color 和 mileage 两个属性
Car = namedtuple('Car', 'color mileage')

my_car = Car('red', 3812.4)

print(my_car.color) # 'red'
print(my_car)       # Car(color='red', mileage=3812.4)

# 添加新的属性引发错误
my_car.name = 'Benz'
# Traceback (most recent call last):
#   File "<stdin>", line 1, in <module>
# AttributeError: 'Car' object has no attribute 'name'
```

### 2. dataclass

在 Python 3.7 中引入的标准库 dataclass 可以更方便的实现简单功能类的定义。

比如，对于上面的示例，使用 dataclass 可以有如下实现：

```Python
from dataclasses import dataclass

@dataclass
class Car:
    color: str
    mileage: float

my_car = Car('red', 3812.4)
print(my_car)      # Car(color='red', mileage=3812.4)

my_car.name = 'Benz'
print(my_car)      # Car(color='red', mileage=3812.4)
print(my_car.name) # 'Benz'
```

可以看到，使用 dataclass 可以定义简单的功能类，并能为其添加未定义的属性。

