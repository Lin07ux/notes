### 视图

Laravel 中的视图使用的是 Blade 语法编写的，基本内容是 HTML 代码，但是通过 Blade 语法添加进了一些逻辑和循环语句，以便能够使用最简单的方式实现非常复杂的页面效果。

Blade 语法也是简洁的、易于理解的。它和 PHP 语言中的逻辑判断和循环语句的书写格式是类似的。其实，Blade 语法最终都会被解析成 PHP 语句。

可以查看`/resources/views/welcome.blade.php`文件中的部分内容：

```blade
@if (Route::has('login'))
  <div class="top-right links">
     @auth
         <a href="{{ url('/home') }}">Home</a>
     @else
         <a href="{{ route('login') }}">Login</a>
         <a href="{{ route('register') }}">Register</a>
     @endauth
  </div>
@endif
```

这里的`@if`、`@endif`、`@auth`、`@else`、`@endauth`等都是 Blade 的逻辑判断语句，我们也可以自定义一些指令，以便书写更简洁易读。具体的添加方式可以查看官方文档。

另外，这里的`{{`和`}}`符号是指示 Blade 解析器将其中的表达式对应的值放在这个位置的。

除了上面见到的指令，还有很多其他常用的指令：`for`、`foreach`、`while`等。

### 向视图传递数据

现代的 MVC 框架要求模型、控制器、视图三者分离，所以我们在控制器(路由处理函数)中生成的数据，在视图中是没有办法直接访问的，这就需要我们通过一定的方式将其传入到视图中。

传递方式有两种：

1. 将数据打包成键值对数组，作为第二个参数传递给`view()`方法(第一个参数是视图名称)；
2. 使用`view()->with()`方式，将变量名和值分别作为`with()`方法的两个参数传递。`with()`方法也接受一个关联数组作为参数，数组的键值分别表示变量的名称和值。

比如，我们将`welcome.blade.php`视图的内容改成下面的形式：

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    Hello, {{ $name }}
</body>
</html>
```

然后，在路由中，可以使用这样的方式传递变量`$name`的值到视图中：

```php
Route::get('/', function () {
    return view('welcome', ['name' => 'World']);
});
```

也可以这样传递：

```php
Route::get('/', function () {
    return view('welcome')->with('name', 'World');
});
```

最终的结果都一样：

<div align="center">
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505660311148.png" width="314"/>
</div>

当然，对于变量已经定义，且比较多的情况下，我们还可以使用`compact()`方法来进行打包：

```php
Route::get('/', function () {
    $name = 'World';
    $age = 23;

    return view('welcome', compact('name', 'age'));
    
    // 或者
    return view('welcome')->with([
        'name' => name,
        'age'  => age,
    ]);
});
```

这样就可以把`$name`、`$age`两个变量及其值传递到视图中了。




