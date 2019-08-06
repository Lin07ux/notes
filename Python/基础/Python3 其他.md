### pass

`pass`语句什么也不做。当语法上需要一个语句，但程序需要什么动作也不做时，可以使用它。一般用其作为占位符。例如：

```Python
while True:
    pass # Busy-wait for keyboard interrupt (Ctrl+C)
```

也可以用其定义最小类：

```Python
class MyEmptyClass:
    pass
```

或者在编写新的代码时作为一个函数或条件子句体的占位符：

```Python
def initlog(*args):
    pass # Remember to implement this!
```


