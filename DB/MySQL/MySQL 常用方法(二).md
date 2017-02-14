### IF 根据条件返回不同的值
语法：`IF(expr1, expr2, expr3) `

参数：`expr1`一个表达式，`expr2`表达式为真时的返回值，`expr3`表达式为假时的返回值。 

效果：如果 expr1 是 TRUE(`expr1<>0`且`expr1<>NULL`)，那么`IF()`返回 expr2，否则它返回 expr3。

注意：expr1 作为整数值被计算，它意味着如果你正在测试浮点或字符串值，你应该使用一个比较操作来做。 

示例：

```mysql
SELECT IF(1>2, 2, 3);  # 3
SELECT IF(1<2, 'yes', 'no');  # 'yes'
SELECT IF(strcmp('test','test1'), 'yes', 'no');  # 'no'
SELECT IF(0.1, 1, 0);    # 0  因为 0.1 被变换到整数值变成测试 IF(0)
SELECT IF(0.1<>0, 1, 0); # 1
```


### IFNULL 判断是否为 NULL
语法：`IFNULL(expr1, expr2) `

参数：`expr1`与 NULL 进行比较的值，`expr2`为 NULL 时的返回值。

效果：如果 expr1 不是 NULL，`IFNULL()`返回 expr1，否则它返回 expr2。

注意：expr1 是与 NULL 来比较的，不会做类型转换。也即是 0、false 并不是 NULL。

示例：

```mysql
SELECT IFNULL(1, 0);    # 1
SELECT IFNULL(0, 10);   # 0
SELECT IFNULL(1/0, 10); # 10
```

转摘：[mysql 将null转代为0](http://blog.csdn.net/johnstrive/article/details/8298604)






