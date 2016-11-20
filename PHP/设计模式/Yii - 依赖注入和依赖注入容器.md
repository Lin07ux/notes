转摘：[依赖注入和依赖注入容器 -- Yii 模式](http://www.digpage.com/di.html) 

为了降低代码耦合程度，提高项目的可维护性，Yii 采用多许多当下最流行又相对成熟的设计模式，包括了依赖注入（Denpdency Injection, DI）和服务定位器（Service Locator）两种模式。 关于依赖注入与服务定位器，[Inversion of Control Containers and the Dependency Injection pattern](http://martinfowler.com/articles/injection.html) 给出了很详细的讲解，这里结合 Web 应用和 Yii 具体实现进行探讨，以加深印象和理解。 这些设计模式对于提高自身的设计水平很有帮助，这也是我们学习 Yii 的一个重要出发点。

## 有关概念
在了解 Service Locator 和 Dependency Injection 之前，有必要先来了解一些高大上的概念。 别担心，你只需要有个大致了解就 OK 了，如果展开来说，这些东西可以单独写个研究报告：

### 依赖倒置原则（Dependence Inversion Principle, DIP）
DIP 是一种软件设计的指导思想。传统软件设计中，上层代码依赖于下层代码，当下层出现变动时， 上层代码也要相应变化，维护成本较高。而 DIP 的核心思想是上层定义接口，下层实现这个接口，从而使得下层依赖于上层，降低耦合度，提高整个系统的弹性。这是一种经实践证明的有效策略。

### 控制反转（Inversion of Control, IoC）
IoC 就是 DIP 的一种具体思路，DIP 只是一种理念、思想，而 IoC 是一种实现 DIP 的方法。 IoC 的核心是将类（上层）所依赖的单元（下层）的实例化过程交由第三方来实现。一个简单的特征，就是类中不对所依赖的单元有诸如`$component = new yii\component\SomeClass()`的实例化语句。

### 依赖注入（Dependence Injection, DI）
DI 是 IoC 的一种设计模式，是一种套路，按照 DI 的套路，就可以实现 IoC，就能符合 DIP 原则。 DI 的核心是把类所依赖的单元的实例化过程，放到类的外面去实现。

### 控制反转容器（IoC Container）
当项目比较大时，依赖关系可能会很复杂。而 IoC Container 提供了动态地创建、注入依赖单元，映射依赖关系等功能，减少了许多代码量。Yii 设计了一个`yii\di\Container`来实现了 DI Container。

### 服务定位器（Service Locator）
Service Locator 是 IoC 的另一种实现方式，其核心是把所有可能用到的依赖单元交由 Service Locator 进行实例化和创建、配置，把类对依赖单元的依赖，转换成类对 Service Locator 的依赖。DI 与 Service Locator 并不冲突，两者可以结合使用。目前，Yii 2.0 把这 DI 和Service Locator 这两个东西结合起来使用，或者说通过 DI 容器，实现了 Service Locator。


是不是云里雾里的？没错，所谓“高大上”的玩意往往就是这样，看着很炫，很唬人。 卖护肤品的难道会跟你说其实皮肤表层是角质层，不具吸收功能么？这玩意又不考试，大致意会下就 OK 了。 万一哪天要在妹子面前要装一把范儿的时候，张口也能来这么几个“高大上”就行了。 但具体的内涵，我们还是要要通过下面的学习来加深理解，毕竟要把“高大上”的东西用好，发挥出作用来。


## 依赖注入
首先讲讲 DI。在 Web 应用中，很常见的是使用各种第三方 Web Service 实现特定的功能，比如发送邮件、推送微博等。假设要实现当访客在博客上发表评论后，向博文的作者发送 Email 的功能，通常代码会是这样：

```php
// 为邮件服务定义抽象层
interface EmailSenderInterface
{
    public function send(...);
}

// 定义 Gmail 邮件服务
class GmailSender implements EmailSenderInterface
{
    ...

    // 实现发送邮件的类方法
    public function send(...)
    {
        ...
    }
}

// 定义评论类
class Comment extend yii\db\ActiveRecord
{
    // 用于引用发送邮件的库
    private $_eMailSender;

    // 初始化时，实例化 $_eMailSender
    public function init()
    {
        ...
        // 这里假设使用 Gmail 的邮件服务
       $this->_eMailSender = GmailSender::getInstance();
        ...
    }

    // 当有新的评价，即 save() 方法被调用之后中，会触发以下方法
    public function afterInsert()
    {
        ...
        //
        $this->_eMailSender->send(...);
        ...
    }
}
```

> 上面的代码只是一个示意，大致是这么个流程。

那么这种常见的设计方法有什么问题呢？主要问题在于 Comment 对于 GmailSender 的依赖（对于`EmailSenderInterface`的依赖不可避免），假设有一天突然不使用 Gmail 提供的服务了，改用 Yahoo 或自建的邮件服务了。那么，你不得不修改`Comment::init()`里面对`$_eMailSender`的实例化语句：

```php
$this->_eMailSender = MyEmailSender::getInstance();
```

这个问题的本质在于，你今天写完这个 Comment，只能用于这个项目，哪天你开发别的项目要实现类似的功能，你还要针对新项目使用的邮件服务修改这个 Comment。代码的复用性不高呀。有什么办法可以不改变 Comment 的代码，就能扩展成对各种邮件服务都支持么？换句话说，有办法将 Comment 和 GmailSender 解耦么？有办法提高 Comment的 普适性、复用性么？

依赖注入就是为了解决这个问题而生的，当然，DI 也不是唯一解决问题的办法，毕竟条条大路通罗马。  Service Locator 也是可以实现解耦的。

在 Yii 中使用 DI 解耦，有 2 种注入方式：构造函数注入、属性注入。

### 构造函数注入
构造函数注入通过构造函数的形参，为类内部的抽象单元提供实例化。具体的构造函数调用代码，由外部代码决定。具体例子如下：

```php
// 这是构造函数注入的例子
class Comment extend yii\db\ActiveRecord
{
    // 用于引用发送邮件的库
    private $_eMailSender;

    // 构造函数注入
    public function __construct($emailSender)
    {
        ...
        $this->_eMailSender = $emailSender;
        ...
    }

    // 当有新的评价，即 save() 方法被调用之后中，会触发以下方法
    public function afterInsert()
    {
        ...
        //
        $this->_eMailSender->send(...);
        ...
    }
}

// 实例化两种不同的邮件服务，当然，他们都实现了 EmailSenderInterface
sender1 = new GmailSender();
sender2 = new MyEmailSender();

// 用构造函数将 GmailSender 注入
$comment1 = new Comment(sender1);
// 使用 Gmail 发送邮件
$comment1.save();

// 用构造函数将 MyEmailSender 注入
$comment2 = new Comment(sender2);
// 使用 MyEmailSender 发送邮件
$comment2.save();
```

上面的代码对比原来的代码，解决了 Comment 类对于 GmailSender 等具体类的依赖，通过构造函数，将相应的实现了 EmailSenderInterface 接口的类实例传入 Comment 类中，使得 Comment 类可以适用于不同的邮件服务。从此以后，无论要使用何何种邮件服务，只需写出新的 EmailSenderInterface 实现即可，Comment 类的代码不再需要作任何更改，多爽的一件事，扩展起来、测试起来都省心省力。

### 属性注入
与构造函数注入类似，属性注入通过 setter 或 public 成员变量，将所依赖的单元注入到类内部。 具体的属性写入，由外部代码决定。具体例子如下：

```php
// 这是属性注入的例子
class Comment extend yii\db\ActiveRecord
{
    // 用于引用发送邮件的库
    private $_eMailSender;

    // 定义了一个 setter()
    public function setEmailSender($value)
    {
        $this->_eMailSender = $value;
    }

    // 当有新的评价，即 save() 方法被调用之后中，会触发以下方法
    public function afterInsert()
    {
        ...
        //
        $this->_eMailSender->send(...);
        ...
    }
}

// 实例化两种不同的邮件服务，当然，他们都实现了 EmailSenderInterface
sender1 = new GmailSender();
sender2 = new MyEmailSender();

$comment1 = new Comment;
// 使用属性注入
$comment1->eMailSender = sender1;
// 使用 Gmail 发送邮件
$comment1.save();

$comment2 = new Comment;
// 使用属性注入
$comment2->eMailSender = sender2;
// 使用 MyEmailSender 发送邮件
$comment2.save();
```

上面的 Comment 如果将`private $_eMailSender`改成`public $eMailSender`并删除  setter 函数， 也是可以达到同样的效果的。

与构造函数注入类似，属性注入也是将 Comment 类所依赖的 EmailSenderInterface 的实例化过程放在 Comment 类以外。这就是依赖注入的本质所在。为什么称为注入？从外面把东西打进去，就是注入。什么是外，什么是内？要解除依赖的类内部就是内，实例化所依赖单元的地方就是外。

## DI 容器
从上面 DI 两种注入方式来看，依赖单元的实例化代码是一个重复、繁琐的过程。可以想像，一个 Web 应用的某一组件会依赖于若干单元，这些单元又有可能依赖于更低层级的单元，从而形成依赖嵌套的情形。那么，这些依赖单元的实例化、注入过程的代码可能会比较长，前后关系也需要特别地注意， 必须将被依赖的放在需要注入依赖的前面进行实例化。 这实在是一件既没技术含量，又吃力不出成果的工作，这类工作是高智商（懒）人群的天敌， 我们是不会去做这么无聊的事情的。

就像极其不想洗衣服的人发明了洗衣机（我臆想的，未考证）一样，为了解决这一无聊的问题，DI 容器被设计出来了。Yii 的 DI 容器是`yii\di\Container`，这个容器继承了发明人的高智商，他知道如何对对象及对象的所有依赖，和这些依赖的依赖，进行实例化和配置。

### DI 容器中的内容
#### DI 容器中实例的表示
容器顾名思义是用来装东西的，DI 容器里面的东西是什么呢？Yii 使用`yii\di\Instance`来表示容器中的东西。当然 Yii 中还将这个类用于 Service Locator，这个在讲 Service Locator 时再具体谈谈。

`yii\di\Instance`本质上是 DI 容器中对于某一个类实例的引用，它的代码看起来并不复杂：

```php
class Instance
{
    // 仅有的属性，用于保存类名、接口名或者别名
    public $id;

    // 构造函数，仅将传入的 ID 赋值给 $id 属性
    protected function __construct($id)
    {
    }

    // 静态方法创建一个 Instance 实例
    public static function of($id)
    {
        return new static($id);
    }

    // 静态方法，用于将引用解析成实际的对象，并确保这个对象的类型
    public static function ensure($reference, $type = null, $container = null)
    {
    }

    // 获取这个实例所引用的实际对象，事实上它调用的是
    // yii\di\Container::get() 来获取实际对象
    public function get($container = null)
    {
    }
}
```

对于`yii\di\Instance`，我们要了解：

* 表示的是容器中的内容，代表的是对于实际对象的引用。
* DI 容器可以通过他获取所引用的实际对象。
* 类仅有的一个属性 id 一般表示的是实例的类型。

#### DI 容器的数据结构
在 DI 容器中，维护了 5 个数组，这是 DI 容器功能实现的基础：

```php
// 用于保存单例 Singleton 对象，以对象类型为键
private $_singletons = [];

// 用于保存依赖的定义，以对象类型为键
private $_definitions = [];

// 用于保存构造函数的参数，以对象类型为键
private $_params = [];

// 用于缓存 ReflectionClass 对象，以类名或接口名为键
private $_reflections = [];

// 用于缓存依赖信息，以类名或接口名为键
private $_dependencies = [];
```

DI 容器的 5 个数组内容和作用如下图所示：

![DI 容器 5 个数组示意图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1479624212393.png)

### 注册依赖
使用 DI 容器，首先要告诉容器，类型及类型之间的依赖关系，声明一这关系的过程称为注册依赖。使用`yii\di\Container::set()`和`yii\di\Container::setSinglton()`可以注册依赖。DI 容器是怎么管理依赖的呢？要先看看`yii\di\Container::set()`和`yii\Container::setSinglton()`。

```php
public function set($class, $definition = [], array $params = [])
{
    // 规范化 $definition 并写入 $_definitions[$class]
    $this->_definitions[$class] = $this->normalizeDefinition($class,
        $definition);

    // 将构造函数参数写入 $_params[$class]
    $this->_params[$class] = $params;

    // 删除 $_singletons[$class]
    unset($this->_singletons[$class]);
    return $this;
}

public function setSingleton($class, $definition = [], array $params = [])
{
    // 规范化 $definition 并写入 $_definitions[$class]
    $this->_definitions[$class] = $this->normalizeDefinition($class,
        $definition);

    // 将构造函数参数写入 $_params[$class]
    $this->_params[$class] = $params;

    // 将 $_singleton[$class] 置为 null，表示还未实例化
    $this->_singletons[$class] = null;
    return $this;
}
```

这两个函数功能类似没有太大区别，只是`set()`用于在每次请求时构造新的实例返回，而 `setSingleton()`只维护一个单例，每次请求时都返回同一对象。

表现在数据结构上，就是`set()`在注册依赖时，会把使用`setSingleton()`注册的依赖删除。否则，在解析依赖时，你让 Yii 究竟是依赖续弦还是原配？因此，在 DI 容器中，依赖关系的定义是唯一的。后定义的同名依赖，会覆盖前面定义好的依赖。

从形参来看，这两个函数的`$class`参数接受一个类名、接口名或一个别名，作为依赖的名称。`$definition`表示依赖的定义，可以是一个类名、配置数组或一个 PHP callable。

这两个函数，本质上只是将依赖的有关信息写入到容器的相应数组中去。在`set()`和`setSingleton()`中，首先调用`yii\di\Container::normalizeDefinition()`对依赖的定义进行规范化处理，其代码如下：

```php
protected function normalizeDefinition($class, $definition)
{
    // $definition 是空的转换成 ['class' => $class] 形式
    if (empty($definition)) {
        return ['class' => $class];

    // $definition 是字符串，转换成 ['class' => $definition] 形式
    } elseif (is_string($definition)) {
        return ['class' => $definition];

    // $definition 是PHP callable 或对象，则直接将其作为依赖的定义
    } elseif (is_callable($definition, true) || is_object($definition)) {
        return $definition;

    // $definition 是数组则确保该数组定义了 class 元素
    } elseif (is_array($definition)) {
        if (!isset($definition['class'])) {
            if (strpos($class, '\\') !== false) {
                $definition['class'] = $class;
            } else {
                throw new InvalidConfigException(
                    "A class definition requires a \"class\" member.");
            }
        }
        
        return $definition;
    // 这也不是，那也不是，那就抛出异常算了
    } else {
        throw new InvalidConfigException(
            "Unsupported definition type for \"$class\": "
            . gettype($definition));
    }
}
```

规范化处理的流程如下：

* 如果`$definition`是空的，直接返回数组`['class' => $class]`；
* 如果`$definition`是字符串，那么认为这个字符串就是所依赖的类名、接口名或别名， 那么直接返回数组`['class' => $definition]`；
* 如果`$definition`是一个 PHP callable，或是一个对象，那么直接返回该`$definition`；
* 如果`$definition`是一个数组，那么其应当是一个包含了元素`$definition['class']`的配置数组。如果该数组未定义`$definition['class']`那么，将传入的`$class`作为该元素的值，最后返回该数组；
* 上一步中，如果`definition['class']`未定义，而`$class`不是一个有效的类名，那么抛出异常；
* 如果`$definition`不属于上述的各种情况，也抛出异常。

总之，对于`$_definitions`数组中的元素，它要么是一个包含了”class”元素的数组，要么是一个 PHP callable，再要么就是一个具体对象。这就是规范化后的最终结果。

在调用`normalizeDefinition()`对依赖的定义进行规范化处理后，`set()`和`setSingleton()`以传入的`$class`为键，将定义保存进`$_definition[]`中，将传入的 `$param`保存进`$_params[]`中。

对于`set()`而言，还要删除`$_singleton[]`中的同名依赖。对于`setSingleton()`而言，则要将`$_singleton[]`中的同名依赖设为`null`，表示定义了一个 Singleton，但是并未实例化。

这么讲可能不好理解，举几个具体的依赖定义及相应数组的内容变化为例，以加深理解：

```php
$container = new \yii\di\Container;

// 直接以类名注册一个依赖，虽然这么做没什么意义。
// $_definition['yii\db\Connection'] = 'yii\db\Connetcion'
$container->set('yii\db\Connection');

// 注册一个接口，当一个类依赖于该接口时，定义中的类会自动被实例化，并供
// 有依赖需要的类使用。
// $_definition['yii\mail\MailInterface', 'yii\swiftmailer\Mailer']
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// 注册一个别名，当调用$container->get('foo')时，可以得到一个
// yii\db\Connection 实例。
// $_definition['foo', 'yii\db\Connection']
$container->set('foo', 'yii\db\Connection');

// 用一个配置数组来注册一个类，需要这个类的实例时，这个配置数组会发生作用。
// $_definition['yii\db\Connection'] = [...]
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// 用一个配置数组来注册一个别名，由于别名的类型不详，因此配置数组中需要
// 有 class 元素。
// $_definition['db'] = [...]
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// 用一个PHP callable来注册一个别名，每次引用这个别名时，这个callable都会被调用。
// $_definition['db'] = function(...){...}
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// 用一个对象来注册一个别名，每次引用这个别名时，这个对象都会被引用。
// $_definition['pageCache'] = anInstanceOfFileCache
$container->set('pageCache', new FileCache);
```

`setSingleton()`对于`$_definition`和`$_params`数组产生的影响与`set()`是一样一样的。不同之处在于，使用`set()`会`unset $_singltons`中的对应元素，Yii 认为既然你都调用`set()`了，说明你希望这个依赖不再是单例了。而`setSingleton()`相比较于`set()`，会额外地将`$_singletons[$class]`置为`null`。以此来表示这个依赖已经定义了一个单例，但是尚未实例化。

从`set()`和`setSingleton()`来看，可能还不容易理解 DI 容器，比如我们说 DI 容器中维护了 5 个数组，但是依赖注册过程只涉及到其中 3 个。剩下的`$_reflections`和`$_dependencies`是在解析依赖的过程中完成构建的。

从 DI 容器的 5 个数组来看也好，从容器定义了`set()`和`setSingleton()`两个定义依赖的方法来看也好，不难猜出 DI 容器中装了两类实例，一种是单例，每次向容器索取单例类型的实例时，得到的都是同一个实例；另一类是普通实例，每次向容器索要普通类型的实例时，容器会根据依赖信息创建一个新的实例给你。

单例类型主要用于节省构建实例的时间、节省保存实例的内存、共享数据等。而普通类型主要用于避免数据冲突。

### 对象的实例化
对象的实例化过程要比依赖的定义过程复杂得多。毕竟依赖的定义只是往特定的数据结构`$_singletons`、`$_definitions`和`$_params` 3 个数组写入有关的信息。稍复杂的东西也就是定义的规范化处理了。其它真没什么复杂的。像你这么聪明的，肯定觉得这太没挑战了。

而对象的实例化过程要相对复杂，这一过程会涉及到复杂依赖关系的解析、涉及依赖单元的实例化等过程。且让我们抽丝剥茧地进行分析。

#### 解析依赖信息
容器在获取实例之前，必须解析依赖信息。这一过程会涉及到 DI 容器中尚未提到的另外 2 个数组`$_reflections`和`$_dependencies`。`yii\di\Container::getDependencies()`会向这 2 个数组写入信息，而这个函数又会在创建实例时，由`yii\di\Container::build()`所调用。如它的名字所示意的，`yii\di\Container::getDependencies()`方法用于获取依赖信息，让我们先来看看这个函数的代码：

```php
protected function getDependencies($class)
{
    // 如果已经缓存了其依赖信息，直接返回缓存中的依赖信息
    if (isset($this->_reflections[$class])) {
        return [$this->_reflections[$class], $this->_dependencies[$class]];
    }

    $dependencies = [];

    // 使用PHP5 的反射机制来获取类的有关信息，主要就是为了获取依赖信息
    $reflection = new ReflectionClass($class);

    // 通过类的构建函数的参数来了解这个类依赖于哪些单元
    $constructor = $reflection->getConstructor();
    if ($constructor !== null) {
        foreach ($constructor->getParameters() as $param) {
            if ($param->isDefaultValueAvailable()) {

                // 构造函数如果有默认值，将默认值作为依赖。即然是默认值了，
                // 就肯定是简单类型了。
                $dependencies[] = $param->getDefaultValue();
            } else {
                $c = $param->getClass();

                // 构造函数没有默认值，则为其创建一个引用。
                // 就是前面提到的 Instance 类型。
                $dependencies[] = Instance::of($c === null ? null :
                    $c->getName());
            }
        }
    }

    // 将 ReflectionClass 对象缓存起来
    $this->_reflections[$class] = $reflection;

    // 将依赖信息缓存起来
    $this->_dependencies[$class] = $dependencies;

    return [$reflection, $dependencies];
}
```

前面讲了`$_reflections`数组用于缓存 ReflectionClass 实例，`$_dependencies`数组用于缓存依赖信息。这个`yii\di\Container::getDependencies()`方法实质上就是通过 PHP5 的反射机制，通过类的构造函数的参数分析他所依赖的单元，然后统统缓存起来备用。

为什么是通过构造函数来分析其依赖的单元呢？因为这个 DI 容器设计出来的目的就是为了实例化对象及该对象所依赖的一切单元。也就是说，DI 容器必然构造类的实例，必然调用构造函数，那么必然为构造函数准备并传入相应的依赖单元。这也是我们开头讲到的构造函数依赖注入的后续延伸应用。

可能有的读者会问，那不是还有 setter 注入么，为什么不用解析 setter 注入函数的依赖呢？这是因为要获取实例不一定需要为某属性注入外部依赖单元，但是却必须为其构造函数的参数准备依赖的外部单元。当然，有时候一个用于注入的属性必须在实例化时指定依赖单元这个时候，必然在其构造函数中有一个用于接收外部依赖单元的形式参数。使用 DI 容器的目的是自动实例化，只是实例化而已，就意味着只需要调用构造函数。至于 setter 注入可以在实例化后操作嘛。

另一个与解析依赖信息相关的方法就是`yii\di\Container::resolveDependencies()`。它也是关乎`$_reflections`和`$_dependencies`数组的，它使用`yii\di\Container::getDependencies()`在这两个数组中写入的缓存信息，作进一步具体化的处理。从函数名来看，他的名字表明是用于解析依赖信息的。下面我们来看看它的代码：

```php
protected function resolveDependencies($dependencies, $reflection = null)
{
    foreach ($dependencies as $index => $dependency) {

        // 前面getDependencies() 函数往 $_dependencies[] 中
        // 写入的是一个 Instance 数组
        if ($dependency instanceof Instance) {
            if ($dependency->id !== null) {

                // 向容器索要所依赖的实例，递归调用 yii\di\Container::get()
                $dependencies[$index] = $this->get($dependency->id);
            } elseif ($reflection !== null) {
                $name = $reflection->getConstructor()
                    ->getParameters()[$index]->getName();
                $class = $reflection->getName();
                throw new InvalidConfigException(
                "Missing required parameter \"$name\" when instantiating \"$class\".");
            }
        }
    }
    return $dependencies;
}
```

上面的代码中可以看到，`yii\di\Container::resolveDependencies()`作用在于处理依赖信息，将依赖信息中保存的 Istance 实例所引用的类或接口进行实例化。

综合上面提到的`yii\di\Container::getDependencies()`和`yii\di\Container::resolveDependencies()`两个方法，我们可以了解到：

* `$_reflections`以类（接口、别名）名为键，缓存了这个类（接口、别名）的ReflcetionClass。一经缓存，便不会再更改。
* `$_dependencies`以类（接口、别名）名为键，缓存了这个类（接口、别名）的依赖信息。
* 这两个缓存数组都是在`yii\di\Container::getDependencies()`中完成。这个函数只是简单地向数组写入数据。
* 经过`yii\di\Container::resolveDependencies()`处理，DI 容器会将依赖信息转换成实例。这个实例化的过程中，是向容器索要实例。也就是说，有可能会引起递归。

#### 实例的创建
解析完依赖信息，就万事俱备了，那么东风也该来了。实例的创建，秘密就在`yii\di\Container::build()`函数中：

```php
protected function build($class, $params, $config)
{
    // 调用上面提到的getDependencies来获取并缓存依赖信息，留意这里 list 的用法
    list ($reflection, $dependencies) = $this->getDependencies($class);

    // 用传入的 $params 的内容补充、覆盖到依赖信息中
    foreach ($params as $index => $param) {
        $dependencies[$index] = $param;
    }

    // 这个语句是两个条件：
    // 一是依赖信息不为空，也就是要么已经注册过依赖，要么为 build() 传入构造函数参数。
    // 二是要创建的类是一个 yii\base\Object 类，
    // 留意我们在《Yii基础》一篇中讲到，这个类对于构造函数的参数是有一定要求的。
    if (!empty($dependencies) && is_a($class, 'yii\base\Object', true)) {
        // 按照 Object 类的要求，构造函数的最后一个参数为 $config 数组
        $dependencies[count($dependencies) - 1] = $config;

        // 解析依赖信息，如果有依赖单元需要提前实例化，会在这一步完成
        $dependencies = $this->resolveDependencies($dependencies, $reflection);

        // 实例化这个对象
        return $reflection->newInstanceArgs($dependencies);
    } else {
        // 会出现异常的情况有二：
        // 一是依赖信息为空，也就是你前面又没注册过，
        // 现在又不提供构造函数参数，你让Yii怎么实例化？
        // 二是要构造的类，根本就不是 Object 类。
        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        $object = $reflection->newInstanceArgs($dependencies);
        foreach ($config as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }
}
```

从这个`yii\di\Container::build()`来看：

* DI 容器只支持`yii\base\Object`类。也就是说，你只能向 DI 容器索要`yii\base\Object`及其子类。再换句话说，如果你想你的类可以放在 DI 容器里，那么必须继承自`yii\base\Object`类。但 Yii 中几乎开发者在开发过程中需要用到的类，都是继承自这个类。一个例外就是上面提到的`yii\di\Instance`类。但这个类是供 Yii 框架自己使用的，开发者无需操作这个类。
* 递归获取依赖单元的依赖在于`dependencies = $this->resolveDependencies($dependencies, $reflection)`中。
* `getDependencies()`和`resolveDependencies()`为`build()`所用。也就是说，只有在创建实例的过程中，DI 容器才会去解析依赖信息、缓存依赖信息。

#### 容器内容实例化的大致过程
与注册依赖时使用`set()`和`setSingleton()`对应，获取依赖实例化对象使用`yii\di\Container::get()`，其代码如下：

```php
public function get($class, $params = [], $config = [])
{
    // 已经有一个完成实例化的单例，直接引用这个单例
    if (isset($this->_singletons[$class])) {
        return $this->_singletons[$class];

    // 是个尚未注册过的依赖，说明它不依赖其他单元，或者依赖信息不用定义，
    // 则根据传入的参数创建一个实例
    } elseif (!isset($this->_definitions[$class])) {
        return $this->build($class, $params, $config);
    }

    // 注意这里创建了 $_definitions[$class] 数组的副本
    $definition = $this->_definitions[$class];

    // 依赖的定义是个 PHP callable，调用之
    if (is_callable($definition, true)) {
        $params = $this->resolveDependencies($this->mergeParams($class,
            $params));
        $object = call_user_func($definition, $this, $params, $config);

    // 依赖的定义是个数组，合并相关的配置和参数，创建之
    } elseif (is_array($definition)) {
        $concrete = $definition['class'];
        unset($definition['class']);

        // 合并将依赖定义中配置数组和参数数组与传入的配置数组和参数数组合并
        $config = array_merge($definition, $config);
        $params = $this->mergeParams($class, $params);

        if ($concrete === $class) {
            // 这是递归终止的重要条件
            $object = $this->build($class, $params, $config);
        } else {
            // 这里实现了递归解析
            $object = $this->get($concrete, $params, $config);
        }

    // 依赖的定义是个对象则应当保存为单例
    } elseif (is_object($definition)) {
        return $this->_singletons[$class] = $definition;
    } else {
        throw new InvalidConfigException(
            "Unexpected object definition type: " . gettype($definition));
    }

    // 依赖的定义已经定义为单例的，应当实例化该对象
    if (array_key_exists($class, $this->_singletons)) {
        $this->_singletons[$class] = $object;
    }

    return $object;
}
```

`get()`用于返回一个对象或一个别名所代表的对象。可以是已经注册好依赖的，也可以是没有注册过依赖的。无论是哪种情况，Yii 均会自动解析将要获取的对象对外部的依赖。

`get()`接受3个参数：

* `$class`表示将要创建或者获取的对象。可以是一个类名、接口名、别名。
* 	`$params`是一个用于这个要创建的对象的构造函数的参数，其参数顺序要与构造函数的定义一致。 通常用于未定义的依赖。
* `$config`是一个配置数组，用于配置获取的对象。通常用于未定义的依赖，或覆盖原来依赖中定义好的配置。

`get()`解析依赖获取对象是一个自动递归的过程，也就是说，当要获取的对象依赖于其他对象时， Yii 会自动获取这些对象及其所依赖的下层对象的实例。同时，即使对于未定义的依赖，DI 容器通过 PHP 的 Reflection API，也可以自动解析出当前对象的依赖来。

`get()`不直接实例化对象，也不直接解析依赖信息。而是通过`build()`来实例化对象和解析依赖。

`get()`会根据依赖定义，递归调用自身去获取依赖单元。因此，在整个实例化过程中，一共有两个地方会产生递归：一是`get()`，二是`build()`中的`resolveDependencies()`。

DI 容器解析依赖实例化对象过程大体上是这么一个流程：

* 以传入的`$class`看看容器中是否已经有实例化好的单例，如有，直接返回这一单例。
* 如果这个`$class`根本就未定义依赖，则调用`build()`创建之。具体创建过程等下再说。
* 对于已经定义了这个依赖，如果定义为 PHP callable，则解析依赖关系，并调用这个 PHP callable。具体依赖关系解析过程等下再说。
* 如果依赖的定义是一个数组，首先取得定义中对于这个依赖的 class 的定义。然后将定义中定义好的参数数组和配置数组与传入的参数数组和配置数组进行合并，并判断是否达到终止递归的条件。从而选择继续递归解析依赖单元，或者直接创建依赖单元。

从`get()`的代码可以看出：

* 对于已经实例化的单例，使用`get()`时只能返回已经实例化好的实例，`$params`参数和`$config`参数失去作用。这点要注意，Yii 不会提示你，所给出的参数不会发生作用的。有的时候发现明明已经给定配置数组了，怎么配置不起作用呀？就要考虑是不是因为这个原因了。
* 对于定义为数组的依赖，在合并配置数组和构造函数参数数组过程中，定义中定义好的两个数组会被传入的`$config`和`$params`的同名元素所覆盖，这就提供了获取不同实例的可能。
* 在定义依赖时，无论是使用`set()`还是使用`setSingleton()`只要依赖定义为特定对象或特定实例的，Yii 均将其视为单例。在获取时，也将返回这一单例。

### 实例分析
为了加深理解，我们以官方文档上的例子来说明 DI 容器解析依赖的过程。假设有以下代码：

```php
namespace app\models;

use yii\base\Object;
use yii\db\Connection;

// 定义接口
interface UserFinderInterface
{
    function findUser();
}

// 定义类，实现接口
class UserFinder extends Object implements UserFinderInterface
{
    public $db;

    // 从构造函数看，这个类依赖于 Connection
    public function __construct(Connection $db, $config = [])
    {
        $this->db = $db;
        parent::__construct($config);
    }

    public function findUser()
    {
    }
}

class UserLister extends Object
{
    public $finder;

    // 从构造函数看，这个类依赖于 UserFinderInterface接口
    public function __construct(UserFinderInterface $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }
}
```

从依赖关系看，这里的`UserLister`类依赖于接口`UserFinderInterface`，而接口有一个实现就是`UserFinder`类，但这类又依赖于`Connection`。

那么，按照一般常规的作法，要实例化一个`UserLister`通常这么做：

```php
$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```

就是逆着依赖关系，从最底层的`Connection`开始实例化，接着是`UserFinder`最后是`UserLister`。在写代码的时候，这个前后顺序是不能乱的。而且，需要用到的单元，你要自己一个一个提前准备好。对于自己写的可能还比较清楚，对于其他团队成员写的，你还要看他的类究竟是依赖了哪些，并一一实例化。这种情况，如果是个别的、少量的还可以接受，如果有个 10－20 个的，那就麻烦了。估计光实例化的代码，就可以写满一屏幕了。

而且，如果是团队开发，有些单元应当是共用的，如邮件投递服务。不能说你写个模块，要用到邮件服务了，就自己实例化一个邮件服务吧？那样岂不是有 N 模块就有 N 个邮件服务了？最好的方式是使邮件服务成为一个单例，这样任何模块在需要邮件服务时，使用的其实是同一个实例。用传统的这种实例化对象的方法来实现的话，就没那么直接了。

那么改成 DI 容器的话，应该是怎么样呢？他是这样的：

```php
use yii\di\Container;

// 创建一个DI容器
$container = new Container;

// 为Connection指定一个数组作为依赖，当需要Connection的实例时，
// 使用这个数组进行创建
$container->set('yii\db\Connection', [
    'dsn' => '...',
]);

// 在需要使用接口 UserFinderInterface 时，采用UserFinder类实现
$container->set('app\models\UserFinderInterface', [
    'class' => 'app\models\UserFinder',
]);

// 为UserLister定义一个别名
$container->set('userLister', 'app\models\UserLister');

// 获取这个UserList的实例
$lister = $container->get('userLister');
```

采用 DI 容器的办法，首先各`set()`语句没有前后关系的要求，`set()`只是写入特定的数据结构， 并未涉及具体依赖关系的解析。所以，前后关系不重要，先定义什么依赖，后定义什么依赖没有关系。

其次，上面根本没有在 DI 容器中定义`UserFinder`对于`Connection`的依赖。但是 DI 容器通过对`UserFinder`构造函数的分析，能了解到这个类会对`Connection`依赖。这个过程是自动的。

最后，上面只有一个`get()`看起来好像根本没有实例化其他如`Connection`单元一样，但事实上，DI 容器已经安排好了一切。在获取`userLister`之前，`Connection`和`UserFinder`都会被自动实例化。其中，`Connection`是根据依赖定义中的配置数组进行实例化的。

经过上面的几个`set()`语句之后，DI 容器的`$_params`数组是空的，`$_singletons`数组也是空的。`$_definintions`数组却有了新的内容：

```php
$_definitions = [
    'yii\db\Connection' => [
        'class' => 'yii\db\Connection',    // 注意这里
        'dsn' => ...
    ],
    'app\models\UserFinderInterface' => ['class' => 'app\models\UserFinder'],
    'userLister' => ['class' => 'app\models\UserLister']    // 注意这里
];
```

在调用`get('userLister')`过程中又发生了什么呢？说实话，这个过程不是十分复杂，但是由于涉及到递归和回溯，可能理解起来会比较费劲。请对照下面的 DI 容器解析依赖获取实例的过程示意图， 以及前面关于`get()`、`build()`、`getDependencies()`、`resolveDependencies()`等函数的源代码，了解大致流程。

![DI 容器解析依赖获取实例的过程示意图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1479631095464.png)

> 在”DI容器解析依赖获取实例的过程示意图“中绿色方框表示 DI 容器的 5 个数组；浅蓝色圆边方框表示调用的函数和方法；蓝色箭头表示读取内存；红色箭头表示写入内存；虚线箭头表示参照的内存对象；粗线绿色箭头表示回溯过程；图中 3 个圆柱体表示实例化过程中，创建出来的 3 个实例。


对于`get()`函数：

* 在第 1 步中调用`get('userLister')`表示要获得一个`userLister`实例。这个`userLister`不是一个有效的类名，说明这是一个别名。那么要获取的是这个别名所代表的类的实例。
* 查找`$_definitions`数组，发现`$_definitions['userLister'] = ['class'=>'app\models\UserLister']`。这里`userLister`不等于 `app\models\UserLister`，说明要获取的这个`userLister`实例依赖于`app\models\UserLister`。这是查找依赖定义数组的第一种情况。
* 而在第 22、23 步中，`get('yii\db\Connection')`调用`get()`时指定要获取的实例的类型，与依赖定义数组`$_definitions`定义的所依赖的类型是相同的，都是`yii\db\Connection`。也就是说，自己依赖于自己，这就基本达到了停止递归调用`get()`的条件，差不多可以开始反溯了。这是查找依赖定义数组的第二种情况。
* 第三种情况是第 3、4 步、第 13、14 步查找依赖定义数组，发现依赖不存在。说明所要获取的类型的依赖关系未在容器中注册。对于未注册依赖关系的，DI 容器认为要么是一个没有外部依赖的简单类型，要么是一个容器自身可以自动解析其依赖关系的类型。
* 对于第一种情况，要获取的类型依赖于其他类型的，递归调用`get()`获取所依赖的类型。
* 对于第二、三种情况，直接调用`build()`尝试获取该类型的实例。


`build()`在实例化过程中，干了这么几件事：

* 调用`getDependencies()`获取依赖信息。
* 调用`resolveDependencies()`解析依赖信息。
* 将定义中的配置数组、构造函数参数与调用`get()`时传入的配置数组和构造参数进行合并。这一步并未在上面的示意图中体现，请参阅`build()`的源代码部分。
* 根据解析回来的依赖单元，调用`newInstanceArgs()`创建实例。请留意第 36、42 步，并非直接由`resolveDependencies()`调用`newInstanceArgs()`，而是`resolveDependencies()`将依赖单元返回后，由`build()`来调用。就像第 31 步一样。
* 将获取的类型实例返回给调用它的`get()`。


`getDependencies()`函数总是被`build()`调用，他干了这么几件事：

* 创建 ReflectionClass，并写入`$_reflections`缓存数组。如第 6 步中，`$_reflections['app\models\UserLister'] = new ReflectionClass('app\models\UserLister')`。
* 利用 PHP 的 Reflection API，通过分析构造函数的形式参数，了解到当前类型对于其他单元、默认值的依赖。
* 将上一步了解到的依赖，在`$_dependencies`缓存数组中写入一个`Instance`实例。如第 7、8 步。
* 当一个类型的构造函数的参数列表中，没有默认值、参数都是简单类型时，得到一个`[null]`。 如第 28 步。


`resolveDependencies()`函数总是被`build()`调用，他在实例化时，干了这么几件事：

* 根据缓存在`$_dependencies`数组中的`Instance`实例的 id，递归调用容器的`get()`实例化依赖单元。并返回给`build()`接着运行。
* 对于像第 28 步之类的依赖信息为`[null]`的，则什么都不干。


`newInstanceArgs()`函数是 PHP Reflection API 的函数，用于创建实例，具体请看 [PHP手册](http://php.net/manual/zh/reflectionclass.newinstanceargs.php)。

这里只是简单的举例子而已，还没有涉及到多依赖和单例的情形，但是在原理上是一样的。

从上面的例子中不难发现，DI 容器维护了两个缓存数组`$_reflections`和`$_dependencies`。这两个数组只写入一次，就可以无限次使用。因此减少了对 ReflectionClass 的使用，提高了 DI 容器解析依赖和获取实例的效率。

另一方面，我们看到，获取一个实例，步骤其实不少。但是，对于典型的 Web 应用而言，有许多模块其实应当注册为单例的，比如上面的`yii\db\Connection`。一个 Web 应用一般使用一个数据库连接，特殊情况下会用多几个，所以这些数据库连接一般是给定不同别名加以区分后，分别以单例形式放在容器中的。因此，实际获取实例时，步骤会简单得。对于单例，在第一次`get()`时，直接就返回了。而且，省去不重复构造实例的过程。

这两个方面，都体现出 Yii 高效能的特点。

上面我们分析了 DI 容器，这只是其中的原理部分，具体的运用，我们将结合《服务定位器（Service Locator）》来讲。


