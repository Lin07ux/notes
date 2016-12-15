Laravel 中的服务容器其本质上是一个 IoC 容器，通过使用闭包、反射类来自动化进行类的实例化。下面先实现一个简单的服务器容器，来帮助理解 Laravel 中的服务容器。

### 示例代码
**Container.php**

实现一个简单的服务容器类，能够添加依赖、解析依赖，然后自动实例化一个对象。主要使用到了反射类来分析类的构造函数的参数，并实例化参数对象。

```php
<?php

class Container {
    /**
     * @var array 设置的依赖绑定
     */
    public $binding = [];

    /**
     * 注入依赖
     *
     * @param string              $abstract 目标类
     * @param null|string|Closure $concrete 依赖
     * @param bool                $shared   是否共享
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->binding[$abstract] = compact('concrete', 'shared');
    }
    
    /**
     * 生成依赖的闭包函数
     *
     * 如果目标类和依赖类相同，则使用 build 方法实例化
     * 否则使用 make 来完成循环实例化
     * 
     * @param string $abstract 目标类
     * @param string $concrete 依赖类
     *
     * @return Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($c) use ($abstract, $concrete) {
            $method = $abstract == $concrete ? 'build' : 'make';
            return $c->$method($concrete);
        };
    }
    
    /**
     * 生成目标类的示例
     *
     * @param string $abstract 目标类的名称
     *
     * @return object
     *
     * @throws Exception
     */
    public function make($abstract)
    {
        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            return $this->build($concrete);
        }

        return $this->make($concrete);
    }

    /**
     * 获取目标类的依赖闭包
     *
     * @param string $abstract 目标类
     *
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (!isset($this->binding[$abstract])) {
            return $abstract;
        }

        return $this->binding[$abstract]['concrete'];
    }

    /**
     * 判断目标类是否可以直接创建
     *
     * @param string         $concrete 目标类
     * @param string|Closure $abstract 依赖
     *
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete == $abstract || $concrete instanceof Closure;
    }

    /**
     * 构造依赖类实例,可能会存在依赖
     *
     * @param string|Closure $concrete 依赖类名或闭包
     *
     * @return object
     *
     * @throws Exception
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // 反射
        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [$concrete] is not instantiable.");
        }

        // 获取要实例化对象的构造函数
        $constructor = $reflector->getConstructor();

        // 没有定义构造函数，只有默认的构造函数，说明构造函数参数个数为空
        if (is_null($constructor)) {
            return new $concrete;
        }

        // 获取构造函数所需要的所有参数
        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        // 从给出的数组参数在中实例化对象
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * 获取类的构造函数的依赖参数
     *
     * @param ReflectionParameter[] $parameters 类的构造函数的参数列表
     *
     * @return array
     */
    protected function getDependencies($parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $c = $parameter->getClass();
            $dependencies[] = is_null($c) ? null : $this->make($c->name);
        }

        return $dependencies;
    }
}
```

**train.php**

这个就是一个应用实例，使用了上面的容器类。在添加了相关的依赖之后，就能够自动的添加实例化出一个对象了。

```php
<?php

require __DIR__ . '/Container.php';

/**
 * 旅行类接口
 */
interface TrafficTool
{
    public function go();
}

/**
 * 火车旅行
 */
class Train implements TrafficTool
{
    public function go()
    {
        echo 'Train...';
    }
}

/**
 * 徒步旅行
 */
class Leg implements TrafficTool
{
    public function go()
    {
        echo 'Leg...';
    }
}

/**
 * 旅行者
 */
class Traveller
{
    protected $trafficTool;

    /**
     * 旅行者构造方法
     * 
     * @param TrafficTool $trafficTool 旅行者使用的旅行方式
     */
    public function __construct(TrafficTool $trafficTool)
    {
        $this->trafficTool = $trafficTool;
    }

    /**
     * 去西藏旅行
     */
    public function visitTibet()
    {
        $this->trafficTool->go();
    }
}

$app = new Container();

$app->bind('TrafficTool', 'Train');
$app->bind('travellerA', 'Traveller');

$traveller = $app->make('travellerA');
$traveller->visitTibet();  // Train...
```

在实例化旅行者之前，先设置好对应的类依赖。然后就可以直接使用容器的`make()`方法直接实例化类了。可以看到，实例化后的旅行者已经自动的传入了一个旅行方式类的实例，所有在`visitTibet()`方法中就能够正常的输出`Train...`了。

### 分析服务器容器
目前来看，我们已经实现了一个简单的服务容器类了。那么 IoC 解决了一个什么问题呢？结合上面的代码来看，就是我们再实例化对象的时候不用使用`new`了，直接调用容器的`make()`方法就可以实例化出一个对象了。

当然，实例化一个对象的时候，可能会需要传入对应的参数，比如 Traveller 的构造函数是需要一个参数的，可是我们调用`make()`方法的时候并没有提供这个参数。这是为什么呢？

这就是 IoC 强大之处了，调用`make()`实例化对象的时候，容器会使用反射功能，去分析我们要实例化对象的构造函数，获取构造函数所需的每个参数，然后分别去实例化这些参数。如果实例化这些参数也要参数，那么就再去实例化参数的参数。到最后成功实例化我们所需要的`traveller`了。在 Container 的`build()`方法中就是使用反射来实例化对象。

> 当然，我们这里实现的服务容器是一个很简单的，仅仅把假定所有的类的构造方法中的参数都是一个对象，没有考虑有默认值、或是基本类型的情况。

还有一个问题：IoC 容器怎么知道实例化 Traveller 的时候需要的 Train 对象，而不是 Leg 对象？

其实 IoC 容器什么都不知道，IoC 会实例化哪些对象都是通过`bind()`方法告诉 IoC 的。上面的例子两次调用`bind()`方法，就是告诉 IoC 可以实例化的对象有 Train 和 Traveller。再通俗讲就是：当需要当我们需要 TrafficTool 这个服务的时候去实例化 Train 这个类；需要一个 travellerA 的旅行者的时候去实例化 Traveller 类。而 Train 这个就是 travellerA 就是去西藏的方式。这样子如果想要走路去青藏的话只要把`$app->bind('Visit', 'Train');`改为`$app->bind('Visit', 'Leg');`就可以。

那又有一个问题了：上面的这些有什么意义直接`$traveller = new Traveller($trafficTool)`来实例化对象好像也没有什么不好的。

这其实就是 IoC 模式主要要解决的问题了：**解耦**。

* 如果使用`new`来实例化对象，可能会产生依赖，比如直接使用`$traveller = new Traveller($trafficTool)`之前，我们要创建一个`$trafficTool`，即 Traveller 依赖于 trafficTool，就产生了依赖，这两个组件就没办法分开了。

* 而使用 IoC 时，如果想要走路去青藏的话只要把`$app->bind('Visit', 'Train');`改为`$app->bind('Visit', 'Leg');`就可以了。这样使用何种方式去青藏我们可以自由的选择。


当然，Laravel 中的服务容器的功能更加强大，会考虑到参数的各种情况，而且还会对解析依赖的过程中的反射类做对应的缓存，甚至还能为类的实例化提供特定的参数等。另外，Laravel 中的服务都是在配置中配置好，然后在程序启动的时候，自动绑定相关依赖的。

### 转摘
[深入Laravel服务容器](https://segmentfault.com/a/1190000007753790)

