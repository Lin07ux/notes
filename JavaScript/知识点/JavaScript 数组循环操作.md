在 ECMAScript5（简称 ES5） 中，有三种数组循环，分别是：

* 简单 for 循环 * for-in * forEach 
在2015年6月份发布的 ECMAScript6（简称 ES6） 中，新增了一种循环，是：

* for-of 
### 简单 for 循环
for 循环是最简单的一种循环方法：

```JavaScript
var arr = [1, 2, 3];
for(var i = 0; i　< arr.length; i++) {
    console.log(arr[i]);
}
```

当然，如果数组长度在循环过程中不会改变时，我们应将数组长度用变量存储起来，这样会获得更好的效率：

```JavaScript
var arr = [1, 2, 3];
for(var i = 0, len = arr.length; i < len; i++) {
    console.log(arr[i]);
}
```

### for-in 循环
for-in 循环是设计用来遍历对象中的属性的，不过由于在 JavaScript 中，数组也是一种特殊的对象，所以也可以使用 for-in 循环来遍历数组中的元素。

```JavaScript
var arr = [1, 2, 3];
var index;
for(index in arr) {
    console.log("arr[" + index + "] = " + arr[index]);
}
```

一般情况下，运行结果如下：

```
arr[0] = 1
arr[1] = 2
arr[2] = 3
```

不过使用 for-in 遍历数组可能会遇到问题：

* 首先，for-in 遍历的是顺序是不确定的，在不同的平台或浏览器中表现可能都不相同。
* 其次，for-in 其实遍历的是对象的属性(字符串)，所以在遍历数组的时候，获取到的数组索引其实是一个字符串，而不是数值。
* 而且，如果我们给数组添加了额外的属性，而不是元素，那么使用 for-in 循环的时候也会将这个额外添加进去的属性检索出来。
* 另外，for-in 不仅仅遍历对象自身的属性，还会遍历对象原型链上的所有可枚举的属性。所以如果更改了 Array 的原型链上的属性，就也有可能会被 for-in 遍历出来。

> Array 的索引不是 Number 类型，而是 String 类型的。
> 
> 我们可以正确使用如`arr[0]`的写法的原因是语言可以自动将 Number 类型的 0 转换成 String 类型的 "0"。所以，在 Javascript 中从来就没有 Array 的索引，而只有类似 "0" 、"1" 的属性。
> 
> 每个 Array 对象都有一个 length 的属性，导致其表现地更像其他语言的数组。但在遍历 Array 对象的时候没有输出 length 这一条属性。这是因为 for-in 只能遍历“可枚举的属性”，length 属于不可枚举属性，实际上，Array 对象还有许多其他不可枚举的属性。

```JavaScript
Array.prototype.fatherName = "Father";

var arr = [1, 2, 3];
arr.name = "Hello world";
var index;
for(index in arr) {
    console.log("arr[" + index + "] = " + arr[index]);
}
```

结果如下：

```JavaScript
arr[0] = 1
arr[1] = 2
arr[2] = 3
arr[name] = Hello world
arr[fatherName] = Father
```

所以，for-in 并不适合用来遍历 Array 中的元素，其更适合遍历对象中的属性，这也是其被创造出来的初衷。不过有一种情况例外，就是稀疏数组。考虑下面的例子：

```JavaScript
var key;
var arr = [];
arr[0] = "a";
arr[100] = "b";
arr[10000] = "c";
for(key in arr) {
    if(arr.hasOwnProperty(key)  &&    
        /^0$|^[1-9]\d*$/.test(key) &&    
        key <= 4294967294               
        ) {
        console.log(arr[key]);
    }
}
```

for-in 只会遍历存在的实体，上面的例子中， for-in 遍历了3次（遍历属性分别为"0"、 "100"、 "10000"的元素，普通 for 循环则会遍历 10001 次）。所以，只要处理得当， for-in 在遍历 Array 中元素也能发挥巨大作用。

如果要想使用 for-in 循环安全的遍历出数组中的元素，可以进行如下的处理：

