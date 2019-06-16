Laravel Database 是与 PHP 底层的 PDO 直接进行交互的工具，通过查询构建器提供了一个方便的接口来创建及运行数据库查询语句。

> Eloquent 是建立在 Database 的基础之上，对数据库进行了抽象的 ORM，功能十分丰富，让用户可以避免写复杂的 SQL 语句，并用优雅的方式解决了数据表之间的关联关系。

> Database 和 Eloquent 两部分都包括在了`Illuminate/Database`包里面，除了作为 Laravel 的数据库层，其还是一个 PHP 数据库工具集，在任何项目里都可以通过`composer install illuminate/databse`安装并使用它。

## 一、初始化

### 1.1 服务注册

Laravel Database 是作为一种服务注册到服务容器里提供使用的，它的服务提供器是`Illuminate\Database\DatabaseServiceProvider`。

首先是注册服务：

```PHP
public function register()
{
    // 在 Eloquent 服务启动之前为了保险起见需要清理掉已经 booted 的 Model 和全局查询作用域
    Model::clearBootedModels();

    // 注册数据库连接服务和 db 单例
    $this->registerConnectionServices();

    // 注册 Eloquent 工厂
    $this->registerEloquentFactory();

    $this->registerQueueableEntityResolver();
}
```

任何是启动数据库服务：

```php
public function boot()
{
    Model::setConnectionResolver($this->app['db']);

    Model::setEventDispatcher($this->app['events']);
}
```

数据库服务的启动主要设置 Eloquent Model 的连接分析器(connection resolver)，让 Model 能够用 db 服务连接数据库。还有就是设置数据库事件的分发器 dispatcher，用于监听数据库的事件。

### 1.2 服务详情

为了建立数据库链接，并进行 PDO 操作，`illuminate/database`将逻辑分成了多个层次，分别负责不同的功能，如下：

* `DB` DatabaseManager 的静态代理。
* `DatabaseManager` Database 面向外部的接口，应用中所有与 Database 有关的操作都是通过与这个接口交互来完成的。
* `ConnectionFactory` 创建数据库连接对象的类工厂。
* `Connection` 数据库连接对象，执行数据库操作最后都是通过它与 PHP 底层的 PDO 交互来完成的。
* `Connector` 作为 Connection 的成员专门负责通过 PDO 连接数据库。

DatabaseManager 是整个数据库服务的接口，通过 DB 门面进行操作的时候实际上调用的就是 DatabaseManager，它会通过数据库连接对象工厂(ConnectionFacotry)获得数据库连接对象(Connection)，然后数据库连接对象会进行具体的 CRUD 操作。

### 1.3 查询构造器

是由`Connection`对象进行 SQL 语句的组装和结果的格式化。

在通过 DB 进行 SQL 查询的时候(如`DB::table('users')->get()`)，是由执行`table`方法返回了一个`QueryBuilder`对象，然后由该对象进行 SQL 语句的格式化和结果的格式化。开发者在开发时使用`QueryBuilder`就不需要写一行 SQL 语句就能操作数据库了，使得书写的代码更加的面向对象，更加的优雅。

`QueryBuilder`类文件`\Illuminate\Database\Query\Builder.php`中，构造函数如下：

```php
public function __construct(ConnectionInterface $connection,
                            Grammar $grammar = null,
                            Processor $processor = null)
{
   $this->connection = $connection;
   $this->grammar = $grammar ?: $connection->getQueryGrammar();
   $this->processor = $processor ?: $connection->getPostProcessor();
}
```

这里注入了数据库连接类、SQL 语法分析类和结果处理器类实例。其中：

* `Illuminate\Database\Query\Grammars\Grammar` 用于对 SQL 语句进行格式化、值绑定等操作。
* `Illuminate\Database\Query\Processors\Processor` 对 SQL 查询的结果进行处理。

在`QueryBuilder`中，在每次调用`where()`等方法的时候，会先对各个条件进行处理，然后在获取最终结果的时候，使用`Grammar`进行最终的 SQL 语句的组装和值的绑定，在获取到 SQL 结果之后，会通过`Processor`将结果进行处理。

