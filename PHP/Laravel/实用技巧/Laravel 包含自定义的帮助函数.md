> 转摘：[Laravel-包含你自己的帮助函数](https://zhuanlan.zhihu.com/p/90625927)

在开发时可能会需要用到一些自定义的帮助函数，这可以通过在`composer.json`中添加自动加载配置来实现。但是如果在一个 PHP 文件中添加了很多的帮助函数之后，可读性会很差。

如果在多个文件中分别编写不同类别的帮助函数，那么可以考虑借助 Laravel 的服务提供者来自动引入这些帮助函数的文件。

### 1. 创建服务提供者

首先创建一个`HelperServiceProvider.php`服务提供者文件：

```php
php artisan make:provider HelperServiceProvider
```

### 2. 设置加载逻辑

在刚才创建的服务提供者中的`register()`方法中添加如下的代码：

```php
public function register ()
{
    foreach (glob(app_path('Helpers').'/*.php') as $helper) {
        require_once $helper;
    }
}
```

这段代码会遍历`app/Helpers`目录下的所有 PHP 文件，所以可以在该目录下创建多个 PHP 文件，它们都会被服务提供者自动加载到应用中，从而可以在全部代码中访问。

> 这个服务提供者中的`boot()`方法不会用到，可以去除。

### 3. 注册服务提供者

还需要将这个服务提供者注册到系统中，对于 Laravel，打开`config/app.php`，然后将`HelperServiceProvider`类名放在`AppServiceProvider`前面：

```php
...
App\Providers\HelperServiceProvider::class,
App\Providers\AppServiceProvider::class,
...
```

> 对于 Lumen 则需要在`bootstrap/app.php`文件中手动加载该服务提供者。

### 4. 示例

注册好服务提供者之后，就可以在`app/Helpers`目录中创建 PHP 文件写入帮助函数了。

比如，在该目录中创建一个`carbon.php`文件，代码如下：

```php
<?php

/**
 * Carbon helper
 *
 * @param  string|int  $time
 * @param  string      $tz
 *
 * @return Carbon\Carbon
 */
function carbon ($time = null, $tz = null)
{
    return new \Carbon\Carbon($time, $tz);
}
```

这个文件不需要添加任何的命名空间，这个帮助函数就会被自动加载到应用中了，然后就可以在应用中使用这个函数。

