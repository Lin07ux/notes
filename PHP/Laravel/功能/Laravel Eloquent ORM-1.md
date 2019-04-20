Laravel 中使用 Eloquent ORM 提供了一个美观、简单的与数据库打交道的 ActiveRecord 实现，每张数据表都对应一个与该表进行交互的“模型”(model)，模型允许你在表中进行数据查询，以及插入、更新、删除等操作。

> ActiveRecord 是什么：
> 1. 每一个数据库表对应创建一个类，类的每一个对象实例对应于数据库中表的一行记录; 通常表的每个字段在类中都有相应的 Field。
> 2. ActiveRecord 同时负责把自己持久化。在 ActiveRecord 中封装了对数据库的访问，即 CRUD。
> 3. ActiveRecord 是一种领域模型。
> 
> 更多解释，可以查看 [Yii 2.0 Active Record](http://www.yiichina.com/doc/guide/2.0/db-active-record)


## 一、Eloquent 模型约定

Laravel Eloquent 模型中，有一些基本的约定，来保证程序的正常使用：

- 默认情况下，通过命令行创建的 Eloquent 模型的文件都位于`app/`目录下，但也可以将其放在其他可以被`composer.json`文件自动加载的地方；
- 所有 Eloquent 模型都继承自`Illuminate\Database\Eloquent\Model`类；
- 命名空间均为`App\`；
- 默认情况下，Eloquent 模型和数据表之间有更多的一些约定，不过，也能够通过设置特定的模型类中的属性来改变这些约定值。

> 关于为什么 Laravel 5 不再设置一个 Models 文件夹的说明，暂未明确搞清楚，大致就是因为这会导致误解。

### 1.1 表名约定

默认情况下，模型类名的复数作为与其对应的数据表名，除非在模型中定义`table`属性来指定自定义的表名。如下，会为`Flight`模型指定使用`my_flights`表：

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'my_flights';
}
```

### 1.2 主键约定

Eloquent 默认每张表的主键名为`id`，你可以在模型类中定义一个`$primaryKey`属性来覆盖该约定。

默认情况下，主键是自增的整数，如果不需要自增，比如使用 UUID 作为主键，则可以更改如下的两个属性值：

* `$incrementing`
* `$keyType`

### 1.3 数据库连接
 
默认情况下，所有的 Eloquent 模型使用应用配置中的默认数据库连接，如果你想要为模型指定不同的连接，可以通过`$connection`属性来设置：

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'connection-name';
}
```


## 二、创建 Eloquent 模型

创建模型实例最简单的办法就是使用 Artisan 命令`make:model`：

```shell
php artisan make:model <model_name>
# model_name 一般首字母大写，如下创建一个 User 的模型
php artisan make:model User
```

当然，也可以自行创建相应的 Eloquent 模型文件。

由于默认请下，Eloquent 模型会创建在`app/`目录下的`App\`命名空间下，如果想要更改目录，可以指定一个完整的`model_name`路径(相对于`app/`目录下)。比如，可以使用下面的方式，在`app/Models/`目录下，建立一个命名空间为`App\Models\Article`的 Eloquent 模型：

```shell
php artisan make:model Models/Article
```

如果想要在生成模型时生成数据库迁移，可以使用`--migration`或`-m`选项：

```shell
php artisan make:model <model_name> --magration
# 或
php artisan make:model <model_name> -m
```

## 三、使用模型操作数据

模型可以直接使用静态方法来进行操作，比如，在 Controller 中可以通过如下的方式来引用`Article`模型，并返回数据：

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Article;

class ArticlesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = Article::all();

        return view('articles.index')->with('articles', $articles);
    }
}
```

常用方法：

* `all()` 获取模型中的所有数据
* `find($id)` 获取模型中指定 ID 值的数据
* `findOrFail($id)` 获取模型中指定 ID 值的数据，找不到记录的时候，会自动返回一个 404 响应。
* `firstOrCreate()` 根据条件查找第一个符合的模型，找不到就使用这些数据新建一个模型
* `fresh()` 重新从数据库加载模型数据并返回一个新的模型对象(比如模型可能被其他请求修改过)
* `refresh()` 从数据库重新加载数据并更新当前模型(不会返回新的模型对象)
* `replicate()` 复制一个模型到一个新的对象中(类似 PHP 中的 Clone)
* `getOriginal([$attributes])` 获取模型或模型属性的原始值
* `isDirty([$attributes])` 确定模型或给定属性是否已被修改
* `getChanges()` 获取更改过的属性(仅当使用`syncChanges()`保存模型或同步更新时，才生效)
* `push()` 一次性保存模型及其关联的更改
* `is()` 确定两个模型是否拥有相同主键并且属于同一张表
* `increment()` 增加属性值并保存
* `decrement()` 减少属性值并保存

### 3.1 访问器(自动完成字段)

在模型的定义中，可以添加相应的方法来对每个字段做一定的预处理，比如可以将用户输入的时间格式化为统一的样式等。这就需要用到另一个方法命名规范。

在模型中定义一个公共方法，方法名是`set + ColumnName + Attribute`三部分组成，其中字段名`ColumnName`是需要用大驼峰格式写，即将原本的字段名中的`_`下划线去掉，下划线后面的字母大写。

比如，在 Article 模型中，可以定义如下的一个方法，表示对`published_at`字段做格式的统一处理：

```php
<?php

class Article extends Model
{
    public function setPublishedAtAttribute($date)
    {
        // 这里的参数 $date 就是该字段被设置的原始值
        $this->attributes['published_at'] = date('Y-m-d H:i:s', strtotime($date));
    }
}
```

### 3.2 修改器

### 3.3 自定义 scope

如果需要重复使用某一条件来查询数据，可以在模型中定义一个 queryScope 方法，之后就可以直接调用这个方法来完成符合一定条件的查询。

queryScope 方法的命名规范是：`scope + Nmae`。其中`scope`是必须的关键字，`Name`就是引用这个 queryScope 方法的名称，在定义中需要首字母大写，但是使用的时候，则是首字母小写。

比如，在 Article 模型中，创建`scopePublished`方法：

```php
class Article extends Model
{
    public function scopePublished($query)
    {
        $query->where('published_at', '<=', date('Y-m-d H:i:s'));
    }
}
```

这样就能在其他地方使用这个 queryScope 方法，就和使用其他的系统自带的 queryScope 方法一样，比如，下面就是获取按时间排序的，符合`scopePublished`方法中定义的条件的数据：

```php
$articles = Article::lasted()->published()->get();
```


