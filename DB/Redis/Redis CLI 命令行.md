> 转摘：[短小精悍之 Redis 命令行工具有趣的罕见用法](https://yq.aliyun.com/articles/656980)

Redis 内置的命令行工具 redis-cli 是一个简单的交互式 Redis 数据库操作程序，通过这个工具可以方便的查看、验证和操作 Redis 数据库中的数据，而且还会有一些不常见但很有用的用法。

### 1. 执行单条命令

平时在访问 Redis 服务器，一般都会使用 redis-cli 进入交互模式，然后一问一答来读写服务器，这种情况下使用的是它的「交互模式」。还有另外一种「直接模式」，通过将命令参数直接传递给 redis-cli 来执行指令并获取输出结果：

```shell
$ redis-cli incrby foo 5
(integer) 5

$ redis-cli incrby foo 5
(integer) 10
```

如果输出的内容较大，还可以将输出重定向到外部文件：

```shell
$ redis-cli info > info.txt

$ wc -l info.txt
120 info.txt
```

上面的命令指向的服务器是默认服务器地址，如果想指向特定的服务器可以这样：

```shell
# -n 2 表示使用第2个库，相当于 select 2
$ redis-cli -h localhost -p 6379 -n 2 ping
PONG
```

### 2. 批量执行命令

Redis CLI 还支持批量执行命令的功能，只需要将批量命令通过管道或者输入重定向方式传递给 redis-cli 即可：

```shell
$ cat cmds.txt
set foo1 bar1
set foo2 bar2
set foo3 bar3
......

# 管道方式
$ cat cmds.txt | redis-cli
OK
OK
OK
...

# 输入重定向方式
$ redis-cli < cmds.txt
OK
OK
OK
...
```

### 3. set 多行字符串

redis-cli 可以配置`-x`选项使用标准输入的内容作为最后一个参数。这个功能可以用在设置多行字符串值的情况，比如：

```shell
$ cat str.txt
Ernest Hemingway once wrote,
"The world is a fine place and worth fighting for."
I agree with the second part.

$ redis-cli -x set foo < str.txt
OK

$ redis-cli get foo
"Ernest Hemingway once wrote,\n\"The world is a fine place and worth fighting for.\"\nI agree with the second part.\n"
```

### 4. 重复执行指令

redis-cli 还支持重复执行指令多次，每条指令执行之间设置一个间隔时间，如此便可以观察某条指令的输出内容随时间变化。该功能主要用到两个参数：

* `-r` 执行次数，如果将次数设置为 -1 那就是重复无数次永远执行下去。
* `-i` 间隔时间，单位是秒(s)。如果不提供该参数，那就没有间隔，连续重复执行。

```shell
# 间隔 1s，执行 5 次，观察 qps 的变化
$ redis-cli -r 5 -i 1 info | grep ops
instantaneous_ops_per_sec:43469
instantaneous_ops_per_sec:47460
instantaneous_ops_per_sec:47699
instantaneous_ops_per_sec:46434
instantaneous_ops_per_sec:47216
```

### 5. 导出 csv

redis-cli 不能一次导出整个库的内容为 csv，但是可以导出单条指令的输出为 csv 格式，主要用到`--csv`参数，如下：

```shell
$ redis-cli rpush lfoo a b c d e f g
(integer) 7

$ redis-cli --csv lrange lfoo 0 -1
"a","b","c","d","e","f","g"

$ redis-cli hmset hfoo a 1 b 2 c 3 d 4
OK

$ redis-cli --csv hgetall hfoo
"a","1","b","2","c","3","d","4"
```

当然这种导出功能比较弱，仅仅是一堆字符串用逗号分割开来。不过，可以结合命令的批量执行来看看多个指令的导出效果：

```shell
$ redis-cli --csv -r 5 hgetall hfoo
"a","1","b","2","c","3","d","4"
"a","1","b","2","c","3","d","4"
"a","1","b","2","c","3","d","4"
"a","1","b","2","c","3","d","4"
"a","1","b","2","c","3","d","4"
```

### 6. 执行 lua 脚本

虽然进入到 redis-cli 交互式模式下也可以使用`eval`来执行 lua 脚本，但需要 lua 脚本是单行字符串形式，会比较繁琐。所以 redis-cli 允许直接运行脚本文件，只是参数形式有所不同，`KEY`和`ARGV`之间需要使用逗号分割，并且不需要提供`KEY`的数量参数：

```shell
$ cat mset.txt
return redis.pcall('mset', KEYS[1], ARGV[1], KEYS[2], ARGV[2])

$ cat mget.txt
return redis.pcall('mget', KEYS[1], KEYS[2])

$ redis-cli --eval mset.txt foo1 foo2 , bar1 bar2
OK

$ redis-cli --eval mget.txt foo1 foo2
1) "bar1"
2) "bar2"
```

### 7. 监控服务器状态

可以使用`--stat`参数来实时监控服务器的状态，间隔 1s 实时输出一次，也可以使用`-i`参数调整输出间隔。

```shell
$ redis-cli --stat
------- data ------ --------------------- load -------------------- - child -
keys mem clients blocked requests connections
2 6.66M 100 0 11591628 (+0) 335
2 6.66M 100 0 11653169 (+61541) 335
2 6.66M 100 0 11706550 (+53381) 335
2 6.54M 100 0 11758831 (+52281) 335
2 6.66M 100 0 11803132 (+44301) 335
2 6.66M 100 0 11854183 (+51051) 335
```

### 8. 扫描大 KEY

redis-cli 提供了`--bigkeys`参数可以很快扫出内存里的大 KEY，同时可以使用`-i`参数控制扫描间隔，避免扫描指令导致服务器的 OPS 陡增报警。

```shell
$ ./redis-cli --bigkeys -i 0.01
# Scanning the entire keyspace to find biggest keys as well as
# average sizes per key type. You can use -i 0.1 to sleep 0.1 sec
# per 100 SCAN commands (not usually needed).

[00.00%] Biggest zset found so far 'hist:aht:main:async_finish:20180425:17' with 1440 members
[00.00%] Biggest zset found so far 'hist:qps:async:authorize:20170311:27' with 2465 members
[00.00%] Biggest hash found so far 'job:counters:6ya9ypu6ckcl' with 3 fields
[00.01%] Biggest string found so far 'rt:aht:main:device_online:68:{-4}' with 4 bytes
[00.01%] Biggest zset found so far 'machine:load:20180709' with 2879 members
[00.02%] Biggest string found so far '6y6fze8kj7cy:{-7}' with 90 bytes
```

redis-cli 对于每一种对象类型都会记录长度最大的 KEY，对于每一种对象类型，刷新一次最高记录就会立即输出一次。它能保证输出长度为 Top1 的 KEY，但是 Top2、Top3 等 KEY 是无法保证可以扫描出来的。一般的处理方法是多扫描几次，或者是消灭了 Top1 的 KEY 之后再扫描确认还有没有次大的 KEY。

### 9. 采样服务器指令

可以使用 redis-cli 的 monitor 指令来采集服务器瞬间执行的指令。

```shell
$ redis-cli monitor
1539853410.458483 [0 10.100.90.62:34365] "GET" "6yax3eb6etq8:{-7}"
1539853410.459212 [0 10.100.90.61:56659] "PFADD" "growth:dau:20181018" "2klxkimass8w"
1539853410.462938 [0 10.100.90.62:20681] "GET" "6yax3eb6etq8:{-7}"
1539853410.467231 [0 10.100.90.61:40277] "PFADD" "growth:dau:20181018" "2kei0to86ps1"
1539853410.470319 [0 10.100.90.62:34365] "GET" "6yax3eb6etq8:{-7}"
1539853410.473927 [0 10.100.90.61:58128] "GET" "6yax3eb6etq8:{-7}"
1539853410.475712 [0 10.100.90.61:40277] "PFADD" "growth:dau:20181018" "2km8sqhlefpc"
1539853410.477053 [0 10.100.90.62:61292] "GET" "6yax3eb6etq8:{-7}"
```

### 10. 诊断服务器时延

Redis 通过参数`--latency`提供了时延诊断指令，它是诊断当前机器和 Redis 服务器之间的指令(PING 指令)时延(单位 ms)，它不仅仅是物理网络的时延，还和当前的 Redis 主线程是否忙碌有关。如果发现 Unix 的 ping 指令时延很小，而 Redis 的时延很大，那说明 Redis 服务器在执行指令时有微弱卡顿。

```shell
$ redis-cli --host 192.168.x.x --port 6379 --latency
min: 0, max: 5, avg: 0.08 (305 samples)

# 图形化输出时延
$ redis-cli --latency-dist
```

### 11. 远程 rdb 备份

执行下面的命令就可以将远程的 Redis 实例备份到本地机器，远程服务器会执行一次`bgsave`操作，然后将 rdb 文件传输到客户端。

```shell
$ ./redis-cli --host 192.168.x.x --port 6379 --rdb ./user.rdb
SYNC sent to master, writing 2501265095 bytes to './user.rdb'
Transfer finished with success.
```

### 12. 模拟从库

如果想观察主从服务器之间都同步了那些数据，可以使用 redis-cli 模拟从库。

```shell
$ ./redis-cli --host 192.168.x.x --port 6379 --slave
SYNC with master, discarding 51778306 bytes of bulk transfer...
SYNC done. Logging commands from master.
...
```

从库连上主库的第一件事是全量同步，所以看到上面的指令卡顿这很正常，待首次全量同步完成后，就会输出增量的 aof 日志。

