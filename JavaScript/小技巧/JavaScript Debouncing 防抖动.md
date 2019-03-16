Debouncing 是限制下次函数调用之前必须等待的时间间隔。正确实现 debouncing 的方法是将若干个函数调用 合成 一次，并在给定时间过去之后仅被调用一次。下面是一个原生 JavaScript 的实现，用到了作用域、闭包、this 和计时事件：

```JavaScript
// 将会包装事件的 debounce 函数
function debounce(fn, delay) {
  // 维护一个 timer
  let timer = null;
  // 能访问 timer 的闭包
  return function() {
    // 通过 ‘this’ 和 ‘arguments’ 获取函数的作用域和变量
    let context = this;
    let args = arguments;
    // 如果事件被调用，清除 timer 然后重新设置 timer
    clearTimeout(timer);
    timer = setTimeout(function() {
      fn.apply(context, args);
    }, delay);
  }
}
```

这个函数当传入一个事件（fn）时 — 会在经过给定的时间（delay）后执行。类似如下的方式使用：

```JavaScript
// 当用户滚动时被调用的函数
function foo() {
  console.log('You are scrolling!');
}

// 在 debounce 中包装我们的函数，过 2 秒触发一次
let elem = document.getElementById('container');
elem.addEventListener('scroll', debounce(foo, 2000));
```


