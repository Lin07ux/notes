### 重启 php-fpm 服务

Homestead 中有多个 PHP 版本，对应也有多个 php-fpm 进程在运行。如果修改了某个版本的 PHP 的配置文件，需要重启 php-fpm，则需要指定对应的版本才能正常重启：

```shell
sudo systemctl restart php7.1-fpm.service
```






