## 路由简介

Laravel 是单入口文件的框架，所有的请求(除了公共静态文件)都要通过入口文件进行应用的启动和后续的逻辑处理，所以就需要通过定义一些路由来设置请求如何被处理。

Laravel 的路由配置存放在`/routes`目录中，默认有四个路由文件：`api.php`、`channels.php`、`console.php`、`web.php`，这四个文件分别对应着 API 请求、订阅频道、命令行和 web 请求的路由。一般情况下，我们主要操作的是`web.php`这个文件。

## 基本路由

Laravel 的路由有很多种定义方式：可以接受不同的 HTTP 请求方法，也可以接受不同类型的处理方法，最终的返回结果可以是字符串、数组等。

### 默认路由文件

所有的 Laravel 路由都在 routes 目录中的路由文件中定义，这些文件都由框架自动加载。

* `routes/web.php` 用于定义 web 界面的路由。这里面的路由都会被分配给 web 中间件组，它提供了会话状态和 CSRF 保护等功能。

* `routes/api.php` 中的路由都是无状态的，并且被分配了 api 中间件组。这里定义的路由通过`RouteServiceProvider`被嵌套到一个路由组里面。在这个路由组中，会自动添加 URL 前缀`/api`到此文件中的每个路由前面。

大多数的应用构建，都是以在`routes/web.php`文件定义路由开始的。可以通过在浏览器中输入定义的路由 URL 来访问`routes/web.php`中定义的路由。

对于 api 的路由前缀，可以在`RouteServiceProvider`类中修改，同时还可以修改其他路由组选项。

### 可用的路由方法

路由器允许你注册能响应任何 HTTP 请求的路由：

```php
// 响应任何 HTTP 请求
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);

// 响应多个 HTTP 请求
Route::match(['get', 'post'], $uri, $callback);

// 响应所有 HTTP 请求
Route::any($uri, $callback);

// 控制器路由
Route::controller($uri, $controller);

// 资源路由
Route::resource($uri, $ResourceController);

// 重定向路由，可快速地实现重定向，而不需定义完整的路由或者控制器
Route::redirect('/here', '/there', 301);

// 视图路由，用于只需要返回一个视图的请求，有三个参数
// 参数1：请求的 uri； 参数2：视图名称； 参数3：可选，传入的数据数组
Route::view('/welcome', 'welcome');
Route::view('/welcome', 'welcome', ['name' => 'Lin07ux']);
```

### 基本路由定义

最简单的路由定义只需要一个 URI 与一个闭包，可以查看`routes/web.php`文件中的预设路由：

```php
Route::get('/', function () {
    return view('welcome');
});
```

这个路由表示，当使用 HTTP GET 请求访问网站根目录时，就通过一个闭包函数来返回视图模板`welcome.blade.php`解析后的内容。

这里，如果我们直接返回一个字符串，或者一个数组，那也是可行的。比如，我们直接返回字符串`welcome`：

```php
Route::get('/', function () {
    return 'Welcome';
});
```

前端页面的结果就如下：

<div align="center">
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505658407874.png" width="344"/>
</div>

如果直接返回一个数组：

```php
Route::get('/', function () {
    return ['Welcome', 'Hello World'];
});
```

那么返回的结果就是一个 JSON 字符串：

<div align="center">
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505658577474.png" width="301"/>
</div>


当然，更常见的方式还是将相关的处理逻辑放在控制器的方法中，然后通过控制器返回相关的视图模板，从而得到更复杂、更有用的页面。

### 资源路由

资源路由和控制器路由都需要控制器做适当的配合。另外，资源控制器在定义的时候，还能够做更多的定制化更改。

```php
// 指定控制器处理的部分行为，而不是所有默认的行为
Route::resource('photo', 'PhotoController', ['only' => [
    'index', 'show'
]]);
Route::resource('photo', 'PhotoController', ['except' => [
    'create', 'store', 'update', 'destroy'
]]);

// 特别的，下面的方法定义的资源路由器会去除了 create 和 edit 行为
Route::apiResource('photo', 'PhotoController');

// 覆盖资源路由器行为的默认名称
Route::resource('photo', 'PhotoController', ['names' => [
    'create' => 'photo.build'
]]);

// 命名资源路由参数
// 默认情况下资源路由会根据资源名称的「单数」形式创建资源路由的路由参数
// 下面的示例将会为资源的 show 路由生成「/user/{admin_user}」的 URI ：
Route::resource('user', 'AdminUserController', ['parameters' => [
    'user' => 'admin_user'
]]);
```

