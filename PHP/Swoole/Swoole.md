Swoole 号称重新定义了 PHP，它是一个 PHP 扩展，使得 PHP 可以使用异步的方式执行，就像 Node 一样。而且还能使用 socket，为 PHP 提供了一系列异步 IO、事件驱动、并行数据结构功能。

Swoole 可以广泛应用于互联网、移动通信、企业软件、云计算、网络游戏、物联网（IOT）、车联网、智能家居等领域，可以大大提升项目的性能。

## 一、加速原理

PHP 作为一种服务器脚本语言，一般并不会让其直接与用户进行交互，而是通过 Nginx 一类的 WEB 服务器作为代理，并通过中间程序接口连接 Nginx 和 PHP 程序。

在每次请求进入的时候，负责解析和执行 PHP 的中间接口组件就会从入口文件开始，加载、解析和执行 PHP 程序。而现在开发 PHP 服务的时候，都会采用框架来完成一些基本处理，虽然方便了开发，但是也会引入大量的文件。对每个请求都会重复执行一些完全相同的逻辑，加载相同的配置。

如果负责连通 Nginx 和 PHP 的中间服务能够保存这些共通的配置，和相同的代码执行结果，那么就可以大幅度的提升 PHP 程序的效率。而目前的 PHP-FPM 和 Swoole 就是从这方面入手的，但是两者也有不同之处。

### 1.1 PHP-FPM

目前常见的方式是使用 Nginx + PHP-FPM 来运行 PHP 代码，它们之间是通过 FastCGI 协议来通讯的。

> 在 FastCGI 之前还有 PHP-CGI、CGI 协议，但是效率都不如 FastCGI 协议。

FastCGI，全称 Fast Common Gateway Interface，中文译作“快速公共网管接口”。它是通过预先加载好配置，然后每一个执行的任务只需要复制当前的进程，而无须重新加载配置来提高效率的。*但这里预先加载的配置是指`php.ini`文件，而不是 PHP 框架中的配置。*

这里就能看到：虽然 FastCGI 能够预先加载 PHP 的配置，但是它在处理请求的时候，依然要重新运行一个脚本，像 Laravel 一样的框架，一开始就要加载那么多依赖和文件，依然是一个不小的开销。比如对于 Laravel 的入口文件`public/index.php`的源码：

```PHP
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
```

前面的两条语句会加载相当多的文件，而且加载的文件和执行的逻辑每次都是相同的。

### 1.2 Swoole

Swoole 不仅能够像 FastCGI 预先加载好 PHP 配置文件，还能给让其预先执行一些通用逻辑代码，加载好框架所需的必要配置。

比如，对于 Laravel 的入口文件，使用 Swoole 预先执行完入口文件前两句，把初始配置环境都生成好，自然就会有更好的效率了。下面就是简单更改后的入口文件代码：

```PHP
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$serv = new \Swoole\Server\Http('127.0.0.1', 9501);
$serv->on('request', function ($req, $res) use ($app) {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $res->end($response);
    $kernel->terminate($request, $response);
});

$serv->start();
```

这段代码使用 Swoole 内置的 Web 服务器替代了 PHP-FPM，也就是不再使用 PHP-FPM 解析 PHP 请求，而改用了 Swoole，然后就像 Nginx 配置 php-fpm 一样来配置它就可以了。当然这个改动很简陋，根本无法用于生产环境的，只是提供一个例子。

这样，借助 Swoole 服务器常驻内存的特性，在收到请求之前，就已经把依赖和配置加载干净了，剩下的就是处理请求了。

## 二、协程

PHP 5.4 开始引入了协程概念和相关方法，通过`yield`关键词即可控制程序主动让出 CPU 控制权，但使用这种方式开发协程需要做较多的基础代码开发。

Swoole 则提供了协程开发所需要的基础方法，使得协程开发非常方便，而且 Swoole 在底层会自动进行协程调度。

### 2.1 协程调度

Swoole 的协程默认是基于 IO 调度，程序中有阻塞会自动让出当前协程。如果是 IO 密集型的场景，可以表现得很不错。但是对于 CPU 密集型的场景，会导致一些协程因为得不到 CPU 时间片被饿死。Swoole 的抢占式调度，可以满足实现有些场景下的不均衡调度带来的问题。

想要做抢占式调度，对于 PHP 来说，有两个途径：

1. 单线程的 PHP 的执行流，通过执行指令做文章，可以在 PHP 执行流程中注入逻辑，以检查执行时间，再加上 Swoole 的协程能力，可以在不同的协程中切换，以达到抢占 CPU 的目的。
2. 考虑另开线程，负责检查当前执行协程执行时间。

注入指令的路数基本是无法得到官方的支持，只能选择多开一个线程，只负责检查当前协程。具体的做法是：利用 PHP-7.1.0 引入的 VM interrupt 机制，默认每隔 5ms 检查一下当前协程是否达到最大执行时间，默认为10ms，如果超过，则让出当前协程，达到被其他协程抢占的目的。

如下代码：

```PHP
<?php
Co::set(['enable_preemptive_scheduler' => 1]);

$flag = 1;
$start = microtime(1);
echo "start\n";

go(function () use (&$flag) {
    echo "coro 1 start to loop\n";
    $i = 0;
    for (;;) {
        if (!$flag) {
            break;
        }
        $i++;
    }
    echo "coro 1 can exit\n";
});
    
$end = microtime(1);
$msec = ($end - $start) * 1000;
echo "use time $msec\n";

go(function () use (&$flag) {
    echo "coro 2 set flag = false\n";
    $flag = false;
});
echo "end\n";

// 输出结果
// start
// coro 1 start to loop
// use time 11.121988296509
// coro 2 set flag = false
// end
// coro 1 can exit
```

可以发现，代码逻辑可以从第一个协程的死循环中自动 yield 出来，执行第二个协程。如果没有这个特性，第二个协程永远不会被执行，导致被饿死。而这样做，第二个协程可以顺利被执行，最后执行结束后，第一个协程也会接着继续往下执行，达到第二个协程主动抢占第一个协程 CPU 的效果。

这个特性在生产环境非常有用，尤其是对于实时系统或者响应时间比较敏感的场景。

## 注意事项

1. 不要在代码中执行`sleep`以及其他睡眠函数，这样会导致整个进程阻塞。
2. `exit/die`方法是危险的，会导致 worker 进程退出。
3. 可通过`register_shutdown_function`来捕获致命错误，在进程异常退出时做一些请求工作。
4. Swoole 不支持`set_exception_handler`，必须使用`try/catch`方式处理异常。
5. PHP 代码中如果有异常抛出，必须在回调函数中进行`try/catch`捕获异常，否则会导致工作进程退出。
6. Worker 进程不得共用同一个 Redis 或 MySQL 等网络服务客户端，Redis/MySQL 创建连接的相关代码可以放到`onWorkerStart`回调函数中。
7. 由于 Swoole 是常驻内存的，所以加载类/函数定义的文件后不会释放。因此引入类/函数的 php 文件时必须要使用`include_once/require_once`，否会发生`cannot redeclare function/class`的致命错误。



