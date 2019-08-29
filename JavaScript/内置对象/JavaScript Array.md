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

### concat()

`concat()`方法可以基于当前数组创建一个新的数组。该方法可以接收任意的参数：

* 如果没有传入参数，则相当于复制当前数组创建并返回这个一个副本；
* 如果传入了一个或多个参数，则会依次将这些参数追加在当前数组副本的后面。
* 如果传入的参数有数组，则会将该数组中的每一项依次追加到当前数组副本的后面，但不会将数组的更多层次进行展开。

**该方法不会修改原数组**。

```JavaScript
let colors = ['red', 'green', 'blue']
let colors2 = colors.concat('yellow', ['black', ['brown']])

console.log(colors)  // ["red", "green", "blue"]
console.log(colors2) // ["red", "green", "blue", "yellow", "black", ['brown']]
```

### slice()

`slice()`方法能够基于当前数组中的一个或多个项创建新数组。可以接收一个或两个参数，分别表示返回项的起始和结束位置，该方法会返回从起始和结束位置之间的项——包括起始位置但不包括结束位置。

* 第一个参数的默认值为 0，第二个参数的默认值为数组的长度。
* 参数如果为负值，则会将其参数值加上数组长度之后再进行计算，比如，对于一个长度为 5 的数组，`slice(-2, -1)`和`slice(3, 1)`的结果相同。
* 如果第二个参数不比第一个参数大，那么会返回一个空数组。

**该方法不会修改原数组**。

```JavaScript
let colors = ['red', 'green', 'blue']

console.log(colors.slice(1))      // ["green", "blue"]
console.log(colors.slice(1, 4))   // ["green", "blue"]
console.log(colors.slice(3, 3))   // []
console.log(colors.slice(-2, -1)) // ["green"]
console.log(colors)               // ["red", "green", "blue"]
```

### splice()

`splice()`方法也可以从数组中获取一个副本，但比`slice()`方法的功能更多、更复杂。`splice()`方法的主要用途是替换数组中的部分项，可以实现如下功能：

* 删除：可以删除任意数量的项，只需要指定 2 个参数：要删除的第一项的位置和要删除的项数。例如`splice(0, 2)`会删除数组中的前两项。
* 插入：可以向指定位置插入任意数量的项，只需要提供 3 个参数：起始位置、要删除的项数(0)和要插入的项。如果要插入多个项，可以继续传入参数。例如`splice(2, 0, 'red', 'green')`会从当前数组的位置 2 处开始插入两个字符串，插入后，这两个字符串在当前数组的索引分别为 2、3。
* 替换：可以从指定位置处开始删除指定数量的项，并从指定位置处插入任意数量的项，从而完成替换。该功能和插入功能类似，但此时要删除的项数(也就是第二个参数)不为 0。例如`splice(2, 1, 'red', 'green')`会先删除当前数组中索引为 2 的项，然后再从索引 2 开始插入两个字符串。

`splice()`方法始终都会返回一个数组，该数组中包含从原数组中删除的项。如果没有删除任何项，则返回一个空数组。

* 如果前两个参数为负值，则会将其加上当前数组的长度之和进行操作。
* 如果第一个参数(起始位置参数)大于或等于数组长度，则不会删除任何项。
* 如果要删除的项超过从起始位置开始的总项数，则会将从起始位置开始处的每一项都删除。

**该方法会修改原数组**。

```JavaScript
let colors = ['red', 'green', 'blue']

console.log(colors)                // ["red", "green", "blue"]

console.log(colors.splice(0, 1))   // ["red"]
console.log(colors)                // ["green", "blue"]

console.log(colors.splice(1, 0, 'yellow', 'orange'))   // []
console.log(colors)                // ["green", "yellow", "orange", "blue"]

console.log(colors.splice(1, 1, 'red', 'purple'))   // ["yellow"]
console.log(colors)                // ["green", "red", "purple", "orange", "blue"]

console.log(colors.splice(5, 2))   // []
console.log(colors)                // ["green", "red", "purple", "orange", "blue"]

console.log(colors.splice(-2, -1)) // []
console.log(colors)                // ["green", "red", "purple", "orange", "blue"]

console.log(colors.splice(-2, 1))  // ["orange"]
console.log(colors)                // ["green", "red", "purple", "blue"]
```

### indexOf()/lastIndexOf()

这两个方法都是用来查找指定项在数组中的位置的。前者表示从数组的开头往后查找，后者表示从数组的末尾开始向前查找。如果找到了则返回所在位置的索引，否则返回 -1。

这两个方法都可以接收两个参数：

* 第一个参数表示要查找的项
* 第二个参数表示查找起点位置的索引，可选。对于`indexOf()`方法，其默认值为 0，对于`lastIndexOf()`方法，其默认值则为数组的长度 - 1。

在查找过程中，会将第一个参数和数组中的项进行全等(`===`)比较，也就是说，第一个参数和数组项的类型不同也会导致查找不到。

```JavaScript
let numbers = [1, 2, 3, 4, 5, 4, 3, 2, 1]

numbers.indexOf(4)      // 3
numbers.lastIndexOf(4)  // 5

numbers.indexOf(4, 4)      // 5
numbers.lastIndexOf(4, 4)  // 3

numbers.indexOf('4')      // -1
numbers.lastIndexOf('4')  // -1
```


