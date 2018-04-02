PHP session 的锁机制也许不是每个人都很清楚，如果不注意就会造成程序运行慢的问题。如果能了解其背后的机制，且能预判这种机制给的 PHP 程序所带来的影响，并且避免它，那么 session 阻塞问题就根本算不上什么问题。

要了解 PHP session 的锁机制，就需要从 PHP 的 session 配置和`session_start()`开始后的处理流程开始逐步分析。

### 1. PHP session 配置

默认情况下，PHP 使用的是 file 形式将会话信息存储在`/var/lib/php/session`路径中的：

```ini
session.save_handler = files
session.save_path = "/var/lib/php/session"
```

### 2. session_start()

调用`session_start()`后，它会做如下的工作：

如果用户还没有一个 session cookie，那么 PHP 将产生一个新的 ID，并设置到用户机器的 cookie 中。如果是一个已访问过的用户，那么他会将 cookie 发送给你的 web 服务器，PHP 则会解析它，并从`session.save_path`路径下加载到相应的 session 数据。

> 默认情况下，PHP 配置中，会设置`session.auto_start = 0`，不会自动启动 session。如果设置为`1`，那么会自动调用`session_start()`。

### 3. 与锁相关的示例

上面两步还看不出 session 锁机制，下面会实用一个实例来明确的看到锁机制的出现。

创建有如下内容的两个 PHP 脚本：

**a.php**

```php
<?php

session_start();

$_SESSION['a'] = date('H:i:s');
echo $_SESSION['a'];

// session_write_close();

sleep(5);
```

**b.php**

```php
<?php

session_start();

$_SESSION['b'] = date('H:i:s');
echo $_SESSION['b'];

// session_write_close();

sleep(5);
```

在不启动会话的时候，脚本之间执行之间不会有干扰(在系统资源允许的情况下)，下面看下开启 session 之后的情况：

1. 现在在浏览器中同时访问这两个脚本，这两个页面显示的时间至少是相差 5s 的。也就是说：一个用户，即便是同时访问两个脚本，那么后访问的脚本需要等待前面的脚本完全退出时才能开始执行。

2. 在浏览器中打开两个 tab，同时访问相同的一个脚本，这两个 tab 中显示的时间相差至少是 5s。也就是说：一个用户，在两个 tab 中访问同一个页面，后一次访问需要等待前一次访问结束之后才能开始执行。

3. 使用两个浏览器（或者使用浏览器的隐私模式）同时访问同一个脚本，显示出来的时间是很接近的。这说明：不同用户访问同一个脚本，不会相互干扰其执行顺序。

4. 将两个脚本中的`session_write_close()`注释去掉，在同一个浏览器中同时访问这两个脚本，显示的时间很接近，这说明：提早关闭会话(将会话数据写入文件中)可以避免后面的脚本访问被推延执行。

5. 取消`session_write_close()`注释后，在同一个浏览器中同时访问同一个脚本，显示的时间依旧相差有 5s 以上，这说明：提早关闭会话，同一个用户访问同一个脚本时，仍旧会有干扰，后一个访问需要等待前一个访问结束之后才能执行。

6. 取消`session_write_close()`注释后，在两个浏览器中，同时访问同一个脚本，显示的时间很接近，这说明：提早关闭会话后，不同用户访问同一个脚本，不会造成相互干扰。

### 4. 分析

通过上面的示例，可以看到会话确实会对代码的执行有影响，而这个影响则是由 session 锁机制导致的。

当调用`session_start()`，操作系统会锁住 session 文件。大多数文件锁的实现都是 flock，在 Linux 上，它也用于防止定时任务的重复执行或者其它文件锁定工作。该 session 的文件锁会保持到脚本执行结束或者被主动移除。这是一个读写锁：**任何对 session 读取都必须等到锁被释放之后**。

在 Linux 机器上，一个 session 文件锁看起来就像这样子：

```
$ fuser /var/lib/php/session/sess_cdmtgg3noi8fb6j2vqkaai9ff5
/var/lib/php/session/sess_cdmtgg3noi8fb6j2vqkaai9ff5:  2768  2769  2770
```

`fuser`报告了 3 个进程的 PID，这些进程要么正持有此文件锁，或者正在等待此文件锁的释放。

可以继续使用`lsof`获取当前持有文件锁的 PID 以及指令：

```
$ lsof /var/lib/php/session/sess_cdmtgg3noi8fb6j2vqkaai9ff5
COMMAND PID  USER      FD  TYPE DEVICE SIZE/OFF NODE   NAME
php-fpm 2769 http_demo 5uW REG  253,1  0        655415 sess_cdmtgg3noi8fb6j2vqkaai9ff5
```

锁本身并不是问题。它可以保护 session 文件中的数据，防止多个同时写入损毁数据或者覆盖之前的数据。但是当第二个并发的 PHP 执行想要获取同一个 PHP 会话的时候，就会造成问题了。这也是为什么上面的示例中后面的访问会比前面的访问延迟 5s 的原因(5s 是脚本中设置`sleep()`的时间)。

当使用`session_write_close()`（或者使用其别名函数`session_commit()`）后，PHP 会将会话数据写会会话文件中，并释放其所持有的此文件的读写锁，然后就会被另一个等待的脚本获取到会话的读写权限，进而开始脚本的执行。这就是为什么上面的示例中，取消`session_write_close()`之后，前后两次访问不同的脚本显示的时间基本相同的原因。

