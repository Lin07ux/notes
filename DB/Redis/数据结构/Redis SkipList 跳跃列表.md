跳跃表(shiplist)是实现 sortset(有序集合)的底层数据结构之一。

> 跳跃列表可以参看这个文章的解释：[漫画算法：什么是跳跃表？](http://blog.jobbole.com/111731/)

### 1. 数据结构

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

### 2. 查询方式

根据上面的示意图可知，每个 zskiplistNode 节点中都包含有分值、元素值和一系列的 Level 数组，而且 zskiplistNode 节点是按照分值排序的。

每个 node 的 level 数组大小都不同，level 数组中的值是指向下一个 node 的指针的跨度值(span)，跨度值是两个节点的 score 的差值。越高层的 level 数组值的跨度值就越大，底层的 level 数组值的跨度值越小。

level 数组就像是不同刻度的尺子。度量长度时，先用大刻度估计范围，再不断地用缩小刻度，进行精确逼近。

当在跳跃表中查询一个元素值时，都先从第一个节点的最顶层的 level 开始。比如说，在上图的跳表中查询 o2 元素时，先从 o1 的节点开始，因为 zskiplist 的 header 指针指向它。

先从其 level[3] 开始查询，发现其跨度是 2，o1 节点的 score 是1.0，所以加起来为 3.0，大于 o2 的 score 值 2.0。所以可以知道 o2 节点在 o1 和 o3 节点之间。这时，就改用小刻度的尺子了。就用 level[1] 的指针，顺利找到 o2 节点。