```JavaScript
function arrayHasOwnIndex(array, prop) {
    return array.hasOwnProperty(prop) && 
        /^0$|^[1-9]\d*$/.test(prop) && 
        prop <= 4294967294; // 2^32 - 2
}

for (key in arr) {
    if (arrayHasOwnIndex(arr, key)) {
        console.log(arr[key]);
    }
}
```

由于每次迭代操作会同时搜索实例或者原型属性， for-in 循环的每次迭代都会产生更多开销，因此要比其他循环类型慢，一般速度为其他类型循环的 1/7。因此，除非明确需要迭代一个属性数量未知的对象，否则应避免使用 for-in 循环。

### forEach 循环
forEach 循环是 ES5 中新引入的循环方法。

forEach 方法为数组中含有有效值的每一项执行一次 callback 函数，那些已删除（使用 delete 方法等情况）或者从未赋值的项将被跳过（不包括那些值为 undefined 或 null 的项）。 callback 函数会被依次传入三个参数：

* 数组当前项的值；
* 数组当前项的索引；
* 数组对象本身； 
```JavaScript
var arr = [1, 2, 3];
arr.forEach(function(data) {
    console.log(data);
});
```

输出：

```
1
2
3
```

需要注意的是：

* forEach 遍历的范围在第一次调用 callback 前就会确定。调用 forEach 后添加到数组中的项不会被 callback 访问到。
* 传递给 callback 的值是 forEach 遍历到他们那一刻的值。所以如果在 forEach 的回调中改变了后续才会遍历到的值，那么会影响到后续的遍历。
* 已删除的项不会被遍历到。
* forEach 的回调函数中，传入的 index 是数值，而不是字符串。而且不会像 for-in 循环那样遍历到原型链上的属性。

另外，在 ES5 中还增加了其他的几个数组遍历的方法：

* every: 循环在第一次`return fasle`后返回
* some: 循环在第一次`return true`后返回
* filter: 返回一个新的数组，该数组内的元素满足回调函数
* map: 将原数组中的元素处理后再返回
* reduce: 对数组中的元素依次处理，将上次处理结果作为下次处理的输入，最后得到最终结果。 
forEach 在大多数浏览器中效率比较稳定且比普通 for 循环的效率要高。但在 chrome 中，for 循环不仅比其他浏览器中的 for 循环更加高效，甚至比 forEach 还要高效。

### for-of 循环
for-of 循环是在 ES6 中新引入的，是为了解决前面三种循环的一些缺陷：

* forEach 不能 break 和 return；
* for-in 缺点更加明显，它不仅遍历数组中的元素，还会遍历自定义的属性，甚至原型链上的属性都被访问到。而且，遍历数组元素的顺序可能是随机的。

相对的，for-of 循环的特点就在于：

* 跟 forEach 相比，可以正确响应 break, continue, return。
* for-of 循环不仅支持数组，还支持大多数类数组对象，例如 DOM nodelist 对象。
* for-of 循环也支持字符串遍历，它将字符串视为一系列 Unicode 字符来进行遍历。
* for-of 也支持 Map 和 Set （两者均为 ES6 中新增的类型）对象遍历。 
需要注意的是：**for-of 循环不支持普通对象**。如果你想迭代一个对象的属性，你可以用 for-in 循环（这也是它的本职工作）。

下面是一个使用 for-of 循环的基本示例：

```JavaScript
var arr = ['a', 'b', 'c'];
for(var data of arr) {
    console.log(data);
}
```

输出：

```
a
b
c
```

> ES6 引进的 Iterator 也能实现遍历数组的值。
> 
> ```JavaScript
> var arr = ['a', 'b', 'c'];
> var iter = arr[Symbol.iterator]();

> iter.next() // { value: 'a', done: false }
> iter.next() // { value: 'b', done: false }
> iter.next() // { value: 'c', done: false }
> iter.next() // { value: undefined, done: true }
> ```


转摘：[JavaScript 中的 for 循环](https://zhuanlan.zhihu.com/p/23812134)

