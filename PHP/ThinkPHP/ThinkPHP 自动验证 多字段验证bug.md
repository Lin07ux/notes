ThinkPHP 在设置自动验证的时候，如果需要同时根据两个或多个字段来进行验证，可以使用如下的多字段验证：

```php
protected $_validate = array(
     array('field1,field2', 'check', '错误提示', 0, 'callback', 3),
);
```

但是上面的代码并不会调用`check`方法进行自动验证，只有当字段为 1 个的时候才会正常。

解决的方法是：

* 将第 4 个参数设置为 1(也就是必须验证)；
* 或者，修改 ThinkPHP 核心文件进行修复。

```php
// TP多字段验证存在BUG：
// 在“值不为空时才验证”和“存在该字段就验证”的情况下，多字段验证不会激活
// 这是因为获取data字段值的时候，检查时使用的数组索引是字符串'field1,field2'
// 这边临时解决的方法是只检查第一个字段是否存在或者不为空。
// if(isset($data[$val[0]]))         原代码
$fields = explode(",",$val[0]);   // 新代码
if(isset($data[$fields[0]]))      // 新代码
```

转摘：[thinkphp 自动验证 多字段验证BUG](http://www.thinkphp.cn/topic/9640.html)


