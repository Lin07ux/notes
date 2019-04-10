Set 和 WeakSet 是一个值集合，与数组类似，和 Map 也有点像，只是其没有键，只有值。Set 和 WeakSet 一般用来判断某个值是否存在其中，而且不会逐一遍历其中的元素。

Set 和 WeakSet 是有序的，即元素被添加进去的顺序就是在内部保存的顺序。对于用数组来初始化的情况也一样，会按照在数组中的位置依次添加进集合中。

Set 和 WeakSet 非常类似，下面主要介绍 Set，两者的不同会单独介绍。

### 1. 创建

可以用`new Set()`构造函数来创建一个空的 Set，也可以在 Set 构造函数中传入一个数组来创建并初始化一个 Set，传入的数组是一维数组，每个元素都会成为 Set 的值。

```JavaScript
let set1  = new Sap();
let set2 = new Sap([1, 'str'])；
```

### 2. 可用方法

* `add(value)` 向 Set 中加入一个值
* `has(value)` 判断是否存在指定 value，返回布尔值
* `delete(value)` 删除指定 value，删除成功则返回 true，若 value 不存在或者删除失败会返回 false
* `clear()` 清除 Map 中的全部键值对
* `entries()` 返回一个 MapIterator 对象，包含 Map 中的全部键值对
* `keys()` 返回一个 MapIterator 对象，包含 Map 中的全部键名
* `values()` 返回一个 MapIterator 对象，包含 Map 中的全部值
* `forEach(value, key, set)` 迭代 Map 中的键值对。

Set 中的`forEach()`方法和数组的`forEach`方法类似，回调函数中都包含 3 个参数`value`(值)、`key`(键)和调用这个方法的 Set 集合本身。由于 Set 中没有键，而为了保持`forEach()`方法参数的一致性，所以也有`key`参数，这个`key`和`value`是一样的。

```JavaScript
set.forEach(function(value, key, ownerSet){
    console.log(value === key, set === ownerSet);   // true true
});
```

### 3. 属性

* `size` 值为 Map 中键值对的个数

### 4. Set 和 WeakSet 的区别

WeakSet 只有`add`、`has`、`delete`和 Object 中的方法可以使用，比 Set 少了很多方法，没有`forEach`方法，也不能使用`for...in`语句对其中的值遍历。


