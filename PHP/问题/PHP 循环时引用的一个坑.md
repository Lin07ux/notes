
PHP 中，使用 foreach 时，如果将数组元素使用了引用，那么循环结束时，这个变量引用还存在。
如果不 unset() 掉这个元素，那么之后可能会出现问题。

如下代码：

```php
<?php
$arr = array(
    'a'=>'a11',
    'b'=>'b11',
    'c'=>'c11',
);

foreach ($arr as $k=>&$v) {
    var_dump($v);
}

echo "<br />";

var_dump($v);

echo "<br />";

foreach ($arr as $k=>$v) {
    var_dump($v);
}
```

上面的代码中：
第一个输出为：
    string(3) "a11"
    string(3) "b11"
    string(3) "c11"

第二个输出：
    string(3) "c11"

第三个输出为：
    string(3) "a11"
    string(3) "b11"
    string(3) "b11"

解释：
    因为 PHP foreach 循环中，使用引用的时候，在循环结束时，引用不会取消，所以第一次循环结束后，$v 仍然指向的是 $arr['c']。
    于是，第二个输出就是 "c11"。
    在第二个循环中，每一次都会将 $arr 数组的元素值赋值给 $v 变量，也即是赋值给 $arr['c']，
    于是，在循环到 $arr['c'] 时，其值已经变成了 $arr['b'] 元素的值，也即是 "b11"，所以最后输出的就是 "b11" 而不是 "c11"。



