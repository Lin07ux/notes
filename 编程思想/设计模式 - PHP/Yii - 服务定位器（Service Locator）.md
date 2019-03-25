跟 DI 容器类似，引入 Service Locator 目的也在于解耦。有许多成熟的设计模式也可用于解耦，但在 Web 应用上，Service Locator 绝对占有一席之地。对于 Web 开发而言，Service Locator 天然地适合使用，主要就是因为 Service Locator 模式非常贴合 Web 这种基于服务和组件的应用的运作特点。这一模式的优点有：

* Service Locator 充当了一个运行时的链接器的角色，可以在运行时动态地修改一个类所要选用的服务，而不必对类作任何的修改。
* 一个类可以在运行时，有针对性地增减、替换所要用到的服务，从而得到一定程度的优化。
* 实现服务提供方、服务使用方完全的解耦，便于独立测试和代码跨框架复用。

## Service Locator 的基本功能
在 Yii 中 Service Locator 由`yii\di\ServiceLocator`来实现。从代码组织上，Yii 将 Service Locator 放到与 DIC 同一层次来对待，都组织在`yii\di`命名空间下。下面是`Service Locator`的源代码：

```php
class ServiceLocator extends Component
{
    // 用于缓存服务、组件等的实例
    private $_components = [];

    // 用于保存服务和组件的定义，通常为配置数组，可以用来创建具体的实例
    private $_definitions = [];


    // 重载了 getter 方法，使得访问服务和组件就跟访问类的属性一样。
    // 同时，也保留了原来Component的 getter所具有的功能。
    // 请留意，ServiceLocator 并未重载 __set()，
    // 仍然使用 yii\base\Component::__set()
    public function __get($name)
    {
        ... ...
    }

    // 对比Component，增加了对是否具有某个服务和组件的判断。
    public function __isset($name)
    {
        ... ...
    }

    // 当 $checkInstance === false 时，用于判断是否已经定义了某个服务或组件
    // 当 $checkInstance === true 时，用于判断是否已经有了某个服务或组件的实例
    public function has($id, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_components[$id]) :
            isset($this->_definitions[$id]);
    }

    // 根据 $id 获取对应的服务或组件的实例
    public function get($id, $throwException = true)
    {
       ... ...
    }

    // 用于注册一个组件或服务，其中 $id 用于标识服务或组件。
    // $definition 可以是一个类名，一个配置数组，一个 PHP callable，或者一个对象
    public function set($id, $definition)
    {
        ... ...
    }

    // 删除一个服务或组件
    public function clear($id)
    {
        unset($this->_definitions[$id], $this->_components[$id]);
    }

    // 用于返回 Service Locator 的 $_components 数组或 $_definitions 数组，
    // 同时也是 components 属性的 getter 函数
    public function getComponents($returnDefinitions = true)
    {
        ... ...
    }

    // 批量方式注册服务或组件，同时也是 components 属性的 setter 函数
    public function setComponents($components)
    {
        ... ...
    }
}
```

