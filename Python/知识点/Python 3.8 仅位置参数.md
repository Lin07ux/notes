Python 函数传递参数的方式有很多：位置参数、默认参数、可变参数、命名关键字参数等。比如：

```Python
def add(x, y, *args, **kwargs):
  print(f"x={x}, y={y}")
```

这个函数中 x 和 y 就是两个位置参数，为这两个参数传递值时，需要按照顺序传递，也可以将它们作为命名关键字参数进行传递，这样就可以改变参数的顺序了，比如：

```Python
# 按照位置传递，此时输出：x=1, y=2
add(1, 2)

# 按照明明关键字参数传递，此时输出：x=2, y=1
ad(y=1, x=2)
```

使用命名关键字方式传递参数，虽然很方便，但是也增加了出错的机会，特别是多人合作的项目中。

Python 3.8 中引入仅位置参数语法来限制命名关键字参数传递的使用。在函数定义时，参数之间可以指定一个斜杠(`/`)，在斜杠之前的参数严格遵守仅位置参数的定义。

例如：

```Python
def add(x, y, /, * args, **kwargs):
  print(f"x={x}, y={y}")

add(1, 2) # 输出：x=1, y=2
```

`/`告诉解释器，x 和 y 是两个严格的位置参数，不能当做命名关键字参数进行传递。如果把它当作命名关键字参数进行传递参数时，就会引发错误：

```Python
add(y=2, x=1)
# Traceback (most recent call last):
#  File "<stdin>", line 1, in <module>
# TypeError: add() missing 2 required positional arguments: 'x' and 'y'
```


