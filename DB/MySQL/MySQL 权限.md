## 相关表

在 MySQL 数据库中，有 mysql_install_db 脚本初始化权限表，存储权限的表有：

1. user 表
2. db 表
3. host 表
4. table_priv 表
5. columns_priv 表
6. proc_priv 表
 
MySQL 存取控制包含 2 个阶段：

* 阶段1：服务器检查你是否允许连接。
* 阶段2：假定你能连接，服务器检查你发出的每个请求。看你是否有足够的权限实施它。例如，如果你从数据库中一个表选取(select)行或从数据库抛弃一个表，服务器确定你对表有`select`权限或有`drop`权限。

服务器在存取控制的两个阶段使用在`mysql`的数据库中的`user`、`db`和`host`表，对存取控制的第二阶段(请求证实)，如果请求涉及表，服务器可以另外参考`tables_priv`和`columns_priv`表。

> `mysql.user`表中没有一个列是保存用户创建时间的。

## 一、帐户管理

MySQL 提供许多语句用来管理用户帐号，这些语句可以用来包括登录和退出 MySQL 服务器、创建用户、删除用户、密码管理、权限管理。MySQL 数据库的安全性，需要通过帐户管理来保证。

### 1.1 登录和退出

在命令行中登录 MySQL，命令如下：

```
mysql [-h <host>] -u <username> -p[<password>] [-P <port>] [-e <sql>]
```

其中：

* `-h` 指定 MySQL 服务器的地址
* `-u` 指定登录 MySQL 的账户名，账户名和`-u`直接可以不加空格
* `-p` 指定密码。如果要在登录的命令中指定名，则密码不能和`-p`之间有空格。另外，不建议在登录命令中写密码，不加密码的时候回车执行命令的时候会提示输入密码的。
* `-P` 指定 MySQL 服务的端口号。默认是 3306，可以不填写。
* `-e` 执行 SQL 语句。如果指定该参数，将在登录后执行`-e`后面的命令或`sql`语句并退出。

退出登录的话，就用`exit`即可。

### 1.2 创建用户

创建用户有多种方式。

#### 1.2.1 CREATE

```sql
-- 语法
CREATE USER user [IDENTIFIED BY [PASSWORD] 'password']
    [, user [IDENTIFIED BY [PASSWORD] 'password']];
    
-- 示例：用户名部分为“jeffrey”，主机名默认为“%”（即对所有主机开放权限）
CREATE USER 'jeffrey'@'localhost' IDENTIFIED BY 'mypass';
-- 示例：使用 IDENTIFIED WITH 方式
CREATE user 'jeffrey'@'localhost' IDENTIFIED WITH my_auth_plugin;
```

如果指定用户登录不需要密码，则可以省略`IDENTIFIED BY`部分。对于使用插件认证连接的用户，服务器调用指定名称的插件，客户端需要提供验证方法所需要的凭据。如果创建用户时或者连接服务器时，服务器找不到对应的插件，将返回一个错误。

`IDENTIFIED WITH`只能在 MySQL 5.5.7 及以上版本使用。`IDENTIFIED WITH`和`IDENTIFIED BY`是互斥的，所以对一个帐户来说只能使用一个验证方法。

`CREATE USER`语句的操作会被记录到服务器日志文件或者操作历史文件中。例如 `~/.mysql_history`。这意味着对这些文件有读取权限的人，都可以读取到新添加用户的明文密码。解决办法是：新建用户的时候使用`password`关键字。

```sql
# 先查出密码对应的哈希值
SELECT password('mypass');
# 然后使用PASSWORD关键字设置密码
CREATE user 'tom'@'localhost' identified BY
PASSWORD'*6C8989366EAF75BB670AD8EA7A7FC1176A95CEF4';
```

#### 1.2.2 GRANT

`GRANT USER`语句可以用来创建帐户，通过该语句可以在`user`表中添加一条新记录。比起`CREATE USER`语句创建的新用户，还需要使用`GRANT`语句赋予用户权限。使用`GRANT`语句创建新用户时必须有`GRANT`权限。

```sql
GRANT priv_type [(column_list)] [, priv_type [(column_list)]] ...
    ON [object_type] {tbl_name | * | *.* | db_name.*}
    TO user [IDENTIFIED BY [PASSWORD] 'password']
        [, user [IDENTIFIED BY [PASSWORD] 'password']] ...
    [REQUIRE
        NONE |
        [{SSL| X509}]
        [CIPHER 'cipher' [AND]]
        [ISSUER 'issuer' [AND]]
        [SUBJECT 'subject']]
    [WITH with_option [with_option] ...]
```

比如，使用`GRANT`语句创建一个新用户`testUser`，限制为本地登录，密码为`testpwd`，并授予用户对所有数据表的`SELECT`和`UPDATE`权限：

