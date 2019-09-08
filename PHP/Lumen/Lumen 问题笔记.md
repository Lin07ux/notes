### Request

Version：5.7、5.8

从 5.7 开始，Lumen 定义了一个`\Laravel\Lumen\Http\Request`类，这是对`\Illuminate\Http\Request`进行封装得到的，其重新定义了`routeIs()`等几个方法，以便更好的适应 Lumen 的路由上的变化。

在 Application 启动的时候，实例化的 Request 对象就是`\Laravel\Lumen\Http\Request`实例，而不是`\Illuminate\Http\Request`实例了，但是注册在 Application 中的键依旧是`\Illuminate\Http\Request::class`，也就是如果在路由闭包或者控制器中使用解析参数的话，还是需要将参数中的`$request`定义为`\Illuminate\Http\Request`类型。如果定义为`\Laravel\Lumen\Http\Request`类型，那么 Application 将会重新生成一个该类的实例，而不是使用已解析好的 Request 实例。

这就导致一个问题：在应用启动生成 Request 实例的时候，已经将客户端提交的数据都读取了。此时如果重新解析得到一个`\Laravel\Lumen\Http\Request`实例，那么它将无法获得客户端提供的数据。所以**在路由闭包和控制器方法中，还是要将`$request`参数定义为`\Illuminate\Http\Request`类型。**

有个例外：在 Middleware 的`handle()`方法中的第一个参数可以定义为`\Laravel\Lumen\Http\Request`类型。这是由于 Middleware 的第一个参数是由 Application 在调用中间件时直接传入`$app->make('request')`的结果得到的，而不是由 Application 解析生产的。所以这个参数无论是被定义为`\Laravel\Lumen\Http\Request`类型，还是`\Illuminate\Http\Request`，都不会有问题。



