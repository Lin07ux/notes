### 两者区别

array_merge 和 array 直接相加都能合并成一个新数组。但是这两者也有一些区别：

**关联数组**

关联数组中，当数组键名是字符，在遇到键名相同的情况时：

* array_merge 会用后面出现的数组元素值覆盖前面的数组元素值；
* array 直接相加则相反，会将先出现的数组元素值作为新数组的元素值。
    
```php
$arr1 = array('a'=>'PHP');
$arr2 = array('a'=>'JAVA');

// 如果键名为字符且键名相同，array_merge()后面数组元素值会覆盖前面数组元素值
print_r(array_merge($arr1,$arr2)); // Array ( [a] => JAVA )

// 如果键名为字符且键名相同，数组相加会将最先出现的值作为结果
print_r($arr1+$arr2); // Array ( [a] => PHP )
```

**索引数组**

索引数组中键名都是数字，在遇到相同的索引的时候：

* array_merge() 不会进行覆盖，而是继续追加在后面，对应的索引也自动增加；
* array 直接相加则和关联数组中的行为一样，直接抛弃了后面键名相同的元素。
    
```php
$arr1 = array("C","PHP");
$arr2 = array("JAVA","PHP");

// 如果键名为数字，array_merge()不会进行覆盖
// Array ( [0] => C [1] => PHP [2] => JAVA [3] => PHP )
print_r(array_merge($arr1, $arr2));

// 如果键名为数组，数组相加会将最先出现的值作为结果，后面键名相同的会被抛弃
// Array ( [0] => C [1] => PHP )
print_r($arr1 + $arr2);
```

### 补充 array_merge_recursive

如果要保留两个数组中的全部值，不论是否有相同的键名，都不会发生覆盖行为，那么就可以使用`array_merge_recursive()`函数。

这个函数的行为是：

* 将两个数组的元素都存入到一个新的数组中；
* 如果有相同的字符串键名，那么新数组中会将这个键名对应一个子数组，子数组中包含着两个数组中该键名对应的值；
* 如果有相同的数字键名，那么按照出现的顺序，重新从 0 开始设置键名。

```php
$arr1 = array("a"=>"php","c");
$arr2 = array("a"=>"java","c","ruby");
$arr3 = array_merge_recursive($arr1, $arr2);

// 结果是：array("a" => ["php", "java"], 0 => "c", 1 => "c", 2 => "ruby");
```


