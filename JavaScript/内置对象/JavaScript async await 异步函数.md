Async 异步函数是 ES8 标准中定义的，可以用于实现可控的异步执行函数。实际上，`async/await`是 Promise 的语法糖。

异步函数与普通函数的区别如下：

* 异步函数需要使用`async function`进行定义声明，属于`AsyncFunction`类型，而且返回值总是一个 Promise 对象。
* 普通函数则是只需要使用`function`方法即可声明，属于`Function`类型，返回值则可以是任意类型。

> AsyncFunction 继承自 Function。

### 异步函数的特点

**返回值总是 Promise 对象**

Async 异步函数返回的总是一个 Promise 对象，如果 return 的是一个直接量，会被通过`Promise.resolve()`封装成 Promise 对象。即便没有明确指定返回值，也和返回一个 undefined 一样，是一个被 resolve 的 Promise 对象。

```JavaScript
async function asyncFunc(){
    return 666;
}

console.log(asyncFunc)    // [AsyncFunction: asyncFunc]
console.log(asyncFunc())  // Promise { 666 }

asyncFunc().then(function(data){
    console.log(data);    // 666
}).catch(function(err){
    console.log(err)
});
```

### await 操作符

`await`操作符**只能**用于异步函数中，并且 `await`必须在`async`函数的上下文中的，表示异步函数先暂停在这里等待，直到其后的语句返回结果后，再继续执行异步函数。这里并不会占用 CPU 资源，因为引擎可以同时执行其他脚本或处理事件。

`await`操作符后可以跟一个 Promise 或者其它任何等待解析的值。如果`await`后跟的是一个 Promise，则`await`语句会返回`Promise.resolved`后的值，否则将会返回值本身。

如，下面循环多个`await`的时候就容易出错：

```JavaScript
// 错误
async function asyncFunc () {
    let arr = ['a', 'b', 'c'];

    arr.forEach(el => {
        console.log(await el); // 报错
    })
}

// 正确
async function asyncFunc () {
    let arr = ['a', 'b', 'c'];

    for (let i = 0; i < arr.length; i++) {
        console.log(await arr[i]);
    }
}
```

### 捕捉异步函数中的错误

异步函数中，`await`语句返回的是`Promise.resolved`值，默认是不会捕获错误的，如果 Promise 是`rejected`状态，就会直接抛出异常。所以最好在异步函数中使用`try-catch`语句捕获错误。

```JavaScript
let sleep = function (time) {
    return new Promise(function (resolve, reject) {
        setTimeout(function () {
            // 模拟出错，返回 error
            reject('error');
        }, time);
    })
};

let start1 = async function () {
    console.log('start1');
    await sleep(1000); // 发生了错误

    // 以下代码不会被执行
    console.log('end1');
};

let start2 = async function () {
    try {
        console.log('start2');
        await sleep(1000); // 发生了错误

        // 以下代码不会被执行
        console.log('end2');
    } catch (err) {
        console.log(err); // 捕获错误
    }
};

start1();
start2();

# 运行上面的代码，会得到下面的信息
# start1
# start2
# (node:2164) UnhandledPromiseRejectionWarning: Unhandled promise rejection (rejection id: 1): error
# (node:2164) [DEP0018] DeprecationWarning: Unhandled promise rejections are deprecated. In the future, promise rejections that are not handled will terminate the Node.js process with a non-zero exit code.
error
```

### 参考

* [JavaScript async 异步函数](http://www.tuicool.com/articles/UbaIzij)


