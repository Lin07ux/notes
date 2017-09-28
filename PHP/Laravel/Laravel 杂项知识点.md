## 启动内置服务器

默认情况下，PHP 已经提供了一个内置的 web server，可以通过如下的命令来启动：

```shell
# 启动一个服务器在 localhost:8000 上
# 并设置根目录为当前目录下的 public 文件夹
php -S localhost:8000 -t public
```

另外，Laravel 对这个命令做了一些封装，从而能更方便的使用这个服务器：

```shell
php artisan serve
```

## tinker

Laravel artisan 中提供了一个和 php 命令行交互界面类似的工具 tinker，不过他能更方便的我们在命令行中查看 PHP 代码的效果。比如，在命令行中，我们不再需要每次输出变量的值的时候都要使用`echo`等方法，只需要在命令行中输入这个变量即可。和 Chrome 开发者调试工具类似。

进入方式：

```shell
php artisan tinker
```

在 tinker 命令工具中，可以使用 Laravel 应用中的所有的代码和模块。比如，我们直接通过一个完成的命名空间来实例化一个类：`$article = new App\Article();`

## 路由缓存

在 L5 之前对于 100 个以上路由的情况很尴尬，效率确实是个问题。在 L5 中，果断添加了缓存来提高效率，对于一些稍微大点的项目来说，收益不小。

```shell
php artisan route:cache
```

## 调试

如果需要在页面中输出对应变量的内容，可以使用`dd()`方法来输出。比如，`dd($article)`将会在页面上显示变量`$article`的值。调用了`dd()`方法之后，后面的操作都不会再执行了。

