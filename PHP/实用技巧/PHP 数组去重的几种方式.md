### 1. array_unique()

PHP 中提供了一个`array_unique()`方法可以用来移除一个数组的重复值，返回一个拥有唯一值的数组。

```php
$arr = [1, 2, 3, 2, 1, 'four', 'five', 'five'];

array_unique($arr);  // [1, 2, 3, 'four', 'five'];
```

但是，如果尝试用`array_unique()`函数来过滤一个大的数组里的重复值，会运行的较慢。

### 2. array_flip()

`array_flip()`函数用来将数组的键和值互换，而 PHP 中数组的键是唯一的，利用这个特性，可以通过`array_flip()`函数来实现过滤数组中的重复值。

```php
$arr = [1, 2, 3, 2, 1, 'four', 'five', 'five'];
$arr = array_flip($arr);

array_flip($arr); // [4 => 1, 3 => 2, 2 => 3, 5 => 'four', 7 => 'five']

array_keys($arr); // [1, 2, 3, 'four', 'five']
```

通过两次键值翻转，或者一次键值翻转和一次取数组键，即可实现对原数组的去重处理。


