### 定时器

`swoole_timer_tick`重复执行指定方法，`swoole_timer_after`延迟指定时间后执行回调。这两者分别类似于 JavaScript 中的`setInterval`方法和`setTimeout`方法。

这两个方法设置的定时器都可以使用`swoole_timer_clear`方法进行清除。

```php
// 重复执行定时任务
$tick_timer_id = swoole_timer_tick(1000, function ($timer_id) {
  echo 'same as setInterval.And this timer id :'.$timer_id.PHP_EOL;
});

var_dump($tick_timer_id);

// 一次性延时定时任务
swoole_timer_after(2000, function () {
   echo 'same as setTimeout'.PHP_EOL;
});

// 清除定时任务
swoole_timer_clear($tick_timer_id);
```



