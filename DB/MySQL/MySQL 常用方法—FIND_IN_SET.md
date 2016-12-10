该方法可以查询指定的字符在字符集合(字符串)中的位置。在用法和结果上，和`IN`类似，但是也有明显的区别。

另外，`FIND_IN_SET`和`LIKE`有些相像但是也有不同：`LIKE`是广泛的模糊匹配，字符串中不需要分隔符，`FIND_IN_SET`是精确匹配，字段值以英文`,`分隔，`FIND_IN_SET`查询的结果要小于`LIKE`查询的结果。

### 语法

```
FIND_IN_SET(str, strlist)
```

### 参数
该方法接受两个参数：

* str  要查询的字符。
* strlist  字符列表。


> 一个字符串列表就是一个由一些被`,`符号分开的子链组成的字符串。
> 
> 如果第一个参数是一个常数字符串，而第二个是 type SET 列，则`FIND_IN_SET()`函数被优化，使用比特计算。
> 
> 这个函数在第一个参数包含一个逗号(`,`)时将无法正常运行。


### 返回结果
假如字符串 str 在由 N 子链组成的字符串列表 strlist 中，则返回值的范围在 1 到 N 之间。如果 str 不在 strlist 或 strlist 为空字符串，则返回值为 0 。如任意一个参数为 NULL，则返回值为 NULL。

比如：

```mysql
SELECT FIND_IN_SET('b', 'a,b,c,d');
```

结果为 2，因为字符`b`是字符串列表`a,b,c,d`中的第二子字符串。

### 示例
该方法常用于表 A 中的一个字段包含一个或多个表 B 中的主键(唯一索引等)字段的值，而且用逗号分隔。当根据 A 表中的这个字段来查询记录对应在 B 表中的数据，或者根据表 A 的这个字段查询其记录是否含有 B 表中主键字段中的某个值的情况。

具体示例如下：

有个文章表中有个字段为`type`，包含文章所属的类型的ID，而且一篇文章可以包含多个类型，每两个类型之间使用逗号(`,`)分割。

文章类型如下：

+---------------+
|  ID  |  name  |
+------+--------+
|  1   |  头条   |
|  2   |  推荐   |
|  3   |  热点   |
|  4   |  图文   |
+---------------+

如果有篇文章，既是头条，又是热点，还是图文，则其`type`字段的值为`1,3,4`。

**1. 查找所有 type 为图文的文章**

```mysql
SELECT * FROM article WHERE FIND_IN_SET('4', type);
```

**2. 查找表 types 中 id 为 1、2、4 的分类信息**

```mysql
SELECT * FROM types WHERE FIND_IN_SET(id, '1,2,4');

-- 等效于如下的 IN 方法
SELECT * FROM types WHERE id IN (1,2,4);
```

可以看到，`FIND_IN_SET`方法和`IN`方法很类似，只是他们的参数和参数类型不同。

**3. 获取全部文章所属分类的分类名，并将每个文章的分类名连接成一个字符串**

这个需求需要和`GROUP_CONCAT`方法结合起来使用：

```mysql
SELECT a.id, GROUP_CONCAT(t.name) AS types FROM articles AS a LEFT JOIN types AS t ON FIND_IN_SET(t.id, a.type) GROUP BY a.id;
```



