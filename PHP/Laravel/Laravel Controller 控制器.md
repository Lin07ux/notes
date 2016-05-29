Laravel 中的控制器和其他框架中的控制器一样，一般都是用于完成业务逻辑。所以网站的主体代码基本都是在控制器中的。

Laravel 中的控制器文件一般存放在：`app/Http/Controllers/`文件夹中。

## 创建控制器
在 Laravel 中，可以使用`artisan`命令行工具来快速创建一个控制器模板：

```shell
# 创建基本的控制器
php artisan make:controller <controller_name>
# 创建资源控制器
php artisan make:controller <controller_name> --resource
```

这样就会在控制器目录中生成一个基础的控制器文件或者一个符合 RESTful 的资源控制器文件。

> 当然，我们也可以直接在控制器目录中自己新建一个控制器文件。

控制器的类名一般会使用形如`NameController`的方式，控制器文件的名称就是控制器的名称，扩展名为`.php`。

需要注意的是：控制器中只有`public`方法能响应请求，`protect`和`private`方法都不能被用于响应请求。

## 使用控制器
为了使控制器能工作，我们必须要在路由中对某一地址(一个或一类)专门指定控制器。这有三种方式：

1. 直接指定路由到控制器中的方法

这种方式可以将请求直接转交给控制器中的一个指定的方法，比如：

```php
Route::get('/', 'HomeController@index');
```

这样就将访问网站根目录的请求转交给`HomeController`控制器中的`index`方法来处理了。

2. 使用控制器路由

使用控制器路由可以将某一 URI 及其子 URI 统一交给一个控制器来处理。在这个控制器中，需要根据 HTTP 请求的方法和请求的 URI 来分别创建对应的方法。而且，也需要使用不同的路由方法来定义路由。

比如，将`/`及其子 URI 都交给`SiteController`控制器来响应：

- 首先，需要使用`Route`的`controller`方法来定义路由：

```php
Route::controller('/', 'SiteController');
```

- 然后，我们的`AboutController`中的方法名称也需要有一定的改变：

```php
<?php 
namespace App\Http\Controllers;
 
use App\Http\Controllers\Controller;
 
class HomeController extends Controller {
 
    /**
     * 显示首页
     *
     * @return Response
     */
    public function getIndex()
    {
        return view('home');
    }
 
    /**
     * 显示关于界面
     *
     * @return Response
     */
    public function getAbout()
    {
        return view('about');
    }
}
```

此时，当我们访问地址`http://domain/`就会显示`HomeController`的`getIndex`方法产生的内容；访问`http://domain/about`，就会显示`getAbout`方法产生的内容。依次类推。除了使用如`get{Method}`这种格式，还可以有`post{Method}`、`delete{Method}`等。

这里的 get、post、delete 对应的就是 HTTP 请求的方法。

3. 使用资源路由

资源路由和控制器路由有点类似：都需要修改 Route 中的定义方式，都需要修改控制器的方法。不同的地方在于，资源路由需要使用的是`Route::resource()`方法，而且资源控制器中的方法不需要带上 HTTP 请求的方法，而是特定的控制器方法对应特定的 HTTP 请求的方法或 URI。

Laravel 中的资源路由是满足 RESTful 规则的。

更多资源路由，可以查看《Laravel @基础》中的 [资源控制器] 部分的内容。


## 控制器内容
### 基本
在控制器中，可以定义一个或多个方法，其中只有公开方法能够用于响应请求。

在公开方法中，一般需要返回一个 Response 类型的数据，作为响应数据。这可以通过调用`view()`方法获取视图内容，或者直接返回对应数据的方法生成。

### 向视图传参
控制器方法中，在使用`view()`方法调用视图的时候，有多种方式可以用于给视图传参：

1. 使用`view()`方法的第二个参数

`view()`除了可以接受一个参数表示引用的模板外，还可以接受第二个数组参数，表示要传入到模板中的变量。

```php
$data = array(
    'first' => 'Lin07ux',
    'last'  => 'Lin',
);
view('welcome', $data);
```

2. 使用`with()`方法

可以在调用`view()`方法后，继续连接调用一个`with()`方法来传递参数。

`with()`方法可以接受两个参数，分别表示变量的名称(不带`$`符号)和变量的值。这样，在模板中就能使用`name`参数表示的变量名来引用对应的值了。

`with()`方法还可以接受一个数组参数，数组的键值分别表示变量的名称和值。这样就能够一次传递多个变量到模板中了。

```php
view('welcome')->with('name', 'value');

view('welcome')->with([
    'first' => 'Lin07ux',
    'last'  => 'Lin',
]);
```


