ThinkPHP 虽然号称对 PostgreSQL 支持，但是其实支持的并不完善，在使用中，需要有所注意。

### 提示找不到 pgsql 驱动
首先需要检查服务器上 php 是否安装了 php-pgsql 的扩展：
`php -m`

如果没有安装，则需安装对应版本的扩展：
`yum -y install php56w-pgsql`

### table_msg() 不存在
参考：[ThinkPHP 3.2 + PostgreSQL 9.4 的错误及解决办法](http://blog.csdn.net/liigo/article/details/48396075)

这个问题是由于 THinkPHP 在获取表字段的时候，需要借助 pgsql 中的函数来实现，但是这个函数需要我们自行创建。

- 第一步，在数据库中执行下面的语句。
> 需要注意的是，需要将代码中的`crawl`替换成你 pgsql 中实际使用的数据库中的`命名空间`。

```pgsql
CREATE OR REPLACE FUNCTION "crawl"."pgsql_type"(a_type varchar) RETURNS varchar AS
$BODY$
DECLARE
     v_type varchar;
BEGIN
     IF a_type='int8' THEN
          v_type:='bigint';
     ELSIF a_type='int4' THEN
          v_type:='integer';
     ELSIF a_type='int2' THEN
          v_type:='smallint';
     ELSIF a_type='bpchar' THEN
          v_type:='char';
     ELSE
          v_type:=a_type;
     END IF;
     RETURN v_type;
END;
$BODY$
LANGUAGE PLPGSQL;

CREATE TYPE "crawl"."tablestruct" AS (
  "fields_key_name" varchar(100),
  "fields_name" VARCHAR(200),
  "fields_type" VARCHAR(20),
  "fields_length" BIGINT,
  "fields_not_null" VARCHAR(10),
  "fields_default" VARCHAR(500),
  "fields_comment" VARCHAR(1000)
);

CREATE OR REPLACE FUNCTION "crawl"."table_msg" (a_schema_name varchar, a_table_name varchar) RETURNS SETOF "crawl"."tablestruct" AS
$body$
DECLARE
     v_ret crawl.tablestruct;
     v_oid oid;
     v_sql varchar;
     v_rec RECORD;
     v_key varchar;
BEGIN
     SELECT
           pg_class.oid  INTO v_oid
     FROM
           pg_class
           INNER JOIN pg_namespace ON (pg_class.relnamespace = pg_namespace.oid AND lower(pg_namespace.nspname) = a_schema_name)
     WHERE
           pg_class.relname=a_table_name;
     IF NOT FOUND THEN
         RETURN;
     END IF;

     v_sql='
     SELECT
           pg_attribute.attname AS fields_name,
           pg_attribute.attnum AS fields_index,
           pgsql_type(pg_type.typname::varchar) AS fields_type,
           pg_attribute.atttypmod-4 as fields_length,
           CASE WHEN pg_attribute.attnotnull  THEN ''not null''
           ELSE ''''
           END AS fields_not_null,
           pg_attrdef.adsrc AS fields_default,
           pg_description.description AS fields_comment
     FROM
           pg_attribute
           INNER JOIN pg_class  ON pg_attribute.attrelid = pg_class.oid
           INNER JOIN pg_type   ON pg_attribute.atttypid = pg_type.oid
           LEFT OUTER JOIN pg_attrdef ON pg_attrdef.adrelid = pg_class.oid AND pg_attrdef.adnum = pg_attribute.attnum
           LEFT OUTER JOIN pg_description ON pg_description.objoid = pg_class.oid AND pg_description.objsubid = pg_attribute.attnum
     WHERE
           pg_attribute.attnum > 0
           AND attisdropped <> ''t''
           AND pg_class.oid = ' || v_oid || '
     ORDER BY pg_attribute.attnum' ;

     FOR v_rec IN EXECUTE v_sql LOOP
         v_ret.fields_name=v_rec.fields_name;
         v_ret.fields_type=v_rec.fields_type;
         IF v_rec.fields_length > 0 THEN
            v_ret.fields_length:=v_rec.fields_length;
         ELSE
            v_ret.fields_length:=NULL;
         END IF;
         v_ret.fields_not_null=v_rec.fields_not_null;
         v_ret.fields_default=v_rec.fields_default;
         v_ret.fields_comment=v_rec.fields_comment;
         SELECT constraint_name INTO v_key FROM information_schema.key_column_usage WHERE table_schema=a_schema_name AND table_name=a_table_name AND column_name=v_rec.fields_name;
         IF FOUND THEN
            v_ret.fields_key_name=v_key;
         ELSE
            v_ret.fields_key_name='';
         END IF;
         RETURN NEXT v_ret;
     END LOOP;
     RETURN ;
END;
$body$
LANGUAGE 'plpgsql' VOLATILE CALLED ON NULL INPUT SECURITY INVOKER;

COMMENT ON FUNCTION "crawl"."table_msg"(a_schema_name varchar, a_table_name varchar)
IS '获得表信息';

---重载一个函数
CREATE OR REPLACE FUNCTION "crawl"."table_msg" (a_table_name varchar) RETURNS SETOF "crawl"."tablestruct" AS
$body$
DECLARE
    v_ret ctawl.tablestruct;
BEGIN
    FOR v_ret IN SELECT * FROM table_msg('crawl',a_table_name) LOOP
        RETURN NEXT v_ret;
    END LOOP;
    RETURN;
END;
$body$
LANGUAGE 'plpgsql' VOLATILE CALLED ON NULL INPUT SECURITY INVOKER;

COMMENT ON FUNCTION "crawl"."table_msg"(a_table_name varchar)
IS '获得表信息';
```

- 第二步，配置 ThinkPHP 的 config 配置，增加表前缀为 pgsql 的命名空间，比如这里就是`crawl.`(包含那个点号)。

- 第三步，修改`./ThinkPHP/Library/Think/Db/Driver/Pgsql.class.php`文件中的代码：
    * 41 行(getFields()方法)：`select fields_name as "field",fields_type as "type",fields_not_null as "null",fields_key_name as "key",fields_default as "default",fields_default as "extra" from table_msg(\''.$tableName.'\');`。即：在`$tableName`前后加上单引号。
    * 40 行(list 语句)下面增加一行：`$tableName = str_replace(C('DB_PREFIX'), '', $tableName);`


如果上面两步执行完成之后，还会提示函数不存在，或者`relation "articles" does not exist`一类的错误，一般是由于第一步中生成函数时，相应的函数或者结构的前缀没有修改完全。默认情况下，pgsql 的命名空间应该是 public。

### add() 之后无法返回正确的自增 ID

**现象**

在使用 add() 方法插入数据之后，返回的结果总是 1，而不是新插入数据的自增 ID。

**原因**

ThinkPHP 3.2.3 中，所有的数据库都是采用 PDO 方式连接数据库的。PDO 中返回插入数据的自增 ID 的方法是下面的这个方法：

```php
string PDO::lastInsertId ([ string $name = NULL ] )
```
参数 name 表示应该返回 ID 的那个序列对象的名称，是可选的。

但是这个函数的具体行为是基于具体的底层驱动实现的。在不同的 PDO 驱动之间，此方法可能不会返回一个有意义或一致的结果，因为底层数据库可能不支持自增字段或序列的概念。

对于 pgsql 来说：PDO_PGSQL() 要求为 name 参数指定序列对象的名称。

而这正是导致问题的所在：
在 ThinkPHP 的 DB 类 Driver.class.php 中，其获取自增 ID 的方式是使用无参数的 lastInsertId() 方法。增加数据后，没有参数时，pgsql 返回的就是 1。

**解决**

对于 pgsql，如果要返回正确的自增 ID，必然就是需要在调用 lastInsertId() 方法时添加对应的序列对象的名称(sequence name)。

所以，首先，我们需要在 pgsql 的对应驱动中重写 DB 驱动的`getLastInsID()`方法，给其传入参数：

```php
# 位置：ThinkPHP/Library/Think/Db/Driver/Pgsql.class.php
/**
* 用于获取最后插入的ID
* @access public
* @return integer
*/
public function getLastInsID($sequenceName) {
   return $this->_linkID->lastInsertId($sequenceName);
}
```

那么，这个参数应该是什么呢？

一般情况下，pgsql 数据表中设置了自增主键的话，那么自增主键的命名方式为：

`tableNmae_columnName_seq`

其中：

* tableName 表示当前表的名称(如果不是 public 命名空间，则需要添加命名空间)；
* columnName 表示设置主键的列的名称；
* seq 就是默认设置的，一般不需要修改。

所以，参数不是一个确定的字符串：表名不确定、主键列名不确定：

* 对于表名，我们在用 M() 或者 D() 方法的时候，一般都会传入表名，那么可以考虑在 Model 类中进行拼接参数；
* 对于主键列名，如果做一个约定：数据表中，主键列名均为`id`，那我们就可以绕过去这个问题了。如果不能遵守这个约定，那就只能考虑在 Model 类中增加一个方法来传入对应的参数了。

现在来实现上述的方法，找到并修改 Model 类中的`getLastInsID()`方法：

```php
# 位置：ThinkPHP/Library/Think/Model.class.php
/**
* 返回最后插入的ID
* @access public
* @return string
*/
public function getLastInsID() {
   $col = null;
   if (strtolower(C('DB_TYPE')) == 'pgsql')
       $col = strtolower($this->trueTableName).'_id_seq';

   return $this->db->getLastInsID($col);
}
```
> 这里使用`$this->trueTableName`来获取表名，就可以获取到包含前缀，甚至数据库名的真实表名了，能避免出错。

这样修改之后，基本上就能正常了。但是很快就会发现，有时候还是会出错，提示`relation xx does not exist`。一般这是在访问没有设置主键的表的时候出现的错误。因为使用 pgsql 驱动的`lastInsertId()`方法获取没有主键的表的时候，这个序列对象不存在，于是报错。

知道原因之后，就可以考虑使用`try...catch`语句来重写一下 pgsql 的`lastInsertId()`方法：

```php
# 位置：ThinkPHP/Library/Think/Db/Driver/Pgsql.class.php
/**
* 用于获取最后插入的ID
* @access public
* @return integer
*/
public function getLastInsID($sequenceName) {
   try {
       return $this->_linkID->lastInsertId($sequenceName);
   } catch (\PDOException $e) {
       return $this->lastInsID;
   }
}
```

### 批量插入提示数据类型错误
在使用`addAll()`方法批量插入数据的时候，如果 pgsql 中对应的字段是 int 类型时，会出现错误，提示这个 int 类型的字段的格式不正确，传入的数据是 text 格式。

追踪`addAll()`方法，发现其调用了 DB 类中的`insertAll()`方法。

在基础 DB 类中，`insertAll()`方法是使用`INSERT INTO ... SELECT ... UNION ALL SELECT`的方式插入数据的。而 ThinkPHP 中，pgsql 的驱动类并没有重写这个方法，所以批量插入时，也是使用这种方式插入数据，这时数据库插入数据时就不会自动做类型转换了。而 TP 在预处理插入数据时，会将数据都使用引号包裹，从而把数值也变成了字符串，所以插入数据的时候就会出现类型错误。

为了解决这个问题，我们可以改变插入数据的方式，将`SELECT`改成`VALUES(...)`的方式。可以直接将 ThinkPHP 中的 mysql 数据库的驱动类中的`insertAll()`方法拷贝到 pgsql 的驱动类中，即可解决批量插入的问题。