另外，默认情况下，`Route::resource`将会用英文动词创建资源 URI。如果需要本地化`create`和`edit`行为动作名，可以在`AppServiceProvider`的`boot()`中使用`Route::resourceVerbs`方法实现：

```php
use Illuminate\Support\Facades\Route;

/**
 * 引导任何应用服务。
 *
 * @return void
 */
public function boot()
{
    Route::resourceVerbs([
        'create' => 'crear',
        'edit' => 'editar',
    ]);
}
```

动作被自定义后，像`Route::resource('fotos', 'PhotoController')`这样注册的资源路由将会产生如下的 URI：

```
/fotos/crear
/fotos/{foto}/editar
```

> 如果想给资源路由器添加其他的行为，则需要在资源路由器前面定义这些路由。否则由`resource`方法定义的路由可能会无意中优先于补充的路由：
> 
> ```php
> Route::get('photos/popular', 'PhotoController@method');
>
> Route::resource('photos', 'PhotoController');
> ```

## 路由参数

### 参数定义

有时需要在路由中捕获一些 URL 片段，可以在路由中定义参数，该参数将会被传递给对应的处理函数。例如，从 URL 中捕获任务的 ID，可以通过定义路由参数来执行此操作：

```php
Route::get('/tasks/{id}', function ($id) {
    dd($id);
});
```

路由的参数通常都会被放在`{}`内，并且参数名只能为字母，同时路由参数不能包含`-`符号，如果需要可以用下划线(`_`)代替。路由参数会按顺序依次被注入到路由回调或者控制器中，而不受回调或者控制器的参数名称的影响。

例如，下面是一个在 URL 中定义多个参数的路由：

```php
Route::get('/tasks/{task}/{pro}', function ($id, $profile) {
    dd([$id, $profile]);
});
```

结果如下：

<div align="center">
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505884431004.png" width="304"/>
</div>

如果需要某个参数是可选的，可以在参数后面加上`?`标记来实现，但前提是要确保路由的相应变量有默认值：

```php
Route::get('/user/{name?}', function ($name = 'anonymous') {
    return $name;
});
```

> 参数的默认值可以为 null。

### 正则约束

可以使用路由实例上的`where`方法约束路由参数的格式。`where`方法接受参数名称和定义参数应如何约束的正则表达式：

```php
Route::get('user/{name}', function ($name) {
    //
})->where('name', '[A-Za-z]+');

Route::get('user/{id}', function ($id) {
    //
})->where('id', '\d+');

Route::get('user/{id}/{name}', function ($id, $name) {
    //
})->where(['id' => '[0-9]+', 'name' => '[a-z]+']);
```

如果你希望某个具体的路由参数都遵循同一个正则表达式的约束，就使用`pattern`方法在`RouteServiceProvider`的`boot`方法中定义这些模式：

```php
/**
 * 定义你的路由模型绑定, pattern 过滤器等。
 *
 * @return void
 */
public function boot()
{
    Route::pattern('id', '\d+');
    
    parent::boot();
}
```

定义好之后，便会自动应用到所有使用该参数名称的路由上：

```php
Route::get('user/{id}', function ($id) {
    // 仅在 {id} 为数字时执行...
})
```

## 命名路由

命名路由可以方便地为指定路由生成 URL 或者重定向。通过在路由定义上链式调用`name()`方法指定路由名称：

```php
Route::get('user/profile', function () {
    // TODO
})->name('profile');

// 或者对控制器路由命名
Route::get('user/profile', 'UserController@showProfile')->name('profile');

// 有参数的命名路由
Route::get('task/{id}/detail', function ($id) {
    // TODO
})->name('task');
```

### 生成命名路由链接

为路由指定了名称后，就可以使用全局辅助函数`route()`来生成链接或者重定向到该路由：

```php
// 生成 URL
$url = route('profile');

// 重定向
return redirect()->route('profile');

// 参数命名路由
$url = route('task', ['id' => 1]);
```

### 检查当前路由

如果你想判断当前请求是否指向了某个路由，你可以调用路由实例上的`named`方法。例如，你可以在路由中间件中检查当前路由名称：

```php
/**
 * 处理一次请求。
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Closure  $next
 * @return mixed
 */
public function handle($request, Closure $next)
{
    if ($request->route()->named('profile')) {
        //
    }

    return $next($request);
}
```

## 路由组

路由组允许你在大量路由之间共享路由属性，例如中间件或命名空间，而不需要为每个路由单独定义这些属性。