对于每个不同的用户（分别拥有不同的会话即为两个不同的用户）而言，其会话文件也不相同，那么相互之间的访问就不会对同一个文件进行读写，自然也就不会有干扰。

> **疑问：**不知道为什么，脚本中有调用`session_write_close()`，可是同一个用户访问同一个脚本依旧会有相应的延迟。

### 5. 锁的作用

由于会话锁机制的存在，在绝大多数场景下，这都使得 PHP 对于同一个用户来说，表现得像是一系列同步脚本：一个执行完成后执行下一个，没有平行的请求。即使使用 AJAX 调用这些 PHP 脚本也无济于事。

这样会保证脚本对会话的读写是独占而安全的，避免了数据覆盖的问题。可以想象得到，如果没有这个锁机制存在，两个脚本并行执行的时候，不可避免的会造成一个脚本对会话的写入会覆盖另一个会话刚刚写入的数据。

如果没有会话锁机制时，就会发生类似下面的例子：

Timing   |  script 1                                              |  script 2
-------- | ------------------------------------------------------ | -------------------
0ms      | `session_start();` session 数据被读入到`$_SESSION` 变量中 | `session_start();` session 数据被读入到`$_SESSION`变量中
15ms     | 写入 session 数据：`$_SESSION['payment_id'] = 1;`        | 写入 session 数据：`$_SESSION['payment_id'] = 5;`
350ms    | sleep(1);                                               | 脚本结束，保存 session 数据
450ms    | 脚本结束，保存 session 数据                                |

到这两个访问都结束时，会话中的数据应该是什么？应该是脚本 1 写入的结果，因为脚本 2 所保存的值被脚本 1 最后所保存的值覆盖了。

这是一个非常尴尬，而且又很难排查的并发问题。session 锁机制就可以防止这种情况发生。

绝大多数情况下，这都是在 session 会话数据被写时才会碰到的问题。如果只有一个脚本是读写会话，其他脚本是只读 session 数据（大多数 ajax 请求都是），那么就可以安全地对数据进行多次读取。

另一方面，如果一个长时间运行的脚本，读取了 session 数据并且还会修改 session 数据，而另一个脚本开始执行并且读取到了旧的过时数据 — 这也可能使应用出错。

### 6. 关闭会话锁

如前面的示例所示，尽早使用`session_write_close()`或`session_commit()`提前关闭会话也是一种能够提升页面性能的方式。

另外，从 PHP 7 开始，在调用`session_start()`的时候还可设置额外的选项：

```php
<?php
session_start(['read_and_close' => true]);
```

它先读取了 session 数据，然后立刻释放了锁，这样就不会阻塞其它脚本了。以上语法等同于：

```php
<?php
session_start();
session_write_close();
```

总结如下：

1. 只读取 session 数据，建议打开后，就直接关闭，这时`$_SESSION`变量已经生成了。
2. 有对 session 进行写入地方，建议修改完`$_SESSION`后立即调用关闭
3. 不建议多次打开并且写入，打开和写入文件都是耗费时间的。如果能一次搞定的就不要做多次，除非中间执行很耗时的业务。

总而言之，就是尽量做到随开随关。

### 7. 其他 session 处理器

除了可以使用默认的 file 方式存储会话之外，还可以配置 PHP 使用 Redis、Memcached、MySQL 来设置进行会话处理。这三个处理器各有特点：

* Memcached：很多人都说只要把会话存入到 Memcached 就可以极大的提升会话性能，这没错，但是默认的 Memcached 配置使用了与前面的描述相同的、安全的逻辑：对同一个用户，只要有一个 PHP 脚本使用了 sessions 那它们就会阻塞。当然，可以更改 Memcached 的配置，设置`memcached.sess_locking = 0`就可以关闭会话锁，但此时就需要在脚本中避免对会话数据的随意写入了。

* Redis：目前 Redis 还没有释放支持 session 锁机制的版本，但是已经在测试阶段了。也就说，目前 Redis 是没有锁的。

* MySQL：目前还没有 PHP 扩展实现了使用 MySQL 作为 session 存储的功能。在 PHP 代码中有一个函数`session_set_save_handler()`申明了负责 session 数据读取和写入的类或者方法。也就是说需要自己来决定 session 是否会产生阻塞。

> 关于 Redis 锁机制的开发可以关注这几个 issue：[#37](https://github.com/phpredis/phpredis/issues/37)、[#1181](https://github.com/phpredis/phpredis/pull/1181)、[#1267](https://github.com/phpredis/phpredis/issues/1267)。

### 8. 参考

1. [PHP session锁：如何避免session阻塞PHP请求](https://log.zvz.im/2016/02/27/PHP-session/)
2. [PHP Session可能会引起并发问题](http://justcode.ikeepstudying.com/2015/10/php-session%E5%8F%AF%E8%83%BD%E4%BC%9A%E5%BC%95%E8%B5%B7%E5%B9%B6%E5%8F%91%E9%97%AE%E9%A2%98/)
3. [Memcached Configuration](http://php.net/manual/en/memcached.configuration.php)
4. [PHP Session锁及并发机制 | void session_write_close(void)函数](https://blog.csdn.net/soonfly/article/details/52175578)


