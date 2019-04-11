MySQL 5.6 中，如果有类似如下提示，或者 MySQL 日志文件中有类似如下警告：

```
TIMESTAMP with implicit DEFAULT value is deprecated. Please use --explicit_defaults_for_timestamp server option (see documentation for more details
```

则说明需要更改 MySQL 的配置文件，显示增加对 Timestamp 使用默认值的设置：

```cnf
-- /etc/my.cnf
[mysqld]
explicit_defaults_for_timestamp=true
```

