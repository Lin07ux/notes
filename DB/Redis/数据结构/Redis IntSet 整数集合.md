整数集合是 Set(集合)的底层数据结构之一：当一个 Set(集合)只包含整数值元素，并且元素的数量不多时，Redis 就会采用整数集合(intset)作为 Set(集合)的底层实现。

整数集合(intset)保证了元素是不会出现重复的，并且是有序的(从小到大排序)，intset 的结构是这样子的：

```c
typeof struct intset {
   // 编码方式
   unit32_t encoding;
   
   // 集合包含的元素数量
   unit32_t length;
   
   // 保存元素的数组
   int8_t contents[];
}
```

示意图如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558873467813.png)

虽然 intset 结构将`contents`属性声明为`int8_t`类型的数组，但实际上`contents`数组并不保存任何`int8_t`类型的值，`contents`数组的真正类型取决于`encoding`属性的值：

* INTSET_ENC_INT16
* INTSET_ENC_INT32
* INTSET_ENC_INT64

如果本来是`INTSET_ENC_INT16`的编码，想要存放大于`INTSET_ENC_INT16`编码能存放的整数值，此时就得编码**升级**(从 16 升级成 32 或者 64)。步骤如下：

1. 根据新元素类型拓展整数集合底层数组的空间并为新元素分配空间。
2. 将底层数组现有的所有元素都转换成与新元素相同的类型，并将类型转换后的元素放到正确的位上，需要维持底层数组的有序性质不变。
3. 将新元素添加到底层数组。

另外，**只支持升级操作，并不支持降级操作。**


