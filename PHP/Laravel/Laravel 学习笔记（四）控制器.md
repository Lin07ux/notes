Laravel 的路由是所有请求的必经之处，所以我们可以把业务处理逻辑直接放在路由的回调函数中，只是这样会造成路由文件的过度复杂化。这样就需要通过控制器来精简路由设置。所以，Laravel 中控制器的作用其实和路由回调函数的作用差不多，都是用于完成请求的处理而已。

Laravel 中的控制器文件一般存放在`app/Http/Controllers/`文件夹中。

## 控制器内容

### 创建控制器

我们可以直接在控制器目录中新建一个控制器文件，也可以使用`artisan`命令行工具来快速创建一个控制器模板：

```shell
# 创建基本的控制器
php artisan make:controller <controller_name>

# 创建资源控制器
php artisan make:controller <controller_name> --resource

# 创建资源控制器，并指定资源模型
php artisan make:controller PhotoController --resource --model=Photo
```

第一个操作会在控制器目录中生成一个基础的控制器文件；第二个操作会创建一个符合 RESTful 的资源控制器文件，而第三个操作适用于：使用了路由模型绑定，并且想在资源控制器的方法中使用类型提示的时候。

控制器的类名一般会使用形如`NameController`的方式，控制器文件的名称就是控制器的名称，扩展名为`.php`。

> 控制器一般会继承 Laravel 内置的基础控制器类`App\Http\Controllers\Controller`，但是不继承也没有关系，控制器基类中只是多了一些便捷的方法而已，如`middleware()`用于给控制器添加中间件等。

### 方法

在控制器中，可以定义一个或多个方法，其中**只有`public`方法能响应请求，`protect`和`private`方法都不能被用于响应请求**。

在公开方法中，一般需要返回一个 Response 类型的数据，作为响应数据。可以通过调用`view()`方法获取视图内容，或者直接返回对应数据的方法生成。

如下就定义了一个简单的控制器，并有一个`show()`方法可以用于响应请求：

```php
<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 展示给定用户的信息。
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
```

### 单个行为控制器

在注册单个行为控制器的路由时，不需要指定方法。如果想定义一个只处理单个行为的控制器，可以在控制器中定义一个`__invoke()`方法：

```php
<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;

class ShowProfile extends Controller
{
    /**
     * 展示给定用户的信息。
     *
     * @param  int  $id
     * @return Response
     */
    public function __invoke($id)
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
```

在路由中这样注册即可：

```php
Route::get('user/{id}', 'ShowProfile');
```

### 控制器中间件

虽然在路由注册时也可以指定中间件，但是在控制器的构造函数中注册可能会更加方便。在控制器构造函数中使用`middleware`方法，可以很容易地将中间件分配给控制器的行为，甚至可以约束中间件只对控制器类中的某些特定方法生效：

```php
class UserController extends Controller
{
    /**
     * 实例化一个新的控制器实例。
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('log')->only('index');

        $this->middleware('subscribed')->except('store');
        
        // 闭包中间件
        $this->middleware(function ($request, $next) {
            // ...
        
            return $next($request);
        });
    }
}
```

> 如果有很多中间件附加在控制器的某些行为上，这意味着控制器已经在变大变复杂，建议拆分成更小的控制器。

### 依赖注入

Laravel 使用服务容器来解析所有的控制器。因此，可以在控制器的构造函数、方法中使用类型提示需要的依赖项，而声明的依赖项会自动解析并注入控制器实例中。比如，经常会把`Illuminate\Http\Request`实例注入到控制器方法中。

如果控制器方法需要从路由参数中获取输入内容，只需要在其他依赖项后列出路由参数即可。

```php
<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserController extends Controller
{
    /**
     * 用户 repository 实例.
     */
    protected $users;

    /**
     * 创建一个新的控制器实例。
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }
    
    /**
     * 更新给定用户的信息。
     *
     * @param  Request  $request
     * @param  string  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // 这里的 $id 就是从路由参数中自动提供的
    }
}
```

## 使用控制器

