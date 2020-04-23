异步任务可分为 task 和 microtask 两类，不同的 API 注册的异步任务会依次进入自身对应的队列中，然后等待 Event Loop 将它们依次压入执行栈中执行。

* **(macro)task** 也就是宏任务，是由宿主环境协助管理的，主要包含：script(整体代码)、setTimeout、setInterval、I/O、UI 交互事件、postMessage、MessageChannel、setImmediate(Node.js 环境)。

* **microtask** 也就是微任务，是由 JavaScript 引擎自身管理的，主要包含：Promise.then、MutaionObserver、process.nextTick(Node.js 环境)。

每一次 Event Loop 触发时，会按照如下的步骤进行：

1. 执行完主执行线程中的任务。
2. 取出 micro-task 中任务执行直到*清空*。
3. 取出 macro-task 中*一个*任务执行。
4. 重复 2 和 4。

所以：**微任务队列中的事件会比宏任务队列中的事件有更高的响应级别**。同一次事件循环中，微任务永远在宏任务之前执行。 
下面的代码的输出就是`2、3、1`：

```js
setTimeout(function () {
    console.log(1);
});

new Promise(function(resolve,reject){
    console.log(2)
    resolve(3)
}).then(function(val){
    console.log(val);
})
```





