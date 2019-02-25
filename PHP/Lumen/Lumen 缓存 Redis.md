默认情况下，Lumen 使用的是 Memcached 缓存，在确保已经安装好 Memcached PECL 扩展之后，就可以使用了，如果需要特别的配置，则在`.env`文件中设置即可。

### 使用 Redis 缓存

如果要换用 Redis 缓存，则需要先做好如下的配置：

1. 安装`predis/predis`扩展：`composer require predis/predis`；
2. 安装`illuminate/redis(5.5.*)`扩展：`composer require illuminate/redis`；
3. 注册 rerids 服务提供者：在`bootstrap/app.php`文件中添加`$app->register(Illuminate\Redis\RedisServiceProvider::class);`；
4. 调用配置：如果没有在`bootstrap/app.php`文件中调用`$app->withEloquent()`，那么应该在`bootstrap/app.php`文件中调用`$app->configure('database');`，这样才能保证 Redis 数据库配置的正确加载。

### 使用 PHPRedis 代替 Predis

predis 是使用 php 写的，phpredis 则是 C 语言写的原生模块，所以后者的速度相对就更快一些。但 Laravel/Lumen 原生的并不支持 phpredis，可以通过如下的改写来使 Lumen 支持 phpredis。

1. 安装 phpredis：具体可以参见[phpredis/phpredis](https://github.com/phpredis/phpredis)；
2. 注册单例：在`bootstrap/app.php`中加入如下语句：
    
    ```php
    $app->singleton('redis', function(){
    	$redis = new Redis;
    	$redis->pconnect('127.0.0.1');  // 可以用 config 或者 env 来获取 host
    	return $redis;
    });
    unset($app->availableBindings['redis']); // Lumen 5.6 之后不需要
    ```


