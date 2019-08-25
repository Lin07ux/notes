Redis 提供了丰富的指令集，但在一些特定场景下，需要自定义一些指定来完成某些功能。因此，Redis 提供了对 Lua 脚本的支持，使用户可以自己编写脚本来实现想要的功能。

**Redis 运行所有的 Lua 命令**都使用相同的 Lua 解释器，**具有原子性**。当一个脚本正在执行时，其他的脚本或 Redis 命令都不能执行。这很像Redis 的事务`multi/exec`，也意味着要尽量避免脚本的执行时间过长。

Redis 中的事务命令和 Lua 脚本支持并不是互相替代的关系，各有使用场景。MULT 块中所有操作独立，但在 Lua 中，后面的操作能依赖前面操作的执行结果。

## 一、Redis 执行 Lua 脚本

在 Redis 中可以运行 Lua 脚本，并得到相应的结果。一般通过 Redis 的`EVAL`或者`EVALSHA`命令来运行 Lua 脚本。

### 1.1 EVAL

`EVAL`命令一般用作简单 Lua 脚本的执行，可以在交互模式下进行，方便处理错误。EVAL 命令的基本格式如下：

```Redis
EVAL script numkeys [key ...] [val ...]
```

其中：

* `script` 表示要执行的 Lua 脚本
* `numkeys` 表示为 Lua 脚本提供的参数个数，为 0 表示不提供参数
* `key` 表示提供给 Lua 的参数的键名
* `arg` 表示提供给 Lua 的参数的值

EVAL 命令的参数均是通过 Key-Value 格式传入的，也就是每个参数都要有相应的名称和值。在 Lua 脚本中，可以通过`KEYS`和`ARGV`表来分别获取传入的参数的名称和值，且这两个表的索引都是从 1 开始。

比如：

```Redis
EVAL 'local val="Hello" return val' 0
# Hello

EVAL 'local val=KEYS[1] return val.." "..ARGV[1]' Hello Redis
# Hello Redis
```

> 脚本中的两个点是 Lua 脚本中字符串连接的操作符。

使用 EVAL 命令必须每次都要把脚本从客户端传到服务器，由于 Redis 的内部缓存机制，并不会每次都重新编译脚本，但是传输上仍然浪费带宽。

### 1.2 EVALSHA

EVALSHA 和 EVAL 命令类似，都是用来执行 Lua 脚本的，不同的地方在于，EVALSHA 是用来执行已经被加载到 Redis 中的 Lua 脚本，而且不需要提供脚本的具体内容，只需要提供在 Redis 中该脚本的名称即可。

首先上传脚本文件，得到脚本名称：

```shell
redis-cli SCRIPT LOAD "$(cat hello.lua)"
# 假如输出："463ff2ca9e78e36cd66ee9d37ee0dcd59100bf46"
```

然后使用 EVALSHA 调用该命令：

```redis
EVALSHA 463ff2ca9e78e36cd66ee9d37ee0dcd59100bf46 1 my_name Hello
# "Hello Jackeyzhe"
```

### 1.3 执行 Lua 脚本文件

当 Lua 脚本内容较多时，直接通过 EVAL 命令进行执行会比较凌乱，可以将 Lua 脚本写在一个文件中，然后通过`redis-cli --eval`命令来执行该脚本。

比如有一个`hello.lua`脚本文件：

```lua
local name = redis.call("get", KEYS[1])
local greet = ARGV[1]
local result = greet.." "..name
return result
```

然后可以通过如下方式调用：

```shell
redis-cli --eval hello.lua my_name, Hello
```

使用这种方式传入参数时，不需要指定 key 的数量，而是用逗号分隔 key 和 arg。

### 1.4 终止脚本

Redis 中 Lua 脚本到默认执行时长是 5 秒，一般情况下脚本的执行时间都是毫秒级的。如果执行超时，脚本也不会停止，而是记录错误日志。

终止脚本执行的方法有两种

1.	使用`KILL SCRIPT`命令
2.	使用`SHUTDOWN NOSAVE`命令关闭服务器 
不过不建议手动终止脚本。

## 二、Lua 脚本调用 Redis 命令

虽然可以使用`EVAL`和`EVALSHA`在 Redis 中执行 Lua 脚本，但是还需要 Lua 脚本中能够调用 Redis 的相关操作，才能给实现用 Lua 操作 Redis。

### 2.1 redis.call/redis.pcall

