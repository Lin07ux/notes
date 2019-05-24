Laravel 提供了很多 artisan 工具命令，这些命令通过 Laravel 的 Console 内核来完成对应用核心组件的调度来完成任务。

artisan 命令均是从根目录下的`artisan`文件进入，该文件类似 HTTP 模块中的入口文件`public/index.php`。

### 1. 内核绑定

跟 HTTP 内核一样，在应用初始化阶段有一个内核绑定的过程，将 Console 内核注册到应用的服务容器里去，也就是 Console 的入口文件：`artisan`。

同 HTTP 的入口文件`public/index.php`一样，`artisan`也是引入`bootstrap/app.php`文件，得到应用实例，并进行命令行参数的解析和执行。

```php
define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

$kernel->terminate($input, $status);

exit($status);
```

### 2. 注册命令

上面代码中，实例化的`$kernel`对象就是`App\Console\Kernel`类，该类继承自`Illuminate\Foundation\Console\Kernel`。

在该类中，定义了两个方法，分别用于设置计划任务和自动注册命令行命令：

```php
/**
 * Define the application's command schedule.
 *
 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
 * @return void
 */
protected function schedule(Schedule $schedule)
{
    // $schedule->command('inspire')
    //          ->hourly();
}

/**
 * Register the commands for the application.
 *
 * @return void
 */
protected function commands()
{
    $this->load(__DIR__.'/Commands');

    require base_path('routes/console.php');
}
```

可以看到，Laravel 会自动搜索`Commands`目录中的文件进行注册，并且引入`routes/console.php`中注册的命令。

另外，在实例化 Console 内核的时候，`Illuminate\Foundation\Console\Kernel::__construct()`还会定义应用的命令计划任务(schedule 方法中定义的计划任务)

### 3. 执行命令

完成 Console 的实例化之后，就通过`Illuminate\Foundation\Console\Kernel::handle()`方法来执行命令了。

#### 3.1 引导

与 HTTP 内核处理一样，Console 内核也会在开始处理命令任务之前进行引导操作。引导操作使用`bootstrap()`方法加载`$bootstrappers`属性中定义的引导程序。

```php
protected $bootstrappers = [
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
];
```

这些引导程序主要作用为：加载环境变量、加载配置文件、注册异常处理器、设置 Console 请求、注册应用中的服务容器、注册Facade 和 启动服务。其中设置 Console 请求是唯一区别于 HTTP 内核的一个引导程序。

#### 3.2 执行

命令的执行是通过`Illuminate\Console\Application::run()`方法来执行，它继承自 Symfony 框架的`Symfony\Component\Console\Application`类。

```php
public function run(InputInterface $input = null, OutputInterface $output = null)
{
    $commandName = $this->getCommandName(
        $input = $input ?: new ArgvInput
    );

    $this->events->fire(
        new Events\CommandStarting(
            $commandName, $input, $output = $output ?: new ConsoleOutput
        )
    );

    $exitCode = parent::run($input, $output);

    $this->events->fire(
        new Events\CommandFinished($commandName, $input, $output, $exitCode)
    );

    return $exitCode;
}
```

执行命令时主要有三步操作：

1. 通过命令行输入解析出命令名称和参数选项。
2. 通过命令名称查找命令类的命名空间和类名。
3. 执行命令类的run方法来完成任务处理并返回状态码。

和命令行脚本的规范一样，如果执行命令任务程序成功会返回 0，抛出异常退出则返回 1。

#### 3.3 解析参数

PHP 是通过全局变量`$_SERVER['argv']`来接收所有的命令行输入，和命令行里执行 shell 脚本一样，索引 0 对应的是脚本文件名，接下来依次是命令行里传递给脚本的所有参数选项，所以在命令行里通过 artisan 脚本执行的命令，在 artisan 脚本中`$_SERVER['argv']`数组里索引 0 对应的永远是 artisan 这个字符串，命令行里后面的参数会依次对应到`$_SERVER['argv']`数组后续的元素里。

> 在 shell 脚本里可以通过`$0`获取脚本文件名，`$1`、`$2`这些依次获取后面传递给 shell 脚本的参数选项。

