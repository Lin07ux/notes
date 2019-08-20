Lua 是**动态类型语言**，变量不要类型定义，只需要为变量赋值。值可以存储在变量中，也可以作为参数传递或结果返回。

Lua 可以支持如下几种数据类型：

 数据类型    | 描述
------------|-------------------------------------------------------
 `nil`      | 这个最简单，只有值 nil 属于该类，表示一个无效值（在条件表达式中相当于 false）
 `boolean`  | 包含两个值：`false`和`true`
 `number`   | 表示双精度类型的实浮点数
 `string`   | 字符串由一对双引号或单引号来表示
 `function` | 由 C 或 Lua 编写的函数
 `userdata` | 表示任意存储在变量中的 C 数据结构
 `thread`   | 表示执行的独立线路，用于执行协同程序
 `table`    | Lua 中的表(table)其实是一个"关联数组"(associative arrays)，数组的索引可以是数字、字符串或表类型。在 Lua 里，table 的创建是通过"构造表达式"来完成，最简单构造表达式是`{}`，用来创建一个空表。

可以使用`type()`函数测试给定变量或者值的类型：

```lua
print(type("Hello world"))      --> string
print(type(10.4*3))             --> number
print(type(print))              --> function
print(type(type))               --> function
print(type(true))               --> boolean
print(type(nil))                --> nil
print(type(type(X)))            --> string
```

### 1. nil 

`nil`类型表示一种没有任何有效值，它只有一个值`nil`。对于全局变量和`table`类型数据，`nil`还有"删除"作用：给全局变量或者`table`表里的变量赋值为`nil`等同于把它们删掉。

例如：

```lua
table = { key1 = "val1", key2 = "val2", "val3" }
for k, v in pairs(table) do
    print(k .. " - " .. v)
end
-- 输出：
-- 1 - val3
-- key1 - val1
-- key2 - val2

table.key1 = nil
for k, v in pairs(table) do
    print(k .. " - " .. v)
end
-- 输出：
-- 1 - val3
-- key2 - val2
```

### 2. boolean

boolean 类型只有两个可选值：`false`和`true`。Lua 中会把`false`和`nil`看做假，其他值都为真(空字符串和数字 0 也都是真)。

```lua
print(type(true))   --> boolean
print(type(false))  --> boolean
print(type(nil))    --> boolean

if false or nil then
    print("至少有一个是 true")
else
    print("false 和 nil 都是假")
end
--> false 和 nil 都是假
```

### 3. number

Lua 默认只有一种 number 类型：double 双精度类型，但是这个默认类型可以通过修改`luaconf.h`中的定义来改变。

以下几种写法都被看作是 number 类型(都会输出 number)：

```lua
print(type(2))
print(type(2.2))
print(type(0.2))
print(type(2e+1))
print(type(0.2e-1))
print(type(7.8263692594256e-06))
```

### 4. 字符串

字符串是由一对双引号或单引号包裹的字符组成，也可以用两个中括号(方括号)来表示一块字符串。比如：

```lua
print(type("this is string1"))  --> string
print(type('this is string2'))  --> string

html = [[
<html>
<head></head>
<body>
    <a href="http://www.runoob.com/">菜鸟教程</a>
</body>
</html>
]]
print(type(html))  --> string
```

对一个字符串其进行算术操作时，Lua 会尝试将这个字符串转换成一个数字。如果转换失败，那么就会导出错误。比如：

```lua
print("2" + 6)        --> 8.0
print("2" + "6")      --> 8.0
print("2 + 6")        --> 2 + 6
print("-2e2" * "6")   --> -1200.0
print("error" + 1)    --> 抛出错误
--[[
stdin:1: attempt to perform arithmetic on a string value
stack traceback:
    stdin:1: in main chunk
    [C]: in ?
--]]
```

如果要将两个字符串拼接在一起，则需要使用`..`操作符。这个操作符也可以用在数值中，这就会将数值转换成字符串进行拼接，而不是进行算术运算。比如：

```lua
print("a" .. 'b')  --> ab
print(157 .. 428)  --> 157428
```

> `..`运算符不可用在除字符串和数值外的其他类型数据中，否则会触发错误。

另外，可以通过`#`运算符来方便的获取字符串的长度。比如：

