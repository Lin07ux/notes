`Illuminate\Http\Request::user()`可以获取到当前认证的用户，其定义如下：

```php
public function user($guard = null)
{
    return call_user_func($this->getUserResolver(), $guard);
}
```

这里的`getUserResolver()`会返回通过`setUserResolver()`方法设置的匿名方法。而这个匿名方法会在两个地方被设置。

### 1. 预处理设置

在进行初始化的时候，会通过`Laravel\Lumen\Application::prepareRequest()`方法，为`Illuminate\Http\Request`实例注入查询用户的函数：

```php
$request->setUserResolver(function ($guard = null) {
    return $this->make('auth')->guard($guard)->user();
});
```

可以看到，还是会通过`Illuminate\Auth\AuthManager`生成相应 guard 来获取当前认证的用户。

### 2. 认证服务提供者设置

同时，由于系统在加载的时候，还会自动注入`Illuminate/Auth/AuthServiceProvider`服务提供者，而在这个服务提供者中，则会通过`registerRequestRebindHandler()`方法设置了`Request::userResolver`：

```php
protected function registerRequestRebindHandler()
{
    $this->app->rebinding('request', function ($app, $request) {
        $request->setUserResolver(function ($guard = null) use ($app) {
            return call_user_func($app['auth']->userResolver(), $guard);
        });
    });
}
```

这里的`$app['auth']->userResolver()`则是在`Illuminate\Auth\AuthManager::__construct()`中定义的：

```php
$this->userResolver = function ($guard = null) {
    return $this->guard($guard)->user();
};
```

### 3. 结论

所以，`Illuminate\Http\Request::user()`方法获取当前认证用户最终是通过`Illuminate\Auth\AuthManager`来实现的。