为了使控制器能工作，我们必须要在路由中将控制器的方法(甚至整个控制器)指定为某个 URI 请求的回调。

需要注意的是，在定义控制器路由时，并不需要指定完整的控制器命名空间。因为`RouteServiceProvider`会在一个包含命名空间的路由器组中加载路由文件，所以我们只需要指定类名中`App\Http\Controllers`命名空间之后的部分就可以了。

如果将控制器存放在`App\Http\Controllers`目录下的某一目录，只需要简单地使用相对于`App\Http\Controllers`根命名空间的特定类名。也就是说，如果完整的控制器类是`App\Http\Controllers\Photos\AdminController`，那你应该用以下这种方式向控制器注册路由：

```php
Route::get('foo', 'Photos\AdminController@method');
```


### 指定控制器的方法

这种方式可以将请求直接转交给控制器中的一个指定的方法，比如下面的路由就指向了前面创建的控制器的`show()`方法：

```php
Route::get('user/{id}', 'UserController@show');
```

使用这种方式时，路由中的参数也会传入到控制器的方法中，也可以有 DI 效果，可以路由中的匿名函数一样的。

### 控制器路由

使用控制器路由可以将某一 URI 及其子 URI 统一交给一个控制器来处理。在这个控制器中，需要根据 HTTP 请求的方法和请求的 URI 来分别创建对应的方法。而且，也需要使用不同的路由方法来定义路由。

比如，将`/`及其子 URI 都交给`SiteController`控制器来响应：

1. 首先，需要使用`Route`的`controller`方法来定义路由：

    ```php
    Route::controller('/', 'SiteController');
    ```

2. 然后，`SiteController`中的方法名称也需要有一定的改变：

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

这样定义之后，当我们访问地址`http://domain/`就会显示`SiteController`的`getIndex`方法产生的内容；访问`http://domain/about`，就会显示`getAbout`方法产生的内容。依次类推。除了使用如`get{Method}`这种格式，还可以有`post{Method}`、`delete{Method}`等。

### 资源路由

资源路由和控制器路由有点类似：都需要修改 Route 中的定义方式，都需要修改控制器的方法。不同的地方在于：资源路由需要使用的是`Route::resource()`方法，而且资源控制器中的方法不需要带上 HTTP 请求的方法，而是特定的控制器方法对应特定的 HTTP 请求的方法或 URI。

Laravel 中的资源路由是满足 RESTful 规则的。

比如，通过 Artisan 命令创建一个`ArticleController`控制器之后，在生成的资源控制器中，默认就已经生成了多个方法，而每个方法都对应着一个符合 RESTful 的 URI：

|    请求方法   |   	请求 URI       | 对应的控制器方法 |    代表的意义    |
|-------------|--------------------|--------------|-----------------|
|  GET        | /article           |  index       |  索引/列表       |
|  GET	      | /article/create    |  create      |  创建（显示表单） |
|  POST	      | /article	         |  store       |  保存你创建的数据  |
|  GET	      | /article/{id}      |  show        |  显示对应id的内容 |
|  GET	      | /article/{id}/edit |  edit        |  编辑（显示表单）  |
|  PUT/PATCH	| /article/{id}      |  save        |  保存你编辑的数据  |
|  DELETE	   | /article/{id}      |  destroy     |  删除            |

然后，在路由中定义一个资源路由器就是很简单的一条语句了：

```php
Route::resource('article', 'ArticleController');
```

此时，访问地址`http://domain/article`，就相当于访问控制器`ArticleController`的`index`方法；访问地址`http://domain/article/create`，就会访问到`create`方法(在此方法中一般需要提供一个表单)；当通过`POST`提交数据至地址`http://domain/article`，相当于由`store`方法处理。

通过资源控制器，能很容易实现一个符合 RESTful 架构的接口，这种很适合作为 APP 后端开发时使用。这种规范下，不但访问策略清晰易理解，更容易维护，也使架构更为合理和现代化。


## 杂项

### 转摘

[Laravel 的 HTTP 控制器](https://d.laravel-china.org/docs/5.5/controllers)

