## 错误

向一个数据库中增加表的时候，正常；从这个表创建外键到其他表的时候，就遇到了问题，提示：

```
ERROR 1215 (HY000): Cannot add foreign key constraint
```

## 原因

提示这个错误一般都是由于两个表创建外键的字段类型未能完全匹配，或者外键指向的字段不是自增字段或 Unique Index 字段。

## 解决

1. 首先检查两个表中做外键字段的类型。
    如果类型不完全相同，则需要进行修改。需要注意的是，`int`类型有和没有`unsigned`标记是不相同的；字符如果长度不同也是不同的。

2. 检查外键指向的字段是否是自增主键或者唯一索引。
    > A foreign key can only reference a primary or unique column. 

3. 如果还是没有问题，那么就要检查数据库的引擎和数据表的字符集。
    使用`show create table table_name \G;`就可以查看到表创建时的引擎和字符集。

    显示的结果类似如下：
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1495702369207.png)

    可以从红框中的地方看到对应的表信息。
    
经过三步检查之后应该就能找到对应的问题了。

