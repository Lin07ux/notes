> 参考：
> 
> 1. [SQL执行顺序-以MySQL为例](https://zhuanlan.zhihu.com/p/532115107)
> 2. [深入理解MySQL执行过程及执行顺序](https://dockone.io/article/2434613)
> 3. [SQL执行顺序-以MySQL为例](https://zhuanlan.zhihu.com/p/532115107)

SQL 的执行顺序并不是按照书写的顺序来从前往后、从左往右依次执行的，而是按照固定的顺序解析和执行的。主要的作用就是从上一个阶段的执行返回结果提供给下一阶段使用，而在不同的执行过程中会有不同的临时中间表产生。

MySQL 的执行过程基本按照 SQL 执行顺序来实现，但是也并非完全一致，MySQL 会有一些优化操作来提升 SQL 语句的执行过程，优化查询效果，从而导致其实际的执行流程可能发生变化。

### 1. 顺序图

SQL 的一般执行顺序如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669773055)

### 2. 详细说明

**FROM**

第一步就是选择出 FROM 关键词后面的表，表示要从数据库中的哪张表中进行操作，如果有多张表就逐步的对其进行两表使用笛卡尔积生成中间表 Temp1。

**ON**

根据筛选条件，在 Temp1 上筛选符合条件的记录，生成临时中间表 Temp2。

**JOIN**

根据链接方式的不同，选择是否在 Temp2 的基础上添加外部行：
    
- 左外连接就把左表在 Temp2 中筛选掉的记录添加回来，生成 Temp3；
- 右外连接则把右表在 Temp2 中筛选掉的记录添加回来，生成 Temp3。

**WHERE**

WHERE 表示筛选，根据其后面的条件对上一步中得到的中间表进行过滤，按照指定的字段值（如果有 AND 连接符会进行联合筛选）从中筛选出符合条件的数据，生成中间表 Temp4。

如果在此阶段找不到数据，会直接返回给客户端，不会继续往下执行。而且，在 WHERE 中不能使用聚合函数。

**GROUP BY**

对 Temp4 中的数据按照指定的字段的值进行分组，产生临时中间表 Temp5。

在这个过程中，只是数据的顺序发生改变，数据总量不会发生变化。分组之后，表中的数据以组的形式存在。

**WITH**

对 Temp5 应用 cube 或 rollup 生成超组，产生临时中间表 Temp6。

**HAVING**

对 Temp6 进行条件过滤，用符合条件的数据生成临时中间表 Temp7。

在这个阶段中可以执行聚合函数，比如`count/sum/avg`等，而且在这个阶段中就可以使用 SELECT 中的别名。

**SELECT**

从 Temp7 中挑选出需要查询的数据，并将`*`解析为所有的数据列，生成临时中间表 Temp8。

**DISTINCT**

对所有的数据进行去重，如果有`min/max`函数，会执行字段函数的计算，然后生成临时中间表 Temp9。

**ORDER BY**

对 Temp9 中的数据进行排序（升序、降序），生成临时中间表 Temp10。

这个过程比较耗费资源，而且如果数据量比多的话，还会使用到文件排序，就会更加费时了。

**LIMIT**

对 Temp10 进行分页，并取出其中需要的分页的数据，产生临时中间表 Temp11，发回给客户端。

### 3. 实例演示

下面使用 MySQL 8.0.29 社区版进行演示说明。

#### 3.1 表结构和数据

本次演示用到两张表，分别是存放商品信息的 product 表与存放商店信息的 shopproduct 表，其结构和数据分别如下：

* product 表结构

    ![product 表结构](https://cnd.qiniu.lin07ux.cn/markdown/1669995720)
    
    表中各字段分别表示：商品编号（具有唯一性）、商品名称、商品种类、商品销售单价、商品进货单价、商品信息登记日期。
    
* product 表数据

    ![product 表数据](https://cnd.qiniu.lin07ux.cn/markdown/1669995806)

* shopproduct 表结构

    ![shopproduct 表结构](https://cnd.qiniu.lin07ux.cn/markdown/1669995825)

    表中个字段分别表示：商店编号、商店名称、商品编号、商店内商品库存数量。
    
* shopproduct 表数据

    ![shopproduct 表数据](https://cnd.qiniu.lin07ux.cn/markdown/1669995905)

对应的 SQL 语句如下：

```sql
-- 创建 product 表
CREATE TABLE Product (
  product_id CHAR(4) NOT NULL COMMENT "商品编号",
  product_name VARCHAR(100) NOT NULL COMMENT "商品名称",
  product_type VARCHAR(32) NOT NULL COMMENT "商品种类",
  sale_price INTEGER COMMENT "商品销售单价",
  purchase_price INTEGER COMMENT "商品进货单价",
  regist_date DATE COMMENT "商品信息登记日期",
  PRIMARY KEY (product_id)
);

-- 向 product 表中插入具体记录
INSERT INTO Product VALUES ('0001', 'T恤衫', '衣服', 1000, 500,'2009-09-20');
INSERT INTO Product VALUES ('0002', '打孔器', '办公用品', 500,320, '2009-09-11');
INSERT INTO Product VALUES ('0003', '运动T恤', '衣服', 4000,2800, NULL);
INSERT INTO Product VALUES ('0004', '菜刀', '厨房用具', 3000,2800, '2009-09-20');
INSERT INTO Product VALUES ('0005', '高压锅', '厨房用具', 6800,5000, '2009-01-15');
INSERT INTO Product VALUES ('0006', '叉子', '厨房用具', 500,NULL, '2009-09-20');
INSERT INTO Product VALUES ('0007', '擦菜板', '厨房用具', 880,790, '2008-04-28');
INSERT INTO Product VALUES ('0008', '圆珠笔', '办公用品', 100,NULL, '2009-11-11');

-- 创建 shopproduct 表
CREATE TABLE ShopProduct (
  shop_id CHAR(4) NOT NULL COMMENT "商店编号",
  shop_name VARCHAR(200) NOT NULL COMMENT "商店名称",
  product_id CHAR(4) NOT NULL COMMENT "商品编号",
  quantity INTEGER NOT NULL COMMENT "商店内商品库存数量",
  PRIMARY KEY (shop_id, product_id)
);

-- 向 shopproduct 表中插入具体记录
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000A', '东京', '0001', 30);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000A', '东京', '0002', 50);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000A', '东京', '0003', 15);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000B', '名古屋', '0002', 30);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000B', '名古屋', '0003', 120);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000B', '名古屋', '0004', 20);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000B', '名古屋', '0006', 10);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000B', '名古屋', '0007', 40);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000C', '大阪', '0003', 20);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000C', '大阪', '0004', 50);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000C', '大阪', '0006', 90);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000C', '大阪', '0007', 70);
INSERT INTO ShopProduct (shop_id, shop_name, product_id, quantity) VALUES ('000D', '福冈', '0001', 100);
```

#### 3.2 完整 SQL

下面是完整的最终查询 SQL 语句：

```sql
SELECT DISTINCT
  IFNULL(shop_name, "所有店铺") AS "店铺名称",
  AVG(sale_price) AS "商品销售均价"
FROM
  shopproduct AS sp LEFT JOIN product AS p
ON
  sp.product_id = p.product_id
WHERE
  quantity < 100
GROUP BY
  shop_name
WITH
  rollup
HAVING
  AVG(sale_price) < 2000
ORDER BY
  "商品销售均价"
LIMIT 
  1,1;
```

这个 SQL 的业务目的为：

* 从商店表（shopproduct 表）中选取所有字段，通过左连接方式从商品表（product 表）中获取商店中在售商品的销售单价；
* 结算商店中库存数量小于 100 个商品销售单价的均值，然后过滤掉商品销售单价均值大于 2000 的商品，并计算所有店铺销售单价的均值；
* 最终结果按照商品销售额均价升序排序，并且进行分页，每页 1 条记录，并查看第二页的记录。

其执行的结果如下：

![最终查询结果](https://cnd.qiniu.lin07ux.cn/markdown/1669996388)

#### 3.3 FROM

在 MySQL 中，首先执行的是 FROM 子句，FROM 子句的完整部分其实是包含 JOIN 的，所以在 FROM 环节就有两张表，不做任何筛选，计算其笛卡尔积。

在最终的 SQL 语句中，与 FROM 相关的代码为：

```SQL
FROM
  shopproduct AS sp LEFT JOIN product AS p
```

其行为是，先获取 shopproduct 表的全部数据，然后获取 product 表的全部数据，计算两表的笛卡尔积，得到第一张中间表 Temp1，如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669996693)

> 在上图中，虽然展示的是字段名称，但实际上字段名的完整信息应该是“表名.字段名”，比如`shop_id`的完整命名应该是`sp.shop_id`。这一点对于两张表中的唯一字段来说意义不大，但是对于像`product_id`这种两张表中都存在的字段来说，表名的前缀是区分两个字段的关键，这是理解后续 ON 子句筛选条件的关键。

#### 3.4 ON

ON 子句起到的起始就是一个筛选过滤的功能，是在 Temp1 的基础上根据筛选条件进行筛选，得到符合条件的中间表。

完整 SQL 中关于 ON 子句的部分如下：

```sql
ON
  sp.product_id = p.product_id
```

也就是说，筛选条件为 sp 表中的`product_id`字段值等于 p 表中的`product_id`字段值。这个时候 SQL 做的事情就是对比 Temp1 表中的`sp.product_id`与`p.product_id`字段的值。

例如，第一条记录中`sp.product_id`的值为“0001”，`p.product_id`的值为“0008”，两者不相等，因此这条记录在这个环节就被 ON 子句过滤掉了。

按照 Temp1 的记录顺序一条条的执行筛选，最终保留符合筛选条件的记录就是 ON 子句产生的中间表 Temp2：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669997128)

可以看到，Temp2 中的两个 product_id 字段中的值都是一一对应的。

#### 3.5 JOIN

在这个环节中，根据 JOIN 方式的不同，会有不同的运行结果。这里主要阐述外连接的运行方式。

以最终完整的 SQL 语句为例，与 JOIN 相关的 SQL 如下：

```sql
FROM
  shopproduct AS sp LEFT JOIN product AS p
ON
  sp.product_id = p.product_id
```

由于选用的是左外连接，因此 SQL 在这一个环节的执行步骤如下：

* 首先遍历左表的所有行，也就是`shopproduct`表的所有行：

    ![](https://cnd.qiniu.lin07ux.cn/markdown/1669997280)

* 然后逐行将左表的所有记录在 Temp2 表中对比一遍，对比是否已经存在 Temp2 表中（红框中的数据是 Temp2 中要进行比较的区域）

    ![](https://cnd.qiniu.lin07ux.cn/markdown/1669997379)

* 如果某条记录不存在，则将该记录添加到 Temp2 中，最终生成 Temp3。

    由于左表中只有 4 个字段，Temp2 表中有 10 个字段，为了将缺失的左表记录添加到 Temp2 中，需要对缺失的字段用空值（即 NULL）进行填充。

在这个案例中，商店表遍历的结果在该 Temp2 表中全部存在的，因此不会添加空行。但是如果改成右外连接，那么结果就不一样了：

```sql
FROM
  shopproduct AS sp RIGHT JOIN product AS p
ON
  sp.product_id = p.product_id
```

此时，JOIN 会先遍历右表，遍历结果如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669997607)

红框中的数据是在 Temp2 中不存在的记录，此时在 Temp2 中对比的区域也相应发生了变化：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669997654)

仔细观察遍历的结果与 Temp2 表中的记录可以发现，右表中`product_id`为“0005”和“0008”的记录在 Temp2 表中是不存的，因为在 shopproduct 表中不存在`product_id`为“0005”和“0008”的记录，不满足 ON 子句中的`sp.product_id = p.product_id`条件，所以就被过滤掉了。

此时 JOIN 就会把这两条记录添加到 Temp2 表中，缺失字段用 NULL 填充，生成新的 Temp3 表：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669997770)

至此 JOIN 的执行基本上就结束了。

而如果 JOIN 了三张表或者更多表，则会逐个的进行数据填充，最终生成的结果作为 Temp3 表。

#### 3.6 WHERE

WHERE 同样是对标中的记录进行筛选。最终 SQL 中 WHERE 子句的筛选条件为：

```sql
WHERE
  quantity < 100
```

业务层面的含义就是筛选出库存小于 100 的商品记录。而 Temp3 表中，库存大于 100 的记录有两条：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669997925)

这两条记录不符合 WHERE 筛选条件，因此将其去除，将去除后的结果作为 Temp4 表：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669997961)

需要注意的是，在这个极端仍然没有对字段数量进行任何的删减。

#### 3.7 GROUP BY

最终代码中，GROUP BY 相关的代码如下：

```sql
GROUP BY
  shop_name
```

表示是对 Temp4 表的结果根据`shop_name`字段进行聚合分组。GROUP BY 的结果需要使用 EXCEL 展示，因为 MySQL 作为关系型数据库，一个“单元格”中只能有一个值，无法很好的展示出来聚合后的结果。也就是说，聚合生成的临时中间表 Temp5 是不能实际存在于 MySQL 中的。

聚合的结果如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669998222)

可以看到，这个结果中，除了`shop_name`字段之外，其余所有字段中均出现了两个以上的值。这时候就需要引入“组”的概念了：GROUP BY 将`shop_name`字段分为了三个组，每个组对应的字段中会有多个值。如果想让其能被正常使用，就必须用一种方式将多个取值变为一个值，这种方式就叫做聚合函数，也就是后面 HAVING 中将要讲到的。

#### 3.8 WITH

WITH 会生成一个或多个超组，其计算方式有两种：CUBE 和 ROLLUP。这两种计算方式的区别在于生成超组的方式不同。

由于 MySQL 支持 ROLLUP，因此就用 ROLLUP 进行演示，相关代码如下：

```sql
WITH
  rollup
```

在 Temp5 的基础上，进行汇总得到一个超组，这个超组的聚合建是空的，用 NULL 填充，其余字段则是所有记录的汇总。

Temp5 的数据加上这个超组，就产生了临时中间表 Temp6，如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669998602)

