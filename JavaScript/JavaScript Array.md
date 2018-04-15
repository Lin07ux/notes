## 方法

### sort()

对数组排序可以使用`sort()`方法，需要注意的是：

* `sort()`其实是利用递归进行冒泡排序的；
* `sort()`方法会修改原数组；
* `sort()`方法接收的是一个比较函数`compare(x,y)`，该比较函数接收两个参数，主要是根据参数的比较来判断返回值的；
* 比较函数**返回的值只要是大于 0，就会交换位置**。

比如，升序排列：

```JavaScript
var arr = new Array(1,5,2,4);
function ascCompare (x, y) {
    if (x > y) {
        return 1;
    } else if (x === y) {
        return 0;
    } else {
        return -1;
    }
}

var newArr = arr.sort(ascCompare);
console.log(arr);     // 会改变原来的数组[1,2,4,5]
console.log(newArr);  // [1,2,4,5]

// 或者，可以简写为
function ascCompare2 (x, y) {
    return x - y;
}
```

同理，降序排列：

```JavaScript
var arr = new Array(1,5,2,4);
function descCompare (x, y) {
    if(x < y) {
        return 1;
    } else if (x === y) {
        return 0;
    } else {
        return -1;
    }
}

var newArr = arr.sort(descCompare);
console.log(arr);     // 会改变原来的数组[5,4,2,1]
console.log(newArr);  // [5,4,2,1]

// 或者，可以简写为
function descCompare2 (x, y) {
    return y - x;
}
```

> 参考：[关于javaScript sort()方法的理解](https://segmentfault.com/a/1190000009338122)

