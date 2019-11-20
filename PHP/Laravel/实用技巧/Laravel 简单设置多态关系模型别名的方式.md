Laravel Eloquent 中的多态关联是一个较为复杂的关联模式，可以分为多态一对一关联、多态一对多关联、多态多对多关联。这几种多态之间的关联基础都是作为多态模型的表中的关联 ID 和关联类别数字段。

比如，文章和视频都可以有标签，那么文章/视频模型与标签模型的关系就是多态多对多关联。

> 下面将父类表(如文章和视频表)称为关联表，子类表(如标签表)称为多态表。

## 一、Laravel 官方方式

### 1.1 默认方式

如果为 ID 为 1 的文章打上两个标签，那么数据库中作为多态表的标签表中的存储结果就是下面的样式：

```
> select * from taggables;
+--------+-------------+---------------+
| tag_id | taggable_id | taggable_type |
+--------+-------------+---------------+
|      1 |           1 | App\Post      |
|      2 |           1 | App\Post      |
+--------+-------------+---------------+
```

这里最主要的关联 ID 是`taggable_id`，关联类别是`taggable_type`。

这种方式下，关联类别中默认存储的就是关联的模型的类名，有时候会过长或者不整洁。

### 1.2 修改模型类名

如果想让关联类别字段不存储默认的模型类名，就需要在 Laravel 中建立特定值与模型类名的映射关系，否则 Laravel 将无法找到正确的多态关联模型。

要实现这种功能，[官方建议](https://laravel.com/docs/6.x/eloquent-relationships#custom-polymorphic-types)是在服务提供者(如`AppServiceProvider`)中添加关联映射：

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'posts' => 'App\Post',
    'videos' => 'App\Video',
]);
```

这样就可以将标签多态表中的关联类别由模型名改成自定义的`posts`或`videos`字符串了。

虽然这样可以将模型名改成自定义的字符串，但是需要专门维护这个映射，而且如果模型较多，多态关联较多也可能造成自定义的关联类别冲突。

## 二、自定义方式

### 2.1 关联模型设置

当为关联表添加`morphMany/morphOne`多态关联时，生成的多态关联类中会通过调用关联模型的`getMorphClass()`方法来获取多态关联的类别：

```php
namespace Illuminate\Database\Eloquent\Relations;

abstract class MorphOneOrMany extends HasOneOrMany
{
    public function __construct(Builder $query, Model $parent, $type, $id, $localKey)
    {
        $this->morphType = $type;
    
        // 获取多态关联的类名，也就是存储在多态表中的关联类别字段的值
        $this->morphClass = $parent->getMorphClass();
    
        parent::__construct($query, $parent, $id, $localKey);
    }
}
```

而模型中的`getMorphClass()`方法默认的实现如下：

```php
public function getMorphClass()
{
    $morphMap = Relation::morphMap();

    if (! empty($morphMap) && in_array(static::class, $morphMap)) {
        return array_search(static::class, $morphMap, true);
    }

    return static::class;
}
```

可以看到，其首先是从`Relation::morphMap()`(也就是官方推荐的多态关联映射)中获取对应的映射值，如果没有的话，再使用当前模型的类名。这解释了前面 Laravel 默认的两种多态关联类别的生成方式。

同样，如果修改了模型中的这个方法，将其返回值改成自定义的值，那么就不需要维护多态关联映射关系了。

比如，下面创建了一个利用表名作为关联类别的 trait：

```php
<?php

namespace App\Traits;

trait UseTableNameAsMorphClass
{
    public function getMorphClass ()
    {
        return $this->getTable();
    }
}
```

然后在关联模型中引入这个 trait 就可以了。

### 2.2 多态模型设置

上面仅在关联模型中组了自定义设置，而没有做多态关联映射，那么如果想从多态模型中获取关联的父模型就无法实现了。

多态模型中的设置的`morphTo()`方法的逻辑如下：

```php
namespace Illuminate\Database\Eloquent\Concerns;

trait HasRelationships
{
    public function morphTo($name = null, $type = null, $id = null, $ownerKey = null)
    {
        $name = $name ?: $this->guessBelongsToRelation();
    
        [$type, $id] = $this->getMorphs(Str::snake($name), $type, $id);
    
        return empty($class = $this->{$type})
                    ? $this->morphEagerTo($name, $type, $id, $ownerKey)
                    : $this->morphInstanceTo($class, $name, $type, $id, $ownerKey);
    }
}
```

可以看到，多态模型的关联模型是通过关联类别字段的值来决定如何生成的，关键就在于代码中的`$class = $this->{$type}`。

对于一个 Eloquent 模型来说，`$this->{$type}`就是获取该模型中的一个属性，最终会通过`__call()`方法来调用`getAttribute()`来得到值。所以可以考虑在模型中设置关联类别的获取器来实现自动关联父模型。

如下，在将表名作为关联类别的多态关联类别时，下面的 trait 可以在多态模型中自动转换成对应的模型名(设定模型的命名空间为`App`)：

```php
namespace App\Traits;

use Illuminate\Support\Str;

trait UseTableNameAsRelatedInstanceName
{
    public function getMorphableTypeAttribute ($value)
    {
        return 'App\\'.Str::snake(Str::singular($value));
    }
}
```

> 这里假设多态关联类别字段的名称是`morphable_type`，在做多态关联的时候也需要注意。

然后就可以在多态模型中做如下的关联了：

```php
public function morphable ()
{
    // return $this->morphTo('morphable', 'morphable_type', 'morphable_id', 'id');
    return $this->morphTo();
}
```

