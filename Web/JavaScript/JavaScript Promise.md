## Promise 是什么
Promise 是对异步处理的一种抽象。

在 JavaScript 中，我们通常使用回调函数来进行异步处理，这样就会导致代码出现“回调地狱”，而且如果出了问题，也很难进行捕获处理。

Promise 将异步处理进行抽象，并形成规范化的接口，通过使用 Promise 的接口，能够将异步代码写成同步形式，而且还能方便的处理错误和异常。

Promise 有三种状态：

* pending    暂停
* fulfilled  成功
* rejected   失败

这三种状态只有两种变化路径：

* pending --> fulfilled
* pending --> rejected

一旦 Promise 对象的状态完成变化，就不会再改变了。

## 创建 Promise 对象
标准的一个 Promise 对象的创建可以使用如下语句进行创建：
`new Promise(function fn(resolve, reject) {});`

这会返回一个状态为`pending`的 Promise 对象。

这里传入了一个函数参数`fn`，并且有两个参数，`resolve`和`reject`，分别是 Promise 中的两个方法，由 JavaScript 引擎提供，不需要自行部署。

我们一般会在 fn 中指定需要的处理逻辑：

* 任务处理成功时，调用`resolve([data])`方法，Promise 对象状态变更为`fulfilled`；
* 任务处理失败时，调用`reject([Error Obj])`，Promise 对象状态变更为`rejected`。

如下，我们通过 Promise 封装`setTimeout()`方法，得到了一个延时函数：

```js
const later = function(timeout) {
  return new Promise(function (resolve, reject) {
    timeout = parseInt(timeout, 10);
    
    if (!timeout) {
      return reject(new Error('Invalid Timeout'));
    }
    
    return setTimeout(function() {
      resolve('later_' + timeout);
    }, timeout);
  });
};
```

## 使用
创建得到一个 Promise 对象后，我们就可以为其添加后续的处理方法：

* 当对象被`resolve`时的处理方法（onFulfilled）
* 当对象被`reject`时的处理方法（onRejected）

这其实就相当于之前的回调方法，他们都是在异步任务完成之后，会被自动的调用：如果成功了，则调用 成功回调，否则调用失败回调。

当然，我们也可以只指定一个后续的处理方法：只处理成功、或者只处理失败。

```js
later(1000).then(
    function onFulfilled(data) {},
    function onRejected(err) {}
);

later(1000).then(
    function onFulfilled(data) {}
);

later(1000).then(
    null,
    function onRejected(err) {}
);

later(1000)
  .then(function onFulfilled(data) {})
  .catch(function onRejected(err) {});
```

## .then() 方法
Promise.then() 方法可以用来给 Promise 对象添加后续的处理回调函数。其可以接受两个参数，分别对应 Promise 成功时的处理回调和失败时的处理回调。

虽然一般我们会给`then()`方法传入函数，但是其实它也可以接收非函数值。给`.then()`传递非函数值时，实际上会被解析成`then(null)`，从而导致上一个 Promise 对象的结果被“穿透”。所以，为了避免不必要的麻烦，建议总是给`then()`传递函数。

**`then()`方法会返回一个新的 Promise 对象，这个对象和调用`then()`方法的对象不是同一个。**

```js
later(1000)
  .then(later(2000))
  .then(function(data) {
    // data = later_1000
  });
  
// 上面的代码等价于
later(1000)
  .then(null)
  .then(function(data) {
    // data = later_1000
  });
```


### onFulfilled()
当 Promise 对象的状态变成 fulfilled(也即是异步任务成功执行)时会被调用。这个函数的第一个参数会被设置为 Promise 对象的返回值。一般我们会在这个函数中对异步任务的结果进行处理。

在这个方法中，我们可以有多种处理方式：

```js
later(1000).then(function() {
  /**
   * 在这里，我们能做以下几种处理
   * 1. 返回一个 promise 对象
   * 2. 返回一个同步值（什么也不返回，那就是返回 undefined）
   * 3. throw 一个 Error 对象
   */
});
```

#### 返回一个 Promise 对象
返回一个 Promise 对象就是在做异步操作的串行化。也就是一个异步任务在前一个异步任务完成后再进行。

比如，下面的代码会在 1 秒后获得第一个 Promise 对象的结果，然后再过 2 秒后获得第二个 Promise 对象的结果。

```js
later(1000)
  .then(function(data) {
    // data = later_1000
    return later(2000);
  })
  .then(function(data) {
    // data = later_2000
  });
```

#### 返回一个同步值
返回一个同步值可以将同步代码 Promise 化。

```js
later(1000)
  .then(function(data) {
    // data = later_1000
    if (data != 'later_1000') {
      return later(1000);
    }
    return data;
  })
  .then(function(data) {
    // data = later_1000
  });
```

上面的代码保证最后获得的结果总是`later_1000`（只是等 1 秒还是 2 秒的区别）。更实用的例子是异步获取某个数据（查询 db），我们可以先从本地 cache 查询，查到直接返回同步值，否则返回一个查询 db 的 Promise 对象，最终都会获得正确的数据。

如果在 onFulfilled() 方法中，什么也不返回，则等于返回了 undefined ，所以小心下面的写法：

```js
later(1000)
  .then(function(data) {
    // data = later_1000
    later(2000);
  })
  .then(function(data) {
    // data = undefined
  });
```
上面的代码在 1 秒后取到第一个 Promise 对象的结果，然后不会等待第二个 Promise 对象的结果，马上就执行到了第二个`.then()`。最终获得的结果就是 undefined，而且第二个 Promise 对象的处理结果无法被后面`.then()`或者`.catch()`捕获到了。

