### 批量删除

Redis 没有提供批量删除的命令，但是自从 Redis 2.8 以后开始支持的`scan`命令可以使用模式匹配扫描键，然后利用 Linux 的`xargs`命令即可批量删除命令。

```shell
redis-cli -n <redis-db> -a <redis-password> -p <redis-port> --scan --pattern "yum:12:*" | xargs -L 5000 redis-cli -n <redis-db> -a <redis-password> -p <redis-port> del
```

### 寻找 bigkey

大 key 对 redis 是一个挑战，包括 redis 集群（集群会迁移节点），内存申请、扩容、删除等场景造成卡顿。

可以通过如下的命令来查找 redis 中的大 key：

```shell
redis-cli --bigkeys -i 0.1
```

这里的`-i 0.1`代表每执行 100 条指令休息 0.1 秒。

