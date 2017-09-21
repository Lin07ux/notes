## 基本流程

Laravel 也是单入口文件的一个系统，所有的请求都会导向`public/index.php`入口文件(所以也需要在 Apache 或者 Nginx 中做相应的配置)。

从这个入口文件开始，依次进行： 

- **自动加载文件**  `require __DIR__.'/../bootstrap/autoload.php';`
- **实例化一个 app 对象**  `$app = require_once .../bootstrap/start.php;`
- **调用 Http 核心程序**  `$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);`
- **根据请求生成响应**  这一步就会调用 router 找到对应的 controller，从而得到生成的最终响应。
- **返回响应**   `$response->send();`
- **结束整个请求**   `$kernel->terminate($request, $response);`

一般情况下，只有第四步(根据请求生成响应)中是需要我们来进行自定义的，其他部分一般都不需要我们介入。

在第四步中，我们需要提供的是： _路由_ 、 _控制器_ 、 _视图_ 。不过，控制器和视图并不一定是必须的。路由可以直接返回响应，也可以直接返回一个视图，或者可以调用指定的控制器来动态生成视图。可以说，在 Laravel 中，路由代替了传统的按照文件目录来定义的网站结构。

> 路由位置：`routes/web.php`
> 控制器位置：`app/Http/controllers`
> 视图位置：`resources/view/`。另外，Laravel 的视图使用的是 blade 模板引擎，文件名以`.blade.php`结尾。


## 路由

路由是 Laravel 的特色，更是一个重中之重。路由文件位于`routes/`目录中。

> 在这里，路由是指分析来自客户端请求的统一资源标识符(URI)，根据设定的规则将请求分发至期待的处理逻辑，这一规则就是路由规则，这一过程就是路由。

Laravel 的控制器为什么不类似于 TP 之流的框架呢？因为 Laravel 的控制器是为了组织一类行为的，或针对某一资源建立一个标准的资源控制器。而 TP、CI 之流控制器的意义变得更为重要，是整个框架中实现逻辑的主要成分。Laravel 实现主要逻辑的可以是一系列类库，简单的逻辑甚至直接可以在路由实现，而控制器仅仅是一种实现方式之一。Laravel 这种设计在复杂项目中更为科学，使得分层系统得以十分容易的实现。

### 示例

