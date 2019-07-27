Python 中也有其他语言常用的流程控制语句，如：`if`、`for`、`while`，只是稍有不同。

### 1. if

`if`语句是条件判断语句，可以有零个或多个`elif`部分，以及一个可选的`else`部分。也就是说，可以只使用`if`，也可以使用`if...else`，还可以使用`if...elif...else`。而且条件表达式也不需要使用括号包裹住。

> 关键字`elif`是`else if`的缩写。

```Python
x = 42

if x < 0:
    print('Negative changed to zero')
elif x == 0:
    print('Zero')
elif x == 1:
    print('Single')
else:
    print('More')

# 输出：More
```

### 2. for

Python 中的`for`语句可以对任意序列进行迭代（例如列表或字符串），条目的迭代顺序与它们在序列中出现的顺序一致，而一般并不需要设置临时变量作为遍历的索引：

```Python
words = ['cat', 'window', 'defenestrate']
for w in words:
    print(w, len(w))

# 输出：
# cat 3
# window 6
# defenestrate 12
```

需要注意的是：如果在`for`循环内需要修改序列中的值（比如重复某些选中的元素），建议先拷贝一份副本，否则有可能导致程序出现非预期的结果。

```Python3
words = ['cat', 'window', 'defenestrate']

for w in words:
    print(w)
    if len(w) > 6:
        words.append(w)

```

上面的代码将会一直运行，并一直向`words`列表中添加`defenestrate`字符串，这是由于在`for`循环中修改了列表本身。

如果要代码能够正常运行，可以创建一个`words`列表的副本进行循环，如：

```Python
words = ['cat', 'window', 'defenestrate']

for w in words[:]:
    print(w)
    if len(w) > 6:
        words.append(w)

```

### 3. while

Python 中的`while`语句和其他语言中的类似，都表示循环执行一段代码：

```Python
x = 10
while x <= 0:
    x = x - 1
    print(x)

```

### 4. break 和 continue

`break`语句，和 C 语言中的类似，用于跳出最近的`for`或`while`循环。`continue`语句也是借鉴自 C 语言，表示跳过循环体中后续的代码，转而继续循环中的下一次迭代。

```Python
for num in range(2, 10):
    if num % 2 == 0:
        print("Found an even number", num)
        continue
    elif num > 8:
        print("Number is greater than 8", num)
        break
    print("Found a number", num)

# 输出
# Found an even number 2
# Found a number 3
# Found an even number 4
# Found a number 5
# Found an even number 6
# Found a number 7
# Found an even number 8
# Number is greater than 8 9
```

### 5. 循环的 else 语句

`for`和`while`循环语句可以带有一个`else`子句；它会在循环遍历完列表(使用`for`)或是在条件变为假(使用`while`)的时候被执行，但是不会在循环被`break`语句终止时被执行。也就是说，循环的`else`语句相当于`then`：当循环正常结束之后，会执行`else`语句，如果循环不是正常结束(比如使用了`break`或者有异常抛出)，那么就不会执行该`else`语句。

比如，以下是搜索素数的循环：

```Python
for n in range(2, 10):
    for x in range(2, n):
        if n % x == 0:
            print(n, 'equals', x, '*', n//x)
            break
    else:
        # loop fell through without finding a factor
        print(n, 'is a prime number')

# 输出如下：
# 2 is a prime number
# 3 is a prime number
# 4 equals 2 * 2
# 5 is a prime number
# 6 equals 2 * 3
# 7 is a prime number
# 8 equals 2 * 4
# 9 equals 3 * 3
```

当和循环一起使用时，`else`子句与`try`语句中的`else`子句的共同点多于`if`语句中的子句：`try`语句中的`else`子句会在未发生异常时执行，而循环中的`else`子句则会在未发生`break`时执行。

