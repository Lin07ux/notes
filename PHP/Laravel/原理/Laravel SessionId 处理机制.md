### 设置 session 名称

在 Laravel 的配置文件`config/session.php`中可以设置 Session Cookie Name，比如这个项目中设置名称为“sns_session”：

```php
/*
|--------------------------------------------------------------------------
| Session Cookie Name
|--------------------------------------------------------------------------
|
| Here you may change the name of the cookie used to identify a session
| instance by ID. The name specified here will get used every time a
| new session cookie is created by the framework for every driver.
|
*/
 
'cookie' => 'sns_session',
```

### SessionId 变化原因

每次刷新页面的时候，Session Cookie 中的值都会发生变化，这是由于 Laravel 内置的 Cookie 加密处理机制造成的。

但实际上，SessionId 并没有发生变化，而仅仅是加密后的结果每次都不同而已。之所以要这样加密处理，是为了避免会话重用攻击导致的问题。

可以在`vendor/laravel/framework/src/Illuminate/Session/Store.php`的`save`方法中调试一下，打印一下这里的调用栈：

```php
public function save()
{
    $this->addBagDataToSession();
 
    $this->ageFlashData();
 
    $this->handler->write($this->getId(), $this->prepareForStorage(serialize($this->attributes)));
 
    $this->started = false;
    
    // 打印追踪信息
    dd(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 5));
}
```

这样就能在页面中看到具体的 session id。

### 加密

Cookie 值的加密是在`vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php`文件中的`encrypt`方法中进行的，这个中间件对所有 cookie 值进行了加密处理，它被包含在 web 中间件。

```php
protected function encrypt(Response $response)
{
    foreach ($response->headers->getCookies() as $cookie) {
        if ($this->isDisabled($cookie->getName())) {
            continue;
        }
 
        $response->headers->setCookie($this->duplicate(
            $cookie, $this->encrypter->encrypt($cookie->getValue())
        ));
    }
    return $response;
}
```


