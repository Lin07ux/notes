Hash 即哈希表，Redis 的 Hash 和传统的哈希表一样，是一种`field-value`型的数据结构，可以理解成将 Java 的 HashMap 搬入 Redis。

**Hash 非常适合用于表现对象类型的数据**，用 Hash 中的 field 对应对象的 field 即可。

### 1. 数据结构

在 Redis 里边，哈希表使用 dictht 结构来定义：

```c
typedef struct dictht{ 
   // 哈希表数组
   dictEntry **table;  
    
   // 哈希表大小
   unsigned long size;    
    
   // 哈希表大小掩码，用于计算索引值，总是等于 size-1
   unsigned long sizemark;     
    
   // 哈希表已有节点数量
   unsigned long used; 
}
```

其中，哈希表的节点 dictEntry 的实现方式如下：

```c
typedef struct dictEntry {     
   // 键
   void *key;
    
   // 值
   union {
       void *value;
       uint64_tu64;
       int64_ts64;
   }v;    
    
   //指向下个哈希节点，组成链表
   struct dictEntry *next;
    
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1558865221672.png)

可以看到，Redis 的 hash 表多了几个属性来记录常用的值：sizemark(掩码)、used(已有的节点数量)、size(大小)。

同样地，Redis 为了更好的操作，对哈希表往上再封装了一层(参考 Redis 链表)，使用 dict 结构来表示：

```c
typedef struct dict {
    // 类型特定函数
    dictType *type;

    // 私有数据
    void *privdata;
  
    // 哈希表
    dictht ht[2];

    // rehash 索引，当 rehash 不进行时，值为 -1
    //
    int rehashidx;  
}

typedef struct dictType{
    // 计算哈希值的函数
    unsigned int (*hashFunction)(const void * key);

    // 复制键的函数
    void *(*keyDup)(void *private, const void *key);
 
    // 复制值得函数
    void *(*valDup)(void *private, const void *obj);  

    // 对比键的函数
    int (*keyCompare)(void *privdata , const void *key1, const void *key2)

    // 销毁键的函数
    void (*keyDestructor)(void *private, void *key);
 
    // 销毁值的函数
    void (*valDestructor)(void *private, void *obj);  
}
```

所以，Redis 所实现的哈希表最后的数据结构是这样子的：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558867293293.png)

从代码实现和示例图上可以发现，Redis 中有两个哈希表：

* `ht[0]`：用于存放真实的 key-vlaue 数据
* `ht[1]`：用于扩容(rehash)

Redis 中哈希算法和哈希冲突跟 Java 实现的差不多，差异就是：Redis 哈希冲突时，是将新节点添加在链表的表头；JDK1.8 后，Java 在哈希冲突时，是将新的节点添加到链表的表尾。

### 2. rehash 的过程

从上面的数据结构可以明显地看到，Redis 是专门使用一个哈希表来做 rehash 的，这跟 Java 一次性直接 rehash 是有区别的：
**在对哈希表进行扩展或者收缩操作时，reash 过程并不是一次性地完成的，而是渐进式地完成的**。

Redis 在 rehash 时采取渐进式的原因：数据量如果过大的话，一次性 rehash 会有庞大的计算量，这很可能导致服务器一段时间内停止服务。

Redis rehash 时具体是这么处理的：

1. 在字典中维持一个索引计数器变量`rehashidx`，并将设置为 0，表示 rehash 开始；
2. 在 rehash 期间每次对字典进行增加、查询、删除和更新操作时，除了执行指定命令外；还会将`ht[0]`中`rehashidx`索引上的值 rehash 到`ht[1]`，操作完成后`rehashidx+1`；
3. 字典操作不断执行，最终在某个时间点，所有的键值对完成 rehash，这时将`rehashidx`设置为 -1，表示 rehash 完成。

在渐进式 rehash 过程中，字典会同时使用两个哈希表`ht[0]`和`ht[1]`，所有的更新、删除、查找操作也会在两个哈希表进行。例如要查找一个键的话，服务器会优先查找`ht[0]`，如果不存在，再查找`ht[1]`。此外当执行新增操作时，新的键值对一律保存到`ht[1]`，不再对`ht[0]`进行任何操作，以保证`ht[0]`的键值对数量只减不增，直至变为空表。

### 3. 优点

Hash 的优点包括：

* 可以实现二元查找，如"查找 ID 为 1000 的用户的年龄"；
* 比起将整个对象序列化后作为 String 存储的方法，Hash 能够有效地减少网络传输的消耗；
* 当使用 Hash 维护一个集合时，提供了比 List 效率高得多的随机访问命令。

