Lua 与其他语言一样，提供了用于流程控制和代码循环的语法功能，在使用方式上也都大同小异。流程控制和循环结构可以相互嵌套使用。

> 流程控制和循环结构都涉及到条件判断，在 Lua 中，只有 false 和 nil 会被作为假，其他都是真，包括数值 0 和空字符串。

## 一、流程控制

Lua 编程语言流程控制语句通过程序设定一个或多个条件语句来设定。在条件为 true 时执行指定程序代码，在条件为 false 时执行其他指定代码。

### 1.1 if

Lua `if`语句由一个布尔表达式作为条件判断，其后紧跟其他语句组成。当条件为真时，则会执行`if`中的代码块，否则不执行。

语法结构如下：

```lua
if (condition) then
   --[ 在布尔表达式为 true 时执行的语句 --]
end
```

### 1.2 if...else

Lua `if`语句可以与`else`语句搭配使用：在条件表达式为 true 时执行`if`语句块代码，为 false 时执行`else`语句代码块。

语法结构如下：

```lua
if (condition) then
   --[ 布尔表达式为 true 时执行该语句块 --]
else
   --[ 布尔表达式为 false 时执行该语句块 --]
end
```

### 1.3 if...elseif...if

Lua `if`语句可以与`elseif...else`语句搭配使用，用于检测多个条件语句。会一次判断整个流程代码中的条件，直到某个条件为 true 时，就执行相应的代码块，如果全不为 true，则会执行`else`代码块。

语法结构如下：

```lua
if (condition1) then
   --[ 在布尔表达式 1 为 true 时执行该语句块 --]
elseif (condition2) then
   --[ 在布尔表达式 2 为 true 时执行该语句块 --]
elseif (condition3) then
   --[ 在布尔表达式 3 为 true 时执行该语句块 --]
else 
   --[ 如果以上布尔表达式都不为 true 则执行该语句块 --]
end
```

## 二、循环结构

循环结构是在一定条件下反复执行某段程序的流程结构。循环语句是由循环体及循环的终止条件两部分组成的。被反复执行的程序被称为循环体。循环体能否继续重复，决定于循环的终止条件。

Lua 语言提供了以下几种循环处理方式：

* while 循环：在条件为 true 时，让程序重复地执行某些语句。执行语句前会先检查条件是否为 true。
* for 循环：重复执行指定语句，重复次数可在 for 语句中控制。
* repeat...until 循环：重复执行循环，直到指定的条件为真时为止。

这些循环结构还可以相互嵌套使用，形成多层循环。

Lua 还支持`break`循环控制语句，用于退出当前循环或语句，并开始脚本执行紧接着的语句。

### 2.1 while

Lua 编程语言中`while`循环语句在判断条件为 true 时会重复执行循环体语句。

语法结构如下：

```lua
while (condition) do
   --[[ statements ]]--
end
```

### 2.2 for

Lua 编程语言中 for 循环语句可以重复执行指定语句，重复次数可在 for 语句中控制。for 语句有两大类：

* 数值`for`循环
* 泛型`for`循环

**数值 for 循环**

Lua 的数值 for 循环可以设定从一个数值逐步变化到另一个数值，每一次变化都会执行一次循环体。语法结构如下：

```lua
for var = start, end, step do  
    --[[ statements ]]--
end
```

这里，`var`会从`start`变化到`end`，每次变化以`step`为步长进行递增，并执行一次执行体。其中`step`是可选的，可以是正数也可以是负数，如果不指定则默认为 1。

另外，`start`、`end`和`step`会在循环开始前一次性求值，以后不再进行求值。比如：

```lua
function f(x)  
    print("f(x)")  
    return x*2   
end

for i=1,f(5) do
    print(i)  
end
```

这只会输出一次`f(x)`，并依次输出从 1 到 10 十个数字。也就是说，在循环开始前，只会对三个表达式进行一次性计算。

**泛型 for 循环**

泛型 for 循环通过一个迭代器函数来遍历所有值，类似 Java 中的`foreach`语句。语法结构如下：

```lua
for key, value in ipairs(var) do
    --[[ statements ]]--
end
```

泛型 for 循环一般用于表(数组)的遍历，其中`key`是索引值，`value`是对应索引的元素值。`ipairs`是 Lua 提供的一个迭代器函数，用来迭代数组。比如：

```lua
-- 打印数组 a 的所有值  
a = {"one", "two", "three"}
for i, v in ipairs(a) do
    print(i, v)
end

--[[ 输出：
1 one
2 two
3 three
--]]
```

### 2.4 repeat...until

Lua 编程语言中`repeat...until`循环语句不同于`for`和`while`循环：

* `for`和`while`循环的条件语句在当前循环执行开始时判断，而`repeat...until`循环的条件语句在当前循环结束后判断；
* `for`和`while`的循环体在条件为真的情况下继续执行，而`repeat...until`的循环体在条件为真是不会继续执行；
* `for`和`while`的循环体有可能不会执行，而`repeat...until`的循环体至少会执行一次。

`repeat...until`循环结构类似于其他语言中的`do...while`循环。

语法结构：

```lua
repeat
   --[[ statements ]]--
until (condition)
```

比如：

```lua
a = 10
repeat
   print("a 的值为: ", a)
   a = a + 1
until( a > 15 )

--[[ 输出：
a 的值为: 10
a 的值为: 11
a 的值为: 12
a 的值为: 13
a 的值为: 14
a 的值为: 15
--]]
```

### 2.5 break 语句

Lua 编程语言`break`语句插入在循环体中，用于退出当前循环或语句，并继续执行循环结构后面的语句。

> 如果是在循环嵌套中使用`break`语句，将停止内层循环的执行，并开始执行的外层的循环语句。

比如，以下实例执行`while`循环，在变量`a`小于 20 时输出`a`的值，并在`a`大于`15`时终止执行循环：

```lua
a = 10

while (a < 20) do
   print("a 的值为:", a)
   a = a + 1
   
   if (a > 15) then
      --[ 使用 break 语句终止循环 --]
      break
   end
end

--[[ 输出：
a 的值为: 10
a 的值为: 11
a 的值为: 12
a 的值为: 13
a 的值为: 14
a 的值为: 15
--]]
```

Lua 没有像其他编程语言一样提供`coutinue`语句，但是可以通过流程控制语句来实现类似功能。

另外，在循环体中也可以使用`return`语句返回数据，从而直接结束循环。





