从数据库查询大量数据时会出现内容不够的提示：

```
PHP Fatal error: Allowed memory size of 268 435 456 bytes exhausted
```

这个问题在 PHP 的官方网站上叫缓冲查询和非缓冲查询(Buffered and Unbuffered queries)。PHP 的查询缺省模式是缓冲模式。也就是说，查询数据结果会一次全部提取到内存里供 PHP 程序处理。这样给了 PHP 程序额外的功能，比如说，计算行数，将指针指向某一行等。更重要的是程序可以对数据集反复进行二次查询和过滤等操作。但这种缓冲查询模式的缺陷就是消耗内存，也就是用空间换速度。

相对的，另外一种 PHP 查询模式是非缓冲查询，数据库服务器会一条一条的返回数据，而不是一次全部返回，这样的结果就是 PHP 程序消耗较少的内存，但却增加了数据库服务器的压力，因为数据库会一直等待 PHP 来取数据，一直到数据全部取完。

很显然，缓冲查询模式适用于小数据量查询，而非缓冲查询适应于大数据量查询。

对于 PHP 的缓冲模式查询大家都知道，下面列举的例子是如何执行非缓冲查询 API。

### 方法一：MYSQLI_USE_RESULT

```php
<?php
$mysqli  = new mysqli("localhost", "my_user", "my_password", "world");
$uresult = $mysqli->query("SELECT Name FROM City", MYSQLI_USE_RESULT);
if ($uresult) {
   while ($row = $uresult->fetch_assoc()) {
       echo $row['Name'] . PHP_EOL;
   }
}
$uresult->close();
```

### 方法二：PDO::MYSQL_ATTR_USE_BUFFERED_QUERY

```php
<?php
$pdo = new PDO("mysql:host=localhost;dbname=world", 'my_user', 'my_pass');
$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
$uresult = $pdo->query("SELECT Name FROM City");
if ($uresult) {
   while ($row = $uresult->fetch(PDO::FETCH_ASSOC)) {
       echo $row['Name'] . PHP_EOL;
   }
}
```

### 方法三: mysql_unbuffered_query

```php
<?php
$conn = mysql_connect("localhost", "my_user", "my_pass");
$db   = mysql_select_db("world");
$uresult = mysql_unbuffered_query("SELECT Name FROM City");
if ($uresult) {
   while ($row = mysql_fetch_assoc($uresult)) {
       echo $row['Name'] . PHP_EOL;
   }
}
```

### 转摘
[如何解决PHP查询大量数据内存耗尽的问题](http://blog.csdn.net/xiaoxiong_web/article/details/50577359)

