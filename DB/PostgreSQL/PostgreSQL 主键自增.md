
### 主键介绍

在 PostgreSQL 中，主键约束只是唯一约束和非空约束的组合。所以，下面两个表定义是等价的：

```sql
CREATE TABLE products (
    product_no integer UNIQUE NOT NULL,
    name text,
    price numeric
);
```

```sql
CREATE TABLE products (
    product_no integer PRIMARY KEY,
    name text,
    price numeric
);
```

主键也可以约束多于一个字段，其语法类似于唯一约束：

```sql
CREATE TABLE example (
    a integer,
    b integer,
    c integer,
    PRIMARY KEY (a, c)
);
```

主键表示一个或多个字段的组合可以用于唯一标识表中的数据行。这是定义一个主键的直接结果。请注意：**一个唯一约束（ unique constraint ）实际上并不能提供一个唯一标识，因为它不排除 NULL**。

一个表最多可以有一个主键(但是它可以有多个唯一和非空约束)。关系型数据库理论告诉我们，每个表都必须有一个主键。PostgreSQL 并不强制这个规则，但我们最好还是遵循它。

为 PostgreSQL 表添加和删除主键的方法如下：

```sql
alter table <tbl_name> add primary key (id);
alter table <tbl_name> drop constraint <tbl_name>_pkey;
```

### 主键自增

在 PostgreSQL 中，主键并不是自增的，而是有一个专门的类型，叫做`serial`，表示自动增加。在 PostgreSQL 里，为每一行生成一个”序列号（serial number）“，通常是用类似下面这样的方法生成的：

```sql
CREATE TABLE products (
    product_no integer DEFAULT nextval('products_product_no_seq'),
    ...
);
```

这里的`nextval()`从一个序列对象(sequence object)提供后继的数值。这种做法非常普遍，以至于我们有一个专门的缩写用于此目的：

```sql
CREATE TABLE products (
    product_no SERIAL,
    ...
);
```

其实，*自动增加字段*是*default 字段*的一种特殊情况。也就是为需要自动增加的字段设置一个默认值即可实现自增。


### 实现字段自增的方法

1. 我们也可以在创建表之后，再创建`sequence`，然后设置需要自增的字段的默认值为这个`sequence`的下一个值，从而实现字段自增：

    ```sql
    # 创建自增队列
    CREATE SEQUENCE event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
    
    # 为自增字段设置默认值
    alter table event alter column id set default nextval('event_id_seq');
    ```

2. 或者，可以修改字段类型为`serial`：

    ```sql
    ALTER TABLE event ALTER COLUMN id TYPE serial;
    ```

### 修改序列的起始值

迁移老数据到新的表中时，一般都需要把 ID 也迁移过来，这时新表的自增序列值并不会自动更新，而是依旧从 1 开始，这就需要手动更新序列的值。

修改序列的起始值使用如下的命令即可：

```sql
ALTER SEQUENCE <sequence_name> RESTART WITH <max_id>;
```

其中，`<sequence_name>`表示需要修改的序列的名称，`<max_id>`表示序列的起始值，一般需设置为当前最大 ID 的值加 1。

## 转载

[Postgresql主键自增](http://zhiwei.li/text/2012/02/15/postgresql%E4%B8%BB%E9%94%AE%E8%87%AA%E5%A2%9E/)