```sql
GRANT SELECT ,UPDATE ON *.* TO 'testUser'@'localhost' identified BY 'testpwd';
```

#### 1.2.3 直接操作MySQL用户表

不管是`CREATE USER`还是`GRANT USER`，在创建用户时，实际上都是在`mysql.user`表中添加一条新记录。所以我们也可以使用`INSERT`语句向`mysql.user`表插入一条记录来创建一个新用户。插入的时候必须要对`mysql.user`表有`INSERT`权限。

```sql
INSERT INTO mysql.user(host,user,password,[privilegelist]) VALUES ('host','username',password('password'),privilegevaluelist);
```

比如，使用`INSERT`创建一个新用户，其用户名称为`customer1`，主机名为`localhost`，密码为`customer1`：

```sql
INSERT INTO mysql.user(host,user,password) VALUES ('localhost','customer1',password('customer1'));
```

### 1.3 删除普通用户

可以使用`DROP USER`语句删除用户，也可以直接通过`DELETE`语句从`mysql.user`表中删除对应的记录来删除用户。

`DROP USER`语句用于删除一个或多个 MySQL 帐户。要使用`DROP USER`，必须拥有`mysql`数据库的全局`CREATE USER`权限或`DELETE`权限。

比如，删除`testUser`这个用户：

```sql
DROP USER 'testUser'@'localhost';
-- 或者
DELETE FROM mysql.user WHERE `Host`='localhost' and `User`='testUser';
```

### 1.4 匿名用户

如果有匿名用户，那么客户端就可以不用密码登录 MYSQL 数据库，这样就会存在安全隐患。检查匿名用户的方法：

```sql
SELECT * FROM mysql.user WHERE `User`='';
```

如果查找到 user 字段值为空的那条记录，说明存在匿名用户，需要把这条记录删除。删除语句：

```sql
DELETE FROM mysql.user WHERE `User`='';
```

### 1.5 修改密码

#### 1.5.1 root 用户修改自己的密码

**1、使用`mysqladmin`命令在命令行指定新密码**

```shell
mysqladmin -u root -p password"rootpwd";
```

**2、修改`mysql`数据库的`user`表**

```sql
UPDATE mysql.user SET `Password` =password('rootpwd') WHERE `User`='root' and `Host`='localhost';
```

执行`update`之后需要执行`flush privileges`语句重新加载用户权限。

**3、使用`SET`语句修改 root 用户的密码**

`SET PASSWORD`语句可以用来重新设置其他用户的登录密码或者自己使用的帐户密码。新密码必须用PASSWORD函数加密。语法如下：

```sql
SET PASSWORD=PASSWORD("ROOTPWD");
```

所以可以使用 root 用户登录到 MySQL 之后执行下面语句来修改 root 的密码：

```sql
SET password=password('123456');
```

执行之后也需要使用执行`flush privileges`语句或者重启 MySQL 重新加载用户权限。

#### 1.5.2 root 用户修改普通用户密码

**1、使用`SET`语句修改普通用户的密码**

```sql
SET PASSWORD FOR 'USER'@'HOST' =PASSWORD("ROOTPWD");
```

**2、使用`UPDATE`语句修改普通用户的密码**

```sql
UPDATE mysql.user SET `Password` =password('rootpwd') WHERE `User`='root' and `Host`='localhost';
```

执行完毕之后需要使用`flush privileges`语句或者重启 MySQL 重新加载用户权限。

**3、使用`GRANT`语句修改普通用户密码**

```sql
GRANT USAGE ON *.* TO 'someuser'@'%'  IDENTIFIED BY 'somepwd';
```

> 注意：使用`GRANT`语句和`MYSQLADMIN`设置密码，他们均会加密密码，这种情况下，不需要使用`PASSWORD()`函数。

#### 1.5.3 普通用户修改自身密码

用普通用户的账号登录 MySQL 之后，使用`SET`语句修改自己的密码：

```sql
SET password=password('newpassword');
```

#### 1.5.4 root 用户密码丢失的解决办法

root 用户的密码丢失后，可以使用`–skip-grant-tables`选项启动 MySQL 服务。这样启动 MySQL 时，服务器将不加载权限判断，任何用户都能访问数据库。所以这样启动 MySQL 服务的时候，建议断开外网接入。

在 Linux 系统下，需要使用`mysqld_safe`来启动 MySQL 服务，也可以使用`/etc/init.d/mysql`命令来启动：

```shell
mysqld_safe --skip-grant-tables user=mysql
# 或
/etc/init.d/mysql start-mysqld --skip-grant-tables
```


## 二、权限管理

对于`GRANT`和`REVOKE`语句，`priv_type`可以被指定为以下任何一种：

