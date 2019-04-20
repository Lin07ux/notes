Laravel 提供了一个命令行工具`artisan`，能够方便我们使用命令行来完成很多工作。

## 基础原理

Laravel 框架自带的 artisan 命令都位于`Illuminate\Foundation\Console`命名空间中，文件名称为`命令名+Comman.php`比如对于`php artisan serve`命令，对应的文件为`Illuminate\Foundation\Console\ServeCommand.php`。每个命令类的主要入口逻辑都位于其`fire()`方法中。

### serve 命令分析

下面以`serve`命令进行分析，其`fire()`方法定义如下：

```PHP
public function fire(){
        chdir($this->laravel->publicPath());

        $this->line("<info>Laravel development server started:</info> <http://{$this->host()}:{$this->port()}>");

        passthru($this->serverCommand());
}
```

* 第一步：首先使用`chdir()`将目录改变至`public/`目录，这是根据`$this->laravel->publicPath()`代码的`publicPath()`来的，这个方法的源码位于`Illuminate\Foundation\Application`中：

```php
public function publicPath()
{
   return $this->basePath.DIRECTORY_SEPARATOR.'public';
}
```

* 第二步：打印提示信息，这是通过`fire()`的第二行代码实现的：

```php
$this->line("<info>Laravel development server started:</info> <http://{$this->host()}:{$this->port()}>");
```

* 第三步：执行命令，这个命令是从`passthru($this->serverCommand())`的原生函数 `passthru()`来实现的，其中`$this->serverCommand()`负责返回一个可以执行的字符串命令，具体代码就在同文件的`serverCommand()`中：

```php
protected function serverCommand() {     return sprintf('%s -S %s:%s %s/server.php',         ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false)),         $this->host(),         $this->port(),         ProcessUtils::escapeArgument($this->laravel->basePath())     ); }
```

其中，`ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false))`是用来返回 php 的可执行全路径。

后面两个分别返回 host(默认是`127.0.0.1`)和 post(默认是`8000`)，这两个都是可以在执行命令的时候进行制定值的。

最后`ProcessUtils::escapeArgument($this->laravel->basePath())`用于获取执行代码的全路径。

最终，`serverCommand()`方法返回的值类似如下：`'/usr/local/cellar/php71/7.1.1_12/bin/php' -S 127.0.0.1:8000 '/Users/[username]/laravel-learn/server.php'`。

这样整体分析后，`php artisan serve`命令就相当于我们直接在命令行中进入到项目根目录后执行下面的命令：

```shell
php -S 127.0.0.1:8000 server.php
```

在这基础上，记得指定`public/`为网站根目录。

> 参考：[源码解读：php artisan serve](https://segmentfault.com/a/1190000009940655)

## make
使用 artisan 中的`make`指令可以创建多种不同的文件。

### make:controller
创建一个控制器文件。

格式：
`php artisan make:controller <controller_name>`

参数：

- `controller_name` 表示要创建的控制器类的名称(一般是大驼峰格式，由一个自定义名称和`Controller`字段组成)。

这个命令执行之后，默认会在`app/Http/Controllers/`文件夹中创建一个名为`<controller_name>.php`的文件，文件内就是`<controller_name>`控制器的定义。

如，`php artisan make:controller HomeController`命令，会在`app/Http/Controllers/`目录中生成一个`HomeController.php`的文件，文件内容如下：

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class HomeController extends Controller
{
    //
}
```




