
## 问题

PHP 环境搭建好了，但是后台无法登陆，分析数据都是正常的，最后分析，很有可能是 session 没有开启导致的。

检查配置文件，均正常，而且程序已经开启了 session，但仍旧无法保存 session 数据。

## 验证

写一个`session_test.php`文件，看看能否每次刷新数值都增加：

```php
<?php
session_start();

$_SESSION['count']++;

print_r($_SESSION);
```

刷新多次，但是依旧显示的是 1，说明是 session 数据没有正常保存。

## 排查

分析环境搭建的过程，确定配置都是正常的。只是更换了下 php-fpm 服务的运行用户和组。

那会不会是因为权限问题导致的不能存储 session 数据呢？

查看 php-fpm 的配置文件(`/etc/php-fpm.d/www.conf`)，可以看到 php-fpm 的相关配置中，session 的目录不是`/tmp`，而是`/var/lib/php/session`。

检查存放 session 数据的目录的权限：

```shell
ls -l /var/lib/php
drwxrwx---. 2 root apache 4096 3月 10 20:34 session
```

这里 session 文件夹是属于 root 账户 和 apache 组的。而设置的 php-fpm 服务的运行账户和组都是 www，没有权限操作这个文件夹，自然不能保存 session 数据了。 

## 解决

直接将 session 文件夹重新设置用户和组，然后重新启动 php-fpm 即可解决。

```shell
chown -R www:www /var/lib/php/session

php-fpm -t
service php-fpm restart
```


