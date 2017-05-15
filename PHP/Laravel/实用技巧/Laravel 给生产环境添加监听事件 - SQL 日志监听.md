### 1. 创建监听器

首先需要创建一个事件监听器，用于监听 SQL 的查询事件：

```shell
php artisan make:listener QueryListener --event=Illuminate\\Database\\Events\\QueryExecuted
```

执行该命令之后会自动生成文件`app/Listeners/QueryListener.php`。

### 2. 注册事件

打开`app/Providers/EventServiceProvider.php`，在`$listen`中添加`Illuminate\Database\Events\QueryExecuted`事件的监听器为`QueryListener`：

```php
protected $listen = [  
    'Illuminate\Database\Events\QueryExecuted' => [
        'App\Listeners\QueryListener',
    ],
];
```

### 3. 添加事件处理逻辑

光有一个空的监听器是不够的，我们需要自己实现如何把`$sql`记录到日志中。为此，对`QueryListener`进行改造，完善其`handle`方法。

打开`app/Listeners/QueryListener.php`，改写其中的`handle`方法：

```php
namespace App\Listeners;
use Log;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
class QueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $sql = str_replace("?", "'%s'", $event->sql);
        $log = vsprintf($sql, $event->bindings);
        Log::info($log);
    }
}
```