```php
// 共用中间件
Route::middleware(['first', 'second'])->group(function () {
    Route::get('/', function () {
        // 使用 first 和 second 中间件
    });
    
    Route::get('user/profile', function () {
        // 使用 first 和 second 中间件
    });
});

// 命名空间
// `RouteServiceProvider`会在命名空间组中引入路由文件
// 因此，只需要指定命名空间`App\Http\Controllers`之后的部分
Route::namespace('Admin')->group(function () {
    // 在 "App\Http\Controllers\Admin" 命名空间下的控制器
});

// 子域名路由
// 子域名可以像路由 URI 一样被分配路由参数
// 允许你获取一部分子域名作为参数给路由或控制器使用。
Route::domain('{account}.myapp.com')->group(function () {
    Route::get('user/{id}', function ($account, $id) {
        //
    });
});

// 路由前缀
Route::prefix('admin')->group(function () {
    Route::get('users', function () {
        // 匹配包含 "/admin/users" 的 URL
    });
});
```

## 路由模型绑定

当向路由或控制器行为注入模型 ID 时，就需要查询这个 ID 对应的模型。Laravel 为路由模型绑定提供了一个直接自动将模型实例注入到路由中的方法。例如，你可以注入与给定 ID 匹配的整个`User`模型实例，而不是注入用户的 ID。

需要注意的是，**路由模型绑定时，回调函数/控制器方法中的参数名称要和路由参数的名称相同**。

### 隐式绑定

Laravel 会自动解析定义在路由或控制器行为中与类型提示的变量名匹配的路由段名称的 Eloquent 模型。例如：

```php
Route::get('api/users/{user}', function (App\User $user) {
    return $user->email;
});
```

在这个例子中，由于`$user`变量被类型提示为 Eloquent 模型`App\User`，变量名称又与 URI 中的`{user}`匹配，因此，Laravel 会自动注入与请求 URI 中传入的 ID 匹配的用户模型实例。如果在数据库中找不到对应的模型实例，将会自动生成 404 异常。

默认情况下，绑定的模型的实例是通过 ID 来获取的，如果想使用除`id`之外的数据库字段，可以在对应的 Eloquent 模型上重写`getRouteKeyName`方法：

```php
/**
 * 为路由模型获取键名。
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'name';
}
```

### 显式绑定

要注册显式绑定，使用路由器的`model()`方法来为给定参数指定类。在`RouteServiceProvider`类中的`boot()`方法内定义这些显式模型绑定：

```php
public function boot()
{
    parent::boot();

    Route::model('user', App\User::class);
}
```

接着，定义一个包含 {user} 参数的路由：

```php
Route::get('profile/{user}', function (App\User $user) {
    //
});
```

因为我们已经将所有`{user}`参数绑定至`App\User`模型，所以`User`实例将被注入该路由。例如`profile/1`的请求会注入数据库中 ID 为 1 的`User`实例。如果在数据库不存在对应 ID 的数据，就会自动抛出一个 404 异常。

如果你想要使用自定义的解析逻辑，就使用`Route::bind`方法。传递到`bind()`方法的闭包会接受 URI 中大括号对应的值，并且返回你想要在该路由中注入的类的实例：

```php
public function boot()
{
    parent::boot();
    
    Route::bind('user', function ($value) {
        return App\User::where('name', $value)->first();
    });
}
```

## 杂项

### 访问当前路由

可以使用`Route` Facade 上的`current`、`currentRouteName`和`currentRouteAction`方法来访问处理传入请求的路由的信息：

```php
$route = Route::current();

$name = Route::currentRouteName();

$action = Route::currentRouteAction();
```

### 路由缓存

如果应用只使用了基于控制器的路由，那么就应该充分利用 Laravel 的路由缓存。使用路由缓存将极大地减少注册所有应用路由所需的时间。

> **基于闭包的路由不能被缓存**。如果要使用路由缓存，你必须将所有的闭包路由转换成控制器类路由。

要生成路由缓存，只需执行下面的 Artisan 命令即可：

```shell
php artisan route:cache
```

运行这个命令之后，每一次请求的时候都将会加载缓存的路由文件。如果添加了新的路由，需要生成 一个新的路由缓存。因此，一般情况下应该只在生产环境运行该命令。

如果要清除路由缓存，可以使用如下的 Artisan 命令：

```shell
php artisan route:clear
```

### 转摘

1. [Laravel HTTP 路由功能](https://d.laravel-china.org/docs/5.5/routing)
2. [Routing](https://laravel.com/docs/5.5/routing)

