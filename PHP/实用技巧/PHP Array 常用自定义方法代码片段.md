一些常用的数组方法代码片段。来源于：[Array - 30 seconds of PHP](https://php.30secondsofcode.org/tag/array)。

### 1. all/every

数组中的每一项经过自定义回调方法之后都返回 true 那么这个方法最终返回 true；否则返回 false。

> 如果使用循环的话，可以更快速的结束：只要遍历到某一项经过回调函数处理之后返回 false，那么就可以结束循环并返回 false，否则返回 true。

```php
function every (array $items, callable $func)
{
    return count(array_filter($items, $func)) === count($items);
}
```

### 2. any/some

数组中的至少有一项经过自定义的回调方法之后返回 true，那么这个方法返回 true；否则返回 false。

> 如果使用循环的话，可以更快的结束：只要遍历到某一项经过会掉函数处理之后返回 true，那么就可以结束循环并返回 true；否则返回 false。

```php
function some (array $items, callable $func)
{
    return count(array_filter($items, $func)) > 0;
}
```

### 3. flatten

将嵌套的多维数组展开成一维的状态，实现数组扁平化。同时可以设置第二个参数表示展开的深度。

```php
function deepFlatten (array $items, int $depth = 512)
{
    if ($depth <= 1) {
        return $items;
    }
    
    $result = [];
    
    foreach ($items as $item) {
        if (is_array($item)) {
            array_push($result, ...deepFlatten($item, $depth - 1));
        } else {
            $result[] = $item;
        }
    }
    
    return $result;
}
```

> 这里合并展开的数据的时候，用到了 PHP 7 中的数组延展操作符。

例如：

```php
$arr = [1, [2], [[3], 4], 5];

deepFlatten($arr, 1); // [1, [2], [[3], 4], 5]
deepFlatten($arr, 2); // [1, 2, [3], 4, 5]
deepFlatten($arr, 3); // [1, 2, 3, 4, 5]
deepFlatten($arr);    // [1, 2, 3, 4, 5]
```

### 4. drop

从数组的左侧(前面)删除`$n`个元素，然后返回删除后的数组。

```php
function drop (array $items, $n = 1)
{
    return array_slice($items, $n);
}
```

### 5. findLast

从数组的尾部开始搜索，并返回第一个符合条件的元素。

首先使用`array_filter()`从数组中过滤出符合条件的全部元素，然后使用`array_pop()`方法返回最后一个符合条件的元素。

> 如果使用循环，可以先获取数组的长度，然后逆序遍历数组，在遇到第一个符合条件的元素时，立即返回，可以比下面的实现更快速一些。

```php
function findLast (array $items, callable $func)
{
    $filteredItems = array_filter($items, $func);
    
    return array_pop($filteredItems);
}
```

例如：

```php
findLast([1, 2, 3, 4], function ($n) {
    return ($n % 2) === 1;
});
// 3
```

### 6. findLastIndex

返回数组中最后一个符合条件的元素的索引。

实现方式和前面的`findLast()`方法类似，也是先用`array_filter()`方法来过滤出符合条件的元素。由于`array_filter()`方法过滤出元素时，还会保留其原本的索引，所以后续的处理也很简单了。

> 同样的，如果先获取数组的长度之和，逆序遍历找到第一个符合条件的元素的索引和会更快。

```php
function findLastIndex (array $items, callable $func)
{
    $keys = array_keys(array_filter($items, $func));
    
    return array_pop($keys);
}
```

例如：

```php
findLastIndex([1, 2, 3, 4], function ($n) {
    return ($n % 2) === 1;
});
// 2
```

### 7. groupBy

使用给定的方法或者键名来对数组中的元素进行分组，分组后的键是方法的返回值或者键对应的值，而分组后的值则是一个数组，包含全部的类似的元素。

> 第二个参数可以是一个 callable 对象或者方法名，也可以是一个键名。

```php
function groupBy (array $items, $func)
{
    $group = [];
    
    foreach ($items as $item) {
        if ((! is_string($func) && is_callable($func)) || function_exista($func)) {
            $key = call_user_func($func, $item);
            $group[$key][] = $item;
        } elseif (is_object($item)) {
            $group[$item->{$func}][] = $item;
        } elseif (isset($item[$func])) {
            $group[$item[$func]][] = $item;
        }
    }
    
    return $group;
}
```

例如：

```php
groupBy(['one', 'two', 'three'], 'strlen'); // [3 => ['one', 'two'], 5 => ['three']]
```

### 8. hasDuplicates

检查数组中是否存在重复的元素。

```php
function hasDuplicates (array $items)
{
    return count($items) > count(array_unique($items));
}
```

### 9. orderBy

根据指定的键对集合元素进行排序，可以指定升序或者降序。

> 集合中的元素需要是数组或者对象。

```php
function orderBy (array $items, string $attr, string $order = 'desc')
{
    $sortedItems = [];
    
    foreach ($items as $item) {
        $key = is_object($item) ? $item->{$attr} : $item[$attr];
        
        $sortedItems[$key] = $item;
    }
    
    if ($order === 'desc') {
        krsrot($sortedItems);
    } else {
        ksrot($sortedItems);
    }
    
    return array_values($sortedItems);
}
```

例如：

```php
orderBy(
    [
        ['id' => 2, 'name' => 'Joy'],
        ['id' => 3, 'name' => 'Khaja'],
        ['id' => 1, 'name' => 'Raja'],
    ],
    'id',
    'desc'
);
// [['id' => 1, 'name' => 'Raja'], ['id' => 2, 'name' => 'Joy'], ['id' => 3, 'name' => 'Khaja']]
```

### 10. pluck

从多维数组中获取每一项中的指定键的值。

实现方式用使用了`array_map()`方法和自定义的匿名方法。

```php
function pluck (array $items, string $key)
{
    return array_map(function ($item) use ($key) {
        return is_object($item) ? $item->{$key} : $item[$key];
    }, items);
}
```

例如：

```php
pluck([
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
], 'name');
// ['Desk', 'Chair']
```

### 11. pull

从数组中去除指定的值，然后作为结果返回。

```php
function pull (array $items, ...$params)
{
    return array_values(array_diff($items, $params));
}
```

例如：

```php
pull(['a', 'b', 'c', 'a', 'c', 'd'], 'a', 'c'); // ['b', 'd']
```

### 12. reject

从集合中删除符合条件的元素。

```php
function remove (array $items, $func)
{
    return array_diff_key($items, array_filter($items, $func));
}
```

> 如果只需要值，不需要键，则可以在返回之前调用一次`array_values()`方法。

例如：

```php
remove(['Apple', 'Pear', 'Kiwi', 'Banana'], function ($item) {
    return strlen($item) > 4;
});
// [1 => 'Pear', 2 => 'Kiwi']
```

### 13. rotate

将数组前 n 个元素按顺序移动到尾部。

```php
function rotate (array $items, int $shift = 1)
{
    for ($i = 0; $i < $shift; $i++) {
        array_push($items, array_shift($items));
    }
    
    return $items;
}
```

例如：

```php
rotate([1, 3, 5, 2, 4]); // [3, 5, 2, 4, 1]
rotate([1, 3, 5, 2, 4], 2); // [5, 2, 4, 1, 3]
```

### 14. take

从数组中获取前 n 个元素。

```php
function take (array $items, int $n = 1)
{
    return array_shift($items, 0, $n);
}
```

### 15. without

从数组中获取除给定元素后的其他项。

```php
function without (array $items, ...$params)
{
    return array_values(array_diff($items, $params));
}
```

例如：

```php
without([2, 1, 2, 3], 1, 2); // [3]
```


