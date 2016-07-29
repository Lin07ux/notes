array_merge 和 array 直接相加，都能够把两个数组合并，然后返回合并后的数组。但是他们两者的最终生成的数组内容并不完全相同。

## array 相加
把两个数组直接用 + 号相加，得到的结果是：

* 两个数组的元素都存到同一个数组中；
* 如果有相同的键名(不论是字符串键名，还是数字键名)，则以 + 前的数组的元素值为准。

示例如下：

```php
// 关联数组(字符键名)
$arr1 = array('a' => 'PHP', 'b' => 'C++');
$arr2 = array('a' => 'JAVA', 'd' => 'Python');
$arr3 = $arr1 + $arr2;
// 结果：array("a" => "PHP", "b" => "C++", "d" => "Python"}

// 索引数组(数字键名)
$arr4 = array('PHP', 'C++');
$arr5 = array('JAVA', 'Python');
$arr6 = $arr4 + $arr5;
// 结果：array('PHP', 'C++')
```

## array_merge
array_merge 的最终的结果和 array 直接相加有两处不同：

* 将两个数组的的元素都存到同一个数组中；
* 如果具有相同的字符串键名，则会以后一个数组的元素值为最终结果；
* 对于两个数组中的数字键名元素，则会忽略其原本的键名，而根据其出现的顺序，重新从 0 开始设置键名。

```php
// 关联数组(字符键名)
$arr1 = array('a' => 'PHP', 'b' => 'C++');
$arr2 = array('a' => 'JAVA', 'd' => 'Python');
$arr3 = array_merge($arr1, $arr2);
// 结果：array("a" => "JAVA", "b" => "C++", "d" => "Python"}

// 索引数组(数字键名)
$arr4 = array('PHP', 'C++');
$arr5 = array('JAVA', 'Python');
$arr6 = array_merge($arr4, $arr5);
// 结果：array('PHP', 'C++', 'JAVA', 'Python')

$arr7 = array('PHP', 2 => 'C++');
$arr8 = array('JAVA', 'Python');
$arr9 = array_merge($arr7, $arr8);
// 结果仍旧是：array('PHP', 'C++', 'JAVA', 'Python')
```

## 补充 array_merge_recursive
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


