### 子查询分类

**子查询**又称内部，而包含子查询的语句称之外部查询（又称主查询）。所有的子查询可以分为两类，即*相关子查询*和*非相关子查询*：

1. 非相关子查询是独立于外部查询的子查询，子查询总共执行一次，执行完毕后将值传递给外部查询。
2. 相关子查询的执行依赖于外部查询的数据，外部查询执行一行，子查询就执行一次。

故**非相关子查询比相关子查询效率高**。


下面分别是非相关查询和相关查询的示例：

```sql
# 非相关
SELECT EMPNO, LASTNAME
　　FROM EMPLOYEE 
　　WHERE WORKDEPT = 'A00' 
　　AND SALARY > (SELECT AVG(SALARY) 
　　　　　　　　　　　　 FROM EMPLOYEE 
　　　　　　　　　　　　 WHEREWORKDEPT = 'A00');

# 相关　　　　　　　　 
SELECT E1.EMPNO, E1.LASTNAME, E1.WORKDEPT 
　　FROM EMPLOYEE E1 
　　WHERE SALARY > (SELECT AVG(SALARY) 
　　　　　　　　　　　　　 FROM EMPLOYEE E2 
　　　　　　　　　　　　　 WHERE E2.WORKDEPT = E1.WORKDEPT) 
　　ORDER BY E1.WORKDEPT
```


### 子查询介绍
子查询自身可以包含一个或多个子查询。一个语句中也能嵌套任意多个子查询。但是建议尽量少用子查询。

在包括相关子查询（也称为重复子查询）的查询中，子查询依靠外部查询获得值。

如果子查询和外部查询引用的是同一个表，则可被表述为**自联接**。

在外部查询中，可以使用`EXISTS`来检查子查询的结果是否存在。此时该子查询其实并不返回任何表字段数据，而是仅仅返回 ture 或 false。比如下面就是这样的示例：

```sql
SELECT last_name, first_name
    FROM authors
    WHERE EXISTS (SELECT * 
        FROM publishers
        WHERE authors.city = publishers.city);
```

### 示例
1. 返回 Orders 表中活动的最后一天生成的所有订单
    涉及到的表`orders`。
    
    该需求仅涉及一个表，所以可以使用自联接。而且表中的最后一天在查询的时候是一个固定的值，所以可以使用非相关子查询：
    
    ```sql
    SELECT orderid, orderdate, custid, empid
        FROM orders
        WHERE orderdate in (SELECT max(orderdate
            FROM orders);
    ```

2. 返回2008年5月1号（包括这一天）以后没有处理过订单的雇员。
    涉及到表：`employees`表和`orders`表。
    
    首先，需要找到订单表中2008年5月1日之后（包含当天）的订单，并选择订单记录中的雇员的id。
    
    然后在雇员表中找到id不在上一步中获取到的id记录中的雇员，并返回其相关信息。
    
    ```sql
    SELECT empid, firstname, lastname
        FROM employees
        WHERE employees.empid NOT IN (SELECT empid
            FROM orders
            WHERE orderdate >= '2008-05-01');
    ```
    
3. 返回订购了第12号产品的客户
    涉及的表：`customers`、`orders`、`order_details`。
    
    首先，需要从 order_details 表中找到产品id是12的订单号。
    
    然后在 orders 表中根据获取到的订单号找到订单对应的客户id。
    
    再使用客户id从 customers 表中获取对应的客户信息。
    
    ```sql
    SELECT custid, companyname
        FROM customers AS c
        WHERE EXISTS (SELECT *
            FROM orders AS o
            WHERE o.custid = c.id
                AND EXISTS (SELECT *
                    FROM order_details AS od
                    WHERE od.orderid = o.id
                        ADN od.productid = 12)
            );
    ```


