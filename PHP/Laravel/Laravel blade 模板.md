Laravel 使用的 Blade 模板，支持 Blade 简写语法。

## 使用变量
在 Blade 模板中，使用传到模板中的变量的方法是`{{ $name }}`的方式，而不需要使用原生的 php echo 的方式。

两个大括号包裹的变量，在显示的时候，会做字符转义，将特殊的 HTML 符号转成实体符号，从而可以避免 XSS 攻击。

如果不需要转义，可以使用`{!! $name !!}`的方式来输出。


## 模板继承
一般网站的大部分页面都有相同的布局，引用相同的 css 和 js 文件。使用模板继承，则可以将这些重复项单独放在一个文件中，方便后期的维护。

Blade 模板的继承使用`@extends('tpl_name')`的方式。

> Laravel 中，模板的继承都是以`resources/views/`为根目录寻找文件的。

比如，在`resources/views/`中新建一个`app.blade.php`文件，并在其中编写网站页面的基础内容，然后就可以在其他的页面引用这个模板了：

```blade
// 在页面顶部引用这个模板
@extends('app')
```

引用了模板之后，就可以在当前文件中填充模板中预留的位置中的内容了。

## 填充模板
为了能够在每个不同的页面显示不同的内容，需要在模板中添加一个区域，能够允许继承这个模板的页面根据自身情况来填充。

1. 首先，在模板文件中，可以通过`@yield('name')`的方式来定义一个名称为`name`的区域。
2. 然后，在继承这个模板的文件中，通过`@section('name')`语句表示开始填充这个区域，用`@stop`表示结束填充这个区域。
3. 之后，在`@section`和`@stop`之间编写的 HTML 代码就会被填充到模板的对应区域中。

模板中可以定义多个`@yield()`区域，对应的，继承这个模板的文件中可以选择填充这些区域中的某些或全部。没有被填充的区域则不会显示任何内容。

示例：

我们可以在`resources/views/app.blade.php`模板文件中填充如下的模板框架：

```html
<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

然后，我们就可以在其他页面继承这个模板了，比如我们在`resources/views/site/contact.blade.php`文件中填充模板：

```html
@extends('app')

@section('title')
联系我们
@stop

@section('content')
<div>
    <h2>联系我们</h2>
    <p>这就是我的联系方式：xxxxx</p>
</div>
@stop
```


