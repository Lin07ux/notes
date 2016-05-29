Laravel 提供了一个命令行工具`artisan`，能够方便我们使用命令行来完成很多工作。

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




