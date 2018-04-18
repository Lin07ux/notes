## 一、简介

在大多数开发中，使用一条 SELECT 查询就会返回一个结果集。如果想将每一条 SELECT 查询的结果合并成一个结果集返回，就需要用到`Union`操作符将多个 SELECT 语句组合起来，这种查询被称为并（Union）或者复合查询。

组合查询适用于下面两种情境中：

1.	从多个表中查询出相似结构的数据，并且返回一个结果集
2.	从单个表中多次 SELECT 查询，将结果合并成一个结果集返回。

比如，查询表中`id = 1`或`id > 3`的数据，可以使用如下的命令：

```mysql
-- 使用 where 查询
SELECT * FROM users WHERE id = 1 OR id > 3;

-- 使用 union 查询
SELECT * FROM users WHERE id = 1
UNION
SELECT * FROM users WHERE id > 3;
```

## 二、使用

Union 有其强大之处，详细介绍之前，需要先明确一下 Union 的使用注意规则：

1.	Union 必须由两条或者两条以上的`SELECT`语句组成，语句之间使用`UNION`或`UNION ALL`连接。
2.	Union 中的每个查询必须包含相同的列、表达式或者聚合函数，他们出现的顺序可以不一致（这里指查询字段相同，表不一定一样）。
3.	列的数据类型必须兼容，兼容的含义是必须是数据库可以隐含的转换他们的类型。

### 2.1 包含与去重

使用`union`的时候，默认会将数据去重后返回，如果不需要去重，则可以使用`union all`来进行组合。

* `UNION`：执行的时候删除重复的记录，所有返回的行都是唯一的；
* `UNION ALL`：不删除重复行也不对结果进行自动排序。

比如，下面的语句就不会去重：

```mysql
SELECT user_id, user_nickname, user_status FROM users WHERE user_status = 1 
UNION ALL
SELECT user_id, user_nickname, user_status FROM users WHERE user_id > 3
```

结果可能如下图所示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494918055570.png)

可以看到，查询的结果中包含两条 user_id 为 4 的记录。

### 2.2 结果排序

使用 Union 组合查询时，只能使用一条`order by`子句对结果集进行排序，而且必须出现在最后一条出现的 SELECT 语句之后。因为 **Union 不允许对于部分结果集进行排序，只能针对最终检索出来的结果集进行排序**。

注意：由于在多表组合查询时候，可能表字段并不相同。所以，**在对结果集排序的时候需要使用检索出来的共同字段**。

比如：

```mysql
(SELECT user_id, user_nickname, user_status FROM users WHERE user_status = 1) 
UNION ALL
(SELECT user_id, user_nickname, user_status FROM users WHERE user_id > 3)
ORDER BY user_id DESC
```

### 2.3 多表组合查询

大型项目中数据经常分布在不同的表，检索的时候需要组合查询出来。多表查询的时候，并不要求两个表完全相同，只需要你检索的字段结构相似就可以。

假设需要将用户昵称和博客文章标题一同混合检索，可以使用类似如下的语句：

```mysql
SELECT posts_id, posts_name, posts_status FROM posts
UNION
SELECT user_id, user_nickname, user_status FROM users
```

**Union 检索遇到不一致的字段名称时候，会使用第一条 SELECT 的查询字段名称**，或者也可以使用别名来改变查询字段名称为相同。

上面这个示例，查询出来的结果无法区分每条结果分别是哪个表中的，其实可以通过在查询语句中加入一个不同的字段值来加以区分，如下所示：

```mysql
SELECT posts_id, posts_name, posts_status, 'users' as table_name FROM posts
UNION
SELECT user_id, user_nickname, user_status, 'posts' as table_name FROM users
```

## 三、其他

### 3.1 Union 与 Where 的关系

`where`和`union`在多数情况下都可以实现相同的结果集。`where`可以实现的语句一定可以使用`union`语句来实现，但是反过来就不一定正确了，比如结果的去重和不去重。

另外，在单表中使用`union`比`where`多条件查询较为复杂。而从多张表中获取数据，使用`union`会相对于简单些。




