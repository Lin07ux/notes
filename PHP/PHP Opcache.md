### 什么是 Opcache

每一次执行 PHP 脚本的时候，该脚本都需要被编译成字节码，而 OPcache 可以对该字节码进行缓存，这样，下次请求同一个脚本的时候，该脚本就不需要重新编译，这极大节省了脚本的执行时间，从而让应用运行速度更快，同时也节省了服务器的开销。

使用 Opcahce 前，需要确保在服务器上安装了 OPcache，从 PHP 5.5 开始，OPcache 已经成为 PHP 核心的一部分，所以基本上不需要手动去安装这个扩展。

### 常用配置

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

### 配置说明

```conf
[opcache]
zend_extension = "G:/PHP/php-5.5.6-Win32-VC11-x64/ext/php_opcache.dll"
 
; Zend Optimizer + 的开关, 关闭时代码不再优化.
opcache.enable=1
 
; Determines if Zend OPCache is enabled for the CLI version of PHP
opcache.enable_cli=1
 
 
; Zend Optimizer + 共享内存的大小, 总共能够存储多少预编译的 PHP 代码(单位:MB)
; 推荐 128
opcache.memory_consumption=64
 
; Zend Optimizer + 暂存池中字符串的占内存总量.(单位:MB)
; 推荐 8
opcache.interned_strings_buffer=4
 
 
; 最大缓存的文件数目 200  到 100000 之间
; 推荐 4000
opcache.max_accelerated_files=2000
 
; 内存“浪费”达到此值对应的百分比,就会发起一个重启调度.
opcache.max_wasted_percentage=5
 
; 开启这条指令, Zend Optimizer + 会自动将当前工作目录的名字追加到脚本键上,
; 以此消除同名文件间的键值命名冲突.关闭这条指令会提升性能,
; 但是会对已存在的应用造成破坏.
opcache.use_cwd=0
 
 
; 开启文件时间戳验证 
opcache.validate_timestamps=1
 
 
; 2s检查一次文件更新 注意:0是一直检查不是关闭
; 推荐 60
opcache.revalidate_freq=2
 
; 允许或禁止在 include_path 中进行文件搜索的优化
;opcache.revalidate_path=0
 
 
; 是否保存文件/函数的注释   如果apigen、Doctrine、 ZF2、 PHPUnit需要文件注释
; 推荐 0
opcache.save_comments=1
 
; 是否加载文件/函数的注释
;opcache.load_comments=1
 
 
; 打开快速关闭, 打开这个在PHP Request Shutdown的时候会收内存的速度会提高
; 推荐 1
opcache.fast_shutdown=1
 
;允许覆盖文件存在（file_exists等）的优化特性。
;opcache.enable_file_override=0
 
 
; 定义启动多少个优化过程
;opcache.optimization_level=0xffffffff
 
 
; 启用此Hack可以暂时性的解决”can’t redeclare class”错误.
;opcache.inherited_hack=1
 
; 启用此Hack可以暂时性的解决”can’t redeclare class”错误.
;opcache.dups_fix=0
 
; 设置不缓存的黑名单
; 不缓存指定目录下cache_开头的PHP文件. /png/www/example.com/public_html/cache/cache_ 
;opcache.blacklist_filename=
 
 
; 通过文件大小屏除大文件的缓存.默认情况下所有的文件都会被缓存.
;opcache.max_file_size=0
 
; 每 N 次请求检查一次缓存校验.默认值0表示检查被禁用了.
; 由于计算校验值有损性能,这个指令应当紧紧在开发调试的时候开启.
;opcache.consistency_checks=0
 
; 从缓存不被访问后,等待多久后(单位为秒)调度重启
;opcache.force_restart_timeout=180
 
; 错误日志文件名.留空表示使用标准错误输出(stderr).
;opcache.error_log=
 
 
; 将错误信息写入到服务器(Apache等)日志
;opcache.log_verbosity_level=1
 
; 内存共享的首选后台.留空则是让系统选择.
;opcache.preferred_memory_model=
 
; 防止共享内存在脚本执行期间被意外写入, 仅用于内部调试.
;opcache.protect_memory=0
```

### 其他

在 Opcache 的配置中，推荐`opcache.validate_timestamps`设置为 0，此时需要在每次修改 PHP 代码后手动清除 OPcache。

对于 Laravel 应用来说，可以通过安装下面的这个扩展包从而能够通过 Artisan 命令来清理 Opcache：[Laravel Opcache](https://github.com/appstract/laravel-opcache)。安装完扩展后，只需执行如下命令即可清理 OPcache：

```shell
php artisan opcache:clear
```

