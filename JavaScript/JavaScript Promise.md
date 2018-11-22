## 基本内容
### Promise 是什么
Promise 是对异步处理的一种抽象。

在 JavaScript 中，我们通常使用回调函数来进行异步处理，这样就会导致代码出现“回调地狱”，而且如果出了问题，也很难进行捕获处理。

Promise 将异步处理进行抽象，并形成规范化的接口，通过使用 Promise 的接口，能够将异步代码写成同步形式，而且还能方便的处理错误和异常。

### Promise 的状态
Promise 有三种状态：

* pending    暂停
* fulfilled  成功
* rejected   失败

这三种状态只有两种变化路径：

* pending --> fulfilled
* pending --> rejected

一旦 Promise 对象的状态完成变化，就不会再改变了。

### 创建 Promise 对象
标准的一个 Promise 对象可以使用如下语句进行创建：

```javascript
new Promise(function fn(resolve, reject) {});
```

这会返回一个状态为`pending`的 Promise 对象。

这里传入了一个函数参数`fn`，并且有两个参数，`resolve`和`reject`，分别是 Promise 中的两个方法，均由 JavaScript 引擎提供，不需要自行部署。

我们一般会在 fn 中指定需要的处理逻辑，并根据处理结果更改 Promise 对象的状态：

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

> 注意：**Promise 对象一旦创建，就会立即执行。**

### 使用
创建得到一个 Promise 对象后，我们就可以为其添加后续的处理方法：

* 当对象被`resolve`时的处理方法（onFulfilled）
* 当对象被`reject`时的处理方法（onRejected）

这其实就相当于之前的回调方法，他们都是在异步任务完成之后，会被自动的调用：如果成功了，则调用 成功回调，否则调用失败回调。当然，我们也可以只指定一个后续的处理方法：只处理成功、或者只处理失败。

```javascript
// 处理成功和失败
later(1000).then(
    function onFulfilled(data) {},
    function onRejected(err) {}
);

// 只处理成功
later(1000).then(
    function onFulfilled(data) {}
);

// 只处理失败
later(1000).then(
    null,
    function onRejected(err) {}
);

// 只处理成功，但是在最后统一处理整个过程中可能的异常
later(1000)
  .then(function onFulfilled(data) {})
  .catch(function onRejected(err) {});
```

当然，Promise 并不是只有`then()`方法可以调用，后面还会介绍其他的方法。


## 方法
### resolve()
`resolve()`方法就是创建 Promise 对象时传入的函数的第一个参数，其用来将 Promise 对象的状态置为成功(fulfilled)，并将异步操作结果 value 作为参数传给成功回调函数。

简单来说，`resolve()`就是用来将 Promise 对象从 pending 状态转成 fulfilled 状态的。而且其有一个参数，表示将要传递给后续的成功处理方法的结果。

由于`resolve()`方法会返回一个新的 Promise 实例，所以它可以看做`new Promise()`的快捷方式，快速创建一个 resovled 状态的 Promise，并可以继续进行链式调用。

```javascript
Promise.resolve('Success');

/*******等同于*******/
new Promise(function (resolve) {
    resolve('Success');
});
```

`Promise.resolve()`的另一个作用就是将 thenable 对象（即带有`then`方法的对象）转换为 promise 实例。

```javasscript
var p1 = Promise.resolve({ 
    then: function (resolve, reject) { 
        resolve("this is an thenable object!");
    }
});
console.log(p1 instanceof Promise);     // => true

p1.then(function(value) {
      console.log(value);     // => this is an thenable object!
}, function(e) {
    // not called
});

var p3 = { 
    then: function(resolve) {
        throw new Error("error");
        resolve("Resolved");
    }
};

var p4 = Promise.resolve(p3);
p4.then(function(value) {
    // not called
}, function(error) {
    console.log(error);       // => Error: error
});
```