#### (同步)抛出一个 Error 对象
在`.then()`里面抛出一个 Error 对象可以让错误处理更加方便。

```js
later(1000)
  .then(function(data) {
    if (data == 'later_1000') {
      throw new Error('later_1000 invalid');
    }
    
    if (data == 'later_2000') {
      return data;
    }
    
    return later(3000);
  })
  .then(function(data) {
    // 
  })
  .catch(function(err) {
    // 捕获到错误 Error('later_1000 invalid');
  });
```

只要我们调用`.catch()`添加`onRejected`回调处理，在`.then()`里面`throw`出的任何**同步**错误都会在`.catch()`里面被捕捉到（比如：不小心访问了未定义值啊、JSON.parse 错误啊等等），这让问题定位非常方便。

需要注意的是，`.catch()`能捕获的是同步错误，请小心下面的代码：

```js
later(1000)
  .then(function(data) {
    setTimeout(function() {
      throw new Error('the err can not catch');
    }, 1000);
  })
  .then(function(data) {
    // data = undefined
  })
  .catch(function(err) {
    // 捕获不到错误
  });
```

另外，虽然`.then()`方法中也可以设置`onRejected`的处理方法，但是其不能处理同属一个`.then()`方法抛出的异常和错误，需要在下一个`.then()`中的`onRejected`事件处理中去捕获这个错误。

```js
later(1000)
  .then(function(data) {
    throw new Error('this is err');
  })
  .catch(function(err) {
    // 捕获到错误 Error('this is err');
  });

later(1000)
  .then(
    function(data) {
      throw new Error('this is err');
    },
    function(err) {
      // 捕获不到错误 Error('this is err');
    }
  );
```

## .all() 方法
`Promise.all()` 方法可以接受一个数组（数组里面的元素是 Promise 对象）。当数组内所有 Promise 对象变为 fulfilled 状态时，才调用`onFulfilled`回调；数组内有任一个 Promise 对象变为 rejectec 状态时，调用`onRejected`回调。

```js
const promises = [1000, 2000, 3000].map(function(timeout) {
  return later(timeout);
});

Promise.all(promises)
  .then(function(data) {
    // data = ['later_1000', 'later_2000', 'later_3000']
  })
  .catch(function(err) {
  });
```

数组内 Promise 对象所表示的异步操作是同时执行的，并且最后的结果和传递给 Promise.all 的数组的顺序是一致的。所以，3 秒钟后我们取得的结果是一个值为`['later_1000', 'later_2000', 'later_3000']`的数组。


## 最佳实践
- 总是在`.then()`里面使用 return 来返回 Promise 对象或者同步值
- 总是在`.then()`里面 throw 同步的 Error 对象
- 总是使用`.catch()`来捕获错误


## 一些技巧
### 快速创建 Promise 对象
我们主要通过`new Promise(fn)`的方式来创建 Promise 对象，实际上有一个快捷方法`Promise.resolve(value)`可以方便的创建 Promise 对象。

Promise.resolve 的使用场景主要包括：

* 用最少的代码快速创建一个 promise 对象；
* 在 Promise 化的 API 接口中将同步代码 Promise 化，可以统一的在`.catch()`中捕获异常。

下面两种写法是等价的，显然使用 Promise.resolve 更加简练：

```js
new Promise(function(resolve, reject) {
  resolve('value');
}).then(function(data) {});

Promise.resolve('value').then(function(data) {});
```

### 解决 Promise 对象间的依赖
实际编码中我们可能经常遇到一个 Promise 对象依赖另一个 Promise 对象的执行，并且我们两个 Promise 对象的结果都需要的情况。这时就需要我们在代码中做一些改变了：

```js
later(1000)
  .then(function(dataA) {
    return later(2000);
  })
  .then(function(dataB) {
    // 我们同时需要 dataA 和 dataB
  });
  
// 可以在 onFulfilled 中返回一个新的 Promise 对象
later(1000)
  .then(function(dataA) {
    return later(2000).then(function(dataB) {
      return dataA + ':' + dataB;
    });
  })
  .then(function(data) {
    // data = later_1000:later_2000
  });
```

### 串行
在`.then()`里面返回一个 Promise 对象就是一种串行。我们需要构造一个类似下面这样的 Promise 对象：

```js
promise1
  .then(function() { return promise2; })
  .then(function() { return promise3; })
  .then(...);
```

我们也可以将一个数组转换为一个值，使用 reduce 可以实现：

```js
[1000, 2000, 3000]
  .reduce(function(promise, timeout) {
    return promise.then(function() {
      return later(timeout);
    });
  }, Promise.resolve())
  .then(function(data) {
    // data = later_3000
  })
  .catch(function(err) {
  });
```

上面代码将以下面的方式来执行：

```js
Promise.resolve()
  .then(function() { return later(1000); })
  .then(function() { return later(2000); })
  .then(function() { return later(3000) })
  .then(function(data) {
    // data = later_3000
  })
  .catch(function(err) {
  }); 
```

每个 Promise 对象表示的异步操作依次执行，最终结果将会在 6 秒后取得（只能取到最后一个 Promise 对象的结果，如果都需要的话，需要单独进行处理存储）。


## 参考
1. [你可能不知道的 Promise](http://kohpoll.github.io/blog/2016/05/02/the-promise-you-may-not-know/)
2. [JavaScript Promise迷你书（中文版）](http://liubin.org/promises-book/)

