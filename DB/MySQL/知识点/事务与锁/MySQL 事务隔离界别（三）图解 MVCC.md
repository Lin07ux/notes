> 转摘：[三分钟图解 MVCC，看一遍就懂](https://mp.weixin.qq.com/s/7MEAUOfWIKxEiNa9Mm37TA)

MySQL InnoDB 存储引擎在 READ COMMITTED 和 REPEATABLE READ 事务隔离级别下会开启一致性非锁定读。而一致性非锁定读是通过 MVCC(Multi Version Concurrency Control，多版本并发控制) 来实现的。事实上，MVCC 没有一个统一的实现标准，所以各个存储引擎的实现机制不尽相同。

> 所谓一致性非锁定读就是每行记录可能存在多个历史版本，多版本之间串联起来形成一条版本链。这样，不同时刻启动的事务可以无锁的访问到不同版本的数据。

## 一、undo log

### 1.1 undo log 版本链

InnoDB 存储引擎中 MVCC 的实现是通过`undo log`来完成的。

简单理解，`undo log`就是每次操作的反向操作。比如，当前事务执行了一个插入`id = 100`的记录操作，那么其对应的 undo log 中存储的就是删除`id = 100`的记录的操作（实际上，插入操作可以不记录 undo log，即表示删除插入的数据）。

所以，这里用多版本来形容并不是非常准确，因为 InnoDB 并不会真正地开辟空间存储多个版本的行记录，只是借助 undo log 记录每次写操作的反向操作。

也就是说，B+ 索引树上对应的记录只会有一个最新版本，只不过 **InnoDB 可以根据 undo log 得到数据的历史版本**，从而实现多版本控制。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635147926985-5713c0053bec.jpg)

### 1.2 undo log 与行记录

undo log 与具体的行记录之间是通过行中的隐藏字段建立关联的：

**InnoDB 存储引擎中每行记录都拥有两个隐藏的字段：`trx_id`和`roll_pointer`，前者表示最近更新这行记录的事务 ID，后者指向之前生成的 undo log**。

例如，在`user`表中，`id = 100`的事务 A 插入一条行记录`id = 1, username = "Jack", age = 18)`，那么，这行记录的两个隐藏字段`trx_id = 100`、`roll_pointer`指向一个空的 undo log（因为在这之前并没有事务操作`id = 1`的这行记录）。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635148245118-3a9444bef23f.jpg)

然后，`id = 200`的事务 B 修改了这条行记录，把`age = 18`修改成了`age = 20`。于是，这条行记录的隐藏字段就发生了变化：`trx_id = 200`、`roll_pointer`指向事务 A 对应的 undo log：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635148353760-031ff6587287.jpg)

接着，`id = 300`的事务 C 再次修改了这条行记录，把`age = 20`修改成了`age = 30`，如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635148395458-08fbce5fcfd2.jpg)

可以看到，每次修改行记录都会更新`trx_id`和`roll_pointer`这两个隐藏字段，之前的多个数据快照对应的 undo log 会通过`roll_pointer`指针串联起来，从而形成一个**版本链**。

在 InnoDB 存储引擎中，undo log 只分为两种：

* `insert undo log`：在`insert`操作中产生的 undo log；
* `update undo log`：在`update`和`delete`操作中产生的 undo log。

> `select`操作是不会产生 undo log 的。

事实上，由于事务隔离性的要求，`insert`操作的记录，只对事务本身可见，对其他事务不可见。也即插入操作不会对已经存在的记录产生影响，所以也就不存在并发情况的问题。也就是说：**MVCC 这个机制其实就是靠 update undo log 实现的**，和 insert undo log 基本上没啥关系。

## 二、ReadView 机制

**ReadView 机制就是用来判断当前事务能够看见行记录的哪些版本的**。

一个 ReadView 主要包含如下几部分：

* `m_ids`：生成 ReadView 时有那些处于执行中还未提交的事务（称为*“活跃事务”*），它们的 id 就存在这个字段中。
* `min_trx_id`：`m_ids`中的最小值。
* `max_trx_id`：生成 ReadView 时 InnoDB 将分配给下一个事务的 ID 值。
* `creator_trx_id`：当前创建 ReadView 的事务的 ID。

