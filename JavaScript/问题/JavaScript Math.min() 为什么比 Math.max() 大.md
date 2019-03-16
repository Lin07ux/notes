#对于下面的代码，输出却是`false`：

```javascript
var min = Math.min();
var max = Math.max();
console.log(min < max);
```

查看 MDN 文档可以发现，`Math.min()`和`Math.max()`都可以接受 0 个或者多个参数：

* 如果传入多个参数，有任何一个不能转成数字，那么就返回 NaN，否则返回其中的最小值/最大值
* 如果传入 0 个参数，则分别会返回**`Infinity`和`-Infinity`**，也即是分别返回 JavaScript 中的最大的正数，和最小的负数。

所以，不传入参数的时候，`Math.min()`是比`Math.max()`大的。

那么，为什么会这样呢？`min`不是应该返回最小值吗？`max`不是应该返回最大值吗？其实这个和代码的实现有关。

一般我们比较数值的大小的时候，会设置一个初始标准值。比如，`Math.min()`需要将其参数和一个标准值来进行比较，较小的值作为中间结果，然后将中间结果与下一个参数继续比较，这样最终的最小值就是结果了。可以考虑如下的一个填空题：

```javascript
var min = ___;
arr.forEach(function(n) {
    if (n > min) {
        min = n;
    }
});
```

自然，这里我们应该讲`min`变量设置初始值为`Infinity`才能符合实际情况。