#### 3.9 HAVING

由于在 HAVING 环境还没有进行字段的选取与聚合，因此 HAVING 子句中能使用的元素只有三类：

* 常数
* 聚合函数
* GROUP BY 子句中指定的列表（即聚合建）

在这个阶段直接使用未经局和处理的字段去做运算时，会有多个值要进行运算。就比如在 Temp6 表的基础上，去筛选`product_id = "0003"`的组，SQL 没有办法执行，因为在在对比“0003”与组对应的`product_id`的值的时候，无法确定应该使用多个值中的哪个，于是就会报错。

对于最终的查询 SQL 来说，HAVING 相关代码如下：

```sql
HAVING
  AVG(sale_price) < 2000
```

从 SQL 的角度来看，HAVING 子句会按照组分别对组内的`sale_price`字段的值进行均值聚合计算，然后将每个组对应的计算结果与 2000 进行比较，保留其中值小于 2000 的组的所有数据作为 Temp7。

Temp6 中的四个组对应的均值聚合计算结果如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669999249)

其中，销售单价均值小于 2000 的有“东京”组、“名古屋”组以及所有店铺的汇总组。因此，最终的 Temp7 的数据如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669999322)

需要注意的是，在这个时候，中间表都是以“组”的形式存在的，而非记录的形式。

