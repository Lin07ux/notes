### macro task 与 micro task

在一个事件循环中，异步事件返回结果回调后会被放到一个任务队列中。然而，根据这个异步事件的类型，这个回调实际上会被放到对应的宏任务队列或者微任务队列中去。然后在当前执行栈为空的时候，主线程会查看微任务队列是否有回调存在。如果不存在，那么再去宏任务队列中取出一个事件回调并把对应的回到加入当前执行栈；如果存在，则会依次执行队列中事件对应的回调，直到微任务队列为空，然后去宏任务队列中取出最前面的一个事件，把对应的回调加入当前执行栈...如此反复，进入循环。

所以：**微任务队列中的事件会比宏任务队列中的事件有更高的响应级别**。同一次事件循环中，微任务永远在宏任务之前执行。 
以下事件属于宏任务：

* setTimeout
* MessageChannel
* postMessage
* setImmediate

以下事件属于微任务

* new Promise()
* new MutaionObserver()

所以，下面的代码的输出就是`2、3、1`：

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





