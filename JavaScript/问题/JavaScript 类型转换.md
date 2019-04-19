> 转摘：[https://segmentfault.com/a/1190000008432611](https://segmentfault.com/a/1190000008432611)

下面是一些常见的类型转换结果：

```JavaScript
[] == []
// false
[] == ![]
// true
{} == !{}
// false
{} == ![]
// VM1896:1 Uncaught SyntaxError: Unexpected token ==
![] == {}
// false
[] == !{}
// true
undefined == null
// true
```

下面进行讲解：

### [] == [] 为什么是 false

数组是对象，而 JavaScript 中，对象是引用类型，除非两个对象引用的是同一个地址，否则它们就不相同，因此`[] == []`的结果是 false。

### [] == ![] 为什么是 true

JavaScript 中`!`取反运算符的优先级会高于`==`运算符的，所以这个表达式首先要求出`![]`的值。

由于 JS 中，对象转成 Boolean 的结果都是 true，而数组也是对象，那么对应的`![]`的值就是 false。

由于表达式两侧的数据类型不同了，此时依据下面的判断转换。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555508701241.png"/>

根据第 8 条规则，表达式变成了比较`[] == ToNumber(false)`。而 false 转成数字的结果为 0，表达式变成了`[] == 0`。

此时两侧的类型依旧不同，需要继续变换。根据规则 10，需要将左侧的`[]`转成原始值，使用内部方法`ToPrimitive`进行。

下面是`ToPrimitive`的基本实现规则：

```
ToPrimitive(obj, preferredType)

该函数接受两个参数，第一个 obj 为被转换的对象，第二个 preferredType 为希望转换成的类型（默认为空，接受的值为 Number 或 String）。

在执行 ToPrimitive(obj, preferredType) 时如果第二个参数为空并且 obj 为 Date 的事例时，此时 preferredType 会被设置为 String，其他情况下 preferredType 都会被设置为 Number。

如果 preferredType 为 Number，ToPrimitive 执行过程如下：

1. 如果 obj 为原始值，直接返回；
2. 否则调用 obj.valueOf()，如果执行结果是原始值，返回之；
3. 否则调用 obj.toString()，如果执行结果是原始值，返回之；
4. 否则抛异常。

如果 preferredType 为 String，将上面的第 2 步和第 3 步调换，即：

1. 如果 obj 为原始值，直接返回；
2. 否则调用 obj.toString()，如果执行结果是原始值，返回之；
3. 否则调用 obj.valueOf()，如果执行结果是原始值，返回之；
4. 否则抛异常。
```

> 原始值指的是 Null、Undefined、String、Boolean、Number 五种基本数据类型之一。

那么，由于`ToPrimitive([]) = ""`，那么前面的表达式就变成了`"" == 0`，而根据前面的比较规则中的第五条，需要将空字符串转成数值。而空字符串转成数值时结果为 0，那么最终就成了`0 == 0`的比较，自然结果就是 true 了。

### {} == !{} 为什么是 false

前面的`[] == ![]`是 true，而这里为什么就是 false 了呢？

前面的判断流程相同，只是到了`ToPrimitive({})`的时候，结果是`"[Object object]"`，这个字符串转成数值时结果为`NaN`，这个值与任何值都不想等，包括它自身，那么结果自然是 false 了。

### 总结

JavaScript 中，`==`的判断逻辑整体来说就是四条：

1. `undefined == null` 结果是 true，且它俩与所有其他值比较的结果都是 false。

2. `String == Boolean` 需要将两个操作数同时转为 Number。

3. `String/Boolean == Number` 需要将 String/Boolean 转为 Number。

4. `Object == Primitive` 需要将 Object 转为 Primitive(具体通过`valueOf`和`toString`方法)。