传递给`resolve()`方法的参数，并不一定是不可变值，也可以是一个新的 Promise 对象。 [Promise/A+](https://promisesaplus.com/) 规范中是这么说的：

> `[[Resolve]](promise, x)`中：
> 2.3.2. 如果 x 是一个 Promise 实例， 则以 x 的状态作为 promise 的状态：
> 2.3.2.1. 如果 x 的状态为 pending， 那么 promise 的状态也为 pending，直到 x 的状态变化而变化。
> 2.3.2.2. 如果 x 的状态为 fulfilled，promise 的状态也为 fulfilled，并且以 x 的不可变值作为 promise 的不可变值。
> 2.3.2.3. 如果 x 的状态为 rejected，promise 的状态也为 rejected，并且以 x 的不可变原因作为 promise 的不可变原因。
> 
> 2.3.4. 如果 x 不是对象或函数，则将 promise 状态转换为 fulfilled 并且以 x 作为 promise 的不可变值。

可以看下面的两个例子来理解：

示例1：传递基本类型值

```javascript
var d = new Date();

var promise = new Promise(function(resolve, reject) {
    // 一秒后进入resolve，并传递值
    setTimeout(resolve, 1000, 'resolve from promise');
});

// 绑定回调函数
promise.then(
    result => console.log('result:', result, new Date() - d),
    error => console.log('error:', error)
);

// result: resolve from promise 1002
```

这个的结果很容易理解：因为传递给`resolve()`方法的值是一个字符串`resolve from promise`，所以匹配规范中的 2.3.4 从而直接将这个字符串作为最终的不可变值传递给了后续的成功回调函数。

示例2：传递 Promise 实例

```javascript
var d=new Date();

// 创建一个promise实例，该实例在2秒后进入fulfilled状态
var promise1 = new Promise(function(resolve, reject) {
    setTimeout(resolve, 2000, 'resolve from promise 1');
});

// 创建一个promise实例，该实例在1秒后进入fulfilled状态
var promise2 = new Promise(function(resolve, reject) {
    setTimeout(resolve, 1000, promise1); // resolve(promise1)
});

promise2.then(
    result => console.log('result:', result,new Date()-d),
    error => console.log('error:', error)
);
```

这里的最终输出的结果是`result: resolve from promise 1 2002`。这是因为：promise2 中调用了`resolve(promise1)`，此时 promise1 的状态会传递给 promise2，或者说 promise1 的状态决定了 promise2 的状态。所以当 promise1 进入 fulfilled 状态，promise2 的状态也变为 fulfilled，同时将 promise1 自己的不可变值作为 promise2 的不可变值，所以 promise2 的回调函数打印出了上述结果。

另外，通过这里例子我们也可以发现。运行时间是 2 秒而不是 3 秒。也就是说 **Promise 新建后就会立即执行**。

前面提到过，Promise 状态一旦改变就会凝固，就不会再改变。因此 Promise 一旦 fulfilled 了，再抛错，也不会变为 rejected，就不会被 catch 了。

```javascript
var promise = new Promise(function(resolve, reject) {
  resolve();
  throw 'error';
});

promise.catch(function(e) {
   console.log(e);      //This is never called
});
```

### reject()
`reject()`用来将 Promise 对象的状态置为失败(rejected)，并将异步操作错误 error 作为参数传给失败回调函数。

`reject()`方法和`resolve()`方法作用类似，只是其是将 Promise 对象从 pending 状态转成 rejected 状态的。而且其参数表示要传递给后续的失败处理的结果，一般是一个异常对象，当然也可以是不可变值。

`reject()`方法的作用，等同于直接抛错。而且它也能够快速创建 Promise 实例，只是其返回的实例的状态是 rejected。

```javascript
var promise = new Promise(function (resolve, reject) {
    throw new Error('test');
});
/*******等同于*******/
var promise = new Promise(function (resolve, reject) {
    reject(new Error('test'));
});

//用catch捕获
promise.catch(function (error) {
    console.log(error);
});

/*
-------output-------
Error: test
*/
```

### then()
`then()`方法是最常用的方法，可以用来给 Promise 对象添加后续的处理回调函数。其可以接受两个参数，分别对应 Promise 成功时的处理回调和失败时的处理回调。简单来说，`then()`就是定义 `resolve`和`reject`函数的。例如，其`resolve`参数相当于：

```javascript
promise.then(function resolveFun(data) {
    // data 为 promise 传出的值
}, function rejectedFun(err) {
    // err 为 promise 抛出的错误
})
```

而新建 Promise 实例中的`resolve(data)`则相当于执行这个`resolveFun`函数，同理，`rejected(err)`则相当于执行这个`rejectedFun`函数。

虽然一般我们会给`then()`方法传入函数，但是其实它也可以接收非函数值。**给`.then()`传递非函数值时，实际上会被解析成`then(null)`，从而导致上一个 Promise 对象的结果被“穿透”**。所以，为了避免不必要的麻烦，建议总是给`then()`传递函数。

> 注意：**`then()`方法会返回一个新的 Promise 对象，这个对象和调用`then()`方法的对象不是同一个。**

```JavaScript
later(1000)
  .then(later(2000))
  .then(function(data) {
    // data = later_1000
  });
  
// 由于 later(2000) 表示函数执行的结果
// 所以上面的代码等价于
later(1000)
  .then(null)
  .then(function(data) {
    // data = later_1000
  });
```

需要注意的是：虽然 Promise 实例一旦创建就会立即执行，但是**`then`方法中指定的回调函数，将在当前脚本所有同步任务执行完才会执行**。如下例：

```javascript
var promise = new Promise(function(resolve, reject) {
    console.log('before resolved');
    resolve();
    console.log('after resolved');
});

promise.then(function() {
    console.log('resolved');
});

console.log('outer');

/*
-------output-------
before resolved
after resolved
outer
resolved
*/
```

由于 resolve 指定的是异步操作成功后的回调函数，它需要等所有同步代码执行后才会执行，因此最后打印'resolved'。


#### onFulfilled 参数
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

#### onRejected 参数
这个参数在 Promise 实例状态变为 rejected 的时候会被调用，通常用于处理异步操作失败的情况。

需要注意的是，onRejected 回调只会在**当前 Promise 实例**为 rejected 的时候被调用，也就是说，在链式调用中，当前`then()`方法的 onRejected 回调只会在上一个`then()`返回的 Promise 实例被 rejected 的时候调用；而对于当前`then()`自身返回的 Promise 实例的 rejected 状态时并不会被调用。

比如，在当前`then()`的 onFulfilled 回调用中抛出了一个异常，这个异常并不能被当前`then()`方法的 onRejected 回调捕获并处理。

所以，一般推荐使用 Promise 实例的`catch()`方法进行异常处理。

#### 返回值
**`then()`方法返回的是一个新的 Promise 实例**。

那么，`then()`绑定的回调方法 onFulfilled 和 onRejected 中，不同的返回值对后续链式有什么影响呢？

Promise/A+ 规定：

> 2.2.7. `then()`方法必须返回一个 Promise 实例：`promise2 = promise1.then(onFulfilled, onRejected);`
> 2.2.7.1. 如果 onFulfilled 或 onRejected 函数返回值为 x，那么执行 Promise 处理过程`[[Resolve]](promise2, x)`。
> 2.2.7.2. 如果 onFulfilled 或 onRejected 函数抛出异常 e，那么 promise2 将执行 `reject(e)`。
> 2.2.7.3. 如果 promise1 的 onFulfilled 不是函数，那么 promise1 的不可变值将传递到promise2 并作为 promise2 的不可变值，并作为 promise2 的 onFulfilled 的入参。
> 2.2.7.4. 如果 promise1 的 onRejected 不是函数，那么 promise1 的不可变原因将传递到promise2 并作为 promise2 的不可变原因，并作为 promise2 的 onRejected 的入参。

所以，根据 2.2.7.1 中的规定，和前面对`resolve()`方法的介绍：如果在`then()`的 onFulfilled 或 onRejected 回调中返回了基本值，那么就会将这个基本值传递给后面的 onFulfilled 回调；如果返回的是一个新的 Promise 实例，那么这个实例的状态将决定`then()`方法返回的 Promise 实例的状态。

> 当`then()`的回调方法没有返回值的时候，就相当于返回了`undefined`。

同时，2.2.7.3 和 2.2.7.4 的规定说明，如果当前`then()`中的 onFulfilled 或 onRejected 参数不是一个函数，那么会将上一个 Promise 实例的结果传递给当前`then()`方法的下一个`then()`，相当于当前的`then()`不存在，被穿透了。

**1. 返回一个 Promise 实例**
返回一个 Promise 实例就是在做异步操作的串行化。也就是一个异步任务在前一个异步任务完成后再进行。

比如，下面的代码会在 1 秒后获得第一个 Promise 实例的结果，然后再过 2 秒后获得第二个 Promise 实例的结果。

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

**2. 返回一个同步值**
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

**3. (同步)抛出一个 Error 对象**
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

### all()
`all()`方法用于将多个 Promise 实例，包装成一个新的 Promise 实例。

该方法接受一个数组作为参数，数组里的元素都是 Promise 对象的实例，如果不是，就会先调用下面讲到的`Promise.resolve()`方法，将参数转为 Promise 实例，再进一步处理。（Promise.all方法的参数可以不是数组，但必须具有 Iterator 接口，且返回的每个成员都是 Promise 实例。）

`all()`方法返回的新的 Promise 实例具有如下的特性：

* 当该数组里的所有 Promise 实例都进入 Fulfilled 状态 ，这个新的的实例才会变成 Fulfilled 状态。并将原来的 Promise 实例数组的所有返回值按参数的顺序（而不是 resolved 的顺序）存入一个数组，并传递给新实例的回调函数。
* 当该数组里的某个 Promise 实例进入 Rejected 状态，返回的新的实例会立即变成 Rejected 状态，并将第一个 rejected 的实例返回值传递给新实例的回调函数。同时，其他的 Promise 实例会继续执行，但是执行的结果并不会影响返回的实例的状态了。

```javascript
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

```javascript
var p1 = new Promise((resolve, reject) => { 
    setTimeout(function(){ 
        resolve("one");
        console.log('one');
    }, 1000); 
}); 
var p2 = new Promise((resolve, reject) => { 
    setTimeout(function(){
        reject("two");
        console.log('two');
    }, 2000); 
});
var p3 = new Promise((resolve, reject) => {
    reject("three");
    console.log('three');
});

Promise.all([p1, p2, p3]).then(function (value) {
    console.log('resolve', value);
}, function (error) {
    console.log('reject', error);    // => reject three
});

/*
-------output-------
three
reject three
one
two
*/
```

### race()
`race()`方法跟`Promise.all()`方法差不多。唯一的区别在于该方法返回的 Promise 实例并不会等待所有 Proimse 都跑完，而是只要有一个 Promise 实例改变状态，它就跟着改变状态。并使用第一个改变状态实例的返回值作为返回值。

在第一个 Promise 实例变为 resolve 后，并不会取消其他 Promise 实例的执行。

```javascript
var fastPromise = new Promise(function (resolve) {
    setTimeout(function () {
        console.log('fastPromise');
        resolve('resolve fastPromise');
    }, 100);
});

var slowPromise = new Promise(function (resolve) {
    setTimeout(function () {
        console.log('slowPromise');
        resolve('resolve slowPromise');
    }, 1000);
});

// 第一个 promise 变为 resolve 后程序停止
Promise.race([fastPromise, slowPromise]).then(function (value) {
    console.log(value);    // => resolve fastPromise
});

/*
-------output-------
fastPromise
resolve fastPromise
slowPromise     //仍会执行
*/
```

### catch()
`Promise.prototype.catch`方法是`.then(null, rejection)`的别名，用于指定发生错误时的回调函数。

```javascript
// 下面两种写法是等价的
somePromise.catch(function(err) {
    //...
})
somePromise.then(null, function(err) {
    //...
})

// 但是下面两段代码是不等价的
// 1
somePromise.then(function() {
    return someOtherPromise();
}, function(err) {
    //...
})
// 2
somePromise.then(function() {
    return someOtherPromise();
}).catch(function(err) {
    //...
})
```

所以推荐大家都是用`catch()`来处理失败情况，而不是`then()`的第二个参数。可以在 promise 最后都加上一个 catch，以处理可能没有察觉到的错误情况。

Promise 对象的错误，会一直向后传递，直到被捕获。即错误总会被下一个 catch 所捕获(或被下一个 then 方法中的 onRejectedFun 捕获)。then 方法指定的回调函数，若抛出错误，也会被下一个 catch 捕获。catch 中也能抛错，但需要更后面的 catch 来捕获。

```javascript
somePromise.then(function(data1) {
    // do something
}).then(function (data2) {
    // do something
}).catch(function (error) {
    //处理前面三个Promise产生的错误
});
```

如果抛出的错误没有没有被其后面的 then 或 catch 捕获，那么这个错误会导致 Promise 状态穿透，跳过其后的 then 或 catch，继续后传，直到被捕获。也就是说，抛出错误后，后面不能捕获这个错误的 then 或 catch 会被忽略，不被执行。

```javascript
/*******状态穿透*********/
function taskA() {
    console.log(x);  // 这里 x 未定义，会出错
    console.log("Task A");
}
function taskB() {
    console.log("Task B");
}
function onRejected(error) {
    console.log("Catch Error: A or B", error);
}
function finalTask() {
    console.log("Final Task");
}
var promise = Promise.resolve();
promise
    .then(taskA)
    .then(taskB)
    .catch(onRejected)
    .then(finalTask);
   
/* 
-------output-------
Catch Error: A or B,ReferenceError: x is not defined
Final Task
*/
```

这段代码的流程图如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1475200270424.png" width="275"/>

可以看出，A 抛错时，会按照`taskA → onRejected → finalTask`这个流程来处理。A 抛错后，若没有对它进行处理，状态就会维持 rejected ，taskB 不会执行，直到 catch 了错误。如果 A 抛错之后，其后立即捕获了这个错误，那么 taskB 就可以执行：

```javascript
function taskA() {
    console.log(x);
    console.log("Task A");
}
function taskB() {
    console.log("Task B");
}
function onRejectedA(error) {
    console.log("Catch Error: A", error);
}
function onRejectedB(error) {
    console.log("Catch Error: B", error);
}
function finalTask() {
    console.log("Final Task");
}
var promise = Promise.resolve();
promise
    .then(taskA)
    .catch(onRejectedA)
    .then(taskB)
    .catch(onRejectedB)
    .then(finalTask);
  
/*  
-------output-------
Catch Error: A ReferenceError: x is not defined
Task B
Final Task
*/
```

如果最终没有使用 catch 方法指定处理错误的回调函数，Promise 对象抛出的错误不会传递到外层代码，即不会有任何反应（Chrome 会抛错），这也是 Promise 的一个缺点。

需要注意的是：**在异步回调中抛错，不会被 catch 到**。

```javascript
// Errors thrown inside asynchronous functions will act like uncaught errors
var promise = new Promise(function(resolve, reject) {
  setTimeout(function() {
    throw 'Uncaught Exception!';
  }, 1000);
});

promise.catch(function(e) {
  console.log(e);       //This is never called
});
```


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
3. [打开Promise的正确姿势](http://imweb.io/topic/57a0760393d9938132cc8da9)
4. [初探Promise](https://segmentfault.com/a/1190000007032448)