从代码可以看出，Service Locator 继承自`yii\base\Component`，这是 Yii 中的一个基础类，提供了属性、事件、行为等基本功能，关于 Component 的有关知识，可以看看 [属性(Property)](http://www.digpage.com/property.html#property)、[事件(Event)](http://www.digpage.com/event.html#event) 和 [行为(Behavior)](http://www.digpage.com/behavior.html#behavior)。

Service Locator 通过`__get()`、`__isset()`、`has()`等方法，扩展了`yii\base\Component`的最基本功能，提供了对于服务和组件的属性化支持。

从功能来看，Service Locator 提供了注册服务和组件的`set()`、`setComponents()`等方法， 用于删除的`clear()`。用于读取的`get()`和`getComponents()`等方法。

细心的读者可能一看到`setComponents()`和`getComponents()`就猜到了，Service Locator 还具有一个可读写的 components 属性。

### Service Locator 的数据结构
从上面的代码中，可以看到 Service Locator 维护了两个数组，`$_components`和`$_definitions`。这两个数组均是以服务或组件的 ID 为键的数组。

其中，`$_components`用于缓存存 Service Locator 中的组件或服务的实例。Service Locator 为其提供了 getter 和 setter。使其成为一个可读写的属性。`$_definitions`用于保存这些组件或服务的定义。这个定义可以是：

* **配置数组**。在向 Service Locator 索要服务或组件时，这个数组会被用于创建服务或组件的实例。与 DI 容器的要求类似，当定义是配置数组时，要求配置数组必须要有`class`元素，表示要创建的是什么类。不然你让 Yii 调用哪个构造函数？
* **PHP callable**。每当向 Service Locator 索要实例时，这个 PHP callable 都会被调用，其返回值，就是所要的对象。对于这个 PHP callable 有一定的形式要求，一是它要返回一个服务或组件的实例。二是它不接受任何的参数。至于具体原因，后面会讲到。
* **对象**。这个更直接，每当你索要某个特定实例时，直接把这个对象给你就是了。
* **类名**。即，使得`is_callable($definition, true)`为真的定义。

下面是`yii\di\ServiceLocator::set()`的代码：

```php
public function set($id, $definition)
{
    // 当定义为 null 时，表示要从Service Locator中删除一个服务或组件
    if ($definition === null) {
        unset($this->_components[$id], $this->_definitions[$id]);
        return;
    }

    // 确保服务或组件ID的唯一性
    unset($this->_components[$id]);

    // 定义如果是个对象或PHP callable，或类名，直接作为定义保存
    // 留意这里 is_callable的第二个参数为true，所以，类名也可以。
    if (is_object($definition) || is_callable($definition, true)) {
        // 定义的过程，只是写入了 $_definitions 数组
        $this->_definitions[$id] = $definition;

    // 定义如果是个数组，要确保数组中具有 class 元素
    } elseif (is_array($definition)) {
        if (isset($definition['class'])) {
            // 定义的过程，只是写入了 $_definitions 数组
            $this->_definitions[$id] = $definition;
        } else {
            throw new InvalidConfigException(
"The configuration for the \"$id\" component must contain a \"class\" element.");
        }

    // 这也不是，那也不是，那么就抛出异常吧
    } else {
        throw new InvalidConfigException(
            "Unexpected configuration type for the \"$id\" component: "
            . gettype($definition));
    }
}
```

服务或组件的 ID 在 Service Locator 中是唯一的，用于区别彼此。在任何情况下，Service Locator 中同一 ID 只有一个实例、一个定义。也就是说，Service Locator 中，所有的服务和组件，只保存一个单例。 这也是正常的逻辑，既然称为服务定位器，你只要给定一个 ID，它必然返回一个确定的实例。这一点跟 DI 容器是一样的。

Service Locator 中 ID 仅起标识作用，可以是任意字符串，但通常用服务或组件名称来表示。 如，以`db`来表示数据库连接，以`cache`来表示缓存组件等。

至于批量注册的`yii\di\ServiceLocator::setCompoents()`只不过是简单地遍历数组，循环调用`set()`而已。就算我不把代码贴出来，像你这么聪明的，一下子就可以自己写出来了。

向 Service Locator 注册服务或组件，其实就是向`$_definitions`数组写入信息而已。

### 访问 Service Locator 中的服务
Service Locator 重载了`__get()`使得可以像访问类的属性一样访问已经实例化好的服务和组件。 下面是重载的`__get()`方法：

```php
public function __get($name)
{
    // has() 方法就是判断 $_definitions 数组中是否已经保存了服务或组件的定义
    // 请留意，这个时候服务或组件仅是完成定义，不一定已经实例化
    if ($this->has($name)) {

        // get() 方法用于返回服务或组件的实例
        return $this->get($name);

    // 未定义的服务或组件，那么视为正常的属性、行为，
    // 调用 yii\base\Component::__get()
    } else {
        return parent::__get($name);
    }
}
```

在注册好了服务或组件定义之后，就可以像访问属性一样访问这些服务（组件）。前提是已经完成注册，不要求已经实例化。访问这些服务或属性，被转换成了调用`yii\di\ServiceLocator::get()`来获取实例。下面是使用这种形式访问服务或组件的例子：

```php
// 创建一个Service Locator
$serviceLocator = new yii\di\ServiceLocator;

// 注册一个 cache 服务
$serviceLocator->set('cache', [
    'class' => 'yii\cache\MemCache',
    'servers' => [
        ... ...
    ],
]);

// 使用访问属性的方法访问这个 cache 服务
$serviceLocator->cache->flushValues();

// 上面的方法等效于下面这个
$serviceLocator->get('cache')->flushValues();
```

在 Service Locator 中，并未重载`__set()`。所以，Service Locator 中的服务和组件看起来就好像只读属性一样。要向 Service Locator 中“写”入服务和组件，没有`setter`可以使用，需要调用`yii\di\ServiceLocator::set()`对服务和组件进行注册。


## 通过 Service Locator 获取实例
与注册服务和组件的简单之极相反，Service Locator 在创建获取服务或组件实例的过程要稍微复杂一点。这一点和 DI 容器也是很像的。Service Locator 通过`yii\di\ServiceLocator::get()`来创建、获取服务或组件的实例：

```php
public function get($id, $throwException = true)
{
    // 如果已经有实例化好的组件或服务，直接使用缓存中的就OK了
    if (isset($this->_components[$id])) {
        return $this->_components[$id];
    }

    // 如果还没有实例化好，那么再看看是不是已经定义好
    if (isset($this->_definitions[$id])) {
        $definition = $this->_definitions[$id];

        // 如果定义是个对象，且不是Closure对象，那么直接将这个对象返回
        if (is_object($definition) && !$definition instanceof Closure) {
            // 实例化后，保存进 $_components 数组中，以后就可以直接引用了
            return $this->_components[$id] = $definition;

        // 是个数组或者PHP callable，调用 Yii::createObject()来创建一个实例
        } else {
            // 实例化后，保存进 $_components 数组中，以后就可以直接引用了
            return $this->_components[$id] = Yii::createObject($definition);
        }
    } elseif ($throwException) {
        throw new InvalidConfigException("Unknown component ID: $id");

    // 即没实例化，也没定义，万能的Yii也没办法通过一个任意的ID，
    // 就给你找到想要的组件或服务呀，给你个 null 吧。
    // 表示Service Locator中没有这个ID的服务或组件。
    } else {
        return null;
    }
}
```

Service Locator 创建获取服务或组件实例的过程是：

* 首先看看缓存数组`$_components`中有没有已经创建好的实例。有的话，皆大欢喜，直接用缓存中的就可以了。
* 缓存中没有的话，那就要从定义开始创建了。
* 如果服务或组件的定义是个对象，那么直接把这个对象作为服务或组件的实例返回就可以了。但有一点要注意，当使用一个 PHP callable 定义一个服务或组件时，这个定义是一个 Closure 类的对象。这种定义虽然也对象，但是可不能把这种对象直接当成服务或组件的实例返回。
* 如果定义是一个数组或者一个 PHP callable，那么把这个定义作为参数，调用`Yii::createObject()`来创建实例。

这个`Yii::createObject()`先放一放，知道他能为 Service Locator 创建对象就 OK 了，等下还会讲这个方法的。


## 在 Yii 应用中使用 Service Locator 和 DI 容器
我们在讲 DI 容器时，提到了 Yii 中是把 Service Locator 和 DI 容器结合起来用的，Service Locator 是建立在 DI 容器之上的。那么一个 Yii 应用，是如何使用 Service Locator 和 DI 容器的呢？

### DI 容器的引入
我们知道，每个 Yii 应用都有一个入口脚本`index.php`。在其中，有一行不怎么显眼：

```php
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
```

这一行看着普通，也就是引入一个`Yii.php`的文件。但是，让我们来看看这个`Yii.php`：

```php
<?php

require(__DIR__ . '/BaseYii.php');

class Yii extends \yii\BaseYii
{
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = include(__DIR__ . '/classes.php');

// 重点看这里。创建一个 DI 容器，并由 Yii::$container 引用
Yii::$container = new yii\di\Container;
```

Yii 是一个工具类，继承自`yii\BaseYii`。 但这里对父类的代码没有任何重载，意味之父类和子类在功能上其实是相同的。但是，Yii 提供了让你修改默认功能的机会。就是自己写一个 Yii 类，来扩展、重载 Yii 默认的、由`yii\BaseYii`提供的特性和功能。尽管实际使用中，我们还从来没有需要改写过这个类，主要是因为没有必要在这里写代码，可以通过别的方式实现。但 Yii 确实提供了这么一个可能。这个在实践中不常用，有这么个印象就足够了。

这里重点看最后一句代码，创建了一个 DI 容器，并由`Yii::$container`引用。也就是说，Yii 类维护了一个 DI 容器，这是 DI 容器开始介入整个应用的标志。同时，这也意味着，在 Yii 应用中，我们可以随时使用`Yii::$container`来访问 DI 容器。一般情况下，如无必须的理由，不要自己创建 DI 容器，使用`Yii::$container`完全足够。

### Application 的本质
再看看入口脚本`index.php`的最后两行：

```php
$application = new yii\web\Application($config);
$application->run();
```

创建了一个`yii\web\Application`实例，并调用其`run()`方法。那么，这个`yii\web\Application`是何方神圣？首先，`yii\web\Application`继承自`yii\base\Application`，这从`yii\web\Application`的源码可以看出来：

```php
class Application extends \yii\base\Application
{
    ... ...
}
```

而`yii\base\Application`又继承自`yii\base\Module`，说明所有的 Application 都是 Module：

```php
abstract class Application extends Module
{
    ... ...
}
```

那么`yii\base\Module`又继承自哪个类呢？不知道你猜到没，他继承自`yii\di\ServiceLocator`：

```php
class Module extends ServiceLocator
{
    ... ...
}
```

所有的 Module 都是服务定位器 Service Locator，因此，所有的 Application 也都是 Service Locator。

同时，在 Application 的构造函数中，`yii\base\Application::__construct()`：

```php
public function __construct($config = [])
{
    Yii::$app = $this;
    ... ...
}
```

第一行代码就把 Application 当前的实例，赋值给`Yii::$app`了。 这意味着 Yii 应用创建之后，可以随时通过`Yii::$app`来访问应用自身，也就是访问 Service Locator。

至此，DI 容器有了，Service Locator 也出现了。那么Yii是如何摆布这两者的呢？这两者又是如何千里姻缘一线牵的呢？

### 实例创建方法
Service Locator 和 DI 容器的亲密关系就隐藏在`yii\di\ServiceLocator::get()`获取实例时，调用的`Yii::createObject()`中。前面我们说到这个 Yii 继承自`yii\BaseYii`，因此这个函数实际上是`BaseYii::createObject()`，其代码如下：

```php
// static::$container 就是上面说的引用了 DI 容器的静态变量

public static function createObject($type, array $params = [])
{
    // 字符串，代表一个类名、接口名、别名。
    if (is_string($type)) {
        return static::$container->get($type, $params);

    // 是个数组，代表配置数组，必须含有 class 元素。
    } elseif (is_array($type) && isset($type['class'])) {
        $class = $type['class'];
        unset($type['class']);

        // 调用 DI 容器的 get() 来获取、创建实例
        return static::$container->get($class, $params, $type);

    // 是个 PHP callable 则调用其返回一个具体实例。
    } elseif (is_callable($type, true)) {

        // 是个 PHP callable，那就调用它，并将其返回值作为服务或组件的实例返回
        return call_user_func($type, $params);

    // 是个数组但没有 class 元素，抛出异常
    } elseif (is_array($type)) {
        throw new InvalidConfigException(
        'Object configuration must be an array containing a "class" element.');

    // 其他情况，抛出异常
    } else {
        throw new InvalidConfigException(
            "Unsupported configuration type: " . gettype($type));
    }
}
```

这个`createObject()`提供了一个向 DI 容器获取实例的接口，对于不同的定义，除了 PHP callable 外，`createObject()`都是调用了 DI 容器的`yii\di\Container::get()`来获取实例的。

`Yii::createObject()`就是 Service Locator 和 DI 容器亲密关系的证明，也是 Service Locator 构建于 DI 容器之上的证明。而 Yii 中所有的 Module，包括 Application 都是Service Locator，因此，它们也都构建在 DI 容器之上。

同时，在 Yii 框架代码中，只要创建实例，就是调用`Yii::createObject()`这个方法来实现。 可以说，Yii 中所有的实例（除了 Application，DI 容器自身等入口脚本中实例化的），都是通过 DI 容器来获取的。

同时，我们不难发现，Yii 的基类`yii\BaseYii`，所有的成员变量和方法都是静态的，其中的 DI 容器是个静态成员变量`$container`。 因此，DI 容器就形成了最常见形式的单例模式，在内存中仅有一份，所有的 Service Locator （Module 和 Application）都共用这个 DI 容器。这就节省了大量的内存空间和反复构造实例的时间。

更为重要的是，DI 容器的单例化，使得 Yii 不同的模块共用组件成为可能。可以想像，由于共用了 DI 容器，容器里面的内容也是共享的。因此，你可以在 A 模块中改变某个组件的状态，而 B 模块中可以了解到这一状态变化。但是，如果不采用单例模式，而是每个模块（Module 或 Application）维护一个自己的 DI 容器，要实现这一点难度会大得多。

所以，这种共享 DI 容器的设计，是必然的，合理的。

另外，前面我们讲到，当 Service Locator 中服务或组件的定义是一个 PHP callable 时，对其形式有一定要求。一是返回一个实例，二是不接收任何参数。这在`Yii::createObject()`中也可以看出来。

由于`Yii::createObject()`为`yii\di\ServiceLocator::get()`所调用，且没有提供第二参数，因此，当使用 Service Locator 获取实例时，`Yii::createObject()`的`$params`参数为空。因此，使用`call_user_func($type, $params)`调用这个 PHP callable 时，这个 PHP callable 是接收不到任何参数的。


## Yii 创建实例的全过程
可能有的读者朋友会有疑问：不对呀，前面讲过 DI 容器的使用是要先注册依赖，后获取实例的。但 Service Locator 在注册服务、组件时，又没有向 DI 容器注册依赖。那在获取实例的时候，DI 容器怎么解析依赖并创建实例呢？

请留意，在向 DI 容器索要一个没有注册过依赖的类型时，DI 容器视为这个类型不依赖于任何类型可以直接创建，或者这个类型的依赖信息容器本身可以通过 Reflection API 自动解析出来，不用提前注册。

可能还有的读者会想：还是不对呀，在我开发 Yii 的过程中，又没有写过注册服务的代码：

```php
Yii::$app->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=db.digpage.com;dbname=digpage.com',
    'username' => 'www.digpage.com',
    'password' => 'www.digapge.com',
    'charset' => 'utf8',
]);

Yii::$app->set('cache', [
    'class' => 'yii\caching\MemCache',
    'servers' => [
        [
            'host' => 'cache1.digpage.com',
            'port' => 11211,
            'weight' => 60,
        ],
        [
            'host' => 'cache2.digpage.com',
            'port' => 11211,
            'weight' => 40,
        ],
    ],
]);
```

为何可以在没有注册的情况下获取服务的实例并使用服务呢？

其实，你也不是什么都没写，至少肯定是在某个配置文件中写了有关的内容的：

```php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => 'cache1.digpage.com',
                    'port' => 11211,
                    'weight' => 60,
                ],
                [
                    'host' => 'cache2.digpage.com',
                    'port' => 11211,
                    'weight' => 40,
                ],
            ],
        ],
        ... ...
    ],
];
```

只不过，在 [配置项(Configuration)]() 和 [Object 的配置方法]() 部分，我们了解了配置文件是如何产生作用的，配置到应用当中的。这个数组会被`Yii::configure($config)`所调用，然后会变成调用 Application 的`setComponents()`，而 Application 其实就是一个 Service Locator。`setComponents()`方法又会遍历传入的配置数组，然后使用使用 Service Locator 的`set()`方法注册服务。

到了这里，就可以了解到：每次在配置文件的`components`项写入配置信息，最终都是在向 Application 这个 Service Locator 注册服务。

让我们回顾一下，DI 容器、Service Locator 是如何配合使用的：

* Yii 类提供了一个静态的`$container`成员变量用于引用 DI 容器。在入口脚本中，会创建一个 DI 容器，并赋值给这个`$container`。
* Service Locator 通过`Yii::createObject()`来获取实例，而这个`Yii::createObject()`是调用了 DI 容器的`yii\di\Container::get()`来向`Yii::$container`索要实例的。因此，Service Locator 最终是通过 DI 容器来创建、获取实例的。
* 所有的 Module，包括 Application 都继承自`yii\di\ServiceLocator`，都是 Service Locator。因此，DI 容器和 Service Locator 就构成了整个 Yii 的基础。