* `ALL [PRIVILEGES]`  设置除`GRANT OPTION`之外的所有简单权限
* `ALTER` 允许使用`ALTER TABLE`语句
* `ALTER ROUTINE` 更改或取消已存储的子程序
* `CREATE` 允许使用`CREATE TABLE`
* `CREATE ROUTINE` 创建已存储的子程序
* `CREATE TEMPORARY TABLES` 允许使用`CREATE TEMPORARY TABLE`
* `CREATE USER` 允许使用`CREATE USER`、`DROP USER`、`RENAME USER`和`REVOKE ALL PRIVILEGES`。
* `CREATE VIEW` 允许使用`CREATE VIEW`
* `DELETE` 允许使用`DELETE`
* `DROP` 允许使用`DROP TABLE`
* `EXECUTE` 允许用户运行已存储的子程序
* `FILE` 允许使用`SELECT…INTO OUTFILE`和`LOAD DATA INFILE`
* `INDEX` 允许使用`CREATE INDEX`和`DROP INDEX`
* `INSERT` 允许使用`INSERT`
* `LOCK TABLES` 允许对您拥有`SELECT`权限的表使用`LOCK TABLES`
* `PROCESS` 允许使用`SHOW FULL PROCESSLIST`
* `REFERENCES` 未被实施
* `RELOAD` 允许使用`FLUSH`
* `REPLICATION CLIENT` 允许用户询问从属服务器或主服务器的地址
* `REPLICATION SLAVE` 用于复制型从属服务器（从主服务器中读取二进制日志事件）
* `SELECT` 允许使用`SELECT`
* `SHOW DATABASES` 显示所有数据库
* `SHOW VIEW` 允许使用`SHOW CREATE VIEW`
* `SHUTDOWN` 允许使用`mysqladmin shutdown`
* `SUPER` 允许使用`CHANGE MASTER`、`KILL`、`PURGE MASTER LOGS`和`SET GLOBAL`语句，`mysqladmin debug`命令；允许您连接（一次），即使已达到`max_connections`。
* `UPDATE` 允许使用`UPDATE`
* `USAGE` “无权限”的同义词
* `GRANT OPTION` 允许授予权限

### 2.1 授权

授权就是为某个用户授予权限。授予的权限可以分为多个层级：

* 全局层级  全局权限适用于一个给定服务器中的所有数据库。这些权限存储在`mysql.user`表中。`GRANT ALL ON *.*`和`REVOKE ALL ON *.*`只授予和撤销全局权限。
* 数据库层级  数据库权限适用于一个给定数据库中的所有目标。这些权限存储在`mysql.db`和`mysql.host`表中。`GRANT ALL ON db_name.*`和`REVOKE ALL ON db_name.*`只授予和撤销数据库权限。
* 表层级  表权限适用于一个给定表中的所有列。这些权限存储在`mysql.talbes_priv`表中。`GRANT ALL ON db_name.tbl_name`和`REVOKE ALL ON db_name.tbl_name`只授予和撤销表权限。
* 列层级  列权限适用于一个给定表中的单一列。这些权限存储在`mysql.columns_priv`表中。当使用`REVOKE`时，您必须指定与被授权列相同的列。
* 子程序层级  `CREATE ROUTINE`、`ALTER ROUTINE`、`EXECUTE`和`GRANT`权限适用于已存储的子程序。这些权限可以被授予为全局层级和数据库层级。而且，除了`CREATE ROUTINE`外，这些权限可以被授予为子程序层级，并存储在`mysql.procs_priv`表中。

当后续目标是一个表、一个已存储的函数或一个已存储的过程时，`object_type`子句应被指定为`TABLE`、`FUNCTION`或`PROCEDURE`。当从旧版本的 MySQL 升级时，要使用本子句，您必须升级您的授权表。

### 2.2 收回权限

收回权限就是取消已经赋予用户的某些权限。收回用户不必要的权限可以在一定程度上保证系统的安全性。

使用`REVOKE`收回权限之后，用户帐户的记录将从`db`、`host`、`tables_priv`、`columns_priv`表中删除，但是用户帐号记录依然在`user`表中保存。

语法：

```sql
REVOKE priv_type [(column_list)] [, priv_type [(column_list)]] ...
    ON [object_type] {tbl_name | * | *.* | db_name.*}
    FROM user [, user] ...
 
REVOKE ALL PRIVILEGES, GRANT OPTION FROM user [, user] ...
```

使用`REVOKE`语句，必须拥有`mysql`数据库的全局`CREATE`权限或`UPDATE`权限。

比如，使用`REVOKE`语句取消用户`grantUser`的`INSERT`权限：

```sql
REVOKE INSERT ON *.* FROM 'grantUser'@'localhost';
```

### 2.3 查看权限

`SHOW GRANTS`语句可以显示用户的权限信息。语法如下：

```sql
SHOW GRANTS FOR 'user'@'host';
```

比如，使用`SHOW GRANTS`语句查询用户`grantUser`的权限信息：

```sql
show grants FOR 'grantUser'@'localhost';
```