```lua
len = "www.runoob.com"
print(#len)              --> 14
print(#"www.runoob.com") --> 14
```

### 5. table

Lua 中的表 table 其实是一个"关联数组"(associative arrays)，数组的索引可以是数字或者是字符串。需要注意的是，**表的数字索引默认是从 1 开始的**，这和其他的编程语言是非常不同的。

table 的创建是通过"构造表达式"来完成，最简单构造表达式是`{}`，用来创建一个空表。也可以在表里添加一些数据，直接初始化表。比如：

```lua
-- 创建一个空的 table
local tbl1 = {}
 
-- 直接初始表
local tbl2 = { "apple", "pear", "orange", "grape" }

tbl1["key"] = "value"
key = 10
tbl1[key] = 22
tbl1[key] = tbl1[key] + 11

for k, v in pairs(tbl1) do
    print(k .. " : " .. v)
end
--[[ 输出：
10 : 33
key : value
--]]
```

table 中的字符串索引数据，可以通过中括号`[]`访问，也可以通过`.`访问，但是数字索引数据就只能通过中括号`[]`方式访问了：

```lua
table = { "apple", "pear", key1 = "orange", key2 = "grape"}
print(table[1])       --> apple
print(table.2)        --> stdin:1: ')' expected near '.2'
print(table['key1'])  --> orange
print(table.key2)     --> grape
```

当访问一个可能为空的 table 时往往需要先判断非空：

```lua
if lib and lib.foo then ....
```

使用这种方式访问结构比较深的表示就会非常痛苦，Lua 没有像 C# 一样提供`?.`这样的操作，不过可以使用`or {}`的形式来处理：

```lua
zip = company and company.director and
              company.director.address and
                      company.director.address.zipcode

-- 等价于
zip = (((company or {}).director or {}).address or {}).zipcode
```

### 6. function

在 Lua 中，函数是被看作是"第一类值(First-Class Value)"，可以存在变量里，或者作为其他函数的参数。Lua 程序中既可以使用定义在 Lua 中的函数，也可以使用定义在 C 语言中的函数。

比如：

```lua
function tableMap(tbl, fun)
    for k, v in pairs(tbl) do
        print(fun(k, v))
    end
end

table = { key1 = "val1", key2 = "val2" }

-- 可以直接传入匿名函数作为参数
tableMap(table, function (key, val)
    return key.."="..val
end)
--> key1=val1
--> key2=val2

-- 也可以传入已定义过的函数的名称作为参数
function mapper (key, val)
    return key.."="..val
end
tableMap(table, mapper)
--> key1=val1
--> key2=val2

-- 将函数赋值给其他变量，依旧有效
newMapper = mapper
tableMap(table, newMapper)
--> key1=val1
--> key2=val2
```

**Lua 函数可以返回多个结果**，而且 Lua 可以自动调整返回结果的数量：

* 当函数作为语句调用时，会舍弃所有返回值；
* 当函数作为表达式调用时，只保留第一个返回值；
* 如果要获得全部返回值，函数调用需要是表达式最后一个(赋值语句的特性)。

```lua
function maximum (a)
   local mi = 1
   local m = a[mi]
   
   for i = 1, #a do
        if a[i] > m then
            mi = i; m = a[i]
        end
    end
    
    return m, mi
end

print(maximum({8,10,23,12,5}))     --> 23   3
```

**Lua 函数也支持可变参数**：

```lua
function add (...)
    local s = 0
    for _, v in ipairs{...} do
        s = s + v 
    end
    
    return s 
end

print(add(3, 4, 10, 25, 12))   --> 54
```

### 7. thread

在 Lua 里，最主要的线程是协同程序(coroutine)。它跟线程(thread)差不多，拥有自己独立的栈、局部变量和指令指针，可以跟其他协同程序共享全局变量和其他大部分东西。

线程跟协程的区别：线程可以同时多个运行，而协程任意时刻只能运行一个，并且处于运行状态的协程只有被挂起(suspend)时才会暂停。

### 8. userdata

userdata 是一种用户自定义数据，用于表示一种由应用程序或 C/C++ 语言库所创建的类型，可以将任意 C/C++ 的任意数据类型的数据(通常是`struct`和指针)存储到 Lua 变量中调用。


