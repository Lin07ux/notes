> 转摘：[深入理解Laravel中间件](https://segmentfault.com/a/1190000007715254)

## 一、基本

HTTP 中间件为过滤访问应用的 HTTP 请求提供了一个方便的机制。例如，Laravel 默认包含了一个验证用户的中间件，如果没有经过身份验证，中间件将会将用户重定向至登录页面；而如果用户经过了验证，中间件将会允许请求继续在应用中执行下去。

当然，除了身份验证，中间件也可以被用来执行多种多样的任务：一个 CORS 中间件可能负责在所有应用发出去的响应中加入适当的头部，一个日志中间件可能记录所有发送给应用的请求的日志。

Laravel 中间件的概念跟装饰器模式很像。简单来讲，装饰器模式就是在开放-关闭原则下动态的增加或者删除某一个功能。而 Laravel 的中间件也差不多是这个道理，只是代码实现上略有不同：

> 一个请求过来，在执行请求之前，可能要进行 Cookie 加密、开启会话、CSRF 保护等等操作。但是每一个请求不一定都需要这些操作。而且在执行请求之后也可能需要执行一些操作。可以需要根据请求的特性动态的增加一些操作，这些需求正好可以使用装饰器模式解决。

## 二、实现解析

### 2.1 使用中间件的步骤

使用 Laravel 中间件有三个步骤：

1. 生成一个中间件(可以使用`php artisan`命令创建)，这里假设生成一个`TestMiddleware`的中间件；
2. 重写`TestMiddleware`中的`handle`函数，其中代码逻辑写在`$next($request);`之前或者之后表示在执行请求之前或者之后运行这段代码。
3. 在`app/Http/Kernel.php`的`routeMiddleware`注册这个中间件。

### 2.2 简单示例

在深入了解 Laravel 中间件工作流程之前，先尝试实现一个基础版本的中间件代码。下面的代码基本模拟了 Laravel 中间件的工作流程：

```php
interface Middleware
{
    public static function handle(Closure $next);
}

class VerfiyCsrfToekn implements Middleware
{
    public static function handle(Closure $next)
    {
        echo '验证csrf Token <br>';
        $next();
    }
}

class ShowErrorsFromSession implements Middleware
{
    public static function handle(Closure $next)
    {
        echo '开启session <br>';
        $next();
        echo '关闭ession <br>';
    }
}

class AddQueuedCookieToResponse implements Middleware
{
    public static function handle(Closure $next)
    {
        $next();
        echo '添加下一次请求需要的cookie <br>';
    }
}

class EncryptCookies implements Middleware
{
    public static function handle(Closure $next)
    {
        echo '解密cookie <br>';
        $next();
        echo '加密cookie <br>';
    }
}

class CheckForMaintenacceMode implements Middleware
{
    public static function handle(Closure $next)
    {
        echo '确定当前程序是否处于维护状态 <br>';
        $next();
    }
}

function getSlice() {
    return function($stack, $pipe) {
        return function() use ($stack, $pipe) {
            return $pipe::handle($stack);
        }
    }
}

function then() {
    $pipe = [
        'CheckForMaintenacceMode',
        'EncryptCookies',
        'AddQueuedCookieToResponse',
        'StartSession',
        'ShowErrorsFromSession',
        'VerfiyCsrfToekn'
    ];
    
    $pipe = array_reverse($pipe);
    
    $firstSlice = function() {
        echo '请求向路由传递,返回响应 <br>';
    };
    
    $callback = array_reduce($pipe, getSlice(), $firstSlice);
    
    call_user_func($callback);
}

then();
```

代码的输出如下：

```
确定当前程序是否处于维护状态 
解密cookie 
开启session 
共享session中的Error变量 
验证csrf Token 
请求向路由传递,返回相应 
关闭ession 
添加下一次请求需要的cookie 
加密cookie 
```

代码的关键处在于`array_reduce`部分。通过这个函数，将定义的中间件串联起来。由于中间件数组`$pipe`中最前面的中间件经过`array_reduce`处理后会被放到最里面，所以在`then()`函数中还调用了一次`array_reverse()`函数来保证最终的执行顺序和定义中间件数组时的顺序一致。

`array_reduce()`函数有三个参数：

* 第一个参数是一个数组，表示迭代的数组；
* 第二个参数是一个方法，用于对每个数组中的元素进行处理。该方法接收两个参数，第一个参数是前面一次迭代处理的结果，第二个参数则为当前迭代的数组元素；
* 第三个参数可选，表示迭代的初始值，在进行第一次迭代的时候作为迭代处理方法(第二个参数)的第一个参数。

在这里，经过`array_reduce()`函数处理后，返回的是一个闭包函数，可以直接被调用。

### 2.3 Laravel 源码解析

通过上面的代码，可以大致了解了中间件调用实现的原理。

#### 2.3.1 编写中间件

在使用一个中间件的时候，自然是要先要有该中间件的代码。这个步骤可以使用 Laravel artisan 命令中的`make:middleware`来实现，也可以自己手动创建。

中间件类文件的存放位置没有特殊要求，一般会放在`app/Http/Middleware`目录中。

#### 2.3.2 注册中间件

有了中间件的类，还需要对其注册进行注册，才能在应用中被使用，因为这是 Laravel 知道其存在的唯一方式。

注册中间件需要在`app/Http/Kernel.php`文件中进行。此文件包含默认 Laravel 提供的所有已注册中间件的列表，包含三个主要的中间件组：

* `$middleware` 应用程序的全局 HTTP 中间件，这些中间件在应用程序的每个请求期间运行。
* `$middlewareGroups` 应用程序的路由中间件组，可以直接将一组中间件应用到路由中，而不需要一个个的注册使用。
* `$routeMiddleware` 应用程序的路由中间件，这里的中间件都是注册到 Laravel 中，但是没有指定要分配到哪些请求中使用的，可以在中间件组中调用，也可以在控制器中引用。
 对自定义中间件来说，所谓注册，也只是在`$routeMiddleware`数组中添加一项而已。

#### 2.3.3 中间件处理流程

**1. 起始**

有关中间件的步骤从应用的入口文件`index.php`中处理请求的方法开始。

```php
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
```

这里`kernel`有一个继承链，`handle`方法的真正实现在`vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php`文件中。

**2. 处理请求**

转到`Http/Kernel.php`类。这类有两个`protected`的成员：`$middleware`跟`$routeMiddleware`。看到这个就可以联想到注册中间件的那个文件`app/Http/Kernel.php`，它正是继承`Illuminate/Foundation/Http/Kernel.php`的，所以当在`app/Http/Kernel.php`注册的全局中间件会在这里被处理。

接着看`Kernel::handle()`方法，调用了`sendRequestThroughRouter`函数。进入这个函数：

```php
protected function sendRequestThroughRouter($request)
{
    $this->app->instance('request', $request);

    Facade::clearResolvedInstance('request');

    $this->bootstrap();

    //这里之后就会去处理中间件
    return (new Pipeline($this->app))
        ->send($request)
        ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
        ->then($this->dispatchToRouter());
}
```

可以看到这里实例化了一个`Pipeline`。这个可以称之为管道，如果懂得 linux 中的管道概念的话，那么就可以理解这里命名为 Pipeline 的原因：客户端发过来的请求被一个又一个的中间件处理，前一个中间件处理往之后的结果交给了下一个中间价，类似管道一样。

pipeline 之后调用了三个方法`send`，`through`，`then`。这三个方法分别做了

* 传递客户端请求`request`到 Pipeline 对象
* 传递在`app/Http/Kernel.php`中定义的全局中间件到 Pipeline 对象
* 执行中间件，其中`then`方法的参数`$this->dispatchToRouter()`返回的是一个回调函数，这个函数可以类比为我们示例代码中输出请求向路由传递，返回相应的函数。因为这里涉及到路由的工作流程，所以暂时这么理解。

**3. 管道处理**

接下来看`then`方法的代码： 
```php
public function then(Closure $destination)
{
    $firstSlice = $this->getInitialSlice($destination);
    $pipes = array_reverse($this->pipes);

    return call_user_func(
        array_reduce($pipes, $this->getSlice(), $firstSlice), $this->passable
    );
}
```

发现`then`方法的代码跟上面的示例代码有点类似，其中：

* `$pipes`就是保存了在`app/Http/Kernel.php`中定义的全局中间件，具体逻辑可以看`through`方法；
* `$this->passable`中保存的是客户端请求的实例对象`requset`。具体逻辑可以从`send`方法看到；

而`getInitialSlice`调用的函数只是对原有的`destination`添加了一个`$passable`的参数，这个`$passabele`就是请求实例。

```php
protected function getInitialSlice(Closure $destination)
{
    return function ($passable) use ($destination) {
        return call_user_func($destination, $passable);
    };
}
```

**4. 中间件处理**

了解了`then`函数里的所有信息，下面执行的操作就跟上面的示例代码基本一样了。唯一不同的地方在于，Laravel 代码中`getSlice`返回的闭包函数。从上面的例子可以知道返回的闭包函数才是调用中间件的核心，来看下`getSlice`到底是怎么工作的：

```php
protected function getSlice()
{
    return function ($stack, $pipe) {
        return function ($passable) use ($stack, $pipe) {
            // If the pipe is an instance of a Closure, we will just call it directly but
            // otherwise we'll resolve the pipes out of the container and call it with
            // the appropriate method and arguments, returning the results back out.
            if ($pipe instanceof Closure) {
                return call_user_func($pipe, $passable, $stack);
            } else {
                list($name, $parameters) = $this->parsePipeString($pipe);

                return call_user_func_array([$this->container->make($name), $this->method],
                                            array_merge([$passable, $stack], $parameters));
            }
        };
    };
}
```

`getSlice`方法的大体逻辑跟上面示例代码的逻辑差不多，只是示例代码中调用中间件的`handle`函数的时候直接使用`$pipe::handle($stack)`，因为示例中的中间件里面的函数是静态函数。而在 Laravel 中，这里只是传递了要实例化的中间件的类名，所以在`getSlice`里面还要去实例化每个要执行的中间件。

```php
list($name, $parameters) = $this->parsePipeString($pipe);

return call_user_func_array([$this->container->make($name), $this->method], array_merge([$passable, $stack], $parameters));
```

上面两句代码中，第一句根据中间件的类名去分离出要实例化的中间件类，和实例化中间件可能需要的参数。然后`call_user_func_array`里面由于糅合了几行代码，所以这里分解一下，函数包含两个参数：

* `[$this->container->make($name), $this->method]`为调用某个类中方法的写法。其中`$this->container->make($name)`是使用服务容器去实例化要调用的中间件对象，`$this->method`就是`handle`函数；
* `array_merge([$passable, $stack], $parameters)`为调用中间件所需要的参数。可以看到调用中间件的`handle`必然会传递两个参数：`$passable`(请求实例`$request`)和下一个中间件的回调函数`$stack`。

到这里 Laravel 中间件的部分就结束了，这部分代码有点难以理解，尤其是一些具有函数式特性的函数调用，比如`array_reduce`，以及大量的闭包函数和递归调用，大家一定要耐心分析，可以多使用`dd`函数和`xdebug`工具来逐步分析。

这里扯一句，方法`then`里面可以看到有`getSlice`和`$firstSlice`的命名，这里`slice`是一片的意思，这里这样子的命名方式是一种比喻：中间件的处理过程就上面讲的类似管道，处理中间件的过程比作剥洋葱，一个中间件的执行过程就是剥一片洋葱。



