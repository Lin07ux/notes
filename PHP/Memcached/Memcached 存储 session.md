PHP 支持使用 Memcached 存储会话信息，需配置`php.ini`如下：

```ini
session.save_handler = memcached 
session.save_path = "127.0.0.1:11211" 
```

如果使用的是 Memcache(没有`d`)，则需要如下的配置：

```ini
session.save_handler = memcache
session.save_path = "tcp://127.0.0.1:11211" 
```