#### 3.10 SELECT

SELECT 是字段选取阶段，选择最终需要返回给客户端的字段值。最终 SQL 中与 SELECT 相关的代码如下：

```sql
SELECT DISTINCT
  IFNULL(shop_name, "所有店铺") AS "店铺名称",
  AVG(sale_price) AS "商品销售均价"
```

这里选取了`shop_name`和`sale_price`字段，其中：

* `shop_name`字段为聚合键，在每条记录中国仅有一个值，因此可以直接选用；
* `sale_price`字段由于存在多个值，必须选用一种聚合方式输出一个唯一的值，在这里选用的方式就是求平均值。

因此，最终输出的 Temp8 表中仅有两个字段，且是以表记录的形式存在，而非组的形式存在：

![](https://cnd.qiniu.lin07ux.cn/markdown/1670069878)

#### 3.11 DISTINCT

DISTINCT 子句起到的去重的效果，针对特定的字段值进行唯一去重。

不过，在 Temp8 中由于不存在重复的记录，因此最终输出的 Temp9 表就与 Temp8 表的数据是一样的：

![](https://cnd.qiniu.lin07ux.cn/markdown/1670069963)

#### 3.12 ORDER BY

ORDER BY 子句就是使用指定的字段的值对记录进行升序或降序排序，而且可以指定做个排序方式，从左向右的排序优先级逐步降低。

在最终的 SQL 语句中，排序字段选择的是商品销售均价字段，因此将会对 Temp9 按照该字段的值进行升序排序，生成 Temp10，依次为：

1. 名古屋 1220.0000
2. 所有店铺 1705.4545
3. 东京 1833.3333

#### 3.13 LIMIT / TOP

最后一步是使用 LIMIT 进行分页，并取指定分页的数据返回给客户端。

在最终 SQL 语句中，LIMIT 子句将 Temp10 表按照每页 1 条记录的方式进行分页，并获取其中的第二页的数据，也就是其中“所有店铺”这一条记录：

![](https://cnd.qiniu.lin07ux.cn/markdown/1670070381)

到这里，最终的 SQL 语句就执行完成了，最终客户端获取到的数据就是“所有店铺”这行记录的数据了。

