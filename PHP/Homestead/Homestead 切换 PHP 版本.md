> 转摘：[Homestead 下切换 PHP 版本](https://learnku.com/articles/16881)

### 1. update-alternatives

通过 Homestead 中内置的`update-alternatives`命令可以方便选择相应的 php 版本，也可以更改 php-config 和 phpize 的版本：

```shell
> sudo update-alternatives --config php
There are 4 choices for the alternative php (providing /usr/bin/php).

  Selection    Path             Priority   Status
------------------------------------------------------------
  0            /usr/bin/php7.2   72        auto mode
  1            /usr/bin/php5.6   56        manual mode
  2            /usr/bin/php7.0   70        manual mode
  3            /usr/bin/php7.1   71        manual mode
* 4            /usr/bin/php7.2   72        manual mode

Press <enter> to keep the current choice[*], or type selection number:
```

**执行`sudo update-alternatives --config php`命令**后会显示当前支持的 php 版本，输入需要的版本前面的序号(如输入 3 表示选择 PHP 7.1)，回车之后即可切换到 PHP 7.1 版本了。

对于 php-config 和 phpize 也是一样的方式：

```php
sudo update-alternatives --config php-config
sudo update-alternatives --config phpize
```

### 2. php + version

还有一个更快捷的方法可以切换 PHP 版本，就是直接**使用`php+主版本+次版本`命令**即可，比如：

```shell
# 自动切换到 php 7.1 版本
php71

# 自动切换到 php 5.6 版本
php56
```


