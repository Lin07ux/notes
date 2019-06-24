> 转摘：[5种处理Vue异常的方法](https://segmentfault.com/a/1190000019497128)

Vue 开发中，对于遇到的错误可以通过如下几种方式来追踪和处理。

### 1. errorHandler

`Vue.config.errorHandler`是 Vue 中最广泛使用的异常处理方式，可以将其定义为一个方法：

```JavaScript
Vue.config.errorHandler = function(err, vm, info) {
    //
}
```

这个方法可以接收三个参数：

* `err` 表示 error 对象
* `vm` 表示 Vue 实例对象
* `info` 是一个 Vue 特有的字符串

这个处理器会处理 Vue 中的代码运行错误，比如变量不存在等；但是对于 Vue 自身的警告并不会触发该处理器，比如在 DOM 模板中使用了不存在的变量。

### 2. warnHandler

`Vue.config.warnHandler`用于处理 Vue warning，但是仅在开发环境中起作用，而在生产环境是不起作用的：

```JavaScript
Vue.config.warnHandler = function(msg, vm, trace) {
    //
}
```

该方法可以接受三个参数：

* `msg` 警告信息
* `vm` 发生警告的 Vue 应用
* `trace` 发生警告时的调用链路

### 3. renderError

`renderError`不是 Vue 的全局配置项，而是和组件相关，与前两者不同，并且只适用于非生产环境。另外，该方法也是只处理错误信息，而不会处理警告信息。

下面是一个简单的例子：

```JavaScript
const app = new Vue({
  el:'#app',
  renderError (h, err) {
    return h('pre', { style: { color: 'red' }}, err.stack)
  }
})
```

这个方法接收两个参数：

* `h` 是 Vue 中的渲染方法
* `err` 具体的错误信息

### 4. errorCaptured

`errorCaptured`也是一个组件配置项，但是只是用于处理捕获来自子孙组件的错误。

此钩子会收到三个参数：错误对象、发生错误的组件实例以及一个包含错误来源信息的字符串。此钩子可以返回`false`以阻止该错误继续向上传播。

下面是一个简单的例子：

```JavaScript
Vue.component('cat', {
  template:`
<div><h1>Cat: </h1>
  <slot></slot>
</div>`,
  props:{
    name:{
      required:true,
      type:String
    }
  },
  errorCaptured(err, vm, info) {
     console.log(`cat EC: ${err.toString()}\ninfo: ${info}`); 
     return false;
  }
});

Vue.component('kitten', {
  template:'<div><h1>Kitten: {{ dontexist() }}</h1></div>',
  props:{
    name:{
      required:true,
      type:String
    }
  }
});
```

这里的`kitten`组件的代码是有 BUG 的，调用的时候会发生错误。捕获的信息如下：

```
cat EC: TypeError: dontexist is not a function
info: render
```

### 5. window.onerror

这是浏览器中一个全局的最终处理器，可以获取所有的 JavaScript 异常：

```JavaScript
window.onerror = function(message, source, line, column, error) {
    //
}
```

但需要注意的是：如果定义了`window.onerror`，但是没有启用`Vue.config.errorHandler`，那么有很多异常都抓不到。Vue 需要要定义它，否则异常不会抛出去的。

