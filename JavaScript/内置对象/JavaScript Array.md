### 1. 栈方法

JavaScript 中的数组可以作为一个栈使用，通过以下的方法实现先进先出或先进后出栈。

#### 1.1 pop()/push()

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

#### 1.2 shift()/unshift()

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

### 2. 排序方法

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

### 3. 操作方法

数组还支持进行合并、切片、替换等功能。

#### 3.1 concat()

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

#### 3.2 slice()

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

#### 3.3 splice()

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

### 4. 查找索引方法

JavaScript Array 提供了两个查找指定项在数组中的位置的方法：`indexOf()`和`lastIndexOf()`。前者表示从数组的开头往后查找，后者表示从数组的末尾开始向前查找。如果找到了则返回所在位置的索引，否则返回 -1。

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

### 5. 迭代方法

除了使用`for`语法对数组进行迭代之外，JavaScript Array 自身还提供了一些更方便的迭代处理方法：

* `every()` 对数组中的每一项运行给定函数，如果回调函数对每一项都返回 true，则该方法最终返回 true。一旦有一项返回的不是 true，则停止迭代，并返回 false。用于确保数组中的每一项都符合条件。
* `some()` 对数组中的每一项运行给定的函数，如果回调函数对任一项返回 true，则立即停止迭代，且该方法返回 true。否则会在迭代完全部的数组项之后返回 false。用于确认数组中至少有一项是符合条件的。
* `filter()` 对数组中的每一项运行给定的函数，最终该方法会将回调函数返回 true 的项组成一个新的数组并返回。用于从数组中过滤出符合条件的项。
* `forEach()` 对数组中的每一项运行给定的函数。这个方法没有返回值。用于对数组中的每一项都进行相关处理。
* `map()` 对数组中的每一项运行给定的函数，返回每次回调函数调用的结果组成的数组。用于更改数组中的每一项。

> 以上方法都不会修改数组中包含的值，除非在回调函数中有显式的修改。

它们的语法都相同，以`every()`方法为例，语法如下：

```JavaScript
Array.prototype.every(function callback(item, index, array) {
    // statements
})
```

这些方法的回调函数可以接收三个参数，分别表示：

* `item` 当前迭代的数组项
* `index` 当前迭代的项在数组中的位置索引
* `array` 当前迭代的数组本身

需要**注意**的是，回调方法的第三个参数表示当前迭代数组本身，所以可以利用这个参数对当前数组进行修改，但是不论是增加还是删除，或者修改数组中的项，都不影响当前的迭代的执行，因为在进行迭代的时候，JavaScript 会创建当前数组的一个副本，并用这个副本进行迭代。

```JavaScript
let numbers = [1, 2, 3, 4, 5, 4, 3, 2, 1]

numbers.every(function (item, index, array) {
    console.log(index)
    return item > 2
})
// 0 false

numbers.some(function (item, index, array) {
    console.log(index)
    return item > 2
})
// 0 1 2 true

numbers.filter(function (item, index, array) {
    return item > 2
})
// [3, 4, 5, 4, 3]

numbers.forEach(function (item, index, array) {
    console.log(item)
})
// 1 2 3 4 5 4 3 2 1

numbers.map(function (item, index, array) {
    return item * 2
})
// [2, 4, 6, 8, 10, 8, 6, 4, 2]
```

### 6. 归并方法

ECMAScript 5 为数组新增了两个方法：`reduce()`和`reduceRight()`。这两个方法都会迭代数组的所有项，然后构建一个最终返回的值。其中，`reduce()`方法表示从数组的第一项开始向后归并处理，而`reduceRight()`表示从数组的最后一项开始向前归并处理。

这两个方法都接收一个回调函数，表示对每一项进行处理的逻辑，并可以接收一个可选的参数，表示归并的起始值。而且这两个方法都不会修改原数组的项。

这两个方法的回调函数接收 4 个参数：前一个归并后的结果、当前项、项的索引、当前数组对象。每一项的回调函数返回的任何值都会作为第一个参数(前一个归并结果)传递给下一项的回调函数。

需要**注意**的是，如果不传入第二个参数，那么：

* `reduce()`方法会自动将数组的第一项作为第二个参数的值，并从第二项开始遍历执行回调函数，而第一项则不会执行回调函数；
* `reduceRight()`方法会自动将数组的最后一项作为第二个参数的值，并从倒数第二项开始遍历执行回调函数，而最后一项不会执行回调函数。

```JavaScript
let numbers = [1, 2, 3]

numbers.reduce((pre, item, index) => {
    console.log(pre, item, index)
    return pre + item
})
// 1 2 1
// 3 3 2
// 6

numbers.reduceRight((pre, item, index) => {
    console.log(pre, item, index)
    return pre + item
})
// 1 3 2 1
// 1 5 1 0
// 6

numbers.reduce((pre, item, index) => {
    console.log(pre, item, index)
    return pre + item
}, 4)
// 4 1 0
// 5 2 1
// 7 3 2
// 10

numbers.reduceRight((pre, item, index) => {
    console.log(pre, item, index)
    return pre + item
}, 4)
// 4 3 2
// 1 7 2 1
// 1 9 1 0
// 10
```

