uuid-ossp 模块提供了几个方法，可以生成 uuid，也可以用于给 uuid 字段设置默认值。

使用之前，需要先安装该模块。

```sql
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
```

只有成功安装该模块之后，才能使用`uuid_generate_v1()`和`uuid_generate_v4()`这两个方法来生成 uuid。如：

```sql
SELECT uuid_generate_v1();
SELECT uuid_generate_v4();

-- 作为默认值
CREATE TABLE customers (
    id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(36)
)；
```

> [9.5 文档](https://www.postgresql.org/docs/9.5/static/uuid-ossp.html)