参考：[laravel 学习笔记——路由（基础）](https://www.insp.top/article/learn-laravel-route-basic)

最简单的一个例子：

```php
// app/Http/routes.php
Route::get('/about', function () {
    return 'It\'s me!';
});
```

这样，在访问`http://domain/about`的时候，就会显示“It's me!”这些字符了。这就是 Laravel 中的一个路由了。

### RESTful

Laravel 中的路由是与 RESTful 的规范相符的，所以，可以借助不同的 HTTP 请求方法(GET/POST/PUT/DELETE 等)来对应不同的路由规则，从而完成不同的业务。

### 中间件

参考：[laravel 学习笔记——路由（中间件与路由组）](https://www.insp.top/article/learn-laravel-middleware-routegroup)

中间件是在请求到处理逻辑之间的一个中间过程，一般用作前置和后置的判断、验证。比如，验证用户权限，是否登录等。经过中间件处理之后的请求，如果没有被中间件直接返回，就会继续进入到正常的逻辑处理中。

通过中间件对请求做一定的过滤处理，使得我们可以在控制器里专注于业务本身的逻辑了。

Laravel 默认已经内置了许多中间件，且默认开启。可以通过编辑`app/Http/Kernel.php`来决定是否启用这些中间件。自己开发的中间件也是在这里进行注册的。

> app/Http/Kernel.php 中的 $middleware 数组是全局中间件，也就是说，任何一条路由都会被应用这些中间件，比如里面的CSRF验证中间件。
> 
> 有时候我们不需要全局中间件，这时候可以将某一个中间件注册至 app/Http/Kernel.php 文件中的 $routeMiddleware 数组，数组的键名是中间件的别名，键值是具体的中间件类，如`'auth' => 'App\Http\Middleware\AuthMiddleware'`。

我们在`app/Http/Kernel.php`文件中的`$routeMiddleware`数组注册了一个独立中间件，这一中间件可被单独用绑定在一个路由和路由组上。在路由定义的时候可以像这样：

```php
Route::get('admin/profile', ['middleware' => 'auth', function () {
    // To Do
}]);
```

这样，当我们在访问`http://domain/admin/profile`的时候，就会先经过`auth`这个中间件做一定的处理。

### 路由群组

路由组群往往适用于给某一类路由分组，给这个路由组分配的中间件、过滤器等，都会被运用到该组内的所有路由。

简单的说，路由组就是简化一部分路由定义过程的。比如，后台的我都想通过地址`http://domain/admin/***`访问，假如我有 用户（user）、文章（article） 两个模块，他们的访问都要经过一个验证权限的中间件，我需要这样定义路由：

```php
Route::get('admin/user', ['middleware' => 'authority', function() {
    // To Do
}]);
 
Route::get('admin/article', ['middleware' => 'authority', function() {
    // To Do
}]);
```

在系统庞大以后，每个都要单独写对应的中间件，容易出错，不易管理。这时候，就应该使用路由组：

```php
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('user', function () {
        // To DO
    });
    
    Route::get('article', function () {
        // To Do
    });
});
```

同时，利用路由群组，定义子域名就非常容易了：

```php
Route::group(['domain' => 'bbs.domain.com'], function () {
    // To Do
});
```

子域名也可以拥有通配符，以此实现更为灵活的结构。比如我希望我的网站每一个用户都拥有自己的二级域名，类似于这样：`userA.yourdomain.com`，`userB.yourdomain.com`。这时候可以这样写：

```php
Route::group(['domain' => '{username}.domain.com'], function () {
    Route::get('profile/{type}', function ($username, $type) {
        // To Do
    });
});
```

## 控制器

### 路由和控制器

参考：[laravel 学习笔记——路由（路由与控制器）](https://www.insp.top/article/learn-laravel-route-router-controller)

对于简单的业务逻辑，我们可以使用匿名函数直接处理并返回响应，或者是返回一个视图。但是当业务逻辑逐渐复杂起来之后，使用控制器就能更清晰了。

和其他框架一样，在 Laravel 中，控制器就是一个类。但是在 Laravel 中，控制器并不能直接提供服务，而是需要通过路由的调用来起作用。下面就是一个简单的控制器：

```php
<?php 
namespace App\Http\Controllers;
 
use App\Http\Controllers\Controller;
 
class HomeController extends Controller {
 
    /**
     * 显示首页。
     *
     * @return Response
     */
    public function index()
    {
        return view('home');
    }
 
}
```

将路由的处理逻辑转到一个控制器的方法中，和转给一个匿名函数的格式是相同的。如，下面的这个路由会将`http://domain`转到`HomeController`控制器中的`index`方法：

```php
Route::get('/', 'HomeController@index');
```

可以看到，将路由转给一个控制器的方法的格式就是'控制器的类名@方法名'。

但是这种定义方法会带来一种问题：每条地址规则都要定义路由，岂不是很累？这确实是个问题，不过，laravel 给了我们一个折中的方案——**控制器路由**。


### 控制器路由

因为大型的应用业务复杂，控制器相当的多，我们不可能每一个控制器的方法都要定义一个路由。Laravel 的控制器路由可以完美解决问题：

```php
Route::controller('/', 'HomeController');
```

这时，我们的控制器方法的写法也要有所变化：

```php
<?php 
namespace App\Http\Controllers;
 
use App\Http\Controllers\Controller;
 
class HomeController extends Controller {
 
    /**
     * 显示首页。
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

依照上述例子，如果我们访问地址`http://domain/`就会显示`HomeController`的`getIndex`方法产生的内容，访问`http://domain/about`，就会显示`getAbout`方法产生的内容。除了使用如`get{Method}`这种格式，还可以有`post{Method}`、`delete{Method}`等。这里的 get、post、delete 对应的就是请求的方式。

### 资源控制器

RESTful 是一种设计思想、一种普遍接受的规范。Laravel 资源控制器，和 RESTful 有着莫大的联系，要理解资源控制器，必须先了解 RESTful。

> 阮一峰关于 RESTful 的文章：[理解 RESTful 架构](http://www.ruanyifeng.com/blog/2011/09/restful.html)


Laravel 的资源控制器原生的支持了 RESTful 架构。其实 laravel 的资源控制器和其他控制器没什么直接区别，只是对控制器类的方法和结构略有规定，不过我们并不要手动创建资源控制器，我们可以利用 laravel 的命令行工具 ——`artisan`。

在laravel框架根目录下，通过命令行输入命令，就可以创建一个名为`ArticleController`的资源控制器，文件默认在`app/Http/Controllers`下：

```shell
php artisan make:controller ArticleController --resource
```

在生成的资源控制器中，默认就已经生成了多个方法，而每个方法都对应着一个符合 RESTful 的 URI：

|    请求方法   |   	请求 URI       | 对应的控制器方法 |    代表的意义    |
|-------------|--------------------|--------------|-----------------|
|  GET        | /article           |  index       |  索引/列表       |
|  GET	      | /article/create    |  create      |  创建（显示表单） |
|  POST	      | /article	         |  store       |  保存你创建的数据  |
|  GET	      | /article/{id}      |  show        |  显示对应id的内容 |
|  GET	      | /article/{id}/edit |  edit        |  编辑（显示表单）  |
|  PUT/PATCH	| /article/{id}      |  save        |  保存你编辑的数据  |
|  DELETE	   | /article/{id}      |  destroy     |  删除            |

于是，我们在路由中定义一个资源路由器就是很简单的一条语句了：

```php
Route::resource('article', 'ArticleController');
```

此时，访问地址`http://domain/article`，就相当于访问控制器`ArticleController`的`index`方法；访问地址`http://domain/article/create`，就会访问到`create`方法(在此方法中一般需要提供一个表单)；当通过`POST`提交数据至地址`http://domain/article`，相当于由`store`方法处理。

通过资源控制器，我们就能很容易实现一个符合 RESTful 架构的接口，这种很适合作为 APP 后端开发时使用。这种规范下，不但访问策略清晰易理解，更容易维护。也使你的架构更为合理和现代化。


## 视图

参考：[laravel 学习笔记——视图](https://www.insp.top/article/learn-laravel-view)

视图，就是人们所看见的内容。需要注意的是，视图并不等同于视图模块。我们称用于实际负责输出（可视）数据的就叫做视图。

框架中的视图，其职责更为单一，不再负责数据的读写、处理，而仅仅负责呈现。这样一个职责独立的视图，就需要外部提供数据，也需要外部的调度。Laravel 中的 View 类就是负责这一工作的。

Laravel 的视图是一个独立的组件，并不和 Controller 耦合，可以在任意位置使用`view()`来获取一个视图。

> `view()`函数实际上是`View`类的快速访问方式，可以在该函数的定义中看得到（在文件`vendor/laravel/framework/src/illuminate/foundation/helpers.php`中可以查看）。
> View 组件在`vendor/laravel/framework/src/illuminate/View/View.php`中定义。我们一般为了方便，直接使用`view()`函数。

在 Laravel 中向视图传递数据有多种方式，比如下面的方式都会向视图传递`$content`和`$author`这两个参数：

```php
// 通过函数创建一个视图
view('article', ['content' => 'Hello, world', 'author' => 'chongyi']);
 
view('article')->with('title', 'Hello, world')->with('author', 'chongyi');
 
view('article')->withTitle('Hello, world')->withAuthor('chongyi');
 
// 通过 Laravel 的 View Facade 创建一个视图
use View;
 
View::make('article', ['content' => 'Hello, world', 'author' => 'chongyi']);
 
View::make('article')->with('title', 'Hello, world')->with('author', 'chongyi');
 
View::make('article')->withTitle('Hello, world')->withAuthor('chongyi');
```


## 杂项

### 请求与响应

参考：[laravel 学习笔记——请求与响应](https://www.insp.top/article/learn-laravel-request-and-response)

在控制器、路由闭包中，使用 echo 输出内容和使用 return 输出内容有什么区别？

控制器和路由闭包中返回的数据，最终会交由 laravel 的 HTTP 组件的 Response（响应）类处理，而直接输出是由 php 引擎处理，php 会以默认的文件格式、响应头输出，除非使用`header()`函数改变。因此与其自己去调取`header()`调整响应头还是其他，都不如 laravel 的 Response 来的简洁实惠。

### 依赖注入

参考：[laravel 学习笔记 —— 神奇的服务容器](https://www.insp.top/article/learn-laravel-container)

只要不是由内部生产（比如初始化、构造函数`__construct`中通过工厂方法、自行手动`new`的），而是由外部以参数或其他形式注入的，都属于**依赖注入（DI）**。

