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







