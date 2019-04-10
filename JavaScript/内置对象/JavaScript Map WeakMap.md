Map 和 WeakMap 是键值对集合，类似数组与对象的结合体，但是其键值可以是任意值(包括对象)。一般用 Map 和 WeakMap 来存储需要频繁取用的数据，而且一般不会逐一遍历其中的元素。

Map 和 WeakMap 是有序的，即元素被添加进去的顺序就是在内部保存的顺序。对于用数组来初始化的情况也一样，会按照在数组中的位置依次添加进集合中。

Map 和 WeakMap 有多情况是类似的，下面主要介绍 Map，两者的不同会单独介绍。

### 1. 创建

可以用`new Map()`构造函数来创建一个空的 Map，也可以在 Map 构造函数中传入一个数组来创建并初始化一个 Map，传入的数组需要是二维数组，其中的每一个子数组都有两个元素，前者会被作为 key，后者会被作为 value，这样就形成了一个`key-value`键值对。

```JavaScript
let map1  = new Map();

// 定义一个二维数组，数组中的每子都有两个元素
let arr = [
    ['key1', 'value 1'],  // key 是 字符串 "key1", value 是字符串 "value 1"
    [{}, 10086],          // key 是个对象, value 是数值 10086
    [5, {}]               // key 是个数值类型, value 是对象
];
let map2 = new Map(arr)
```

### 2. 可用方法

* `set(key, value)` 向 Map 中加入一个键值对
* `get(key)` 若不存在 key 则返回`undefined`
* `has(key)` 判断是否存在指定 key，返回布尔值
* `delete(key)` 删除指定 key，删除成功则返回 true，若 key 不存在或者删除失败会返回 false
* `clear()` 清除 Map 中的全部键值对
* `entries()` 返回一个 MapIterator 对象，包含 Map 中的全部键值对
* `keys()` 返回一个 MapIterator 对象，包含 Map 中的全部键名
* `values()` 返回一个 MapIterator 对象，包含 Map 中的全部值
* `forEach()` 迭代 Map 中的键值对。

Map 中的`forEach()`方法和数组的`forEach`方法类似，回调函数中都包含 3 个参数`value`(值)、`key`(键)和调用这个方法的 Map 集合本身：

```JavaScript
map.forEach(function(value,  key,  ownerMap){
    console.log(key,  value);       // 每对键和值
    console.log(ownerMap === map);  // true
});
```

### 3. 属性

* `size` 值为 Map 中键值对的个数

### 4. WeakMap 和 Map 的区别

WeakMap 只有`set`、`has`、`get`、`delete`和 Object 中的几个方法，没有`forEach`方法，也不能使用`for...in`语句对其遍历。

另外，WeakMap 中的`weak`表示的是弱引用，也就是说，WeakMap 中的对象是弱引用。WeakMap 和 Map 对于 key 是对象时的引用机制如下：

将对象设置为 key 时，就在集合中保存了这个对象的引用。当这个对象没有其他引用了的时候，即只有集合还引用着这个对象的时候，弱类型的集合会放弃对这个对象的引用，把这个对象从集合里移除，不让它继续存在于集合中了，有些“赶尽杀绝”的意思。但是强类型的集合还会一直保存着对这个对象的引用，就把它一直放在集合里。

需要**注意**的是：这个弱引用机制只作用于 key，而 value 位置绑定的对象无论是否还存在别的引用，WeakMap 都不会放弃这个对象。只有这个位置的 key 绑定的对象没有其他引用时，才会把 key 和 value 都放弃。

WeakMap 可以用在需要生命周期管理的地方，例如保存对一个 DOM 对象的引用，如果一个 DOM 对象使用完毕，没有其他的引用了， 那么它应该被垃圾回收，以免产生内存泄漏。


