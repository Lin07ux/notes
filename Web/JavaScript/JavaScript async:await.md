async 函数返回的是一个 Promise 对象，如果 return 的是一个直接量，其会被通过`Promise.resolve()`封装成 Promise 对象。即便没有明确指定返回值，也和返回一个 undefined 一样，是一个被 resolve 的 Promise 对象。



