> 转摘：[彻底解决MySQL中的乱码问题](https://mp.weixin.qq.com/s/58Y11c8cLN1uDfHn_6lyAg)

## 一、字符集概念

### 1.1 计算机表示字符

“字符”其实是面向人类的概念，计算机中所有的数据、字符、对象都是一串二进制数字。为了让计算机能够正确的展现一串二进制数字，需要让计算机按照一定的编码规则来解析二进制数字，这个规则就是字节编码。对字符串数据来说，这个规则就是字符集编码。

在计算机表示一个字符串时，需要同时附带上对应的字符集编码。

以 C++ 语言为例，一个字符串会使用类似如下的方式表示：

```c++
class String {
    byte* content;
    CHARSET_INFO* charset;
}
```

比方说，现在有一个以`utf-8`字符集编码的汉字`'我'`，那么意味着计算机中不仅仅要存储`'我'`的 utf-8 二进制编码`0xE68891`，还要存储它使用什么字符集编码的信息，类似如下：

```c++
{
    content: 0xE68891;
    charset: utf8;
}
```

字符在不同字符集编码中的二进制形式可能相同，也可能不同。计算机内部包含将一种字符集转换成另一种字符集的函数库，也就是某个字符在某种字符集下的编码可以很顺利的转换为另一种字符集的编码，将这个过程称之为**字符集转换**。

比如说，可以将上述采用 utf-8 字符集编码的字符`'我'`，转换成 gbk 字符集编码的形式，就变成了这样：

```c++
{
    content: 0xCED2;
    charset: gbk;
}
```

### 1.2 乱码的形成

如果附带的字符集编码与其二进制表示不一致，那么计算机表示出来的结果就可能会造成错乱，也就是乱码了。

比如，字符的二进制表示是`0xE68891`，但附带的字符集编码为 gbk，那么就无法得到正确的结果，计算机展示的结果是一个其他的字符甚至是一个无法表示的字符。这样人看到的结果就是错误的、混乱的，也就是出现了乱码。

同样的，在 MySQL 执行 SQL 语句并返回结果的过程中，涉及到人机交互、MySQL 客户端与 MySQL 服务器端数据传递等一系列的数据交换。如果在这个过程中没有沟通好各自的字符集，那么最终展示的结果就可能出现乱码了。

## 二、MySQL 客户端和服务器的通信流程

通常情况下，会按照如下的步骤使用 MySQL：

1. 启动客户端并连接到服务器
2. 客户端发送请求
3. 服务器接收到请求
4. 服务器处理请求
5. 服务器处理请求完毕生成对该客户端的响应
6. 客户端接收到响应

下面详细分析每个步骤中都影响到了哪些字符集。

### 2.1 启动客户端并连接到服务器过程

每个 MySQL 客户端都维护着一个客户端默认字符集，这个默认字符集按照下边的套路进行取值：

* 自动检测操作系统使用的字符集

    MySQL 客户端会在启动的时候检测操作系统当前使用的字符集，并按照一定规则映射成为 MySQL 支持的字符集。通常是操作系统当前使用什么字符集，就映射为什么字符集，一些特殊情况除外，比如，操作系统当前使用的 ASCII 字符集，就会被映射为 Latin1 字符集。
    
    * 当使用类 UNIX 操作系统时

        此时会调用操作系统提供的`nl_langinfo(CODESET)`函数来获取操作系统当前正在使用的字符集，而这个函数的结果是依赖`LC_ALL`、`LC_CTYPE`、`LANG`三个环境变量的。这三个环境变量的优先级依次降低，也就说：如果设置了`LC_ALL`那么就以`LC_ALL`为准；否则，如果设置了`LC_CTYPE`就以`LC_CTYPE`为准；否则就以`LANG`为准。如果这三个环境变量都没有设置，那么`nl_langinfo(CODESET)`函数将返回操作系统默认的字符集，比方说在我的`macOS 10.15.3`操作系统中，该默认字符集为`US-ASCII`。
        
        当使用`export LC_ALL=zh_CN.UTF-8`将环境变量`LC_ALL`设置为`zh_CN.UTF-8`时，此时启动 MySQL 客户端的话，MySQL 客户端就会检测到这个操作系统使用的是 utf8 字符集，然后 MySQL 客户端的默认字符集就会被设置称为 utf8。
        
        另外，还需要强调的是，在使用命令行终端启动 MySQL 客户端时，命令行终端一般也会有一个自己使用的字符集，比如在我的 Mac 上使用 iTerm2 作为终端，打开：`Preferences->Profiles->Terminal`选项卡，可以看到 iTerm2 使用 utf8 来展示字符：

        ![](http://cnd.qiniu.lin07ux.cn/markdown/1589162296083.png)
        
        一般情况下，会将终端和操作系统的字符集编码保持一致。如果两者的编码不一致，那么输入的字符可能都无法正常在屏幕中展示。比如说，将系统的`LC_ALL`属性设置成 GBK，再向黑框框上输入汉字的话，屏幕都不会显示了，就如下图所示，敲击了汉字`'我'`的效果：

        ![](http://cnd.qiniu.lin07ux.cn/markdown/1589162418953.png)

    * 当使用 Windows 操作系统时

        此时会调用操作系统提供的`GetConsoleCP`函数来获取操作系统当前正在使用的字符集。在 Windows 里，会把当前`cmd.exe`命令行终端使用的字符集映射到一个数字，称之为代码页(`code page`)，可以通过右键点击`cmd.exe`标题栏，然后点击`属性->选项`，如下图所示，当前代码页的值是 936，代表当前`cmd.exe`使用 gbk 字符集：

        ![](http://cnd.qiniu.lin07ux.cn/markdown/1589162528948.png)

        > 也可以运行`chcp`命令直接看到当前 code page 是什么。

        在 cmd.exe 中启动 MySQL 客户端时，MySQL 客户端就会检测到这个操作系统使用的是 gbk 字符集，并将 MySQL 客户端默认字符集设置称为 gbk 了。如果修改 cmd.exe 的代码页的值为 65001(utf8 字符集的 code page)，再在 cmd.exe 中启动 MySQL 客户端，那么 MySQL 客户端的默认字符集就会变成 utf8。

* 不支持自动检测

    如果 MySQL 不支持自动检测到的操作系统当前正在使用的字符集，或者在某些情况下不允许自动检测的话，MySQL 会使用它自己的内建的默认字符集作为客户端默认字符集。这个内建的默认字符集在 MySQL 5.7 以及之前的版本中是`Latin1`，在 MySQL 8.0 中修改为了`utf8mb4`。

* 使用了`default-character-set`启动参数

    如果启动 MySQL 客户端时使用了`default-character-set`启动参数，那么客户端的默认字符集将不再是检测操作系统当前正在使用的字符集了，而是直接使用该启动参数时所指定的字符集。
    
    比如，使用如下的命令来启动 MySQL 客户端：

    ```shell
    mysql --default-character-set=utf8
    ```

    那么不论使用什么操作系统、也不论操作系统目前使用的字符集是什么，都将会以 utf8 作为 MySQL 客户端的默认字符集。

在确认了 MySQL 客户端默认字符集之后，客户端就会向服务器发起登录请求，传输一些诸如用户名、密码等信息。在这个请求里就会包含客户端使用的默认字符集是什么。服务器接收到后就命令了稍后客户端即将发送过来的请求是采用什么字符集编码的，并将响应用对应的字符集编码后再传输给客户端。

> 其实服务器在明白了客户端使用的默认字符集之后，就会将`character_set_client`、`character_set_connection`以及`character_set_result`这几个系统变量均设置为该值。

### 2.2 客户端发送请求

登录成功之后，就可以在命令行终端中输入 MySQL 语句，然后将该语句作为请求发送到服务器。那 MySQL 客户端发送的语句（本质是字符串）是采用什么字符集编码的呢？这其实涉及到应用程序和操作系统直接的交互。MySQL 客户端程序其实就是一个应用程序，它从命令行终端中读取数据其实是需要调用操作系统的读取接口的。在不同的操作系统中，调用的读取接口其实是不同的，需要分情况讨论：

* 对于类 UNIX 系统

    在命令行终端中输入字符时，改字符采用的编码字符集其实是操作系统当前使用的字符集。比方说当前`LC_ALL`环境变量的值为`zh_CN.UTF-8`，那么意味着终端中的字符其实是使用`utf8`字符集进行编码。稍后 MySQL 客户端程序将调用操作系统提供的`read`函数从终端中读取数据（其实就是所谓的从标准输入流中读取数据），所读取的数据就是采用`utf8`字符集进行编码的字节序列，稍后将该字节序列作为请求内容发送到服务器。
    
    这样其实就会产生一个问题：如果 MySQL 客户端的默认字符集和操作系统当前正在使用的字符集不同，那么就会产生比较尴尬的结果。
    
    比如说，在启动 MySQL 客户端的时候携带了`--default-character-set=gbk`启动参数，那么 MySQL 客户端的默认字符集就会被设置成 gbk。而如果此时操作系统采用的字符集是 utf8，那么 MySQL 客户端读取到的就是 utf8 的字节序列。比如，输入的语句中包含汉字`'我'`，那么 MySQL 客户端调用操作系统的`read`函数得到的字节序列其实是`0xE68891`，然后将`0xE68891`发送给服务器。而服务器认为 MySQL 客户端发送过来的请求都是采用 gbk 进行编码的，这样就会产生问题。（当然，这仅仅是发生乱码问题的前奏，并不意味着产生乱码，乱码只有在最后一步，也就是客户端应用程序将服务器返回的数据写到黑框框里时才会发生。）

* 对于 Windows 操作系统

    在 Windows 操作系统中，从命令行终端中读取数据调用的是 Windows 提供的`ReadConsoleW`函数。在该函数执行后，MySQL 客户端会得到一个宽字符数组（其实就是一组 16 位的 UNICODE），然后客户端需要把该宽字符数组再次转换成客户端使用的默认字符集编码的字节序列，然后才将该字节序列作为请求的内容发送到服务器。这样在类 UNIX 操作系统中可能产生的问题，在 Windows 系统中却可以避免。
    
    比方说：在启动客户端是携带了`--default-character-set=gbk`的启动参数，那么客户端的默认字符集将会被设置成 gbk，假如此时操作系统采用的字符集是 utf8。假如输入语句中包含汉字`'我'`，那么客户端调用`ReadConsoleW`函数先读到一个代表着`我`字的宽字符数组，之后又将其转换为客户端的默认字符集，也就是 gbk 字符集编码的数据`0xCED2`，然后将`0xCED2`发送到服务器。此时服务器也认为客户端发送过来的请求就是采用 gbk 进行编码的，这样就完全正确了～

### 2.3 服务器接收请求

服务器接收到的请求本质上就是一个字节序列，服务器将其看做是采用系统变量`character_set_client`代表的字符集进行编码的字节序列。

> `character_set_client`是一个 SESSION 级别的系统变量。也就是说，每个客户端和服务器建立连接后，服务器都会为该客户端维护一个单独的`character_set_client`变量。每个客户端在登录服务器的时候都会将其默认字符集通知给服务器，然后服务器就会设置该客户端专属的`character_set_client`。

可以使用`SET`命令单独修改`character_set_client`对饮的值，就像这样：

```sql
SET character_set_client=gbk;
```

需要注意的是，`character_set_client`对应的字符集一定要包含请求中的字符，比方说把`character_set_client`设置成`ascii`，而请求中发送了一个汉字`'我'`，将会发生这样的事情：

```sql
mysql> SET character_set_client=ascii;
Query OK, 0 rows affected (0.00 sec)

mysql> SHOW VARIABLES LIKE 'character%';
+--------------------------+------------------------------------------------------+
| Variable_name            | Value                                                |
+--------------------------+------------------------------------------------------+
| character_set_client     | ascii                                                |
| character_set_connection | utf8                                                 |
| character_set_database   | utf8                                                 |
| character_set_filesystem | binary                                               |
| character_set_results    | utf8                                                 |
| character_set_server     | utf8                                                 |
| character_set_system     | utf8                                                 |
| character_sets_dir       | /usr/local/Cellar/mysql/5.7.21/share/mysql/charsets/ |
+--------------------------+------------------------------------------------------+
8 rows in set (0.00 sec)

mysql> SELECT '我';
+-----+
| ??? |
+-----+
| ??? |
+-----+
1 row in set, 1 warning (0.00 sec)

mysql> SHOW WARNINGS \G
*************************** 1. row ***************************
  Level: Warning
   Code: 1300
Message: Invalid ascii character string: '\xE6\x88\x91'
1 row in set (0.00 sec)
```

如图所示，最后提示了`'E6、88、91'`并不是正确的 ascii 字符。

### 2.4 服务器处理请求

#### 2.4.1 字符集转换

服务器在处理请求是会将请求中的字符再次转换为一种特定的字符集，该字符集由 MySQL 服务器系统变量`character_set_connection`表示，该系统变量也是 SESSION 级别的。每个 MySQL 客户端在登录 MySQL 服务器的时候都会将其默认字符集通知给服务器，然后服务器设置该客户端专属的`character_set_connection`。当然，也可以通过`SET`命令单独修改这个`character_set_connection`系统变量。

比如：客户端发送给服务器的请求中包含字节序列`0xE68891`，然后服务器针对该客户端的系统变量`character_set_client`为`utf8`，那么此时服务器就知道该字节序列其实是代表汉字`'我'`，如果此时服务器针对该客户端的系统变量`character_set_connection`为`gbk`，那么服务器还需要将该字符转换为采用`gbk`字符集编码的形式，也就是`0xCED2`。

这个转换初一看可能有点多此一举的意思，但是可以考虑下下面的这个查询语句：

```sql
SELECT 'a' = 'A';
```

这个查询语句的返回结果应该是`TRUE`还是`FALSE`？其实结果是不确定。这是因为并不知道比较两个字符串的大小到底比的是什么！应该从两个方面考虑：

* 考虑一：这些字符串是采用什么字符集进行编码的呢？
* 考虑二：在确定了编码这些字符串的字符集之后，也就意味着每个字符串都会映射到一个字节序列，那么怎么比较这些字节序列呢？是直接比较它们二进制的大小，还是有别的什么比较方式？比方说`'a'`和`'A'`在 utf8 字符集下的编码分别为`0x61`和`0x41`，那么`'a' = 'A'`是应该直接比较`0x61`和`0x41`的大小呢，还是将`0x61`减去`0x20`之后再比较大小呢？其实这两种比较方式都可以，每一种比较方式我们都称作一种比较规则(`collation`)。

#### 2.4.2 字符集和比较规则

MySQL 中支持若干种字符集，可以使用`SHOW CHARSET`命令查看。每一种字符集又对应着若干种比较规则，可以使用`SHOW COLLATIION`查看。以 utf8 字符集为例（太多了，只展示几个）：

```mysql
mysql> SHOW COLLATION WHERE Charset='utf8';
+--------------------------+---------+-----+---------+----------+---------+
| Collation                | Charset | Id  | Default | Compiled | Sortlen |
+--------------------------+---------+-----+---------+----------+---------+
| utf8_general_ci          | utf8    |  33 | Yes     | Yes      |       1 |
| utf8_bin                 | utf8    |  83 |         | Yes      |       1 |
| utf8_unicode_ci          | utf8    | 192 |         | Yes      |       8 |
| utf8_icelandic_ci        | utf8    | 193 |         | Yes      |       8 |
| utf8_latvian_ci          | utf8    | 194 |         | Yes      |       8 |
| utf8_romanian_ci         | utf8    | 195 |         | Yes      |       8 |
+--------------------------+---------+-----+---------+----------+---------+
27 rows in set (0.00 sec)
```

其中`utf8_general_ci`是 utf8 字符集默认的比较规则，在这种比较规则下是不区分大小写的。不过`utf8_bin`这种比较规则就是区分大小写的。

在将请求中的字节序列转换为`character_set_connection`对应的字符集编码的字节序列后，也要配套一个对应的比较规则，这个比较规则就由`collation_connection`系统变量来指定。

现在通过`SET`命令来修改一下`character_set_connection`和`collation_connection`的值，分别设置为`utf8`和`utf8_general_ci`，然后比较一下`'a'`和`'A'`：

```mysql
mysql> SET character_set_connection=utf8;
Query OK, 0 rows affected (0.00 sec)

mysql> SET collation_connection=utf8_general_ci;
Query OK, 0 rows affected (0.00 sec)

mysql> SELECT 'a' = 'A';
+-----------+
| 'a' = 'A' |
+-----------+
|         1 |
+-----------+
1 row in set (0.00 sec)
```

可以看到在这种情况下这两个字符串就是相等的。

再通过`SET`命令来修改一下`character_set_connection`和`collation_connection`的值，分别设置为`utf8`和`utf8_bin`，然后比较一下`'a'`和`'A'`：

```mysql
mysql> SET character_set_connection=utf8;
Query OK, 0 rows affected (0.00 sec)

mysql> SET collation_connection=utf8_bin;
Query OK, 0 rows affected (0.00 sec)

mysql> SELECT 'a' = 'A';
+-----------+
| 'a' = 'A' |
+-----------+
|         0 |
+-----------+
1 row in set (0.00 sec)
```

可以看到在这种情况下这两个字符串就是不相等的。

当然，如果不需要单独指定将请求中的字符串采用何种字符集以及比较规则的话，并不用太关心`character_set_connection`和`collation_connection`设置成啥。不过需要注意一点，就是`character_set_connection`对应的字符集必须包含请求中的字符，这个和`character_set_client`是一样的。

### 2.5 服务器响应

为了故事的顺利发展，先创建一个表：

```mysql
CREATE TABLE t (
    c VARCHAR(100)
) ENGINE=INNODB CHARSET=utf8;
```

然后向这个表插入一条记录：

```mysql
INSERT INTO t VALUE('我');
```

现在这个表中的数据就如下所示：

```sql
mysql> SELECT * FROM t;
+------+
| c    |
+------+
| 我   |
+------+
1 row in set (0.00 sec)
```

该表采用的是 utf8 字符集编码，所以字符`'我'`在底层存放的其实是`0xE68891`。将其读取出来后，需要发送给客户端，是不是直接将`0xE68891`发送到客户端呢？这就不一定了，取决于`character_set_result`系统变量的值。服务器会将该响应转换为`character_set_result`系统变量对应的字符集编码后的字节序列发送给客户端。

`character_set_result`系统变量也是一个 SESSION 级别的变量。每个客户端在登录服务器的时候都会将客户端的默认字符集通知给服务器，然后服务器设置该客户端专属的`character_set_result`。当然，也可以使用`SET`命令来修改`character_set_result`的值。

> 同样，也需要注意，`character_set_result`对应的字符集应该包含响应中的字符。

一般情况下`character_set_client`、`character_set_connection`和`character_set_result`这三个系统变量应该和客户端的默认字符集相同。使用`SET names`命令可以一次性修改这三个系统变量：

```mysql
SET NAMES 'charset_name';

-- 该语句和下边的三个语句等效：
-- SET character_set_client = 'charset_name';
-- SET character_set_connection = 'charset_name';
-- SET character_set_result = 'charset_name';
```

不过这里需要大家特别注意，`SET names`语句并不会改变客户端的默认字符集！

### 2.6 客户端接收响应

客户端接收到的响应其实仍然是一个字节序列。客户端是如何将这个字节序列写到命令行终端中的呢？这又涉及到应用程序和操作系统之间的一次交互。

* 类 UNIX 系统

    对于类 UNIX 系统来说，MySQL 客户端向命令行终端中写入数据使用的是操作系统提供的`fputs`、`putc`或者`fwrite`函数，这些函数基本上相当于之间就把接收到的字节序列写到了终端中（请注意我们用词：*基本上相当于*，其实内部还会做一些工作，但是我们这里就不想再关注这些细节了）。此时如果该字节序列实际的字符集和终端展示字符所使用的字符集不一致的话，就会发生所谓的乱码（大家注意，这个时候和操作系统当前使用的字符集没啥关系）。
    
    比方说，在启动 MySQL 客户端的时候使用了`--default-character-set=gbk`的启动参数，那么服务器的`character_set_result`变量就是`gbk`。然后再执行`SELECT * FROM t`语句，那么服务器就会将字符`'我'`的 gbk 编码，也就是`0xCDE2`发送到客户端，客户端直接把这个字节序列写到终端中。如果终端此时采用 utf8 字符集展示字符，那自然就会发生乱码。
    
* Windows 系统

    对于 Windows 系统来说，MySQL 客户端向命令行终端中写入数据使用的是操作系统提供的`WriteConsoleW`函数，该函数接收一个宽字符数组，所以 MySQL 客户端调用它的时候需要显式地将它从服务器收到的字节序列按照客户端默认的字符集转换成一个宽字符数组。正因为这一步骤的存在，所以可以避免上边类 UNIX 系统中提到的一个问题。
    
    比方说：在启动 MySQL 客户端的时候使用了`--default-character-set=gbk`的启动参数，那么服务器的`character_set_result`变量就是 gbk。执行`SELECT * FROM t`语句后，服务器就会将字符`'我'`的 gbk 编码，也就是`0xCDE2`发送到客户端。客户端将这个字节序列先从客户端默认字符集，也就是 gbk 的编码，转换成一个宽字符数组，然后再调用`WriteConsoleW`函数写到终端，终端自然可以把它显示出来。

## 三、乱码出现的原因

介绍了各个步骤中涉及到的各种字符集及转换，下边总结一下遇到乱码时应该如何分析。

1. 使用的是什么操作系统

    * 类 UNIX 系统

        对于类 UNIX 系统用户来说，要搞清楚使用的命令行终端到底是使用的什么字符集展示字符，比如 iTerm2 中的`character encoding`属性。
        
        同时还要搞清楚操作系统当前使用的是什么字符集。可以使用`locale`命令查看：
        
        ```shell
        > locale
        LANG=""
        LC_COLLATE="zh_CN.UTF-8"
        LC_CTYPE="zh_CN.UTF-8"
        LC_MESSAGES="zh_CN.UTF-8"
        LC_MONETARY="zh_CN.UTF-8"
        LC_NUMERIC="zh_CN.UTF-8"
        LC_TIME="zh_CN.UTF-8"
        LC_ALL="zh_CN.UTF-8"
        ```

        没有什么特别的需求的话，一般要保证上述两个字符集是相同的，否则可能连汉字都无法输入进去。

    * Windows 系统

        搞清楚命令行终端 cmd.exe 的代码页是什么，也就是操作系统当前使用的字符集是什么。

2. 搞清楚 MySQL 客户端的默认字符集是什么

    启动 MySQL 客户端的时候有没有携带`--default-character-set`参数。如果携带了，那么 MySQL 客户端默认字符集就一该参数的值为准，否则需要分析操作系统当前使用的字符集是什么。

3. 搞清楚客户端发送请求时是以什么字符集编码请求的

    * 对于类 UNIX 系统来说，可以认为请求就是采用操作系统当前所使用的字符集进行编码的。

    * 对于 Windows 系统来说，可以认为请求就是采用客户端默认字符集进行编码的。

4. 确认 MySQL 服务器中当前会话的三个字符集是什么

    通过执行`SHOW VARIABLES LIKE 'character%'`命令搞清楚下面三个服务器系统变量的值是什么：

    * `character_set_client`：服务器是怎样认为客户端发送过来的请求是采用何种字符集编码的
    * `character_set_connection`：服务器在运行过程中会采用何种字符集编码请求中的字符
    * `character_set_result`：服务器会将响应使用何种字符集编码后再发送给客户端的

5. 确认客户端收到响应之后如何展示结果

    对于服务器发送过来的字节序列来说：
    
    * 在类 UNIX 系统上，可以认为会把该字节序列直接写到终端中，此时应该搞清楚终端到底是采用何种字符集展示数据的。

    * 在 Windows 系统上，该字节序列会被认为是由客户端字符集编码的数据，然后再转换成宽字符数组写入到终端中。