假设`user`表中已有一条`id = 1`的记录，如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635149007627-b78c1a9243eb.jpg)

然后，有两个事务：B（`id = 200`）和 C（`id = 300`）。假设只有这两个事务在并发执行，且都没有进行提交：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635149123380-1cdd85d1c9d8.jpg)

### 2.1 事务 B 的 ReadView

如果事务 B 开启了一个 ReadView，在这个 ReadView 中：

* `m_ids`包含了当前的活跃事务的 ID：200 和 300（即 B 和 C 这两个事务的 ID）；
* `min_trx_id`就是 200；
* `max_trx_id`是下一个能够分配的事务的 ID，也就是 301；
* `creator_trx_id`就是当前创建 ReadView 的事务 B 的 ID 200。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635149275764-41347cedeca0.jpg)

现在事务 B 进行第一次查询，会对这行记录的隐藏字段`trx_id`和 ReadView 中的`min_trx_id`进行判断：`trx_id = 100`，小于`min_trx_id`的值。这说明，在事务 B 开始之前，修改这行记录的事务 A 已经提交了，所以事务 B 是可以查到该行记录的最新值的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635149552059-5f58f32b5ca8.jpg)

### 2.2 事务 C 的 ReadView

接着，事务 C 来修改这行记录，把`age = 18`改成了`age = 20`，所以这行记录的`trx_id`就变成了 300，同时`roll_pointer`指向了事务 C 修改之前生成的 undo log：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635149716264-3c63014f5dc7.jpg)

### 2.3 事务 B 与版本链

那这个时候，事务 B 再次进行查询操作，会发现：这行记录的`trx_id = 300`大于事务 B 的 ReadView 的`min_trx_id = 200`，并且小于`max_trx_id = 301`。这说明：更新这行记录的事务很有可能也存在于 ReadView 的`m_ids`（活跃事务 ID 列表）中。所以事务 B 会去判断下是否是这样的。显然 ReadV 中的`m_ids`中是有 300 的，这就表示这个`id = 300`的事务（C）是跟自己（事务 B）在同一时间段内并发执行的，所以行记录的`age = 20`这个版本事务 B 是不能查询到的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635149927018-3bda67d2a43a.jpg)

既然这个版本事务 B 是不能查询到的，那么就需要使用 undo log 版本链往前回溯：

事务 B 顺着这行记录的`roll_pointer`指针往前查找，找到最近的一条`trx_id = 100`的 undo log。由于事务 B 的`id = 200`，所以这个版本的 undo log 必然是在事务 B 开启之前就已经提交的了。所以，事务 B 的这次查询操作读到的就是这个版本的数据，即：`age = 18`。

### 2.4 事务自身修改的可见性

事务自己的修改，对其必然是可见的。

假设事务 C 的修改已经提交了，然后事务 B 更新了这行记录，把`age = 20`改成了`age = 66`，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635150429605-115ee1a1ccc3.jpg)

然后，事务 B 再来查询这条记录，发现`trx_id = 200`，与 ReadView 里的`creator_trx_id = 200`一样，这就说明是它自己的修改提交，自然是可以被查询到的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635150482672-3a8a985ff87b.jpg)

### 2.4 新开事务的修改的可见性

如果在事务 B 执行期间，又新开了一个`id = 400`的事务 D，然后更细了这行记录，使得`age = 88`，并且提交了。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635150555780-f55ac0184d7f.jpg)

此时，事务 B 是不能读取到新的更新的。因为这个时候事务 B 会发现行记录的`trx_id = 500`，大于了 ReadView 中的`max_trx_id = 301`，这说明这是另一个新的事务更新的结果，所以事务 B 是不能查询到这个结果的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635150625852-d7da480ceabc.jpg)

### 2.5 结论

通过上面的例子，可以看出，通过 undo log 版本链和 ReadView 机制：

* 可以保证一个事务不会读到并发执行的另一个事务的更新；
* 一个事务只可以读到该事务自己修改的或该事务开始之前的数据。

READ COMMITTED 和 REPEATABLE READ 事务隔离级之间的根本性不同，就在于**生成 ReadView 的时机不同**：REPEATABLE READ 只会在事务开始之前生成 ReadView，而 READ COMMITTED 会在每次执行前都生成一次 ReadView。


