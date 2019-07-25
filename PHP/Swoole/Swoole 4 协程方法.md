Swoole4 提供了强大的 PHP CSP 协程编程模式。底层提供了 3 个关键功能，可以方便地实现各类功能：

* `go()`
* `chan()`
* `defer()`

这 3 个功能底层实现全部为内存操作，没有任何 IO 资源消耗。就像 PHP 的 Array 一样是非常廉价的。如果有需要就可以直接使用。这与 socket 和 file 操作不同，后两者需要向操作系统申请端口和文件描述符，读写可能会产生阻塞的 IO 等待。

### 协程并发

通过`go()`方法调用的函数是在协程级别并发。

比如，对于如下两个函数：

```php
function test1() 
{
    sleep(1);
    echo "b";
}
    
function test2() 
{
    sleep(2);
    echo "c";
}
```

如果按照一般的调用方式进行顺序执行，则至少需要 3s 的时间：

```php
test1();
test2();
```

如果使用`go()`方法包裹运行，则只需要 2s 左右的时间即可完成：

```php
go(function () 
{
    sleep(1);
    echo "b";
});
    
go(function () 
{
    sleep(2);
    echo "c";
});
```

### 协程通信

`go()`方法可以实现程序的并发执行，但是没办法解决协程之间的通信。这需要使用 Swoole4 中的通道 Channel (也就是`chan`类)来解决。

比如，有 2 个协程并发执行，另外一个协程，需要依赖这两个协程的执行结果，那么可以使用`new chan()`建立一个通道，一个协程监听通道，另外两个协程将结果写入通道即可。如下所示：

```php
$chan = new chan(2);

# 协程1
go (function () use ($chan) {
    $result = [];
    for ($i = 0; $i < 2; $i++)
    {
        $result += $chan->pop();
    }
    var_dump($result);
});

# 协程2
go(function () use ($chan) {
   $cli = new Swoole\Coroutine\Http\Client('www.qq.com', 80);
       $cli->set(['timeout' => 10]);
       $cli->setHeaders([
       'Host' => "www.qq.com",
       "User-Agent" => 'Chrome/49.0.2587.3',
       'Accept' => 'text/html,application/xhtml+xml,application/xml',
       'Accept-Encoding' => 'gzip',
   ]);
   $ret = $cli->get('/');
   // $cli->body 响应内容过大，这里用 Http 状态码作为测试
   $chan->push(['www.qq.com' => $cli->statusCode]);
});

# 协程3
go(function () use ($chan) {
   $cli = new Swoole\Coroutine\Http\Client('www.163.com', 80);
   $cli->set(['timeout' => 10]);
   $cli->setHeaders([
       'Host' => "www.163.com",
       "User-Agent" => 'Chrome/49.0.2587.3',
       'Accept' => 'text/html,application/xhtml+xml,application/xml',
       'Accept-Encoding' => 'gzip',
   ]);
   $ret = $cli->get('/');
   // $cli->body 响应内容过大，这里用 Http 状态码作为测试
   $chan->push(['www.163.com' => $cli->statusCode]);
});
```

执行结果可能为：

```
array(2) {
  ["www.qq.com"] => int(302)
  ["www.163.com"] => int(200)
}
```

### 延迟任务

在协程编程中，可能需要在协程退出时自动实行一些任务，做清理工作。类似于 PHP 的`register_shutdown_function`方法，在 Swoole4 中可以使用`defer`实现。

```PHP
Swoole\Runtime::enableCoroutine();

go(function () {
    echo "a";
    defer(function () {
        echo "~a";
    });
    echo "b";
    defer(function () {
        echo "~b";
    });
    sleep(1);
    echo "c";
});
```

执行结果为：

```
abc~b~a
```