Redis 为 Lua 脚本提供了`redis.call()`和`redis.pcall()`两个方法的支持，通过这两个方法就能够在 Lua 中调用 Redis 的命令了。这两个方法唯一的不同就是：当 Redis 命令执行错误时，`redis.call()`会抛出这个错误，从而使`EVAL`命令抛出错误，而`redis.pcall()`会捕获这个错误，并以表的形式返回错误内容。

通过这两个方法时，可以像直接运行 Redis 的命令时一样，将命令本身和所需参数作为`redis.call/redis.pcall`的参数依次传入即可。执行完成之后，这两个方法会将 Redis 命令返回的结果转换成 Lua 数据，并可在后续的 Lua 脚本中继续使用。

比如，建立一个 URL 简写服务器，存储每条进入的 URL 并返回一个唯一数值，以便以后通过这个数值访问到该 URL。对应的 Lua 脚本如下：

```lua
local link_id = redis.call("INCR", KEY[1])
redis.call("HSET", KEYS[2], link_id, ARGV[1])
return link_id
```

这段脚本先调用 Redis 的 INCR 命令，并得到一个唯一标识 ID，然后以这个标识 ID 作为 URL 存储于 Redis 一个哈希表中的键值，最后返回这个 ID。

在这段脚本中，有两次调用`redis.call`方法，分别用于执行 Redis 的 INCR 和 HSET 命令，而这两个命令所需的参数都可以在通过 EVAL 运行 Lua 脚本时作为参数提供。

这里`KEYS[1]`表示 URL 计数器，`KEYS[2]`表示存储 URL 和 ID 映射的哈希表，`ARGV[1]`则表示具体的 URL 值。假如是通过如下方式执行这段脚本：

```shell
redis-cli EVAL "$(cat incr-and-stor.lua)" 2 links:counter links:urls http://malcolmgladwellbookgenerator.com/
```

那么，这段脚本实际运行的就是：

```lua
local link_id = redis.call("INCR", "links:counter")
redis.call("HSET", "links:urls", link_id, "http://malcolmgladwellbookgenerator.com")
return link_id
```

### 2.2 redis.log

使用`redis.log(loglevel, message)`函数可以在 Lua 脚本中打印 Redis 日志。

loglevel 与 Redis 的日志等级是对应的，包括：

* redis.LOG_DEBUG
* redis.LOG_VERBOSE
* redis.LOG_NOTICE
* redis.LOG_WARNING

## 三、相关命令

Redis 中有一个`SCRIPT`命令，可以对 Lua 脚本进行相关操作，以便后续的执行。

### 3.1 SCRIPT DEBUG

该命令用于设置随后执行的 EVAL 命令的调试模式。语法如下：

```redis
SCRIPT DEBUG YES|SYNC|NO
```

> 版本支持：Redis >= 3.2.0

Redis 包含一个完整的 Lua调 试器，代号为 LDB，可以使编写复杂脚本的任务更加简单。在调试模式下，Redis 充当远程调试服务器，客户端可以逐步执行脚本，设置断点，检查变量等。

LDB 可以设置成异步或同步模式：

* 异步模式下，服务器会 fork 出一个调试会话，不会阻塞主会话，调试会话结束后，所有数据都会回滚。
* 同步模式则会阻塞会话，并保留调试过程中数据的改变。

### 3.2 SCRIPT LOAD

该命令用于将脚本加载到服务器端的缓存中，但不会执行。语法如下：

```redis
```

> 版本支持：Redis >= 2.6.0

加载后，服务器会一直缓存，因为良好的应用程序不太可能有太多不同的脚本导致内存不足。每个脚本都像一个新命令的缓存，所以即使是大型应用程序，也就有几百个，它们占用的内存是微不足道的。

### 3.3 SCRIPT EXISTS

返回脚本是否存在于缓存中（存在返回 1，不存在返回 0）。语法如下：

```redis
```

> 版本支持：Redis >= 2.6.0

这个命令适合在管道前执行，以保证管道中的所有脚本都已经加载到服务器端了，如果没有，需要用`SCRIPT LOAD`命令进行加载。

### 3.4 SCRIPT FLUSH

刷新缓存中的脚本，这一命令常在云服务上被使用。语法如下：

```redis
```

> 版本支持：Redis >= 2.6.0

### 3.5 SCRIPT KILL

