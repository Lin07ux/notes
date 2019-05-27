跳跃表(shiplist)是实现 sortset(有序集合)的底层数据结构之一！

> 跳跃列表可以参看这个文章的解释：[漫画算法：什么是跳跃表？](http://blog.jobbole.com/111731/)

Redis 的跳跃表实现由 zskiplist 和 zskiplistNode 两个结构组成，其中 zskiplist 保存跳跃表的信息(表头，表尾节点，长度)，zskiplistNode 则表示跳跃表的节点。

```c
typeof struct zskiplistNode {
   // 后退指针
   struct zskiplistNode *backward;
   
   // 分值
   double score;
   
   // 成员对象
   robj *obj;
   
   // 层
   struct zskiplistLevel {
           // 前进指针
           struct zskiplistNode *forward;
           // 跨度
           unsigned int span;
   } level[];
} zskiplistNode;
```

zskiplistNode 的对象示例图(带有不同层高的节点)：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558875022579.png)

zskiplist 的结构如下：

```c
typeof struct zskiplist {
   // 表头节点，表尾节点
   struct skiplistNode *header, *tail;
   
   // 表中节点数量
   unsigned long length;
   
   // 表中最大层数
   int level;
} zskiplist;
```

示意图如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558875230291.png)


