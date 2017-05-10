### env 函数的误区

当执行`config:cache`之后，在代码中使用的`env()`方法返回的值就都是 null 了，而使用`config:clear`之后就正常了。

Laravel 5.2 的升级日志中有如下的说明：

> If you are using the config:cache command during deployment, you must make sure that you are only calling the env function from within your configuration files, and not from anywhere else in your application.
>
> If you are calling env from within your application, it is strongly recommended you add proper configuration values to your configuration files and call env from that location instead, allowing you to convert your env calls to config calls.

这里明确说明了这个问题，并指出应该在配置文件中使用`env`方法，而在代码中则使用`config`方法来作为替代。

从代码角度来说，这是由于 Laravel 5.2 升级之后代码运行的逻辑决定的，在框架的核心启动类`Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables`中的`bootstrap()`方法中有如下的处理：

```PHP
public function bootstrap(Application $app) {     if ($app->configurationIsCached()) {         return;     }      $this->checkForSpecificEnvironmentFile($app);      try {         (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();     } catch (InvalidPathException $e) {         //     } }
```

在函数中的起始部分我们可以发现，一旦缓存了配置以后，就不会再从`.env`文件加载内容了，所以你在业务代码中使用`env`函数时已经无法读取`.env`中设定的内容了，但是其它环境变量不影响。那为什么配置文件的可以呢？因为配置文件缓存的时候会加载`.env`然后读取值缓存配置内容。



