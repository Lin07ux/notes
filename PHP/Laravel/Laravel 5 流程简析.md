> 文中 PHP 代码默认的 namespace 为 Illuminate。代码中的异常处理流程被忽略，未作描述。

public/index.php
	1.	引入 autoload 文件；
	2.	引入 Bootstrap/app.php；
	3.	通过容器实例化 Kernel 接口对象；
	4.	Request::capture 获取到 Request 对象，并交给 Kernel 实例处理，得到 Response 实例；
	5.	Response 实例输出 send()；
	6.	Kernel 终结，传入 Request 和 Response 作为参数；

Bootstrap/app.php
	1.	实例化 Application 对象；
	1.	容器绑定最基础的两个对象：’app’ -> Application($this); Container -> Application($this);
	2.	注册最基础的 Service Provider: EventServiceProvider & RoutingServiceProvider;
	3.	注册容器核心类和接口对应的别名；
	4.	设置应用根目录；
	2.	绑定核心接口对应的类：Contracts\Http\Kernel, Contracts\Console\Kernel, Contracts\Debug\ExceptionHandler；
	3.	返回 Application 实例；

Foundation\Http\Kernel 构造函数
	1.	注入 Application 和 Router 的依赖对象；
	2.	将自己设置的路由中间件 ( routeMiddleware ) 注册到 Router 对象中；

Http\Request::capture() 捕获请求
	1.	启用 _method 参数，用于支持 HTTP 的 PUT/DELET 方法；
	2.	调用父类的 createFromGlobals() 方法，将 $_GET/$_POST 等全局变量封装在 ParameterBag 对象中，赋值给 Request 对象的相应属性；
	3.	将上一步生产的 Symfony 的 Request 对象复制到 HTTP\Request 对象中，并且更改了 request 属性，加入了对 JSON 数据的处理 ( getInputSource() 方法)；
	4.	返回 Request 对象；

Kernel->handle(Request) 处理请求
	1.	再次启用 Request 的 _method 支持；
	2.	将请求发送给 Router 对象（sendRequestThroughRouter），接收返回的 Response 对象；
	1.	将 Request 对象注入到容器中，对应名称为 ‘request’；
	2.	清除 Facade 中的 ‘request’ 实例；（具体作用尚未知）
	3.	内核的启动过程: 通过容器（Application）启动内核中注册的启动项，并触发相应的启动事件，默认的启动项目有：
	•	Foundation\Bootstrap\DetectEnvironment
	•	Foundation\Bootstrap\LoadConfiguration
	•	Foundation\Bootstrap\ConfigureLogging
	•	Foundation\Bootstrap\HandleException
	•	Foundation\Bootstrap\RegisterFacades
	•	Foundation\Bootstrap\RegisterProviders
	•	Foundation\Bootstrap\BootProviders
	4.	依赖于 app 容器建立一个管道，使 Request 对象经过注册于内核的中间件处理后，最终下发给 Router->dispatch ；
	1.	Router 调用 before 过滤器；
	2.	下发至路由处理（稍后说明），获得 Response 对象；
	3.	生成并优化 Response 对象；
	4.	Router 调用 after 过滤器；
	5.	返回 Response 对象；
	3.	触发 kernel.handled 事件，返回 Response 对象；

Response 输出
	1.	发送头信息；
	2.	输出 $this->content ；
	3.	flush 输出
	•	fastcgi_finish_reqeust
	•	ob_end_flush & ob_end_clean

Kernel 终结
	1.	取得所有的路由中间件，并执行相应的 terminate 方法；
	2.	调用 Application 的 terminate 方法：执行所有注册的终结回调过程（$this->terminatingCallbacks）；

Router->dispatchToRoute 路由处理过程
	1.	根据 Request 实例获取匹配的路由对象；
	1.	根据 Request 从 RouteCollection 中匹配路由；
	2.	将匹配到的路由对象注入到容器中；
	3.	对路由中的参数进行计算替换；（具体作用有待考察）
	2.	给 Request 对象设定路由回调函数，该函数返回此匹配到的路由；
	3.	出发 router.matched 事件；
	4.	调用 Route 的 before 过滤器；
	5.	如果返回为 null，则调用 runRouteWithStack 方法；
	1.	建立基于容器的管道，使请求对象通过通过路由相关的中间件；
	2.	最终执行路由的 run 方法，并返回结果；
	6.	生成并优化 Response 对象；
	7.	调用 Route 的 after 过滤器；
	8.	返回 Response 对象；


> 转摘：[Laravel 5 流程简析](https://log.zvz.im/2016/06/28/Laravel-5/)

