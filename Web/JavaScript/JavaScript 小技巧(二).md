### Debouncing（防抖动）

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

### 获取 url 的 querystring 参数

url 中的 querystring 参数都存放在 location 对象的 search 属性中，但是是以字符串形式存在，一般我们需要将其取出来单独使用。下面提供两种方法：

**方法一：正则匹配**

```javascript
// 获取 url 中的指定参数
function getQueryString(name) {
    // 匹配目标参数
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", i);
    // 对 querystring 匹配目标参数
    var result = window.location.search.substr(1).match(reg);
    
    if (result != null) {
        return decodeURIComponent(result[2]);
    } else {
        return null;
    }
}

// 对于 http://localhost/index.html?q1=abc&q2=efg&q3=h 的 url
// 获取 q1 参数值的方法如下
var q1 = getQueryString('q1'); // abc
```

> 这个方法适合偶尔获取 querystring 中的某个参数的值的情况，因为其没有缓存获取的结果，而且使用的是正则，比较耗费资源。

**方法二：split**

```javascript
function getQueryString() {
    var qs    = location.search.substr(1),   // 获取 url 中"?"符后的字串
        args  = {}, // 保存参数数据的对象
        items = qs.length ? qs.split("&") : [], // 取得每一个参数项,
        item  = null,
        len   = items.length;

    for(var i = 0; i < len; i++) {
        item  = items[i].split("=");
        var name  = decodeURIComponent(item[0]),
            value = decodeURIComponent(item[1]);
            
        if(name) args[name] = value;
    }
    
    return args;
}

// 对于 http://localhost/index.html?q1=abc&q2=efg&q3=h 的 url
// 获取 q1 参数值的方法如下：首先获取全部的 querystring 参数
var qs = getQueryString();  
// 然后获取其中的指定参数
var q1 = qs["q1"]; // abc
```

转摘：[JavaScript：获取url的querystring参数](http://www.dengzhr.com/frontend/1065)

