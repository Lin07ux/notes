> 转摘：[5分钟彻底理解Object.keys](https://github.com/berwin/Blog/issues/24)

## 排序规则

`Object.keys()`是在 ECMAScript5 中引入的新的方法，可以得到对象中的属性(非继承属性)。针对不同的参数，得到的结果可能会被排序，也有可能不被排序，这是由内部的实现规则确定的。具体的排序规则如下：

1.	如果属性名的类型是`Number`，那么`Object.keys`返回值是按照`key`从小到大排序；
2.	如果属性名的类型是`String`，那么`Object.keys`返回值是按照属性被创建的时间升序排序；
3.	如果属性名的类型是`Symbol`，那么逻辑同`String`相同。

> 该顺序规则还适用于其他 API：`Object.entries`、`Object.values`、`Object.getOwnPropertyNames`、`for...in`、`Reflect.ownKeys`。
> 
> 除了`Reflect.ownKeys`之外，其他 API 均会将 Symbol 类型的属性过滤掉。

```JavaScript
// 被排序 ["2", "7", "100"]
Object.keys({
  100: '一百',
  2: '二',
  7: '七'
})

// 未排序  ["c", "a", "b"]
Object.keys({
  c: 'c',
  a: 'a',
  b: 'b'
})

// 混合排序  ["1", "3", "5", "a", "c", "b"]
Object.keys({
  5: '5',
  a: 'a',
  1: '1',
  c: 'c',
  3: '3',
  b: 'b'
})
```

## 实现逻辑

当`Object.keys`函数使用参数 O 调用时，会执行以下步骤：

1.	调用`ToObject(O)`将结果赋值给变量`obj`；
2.	调用`EnumerableOwnPropertyNames(obj, "key")`将结果赋值给变量`nameList`；
3.	调用`CreateArrayFromList(nameList)`得到最终的结果。

### 1. 数据转换

在第一步中，会有转化成 Object 的操作，所以该方法还可以接收其他类型的参数。

* Undefined 和 Null
    当传入`Undefined`和`Null`类型的参数时，会抛出 TypeError 错误。

* Number
    传入数值参数时，返回值是一个空数组。因为数值会被转换成`Number`对象，而`Number`对象是没有可提取属性的。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1534911496673.png)

* String
    传入字符串参数时，返回的是一个从 0 开始的数组，当然，如果是空字符串，返回的就是空数组了。这是由于 String 类型的参数会被转换成 String 对象：
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1534911621316.png)

### 2. 获得属性列表

获取属性列表的过程有很多细节，其中比较重要的是调用对象的内部方法`OwnPropertyKeys`获得对象的`ownKeys`(List 类型，只用于内部实现)。然后声明变量`properties`，也是 List 类型，并循环`ownKeys`将每个元素添加到`properties`列表中。最终将`properties`返回。

> `ownKeys`已经是结果了为什么还要循环一遍将列表中的元素放到`properties`中？这是因为`EnumerableOwnPropertyNames`操作不只是给`Object.keys`这一个 API 用，它内部还有一些其他操作，只是`Object.keys`这个 API 没有使用到，所以看起来这一步很多余。

也正是内部方法`OwnPropertyKeys`决定了属性的顺序。当这个内部方法被调用时，会执行`OrdinaryOwnPropertyKeys`方法，该方法会有如下的处理步骤：

1.	声明变量 keys 值为一个空列表（List 类型）；
2.	把每个 Number 类型的属性，按数值大小升序排序，并依次添加到 keys 中；
3.	把每个 String 类型的属性，按创建时间升序排序，并依次添加到 keys 中；
4.	把每个 Symbol 类型的属性，按创建时间升序排序，并依次添加到 keys 中；
5.	将 keys 返回。

上面这个规则不光规定了不同类型的返回顺序，还规定了如果对象的属性类型是数字，字符与 Symbol 混合的，那么**返回顺序永远是数字在前，然后是字符串，最后是 Symbol**。

> 属性的顺序规则中虽然规定了 Symbol 的顺序，但其实`Object.keys`最终会将 Symbol 类型的属性过滤出去。原因是顺序规则不只是给`Object.keys`一个 API 使用，它是一个通用的规则。

### 3、得到 Array 结果

在这一步，将上一步中得到的 List 类型值，转换成 Array 类型作为结果返回。






