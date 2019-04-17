> 转摘[ES6 迭代器：Iterator, Iterable 和 Generator](http://www.tuicool.com/articles/Ivei6z6)

ES6 中，通过迭代器机制为 Map、Array、String 等对象提供了统一的遍历语法，以及更方便的相互转换。为方便编写迭代器还提供了生成器（Generator）语法。

## 一、基本概念

迭代器包含了如下的一些概念：

* `Iterable` 表示可以迭代的一类对象，这些对象需要实现 Iterable Protocol 协议。这种对象可以用`for...of`来遍历。 Map、Set、Array、String 都属于可迭代对象。自定义的对象也可以使用这一机制，成为可迭代对象。

* `Iterator` 是迭代器对象，也就是遍历的具体实现。该类对象需要实现 Iterator Protocol 协议。

* `Iterable Protocol` 迭代器协议，协议规定：对象需要实现一个 ECMA `@@iterator`方法，即在对象的`[Symbol.iterator]`键上提供一个方法，这个方法需要返回一个迭代器对象(Iterator)。当对象被遍历时，这个方法会被调用。

* `Iterator Protocol` 迭代协议，又称 Iteration Protocol，主要用于定义在对迭代器进行迭代时的处理。要求对象实现一个`next()`方法，每次调用会返回一个有`value`和`done`两个属性的对象，如：`{ value: null, done: false }`，其中前者表示当前遍历得到的值，后者指示迭代是否完成。

* `Symbol.iterator` 对象中的一个特殊的属性，对象被迭代时需要使用该属性中定义的方法来实现。

另外，`Generator`生成器和`Generator Function`生成器函数与迭代器也是息息相关的。

## 二、示例

### 2.1 标准 Iterable

Array 是一个可迭代的对象，也就是说，Array 对象上有一个`Symbol.iterator`属性，该属性是一个可以返回迭代器对象的方法：

```JavaScript
let arr = ['Alice', 'Bob', 'Carol']
let iterator = arr[Symbol.iterator]()
```

多次调用得到的迭代器对象的`.next()`方法，每次都可以得到一个对象，直到得到的对象中的`done`属性的值为`true`：

```JavaScript
console.log(iterator.next())    // { value: 'Alice', done: false }
console.log(iterator.next())    // { value: 'Bob', done: false }
console.log(iterator.next())    // { value: 'Carol', done: false }
console.log(iterator.next())    // { value: undefined, done: true }
```

### 2.2 自定义 Iterables

同样的，可以为自定义的对象设置合适的`Symbol.iterator`属性值，从而使得这个东西变成 Iterables。比如实现一个 50 以内的 斐波那契数列：

```JavaScript
let obj = {
    [Symbol.iterator]: function () {
        let a = 0, b = 0
        
        return {
            next: function () {
                let value = 0
                
                if (!a) {
                    value = a = 1
                } else if (!b) {
                    value = b = 1
                } else if (b < 50) {
                    value = a + b
                    a = b
                    b = value
                }
                
                return { done: value === 0, value: value }
            }
        }
    }
}
```

然后就可以对其进行遍历：

```JavaScript
for (let i of obj) {
    console.log(i)  // 1 1 2 3 5 8 13 21 34 55
}
```

### 2.3 Generator

ES6 中可以增加了 Generator 语法，使用 Generator 语法编写的函数被称为 Generator Function(生成器函数)。由于生成器函数运行后返回的就是一个实现了 Iterator Protocol 的迭代器对象，所以借助生成器函数能够更加方便的实现自定义的可迭代对象。

下面使用生成器方法重新实现 50 以内的斐波那契数列：

```JavaScript
let obj = {
    [Symbol.iterator]: function *() {
        let a = 1, b = 1
        
        yield a
        yield b
        
        while (b < 50) {
            yield b = a + b
            a = b - a
        }
    }
}
```

其遍历的结果与前面是一样的：

```JavaScript
for (let i of obj) {
    console.log(i)  // 1 1 2 3 5 8 13 21 34 55
}
```

## 三、Iterables 对象之间的转换

Iterator Protocol 给出了统一的迭代协议，使得不同类型的集合间转换更加方便，也方便了编写适用于不同类型集合的算法，比如 Map、Set、String、Array 之间的互相转换。

以下是一些很方便的转换技巧：

* 从 Array 生成 Set ，可用于数组去重：
    
```JavaScript
new Set(['Alice', 'Bob', 'Carol'])    // {'Alice', 'Bob', 'Carol'}
// 等价于
new Set(['Alice', 'Bob', 'Carol'][Symbol.iterator]())
```

* 从 Set 得到 Array ：

```JavaScript
let set = new Set(['Alice', 'Bob', 'Carol'])
Array.from(set) // ['Alice', 'Bob', 'Carol']
// 等价于
Array.from(set[Symbol.iterator]())
// 也可以使用展开语法
let names = [...set]        // ['Alice', 'Bob', 'Carol']
```

* 从 String 到 Set ，得到字符串中包含的字符：

```JavaScript
let alphabet = 'abcdefghijklmnopqrstuvwxyz';
new Set(alphabet)           // {'a', 'b', 'c', ...}
// 等价于
new Set('alice bob'[Symbol.iterator]())
```

* 从 Object 到 Map ，也就是把传统的 JavaScript 映射转换为 Map ：

```JavaScript
let mapping = {
    "foo": "bar"
}
new Map(Object.entries(mapping))    // {"foo" => "bar"}
```



