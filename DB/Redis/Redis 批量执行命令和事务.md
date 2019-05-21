Redis 中有多种方式来对一系列的命令进行原子执行，每种方式各有好处和局限性，可以根据实际的需求来选择使用。

### 1. 批量操作命令

Redis 提供许多批量操作的命令，如`MSET/MGET/HMSET/HMGET`等等，这些命令存在的意义是减少维护网络连接和传输数据所消耗的资源和时间。

例如连续使用 5 次`SET`命令设置 5 个不同的 key，比起使用一次`MSET`命令设置 5 个不同的 key，效果是一样的，但前者会消耗更多的 RTT(Round Trip Time)时长，永远应优先使用后者。

使用批量操作命令的时候，会一次性操作多个数据，由于 Redis 的单线程特性，可以确保操作的原子性。

### 2. Pipelining

Pipelining 用于将多条命令串联起来，让 Redis 依次执行这些命令，并返回结果。

Pipelining 主要用于解决 Redis 批量操作命令无法连续执行的多次操作的短处，例如：

```
SET a "abc"
INCR b
HSET c name "hi"
```

使用 Pipelining 时，只需要从客户端一次向 Redis 发送多条命令（以`\r\n`）分隔，Redis 就会依次执行这些命令，并且把每个命令的返回按顺序组装在一起一次返回，比如：

```
$ (printf "PING\r\nPING\r\nPING\r\n"; sleep 1) | nc localhost 6379
+PONG
+PONG
+PONG
```

大部分的 Redis 客户端都对 Pipelining 提供支持，所以开发者通常并不需要自己手工拼装命令列表。

**Pipelining 只能用于执行连续且无相关性的命令**，当某个命令的生成需要依赖于前一个命令的返回时，就无法使用 Pipelining 了。通过 事务和 Scripting 功能可以规避这一局限性。

### 3. 事务

**Redis 的事务可以确保复数命令执行时的原子性**。也就是说 Redis 能够保证：一个事务中的一组命令是绝对连续执行的，在这些命令执行完成之前，绝对不会有来自于其他连接的其他命令插进去执行。

Redis 的事务需要将一组需要一起执行的命令放到`MULTI`和`EXEC`两个命令之间即可：

* `MULTI`表示事务开始
* `EXEC`表示事务结束，并执行事务中的命令，返回结果
* `DISCARD`表示放弃当前的事务，将保存的命令队列清空。

示例如下：

```
> MULTI
OK
> GET vCount
QUEUED
> SET vCount 0
QUEUED
> EXEC
1) 12384
2) OK
```

Redis 在接收到`MULTI`命令后便会开启一个事务，这之后的所有读写命令都会保存在队列中但并不执行，直到接收到`EXEC`命令后，Redis 会把队列中的所有命令连续顺序执行，并以数组形式返回每个命令的返回结果。
 需要注意的是，**Redis 事务不支持回滚**：如果一个事务中的命令出现了语法错误，大部分客户端驱动会返回错误。

2.6.5 版本以上的 Redis 也会在执行`EXEC`时检查队列中的命令是否存在语法错误，如果存在，则会自动放弃事务并返回错误。但如果一个事务中的命令有非语法类的错误（比如对 String 执行`HSET`操作），无论客户端驱动还是 Redis 都无法在真正执行这条命令之前发现，所以事务中的所有命令仍然会被依次执行。在这种情况下，会出现一个事务中部分命令成功部分命令失败的情况，然而与 RDBMS 不同，Redis 不提供事务回滚的功能，所以只能通过其他方法进行数据的回滚。

### 4. Lua Scripting

通过`EVAL`与`EVALSHA`命令，可以让 Redis 执行 LUA 脚本。类似于 RDBMS 的存储过程一样，可以把客户端与 Redis 之间密集的读/写交互放在服务端进行，避免过多的数据交互，提升性能。

Scripting 功能是作为事务功能的替代者诞生的，事务提供的所有能力 Scripting 都可以做到。Redis 官方推荐使用 LUA Script 来代替事务，效率和便利性都超过了事务。

### 5. CAS 乐观锁

Redis 提供了`WATCH`命令与事务搭配使用，实现 CAS 乐观锁的机制。

假设要实现将某个商品的状态改为已售：

```
if(exec(HGET stock:1001 state) == "in stock")
    exec(HSET stock:1001 state "sold");
```

这一伪代码执行时，无法确保并发安全性，有可能多个客户端都获取到了"in stock"的状态，导致一个库存被售卖多次。

使用`WATCH`命令和事务可以解决这一问题：

```
exec(WATCH stock:1001);
if(exec(HGET stock:1001 state) == "in stock") {
    exec(MULTI);
    exec(HSET stock:1001 state "sold");
    exec(EXEC);
}
```

**WATCH 的机制是：在事务 EXEC 命令执行时，Redis 会检查被 WATCH 的 key，只有被 WATCH 的 key 从 WATCH 起始时至今没有发生过变更，EXEC 才会被执行。**如果WATCH 的 key 在 WATCH 命令到 EXEC 命令之间发生过变化，则 EXEC 命令会返回失败。


