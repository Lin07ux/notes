> 转摘：
> 1. [Laravel-Facade实现原理](http://ju.outofmemory.cn/entry/136407)
> 2. [Laravel之Contracts和Facades](https://segmentfault.com/a/1190000004946198)

在 Laravel 中，经常可以使用一个类的静态方法来方便快捷的进行调用，但是这个类一般又无法在 Laravel 中找到直接对应的定义文件。这其实是 Facades 为应用程序的服务容器中可用的类提供了一个「静态」接口。Laravel Facades 作为在服务容器内基类的「静态代理」

比如，经常这样调用系统核心类：`App::make('Test')`，而`\App`类的原文件并不能找到，更不能找到对应的静态方法`make()`了。实际上`make()`是属于`Illuminate\Foundation\Application`类的方法。在`vendor/laravel/framework/src/Illuminate/Foundation/Application.php`文件中可以找到对应的方法声明。

既然`\App`类的定义不存在，为什么能够直接调用呢？下面从源码的流程看一下。

> 下面的代码摘自 Laravel 5.3.19 版本。

### 1. 注册类别名

首先，虽然`\App`这个类的定义不存在，但是这个类是可以直接在代码中调用的，因为它是一个类的别名。看下`config/app.php`中定义的`aliases`数组：

```php
'aliases' => array(  
   'App'           => 'Illuminate\Support\Facades\App',
   'Artisan'       => 'Illuminate\Support\Facades\Artisan',
   'Auth'          => 'Illuminate\Support\Facades\Auth',
   // ...
];
```

可以看到，这里配置了类`Illuminate\Support\Facades\App`的别名就是`\App`。而类别名的注册则是在系统启动的时候进行注册的。通过调用`\Illuminate\Contracts\Foundation\Bootstrap\RegisterFacades`类的`bootstrap`方法来注册：

```php
class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance(array_merge(
            $app->make('config')->get('app.aliases', []),
            $app->make(PackageManifest::class)->aliases()
        ))->register();
    }
}
```

在`Illuminate\Foundation\AliasLoader`的处理中，会将其`load()`方法注册到 PHP 的 autoload 方法序列的最前面：

```php
public function register()
{
    if (! $this->registered) {
        $this->prependToLoaderStack();

        $this->registered = true;
    }
}

protected function prependToLoaderStack()
{
    spl_autoload_register([$this, 'load'], true, true);
}

public function load($alias)
{
   if (isset($this->aliases[$alias])) {
       return class_alias($this->aliases[$alias], $alias);
   }
}
```

可以看到，最终是通过 PHP 自带的`class_alias()`方法来为 Facades 的类注册一个简短的别名，而且这个类别名是惰性加载的，只有在用到的时候才会加载类定义。所以可以随意添加类别名的注册，而不会影响系统的性能。

### 2. Facade 类

通过上面的别名注册知道：`\App`类实际上是`Illuminate\Support\Facades\App`类。根据 PSR-4 规范，找到`vendor/laravel/framework/src/Illuminate/Support/Facades/App.php`文件，下面是它的全部源码。

```php
<?php

namespace Illuminate\Support\Facades;

/**
 * @see \Illuminate\Foundation\Application
 */
class App extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
```

它只定义了一个`getFacadeAccessor()`的静态方法，返回字符串`'app'`。而根据注释`@see \Illuminate\Foundation\Application`可以猜到它和`\Illuminate\Foundation\Application`类有关的，但是`\Illuminate\Foundation\Application`类中没有静态的`make()`方法，只有一个一般的`public`的`make()`方法。那他是怎么能够通过静态方式调用这个`make()`方法的呢？

### 3. __callStatic 魔术方法

根据上面的代码，查看`Illuminate\Support\Facades\App`的父类`Illuminate\Support\Facades\Facade`，可以看到里面有一个`__callStatic()`魔术方法，而这个方法就是在调用类不存在的静态方法的时候，自动被调用的。查看下该类的定义：

```php
public static function __callStatic($method, $args)
{
   $instance = static::getFacadeRoot();

   if (! $instance) {
       throw new RuntimeException('A facade root has not been set.');
   }

   return $instance->$method(...$args);
}
```

发现他获取了一个`$instance`实例变量，然后通过这个实例变量调用对应的方法，并传入相关的参数。现在再看看`$instance`变量到底是什么。

找到`getFacadeRoot()`方法，代码如下：

```php
public static function getFacadeRoot()
{
   return static::resolveFacadeInstance(static::getFacadeAccessor());
}
```

也很简单，就是调用了`getFacadeAccessor()`和`resolveFacadeInstance()`两个静态方法。其中，`getFacadeAccessor()`静态方法前面介绍了，就是返回一个字符串，对于`\App`类就是返回字符串`'app'`，那么这里就是调用了`resolveFacadeInstance('app');`。

继续看`resolveFacadeInstance()`：

```php
protected static function resolveFacadeInstance($name)
{
   if (is_object($name)) {
       return $name;
   }

   if (isset(static::$resolvedInstance[$name])) {
       return static::$resolvedInstance[$name];
   }

   return static::$resolvedInstance[$name] = static::$app[$name];
}
```

很简单，就是获取一个实例。前面的两个`if`语句分别是判断是否传入的是对象，或者是否有缓存(之前已经生成过了)。最关键的就是最后一行`static::$app[$name]`。这里的静态变量`$app`是在系统启动过程中实例化的`Application`对象。

`$app`实例是一个对象，为什么能够使用数组的方式获取对应的变量呢？这是由于`Illuminate\Foundation\Application`继承于`Container`类，而`Illuminate\Container\Container`类实现了`ArrayAccess`接口：

```php
<?php

namespace Illuminate\Container;

// ...

class Container implements ArrayAccess, ContainerContract
{
    // ...
    
    public function offsetGet($key)
    {
        return $this->make($key);
    }
    
    // ...
}
```

所以，最终变量`$instance`变量的生成方式就是`$app->make('app')`。至于这样怎么就能够得到`Illuminate\Foundation\Application`实例就涉及到了 IoC 和 Service Provider 方面的知识了。

### 4. 总结

通过上面的分析，Facade 的实现原理如下：

* 继承`Illuminate\Support\Facades\Facade`基类；
* 实现`getFacadeAccessor()`方法，此方法规定从容器中解析什么，通俗作用就是返回服务容器绑定类的别名；
* Facade 基类通过`__callStatic()`从定义的 Facade 中调用解析的对象。

另外，要完成这一个代理，并实现 Laravel 那样的简洁的类名，则需要先进行类别名的注册、IoC 容器的实现等一系列的配套工作。

### 5. 使用自定义的 Facade

那么，如果要自定义一个 Facade 呢？可以通过以下的步骤来实现：

1. 创建一个基础类，命名为`App\TestFacades`
    
    ```php
    <?php
    namespace App\Test;
    
    class TestFacades{
       public function testingFacades(){
          echo "Testing the Facades in Laravel.";
       }
    }
    ```

2. 创建一个 Service Providers 类，命名为`App\Providers\TestFacadesServiceProvider`：

    * 首先使用如下的命令创建该类：
    
    ```shell
    php artisan make:provider TestFacadesServiceProvider
    ```
    
    * 然后实现该类的`register()`方法

    ```php
    public function register() {
       // 可以这么绑定，这需要 use App;
       // App::bind('test',function() {
       //     return new \App\Test\TestFacades;
       // });
     
       // 也可以这么绑定，推荐
       // 这个 test 对应于 Facade 的 getFacadeAccessor 返回值
       $this->app->bind("test", function(){
            // 给这个 Facade 返回一个代理实例
            // 所有对 Facade 的调用都会被转发到该类对象下。
            return new \App\Test();
        });
    }
    ```
    
3. 创建一个 Facade 类

    ```php
    <?php
    namespace App\Test\Facades;
    use Illuminate\Support\Facades\Facade;
    
    class TestFacades extends Facade{
       protected static function getFacadeAccessor() { return 'test'; }
    }
    ```
    
4. 在`config/app.php`注册 Service Provider 类

    ```php
    'providers' => [
        // ...
        App\Providers\TestFacadesServiceProvider::class,
        // ...
    ];
    ```

5. 在`config/app.php`注册自定义 Facade 的别名

    ```php
    'aliases' => [
        // ...
        'Test' => App\Test\Facades\TestFacades::class,
        // ...
    ];
    ```

6. 使用测试

    ```php
    Route::get('/facadeex', function(){
       return Test::testingFacades();
    });
    ```

