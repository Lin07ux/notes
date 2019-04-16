作为 Web Storage API 的接口，Storage 提供了访问特定域名下的会话存储或本地存储的功能，例如，可以添加、修改或删除存储的数据项。

### 1. 分类

Storage 在目前的浏览器中主要有两种实现：`window.localStorage`和`window.sessionStorage`。这两种属性都是只读的，但是也有一些区别：

* `window.localStorage`可以访问一个 Document 源（origin）的对象 Storage。其存储的数据能在跨浏览器会话保留，也就是在同一个源中的 localStorage 访问的是同一个 Storage。存储在 localStorage 的数据没有过期时间设置，可以长期保留，即便关闭浏览器也存在。

* `window.sessionStorage`可以访问一个 session Storage 对象。当页面被关闭时，存储在 sessionStorage 的数据会被清除。但页面会话在浏览器打开期间一直保持，并且重新加载或恢复页面仍会保持原来的页面会话。在**新标签或窗口**打开一个页面时会在顶级浏览上下文中初始化一个新的会话，这点和 session cookies 的运行方式不同。


应注意，无论数据存储在 localStorage 还是 sessionStorage，它们都特定于页面的协议。

### 2. 属性

Storage 对象自身只有一个属性：

* `Storage.length` 返回一个整数，表示存储在 Storage 对象中的数据项数量。

### 3. 方法

* `Storage.key(n)` 该方法接受一个数值 n 作为参数，并返回存储中的第 n 个键名。
* `Storage.getItem(key)` 该方法接受一个键名作为参数，返回键名对应的值。
* `Storage.setItem(key, value)` 该方法接受一个键名和值作为参数，将会把键值对添加到存储中，如果键名存在，则更新其对应的值。
* `Storage.removeItem(key)` 该方法接受一个键名作为参数，并把该键名从存储中删除。
* `Storage.clear()` 调用该方法会清空存储中的所有键名。

这些方法中，键名都是区分大小写的，而且传入的值如果不是字符串的话，会被转化成字符串进行存储。

使用示例如下：

```JavaScript
localStorage.setItem('bool', true);
localStorage.getItem('bool');     // 输出字符串 "true"

localStorage.setItem('integer', 1);
localStorage.getItem('integer');  // 输出字符串 "1"

localStorage.setItem('object', { name: 'object' });
localStorage.getItem('object');   // 输出字符串 "[object Object]"

localStorage.setItem('object', JSON.stringify({ name: 'object' }));
localStorage.getItem('object');   // 输出字符串 "{"name":"object"}"
localStorage.getItem('Object');   // 输出 null

localStorage.removeItem('object');
localStorage.getItem('object');   // 输出 null

localStorage.clear();
localStorage.length;              // 输出 0
```

### 4. 事件

Storage 对象发生变化(如增加、删除、修改、清空等)的时候，会触发`storage`事件，对应的事件对象是`StorageEvent`。

要注意的是：**事件在同一个域下的不同页面之间触发**，即，在 A 页面注册了 storge 事件的监听处理，只有在跟 A 同域名下的 B 页面操作 Storage 对象(localStorage)，A 页面才会被触发 storage 事件，而在 A 页面上修改 Storage 则不会触发(但会触发在 B 页面中的 storage 事件)。

> 由于 sessionStorage 只在当前会话中有效，无法夸页面共享数据，所以目前 storage 事件也就只有 localStorage 对象的操作才可以触发。

storage 事件对象主要有如下几个属性：

* `key` 代表发生变化的属性名，当被`clear()`方法清除之后所有属性名变为 null。只读。
* `newValue` 修改后的值。当被`clear()`方法执行过或者键名已被删除时值为 null。只读。
* `oldValue` 修改前的值。当被`clear()`方法执行过，或在设置新值之前并没有设置初始值时则返回 null。只读。
* `stroageArea` 被操作的 Storage 对象，如 localStorage 对象。只读。
* `url` 触发该事件的操作发生在的文档的 URL 地址。只读。

比如，打开同一个域名下的两个页面 A 和 B，在 A 页面中执行如下程序监听 storage 事件：

```JavaScript
window.addEventListener('storage', event => console.log(event), false)
```

然后在 B 页面中执行如下程序，分别添加、修改、删除、和清除 localStorage：

```JavaScript
localStorage.setItem('bool', true);
localStorage.setItem('integer', 1);
localStorage.setItem('object', JSON.stringify({ name: 'object' }));
localStorage.setItem('object', { name: 'object' });
localStorage.removeItem('object');
localStorage.clear();
```

相应的，A 页面中会有类似如下的输出：

```JavaScript
// setItem('bool', true)
StorageEvent {isTrusted: true, key: "bool", oldValue: null, newValue: "true", url: "https://learnku.com/articles/6139/laravel-container-container-concept-detailed-last", …}

// setItem('integer', 1)
StorageEvent {isTrusted: true, key: "integer", oldValue: null, newValue: "1", url: "https://learnku.com/articles/6139/laravel-container-container-concept-detailed-last", …}

// setItem('object', JSON.stringify({ name: 'object' }))
StorageEvent {isTrusted: true, key: "object", oldValue: null, newValue: "{"name":"object"}", url: "https://learnku.com/articles/6139/laravel-container-container-concept-detailed-last", …}

// setItem('object', { name: 'object' })
StorageEvent {isTrusted: true, key: "object", oldValue: "{"name":"object"}", newValue: "[object Object]", url: "https://learnku.com/articles/6139/laravel-container-container-concept-detailed-last", …}

// removeItem('object')
StorageEvent {isTrusted: true, key: "object", oldValue: "[object Object]", newValue: null, url: "https://learnku.com/articles/6139/laravel-container-container-concept-detailed-last", …}

// clear()
StorageEvent {isTrusted: true, key: null, oldValue: null, newValue: null, url: "https://learnku.com/articles/6139/laravel-container-container-concept-detailed-last", …}
```

### 5. 参考

* [Storage - MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/Storage)
* [localStorage - MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/Window/localStorage)
* [sessionStorage - MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/Window/sessionStorage)
* [StorageEvent - MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/StorageEvent)
* [两个浏览器窗口间通信](https://xiangwenhu.github.io/blog/2019/04/15/javascript/native/page-communication/)

