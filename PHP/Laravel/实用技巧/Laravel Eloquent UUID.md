> 转摘：
> * [Easily use UUIDs in Laravel](https://dev.to/wilburpowery/easily-use-uuids-in-laravel-45be)
> * [Laravel 5.6 使用 UUID](https://www.ruoxiaozh.com/blog/article/67)

Laravel 5.6 自带了 UUID 的 package 支持，可以方便的在 Migration 和 Eloquent Model 中使用。

### 1. Migration

在 Model 对应的数据表设计的时候，需要通过类似下面的代码生成 UUID 支持：

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->uuid('uuid')->primary();
    // ...
});
```

这里主要是修改了`uuid`字段的定义，不在使用默认的`increment()`，而是使用`uuid()`方法，在指定完 uuid() 之后同时加上`primary()`设定为主键。

### 2. Eloquent Model

为了在 Eloquent 中使用 UUID，首先需设置 Model 的主键不可自增，然后在新增数据的时候，需要自动将主键设置为 UUID 值。为了方便使用，可以将这些修改做成一个 trait 以便在各个 Model 中使用：

```php
namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait UsesUuidAsPrimaryKey
{
    /**
     * Boot all of traits on the model.
     *
     * @return void
     */
    protected static function bootUsesUuidAsPrimaryKey ()
    {
        static::creating(function (Model $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    
    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'uuid';
    }
    
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
}
```

> 由于 Trait 中定义的属性不能和其他类中定义的类的可见性、初始化值不同，所以必须要通过定义方法的方式来改变值，否则会硬错误。

然后就可以在 Model 中使用：

```php
class Task extends Model
{
    use App\Models\Concerns\UsesUuidAsPrimaryKey;
}
```

这样就在 Task 模型中使用 uuid 作为主键了。


