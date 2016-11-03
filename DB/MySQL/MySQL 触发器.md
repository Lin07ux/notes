触发器是一个特殊的存储过程，不同的是存储过程要用`CALL`来调用，而触发器不需要使用`CALL`，也不需要手工启动，只要当一个预定义的事件发生的时候，就会被 MySQL 自动调用。

触发器有如下的限制：

* 只能对永久表创建触发器，不能在临时表中创建触发器；
* 对于具有相同触发程序动作时间和事件的给定表，不能有两个触发程序。例如不能存在两个处于`BEFORE`时间的`INSERT`触发器，但是可以分别有一个`BEFORE`和`AFTER`时间的`INSERT`触发器。


### 创建方式
创建一个触发器需要使用如下的语句结构：

```sql
CREATE TRIGGER trigger_name trigger_time trigger_event
    ON tbl_name
    FOR EACH ROW 
    trigger_stmt
```

触发程序是与表有关的命名数据库对象，当表上出现特定事件时，将激活该对象。

* 触发程序与命名为`tbl_name`的表相关。`tbl_name`必须引用永久性表。不能将触发程序与临时表表或视图关联起来。
* `trigger_time`是触发程序的动作时间。它可以是`BEFORE`或`AFTER`，以指明触发程序是在激活它的语句之前或之后触发。
* `trigger_event`指明了激活触发程序的语句的类型。`trigger_event`可以是下述值之一：
    * INSERT：将新行插入表时激活触发程序，例如，通过`INSERT`、`LOAD DATA`和`REPLACE`语句。
    * UPDATE：更改某一行时激活触发程序，例如，通过`UPDATE`语句。
    * DELETE：从表中删除某一行时激活触发程序，例如，通过`DELETE`和`REPLACE`语句。
* `trigger_stmt`是当触发程序激活时执行的语句。如果你打算执行多个语句，可使用`BEGIN ... END`复合语句结构。

> 请注意：`trigger_event`与以表操作方式激活触发程序的 SQL 语句并不很类似，这点很重要。例如，关于`INSERT`的`BEFORE`触发程序不仅能被`INSERT`语句激活，也能被`LOAD DATA`语句激活。

可能会造成混淆的例子之一是`INSERT INTO .. ON DUPLICATE UPDATE ...`语法：`BEFORE INSERT`触发程序对于每一行将激活，后跟`AFTER INSERT`触发程序，或`BEFORE UPDATE`和`AFTER UPDATE`触发程序，具体情况取决于行上是否有重复键。

如下：创建一个单行触发器：


```sql
# 创建表
CREATE TABLE account(acct_num INT ,amount DECIMAL(10,2));
# 创建触发器：在插入数据的时候，设置变量SUM的值为原本的值加上新插入的值
CREATE TRIGGER ins_sum BEFORE INSERT ON account FOR EACH ROW SET @SUM = @SUM + NEW.amount;
```

### 查看触发器
查看触发器是指数据库中已存在的触发器的定义、状态、语法信息等。

可以使用`SHOW TRIGGERS`查看触发器信息，也可以在`information_schema.TRIGGERS`表中进行查看。

### 删除触发器
使用`DROP TRIGGER`语句可以删除 MySQL 中已经定义的触发器，删除触发器的基本语法。

```sql
DROP TRIGGER [schema_name.]trigger_name;
```

### 示例
下面的示例中，数据库表结构如下：

```sql
# 产品表
CREATE TABLE Product (
  proID INT AUTO_INCREMENT NOT NULL PRIMARY KEY COMMENT '商品表主键',
  price DECIMAL(10,2) NOT NULL COMMENT '商品价格',
  type INT NOT NULL COMMENT '商品类别(0生鲜,1食品,2生活)',
  dtime DATETIME NOT NULL COMMENT '创建时间'
) AUTO_INCREMENT=1 COMMENT='商品表';

# 商品类别汇总表
CREATE TABLE ProductType (
  ID INT NOT NULL COMMENT '商品类别(0生鲜,1食品,2生活)',
  amount INT NOT NULL COMMENT '每种类别商品总金额',
  PRIMARY KEY (ID)
) COMMENT='商品类别资金汇总表';

# 产品价格变动表
CREATE TABLE Product_log (
  ID INT AUTO_INCREMENT NOT NULL COMMENT '主键',
  productid INT NOT NULL COMMENT '产品id',
  newprice DECIMAL(10,2) COMMENT '更改后的价格',
  oldprice DECIMAL(10,2) COMMENT '更改前的价格',
  PRIMARY KEY(ID)
) AUTO_INCREMENT=1 COMMENT='产品价格变动表';

# 插入测试数据
INSERT INTO ProductType VALUES(1,0.00),(2,0.00),(3,0.00);
```

#### INSERT 触发器
在 Insert 触发器中，能够使用的数据只有`NEW.[column]`，表示新插入的数据。

在 Product 表中建立 INSERT 触发器，当往 Product 表中插入产品时，更新 ProductType 表对应的分类商品价格。

```sql
DELIMITER $$
CREATE TRIGGER TR_Product_insert AFTER INSERT ON Product FOR EACH ROW
BEGIN
     UPDATE ProductType
     SET amount = amount + NEW.price
     WHERE ID = NEW.type;
END $$
DELIMITER ;
```

插入测试数据：

```sql
INSERT INTO Product(price,type,dtime) VALUES(10.00,1,NOW()),(10.00,1,NOW()),(10.00,2,NOW()),(10.00,3,NOW());

SELECT * FROM Product;

SELECT * FROM ProductType;
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478148268985.png" />

#### UPDATE 触发器
Update 触发器中`NEW.[column]`代表更新后的值，`OLD.[column]`代表更新前的值。

```sql
DELIMITER $$
CREATE TRIGGER TR_Product_update AFTER UPDATE ON Product FOR EACH ROW
BEGIN
    # 当价格发生变化时生成一条价格变动的日志信息插入 Product_log 表
	 IF NEW.price <> OLD.price THEN 
	   INSERT INTO Product_log(productid, newprice, oldprice)
	   VALUES(NEW.proID, NEW.price, OLD.price);
	   
	 # 当产品类型发生改变时更新 ProductType 表对应的类别 
	 ELSE IF NEW.type <> OLD.type THEN
		 UPDATE ProductType
		 SET amount = amount + (SELECT price FROM Product WHERE proID = NEW.proid)
		 WHERE ID = NEW.type;
		 UPDATE ProductType
		 SET amount = amount - (SELECT price FROM Product WHERE proID = NEW.proid)
		 WHERE ID = OLD.type;
	 END IF;
END $$  
DELIMITER ;
```

测试数据：

```sql
UPDATE Product SET price = 40.00 WHERE proid = 4;
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478148575182.png" />


```sql
UPDATE Product SET type=2 WHERE proid=4;
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478148600198.png" />

#### DELETE 触发器
Delete 触发器中，可以使用的数据为`OLD.[column]`，表示要删除的数据。

```sql
DELIMITER $$
CREATE TRIGGER TR_product_delete BEFORE DELETE ON product FOR EACH row
BEGIN
     UPDATE producttype
     SET amount = amount - (SELECT price FROM product WHERE proID = OLD.proID)
     WHERE ID = OLD.type;
END $$
DELIMITER ;
```

测试数据：


```sql
DELETE FROM product WHERE proID = 4;
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478148725664.png" />


### 转摘
1. [我的MYSQL学习心得（十二） 触发器](http://www.cnblogs.com/lyhabc/p/3802704.html)
2. [MySQL 触发器](http://www.cnblogs.com/chenmh/p/4978153.html)

