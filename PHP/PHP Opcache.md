### 什么是 Opcache

每一次执行 PHP 脚本的时候，该脚本都需要被编译成字节码，而 OPcache 可以对该字节码进行缓存，这样，下次请求同一个脚本的时候，该脚本就不需要重新编译，这极大节省了脚本的执行时间，从而让应用运行速度更快，同时也节省了服务器的开销。

使用 Opcahce 前，需要确保在服务器上安装了 OPcache，从 PHP 5.5 开始，OPcache 已经成为 PHP 核心的一部分，所以基本上不需要手动去安装这个扩展。

### 配置 Opcache

Opcache 的配置文件一般位于`/etc/php.d/opcache.ini`文件中。如果找不到 Opcache 的配置文件，可以运行一个包含`phpinfo();`函数的脚本来查看。

我们一般需要设置如下的配置即可：

```ini
; 启用 OPcache
opcache.enable=1

; 分配给 OPcache 的内存空间（单位：MB），设置一个大于 64 的值即可。
opcache.memory_consumption=512

; 分配给实际字符串的空间（单位：MB），设置一个大于 16 的值即可。
opcache.interned_strings_buffer=64

; 表示可以缓存多少个脚本，将这个值尽可能设置为与项目包含的脚本数接近（或更大）。
opcache.max_accelerated_files=32531

; 用于重新验证脚本，设置为 0（性能最佳）需要手动在每次 PHP 代码更改后手动清除 OPcache。
; 如果不想手动清除，可以将其设置为 1
; 并通过 opcache.revalidate_freq 配置重新验证间隔
; 这可能会消耗一些性能，因为需要每隔 x 秒检查更改。
opcache.validate_timestamps=0

; 是否在脚本中保留注释，推荐开启该选项，因为一些库依赖于这个配置
opcache.save_comments=1

; 是否允许快速关闭。快速关闭会给一个更快速清理内存的机制，
; 不过，在基准测试中关闭该项可能这会带来一些性能提升，但需要自己去尝试。
opcache.fast_shutdown=0
```

### 其他

在 Opcache 的配置中，推荐`opcache.validate_timestamps`设置为 0，此时需要在每次修改 PHP 代码后手动清除 OPcache。

对于 Laravel 应用来说，可以通过安装下面的这个扩展包从而能够通过 Artisan 命令来清理 Opcache：[Laravel Opcache](https://github.com/appstract/laravel-opcache)。安装完扩展后，只需执行如下命令即可清理 OPcache：

```shell
php artisan opcache:clear
```

