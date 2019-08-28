## 方法

### pop()/push()

`pop()`方法从数组末尾弹出一个元素，并返回该元素；`push()`方法在数组末尾压入一个或多个元素，并返回压入元素后的数组长度。

```JavaScript
let arr = []
arr.push('red', 'black') // 2
arr.push('blue') // 3

arr.pop() // 'blue'
arr.pop() // 'black'
arr.pop() // 'red'
arr.pop() // undefined
```

### shift()/unshift()

`shift()`从数组头部弹出一个元素，并返回该元素；`unshift()`方法在数组头部压入一个或多个元素，并返回压入元素后的数组长度。

```JavaScript
let arr = []
arr.unshift('red', 'black') // 2
arr.unshift('blue') // 3

arr.shift() // 'blue'
arr.shift() // 'black'
arr.shift() // 'red'
arr.shift() // undefined
```

### sort()

对数组排序可以使用`sort()`方法，需要注意的是：

* 利用递归进行冒泡排序，且会修改原数组；
* 默认情况下是按照升序排列，而且会调用每个数组项的`toString()`方法将其转换成字符串后进行比较，即使每一项都是数值。这会导致数值`10`会排在数值`5`前面。
* `sort()`方法可以接收一个比较函数`compare(x, y)`，该比较函数接收两个参数，并根据比较函数的返回值来决定每一项的顺序：返回负数则第一个参数会位于第二个参数前面，返回 0 则两个参数位置不变，返回正数则第二个参数位于第一个参数前面；
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

