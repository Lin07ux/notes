### 1. 数据结构

Redis 中使用 list 数据结构来表示链表：

```c
typedef struct list{
    // 表头结点
    listNode *head;

    // 表尾节点
    listNode *tail;

    // 链表长度
    unsigned long len;

    // 节点值复制函数
    void *(*dup) (void *ptr);

    // 节点值释放函数
    void  (*free) (void *ptr);

    // 节点值对比函数
    int (*match) (void *ptr, void *key);
}
```

而每个节点使用 listNode 结构来表示：

```c
typedef strcut listNode{
    // 前置节点
    strcut listNode *pre;

    // 后置节点
    strcut listNode *next;

    // 节点的值
    void *value;
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1558864804133.png)

### 2. 特点

Redis 的链表有以下特性：

1. 无环双向链表；
2. 获取表头指针、表尾指针、链表节点长度的时间复杂度均为`O(1)`；
3. 链表使用`void *`指针来保存节点值，可以保存各种不同类型的值。