停止当前正在执行的 Lua 脚本，通常用来停止执行时间过长的脚本。语法如下：

```redis
```

> 版本支持：Redis >= 2.6.0

停止后，被阻塞的客户端会抛出一个错误。

## 四、其他

### 4.1 数据转换

在 Redis 执行 Lua 脚本时，如果调用了`redis.call()`或者`redis.pcall()`命令，就会涉及到 Redis 和 Lua 之间数据类型转换的问题。转换规则要求：一个 Redis 的返回值转换成 Lua 数据类型后，再转换成 Redis 数据类型，其结果必须和初始值相同，所以每种类型都要是一一对应的。

转换规则如下：

 Redis          | Lua
----------------|------------------
 integer        | number
 bulk           | string
 multi bulk     | table
 status         | table with a single `ok` field
 error          | table with a single `err` field
 Nil bulk       | false boolean type
 Nil multi bulk | false boolean type

除此之外，Lua 到 Redis 的转换还有一些其他的规则：

* 	Lua 中的`true`会转换成 Redis 中的数字 1
* Lua 只有一种数字类型，不会区分整数和浮点数，而数字类型只能转换成 Redis 的 integer 类型。如果要返回浮点数，那么在Lua 中就需要返回一个字符串。
* Lua 数组在转换成 Redis 类型时，遇到`nil`就停止转换。

比如：

```redis
EVAL "return { 1, 2, 3.3333, 'foo', nil, 'bar' }" 0
1) (integer) 1
2) (integer) 2
3) (integer) 3
4) "foo"
```

这里，浮点数`3.3333`会被转成 Redis 中的数值 3；而遇到表中的`nil`时，表的转换就停止了，所以`bar`就没有返回。

### 4.2 全局变量

为了避免数据泄露，Redis 中执行的 Lua 脚本不允许创建全局变量。如果必须有一个公共变量，可以使用 Redis 的 key 来代替。在 EVAL 命令中创建一个全局变量会引起一个异常。

比如：

```redis
eval 'a=10' 0
(error) ERR Error running script ...: Script attempted to create global variable 'a
```

### 4.3 Lua 库

Redis 的 Lua 解释器加载了七个库：base、[table](http://www.lua.org/pil/19.1.html)、[string](http://www.lua.org/pil/20.html)、[math](http://www.lua.org/pil/18.html)、[debug](http://www.lua.org/pil/23.html)、[cjson](http://www.kyne.com.au/~mark/software/lua-cjson-manual.html) 和 [cmsgpack](https://github.com/antirez/lua-cmsgpack)。前几个都是标准库，后面两个可以让支持对 JSON 和 MessagePack 数据进行操作。

比如，Web 应用程序常常使用 JSON 作为返回数据，可以把一堆 JSON 数据序列化成字符串存到 Redis 的 key 中。当想访问某些 JSON 数据时，就需要先将该数据反序列化，并取得其中的值，比如：

```lua
if redis.call("EXISTS", KEYS[1]) == 1 then
    local payload = redis.call("GET", KEYS[1])
    return cjson.decode(payload)[ARGV[1]]
else
    return nil
end
```

这段脚本先检查 key 是否存在，如不存在则快速返回 nil。如存在则从 Redis 中获取 JSON 值，然后用`cjson.decode()`进行解析，并返回请求内容。

在 Redis（更多场合也是如此）中 [MessagePack](https://msgpack.org/) 是比 JSON 更好的替代品，它更小、更快。使用 MessagePack 完成上述功能的 Lua 代码如下：

```lua
if redis.call("EXISTS", KEYS[1]) == 1 then
    local payload = redis.call("GET", KEYS[1])
    return cmsgpack.unpack(payload)[ARGV[1]]
else
    return nil
end
```

### 4.4 脚本调试

可以使用如下命令调试进入脚本调试模式，然后使用相关命令对脚本的运行过程进行跟踪查验：

```shell
redis-cli --ldb --eval <lua_script_file>
```

这里`--ldb`表示进入调试模式，默认是异步调试。如果需要进行同步调试，则将`--ldb`选项改成`--ldb-sync-mode`即可。

## 转摘

1. [Redis Lua脚本小学教程](https://mp.weixin.qq.com/s/zZOtoI_PSaLd2nTC-kmPSA)
2. [Lua: 给 Redis 用户的入门指导](https://www.oschina.net/translate/intro-to-lua-for-redis-programmers)

