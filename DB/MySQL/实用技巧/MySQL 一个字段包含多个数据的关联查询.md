在实际业务中，经常会遇到一个模型(记录)有多个其他的关联模型(记录)，也就是常见的一对多关系。可以考虑用一个中间表存储这个关联关系，而简便的方法就是直接在父记录中用一个字段存储所有的关联字段的 ID，这时候在进行关联查找的时候就需要一定的技巧了。

加入有一个`users`表和一个`places`表，表结构分别如下：

```sql

CREATE TABLE shao_places (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(100) NOT NULL COMMENT '地点名称',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='来自与去往地点数据表';

CREATE TABLE shao_users (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(50) NOT NULL COMMENT '姓名',
    `from` int(11) UNSIGNED NOT NULL COMMENT '来自地点 ID',
    `to` varchar(20) NOT NULL COMMENT '销往地点 ID 列表(, 分隔)',
    PRIMARY KEY (`id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';
```

可以看到，每个`users`记录都有一个`from`键表示来自地区，还有一个`to`表示去往地区，其中，来自只能是一个地方，而去往则可以有多个地方。

现在需要查询每个用户的来往地点，和要去往的地点。由于去往地点是多个，则需要进行一点的处理，可以考虑使用`FIND_IN_SET()`函数来实现：

```sql
select u.id, u.name, f.name as `from`, group_concat(t.name SEPARATOR " ") as `to` from users as u
 join places as f on u.from = f.id
 join places as t on find_in_set(t.id, u.to)
where phone not like '100%'
group by u.id
order by u.id
```



