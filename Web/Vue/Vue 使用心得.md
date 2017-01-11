在非单页应用中，使用 Vue 进行页面操作比传统的 jQuery 操作更加的方便直接。下面是一些使用过程中积累的心得体会。

### ajax 请求
Vue 本身不自带 ajax 功能，但是可以方便的引入任意版本的 ajax 功能或库。比如可以使用 jQuery 来提供 ajax 功能，也可以使用 vue-reoursce 来提供。

在一个页面中，可能会有多个地方需要使用到 ajax，而一般 ajax 的错误处理、loading 层的显示和隐藏都是相同的，那么我们就可以提供一个统一的方法来完成 ajax 请求，同时这个方法可以接收一些基本参数来实现不同的具体功能。

下面就是我使用 jQuery 的 ajax 来封装的一个 post 请求处理方法：

```JavaScript
// 发起 ajax post 请求 // url 请求地址; data 请求数据; callback 请求成功的回调方法 post: function (url, data, callback) {     this.loading = true;     var self = this;      $.ajax({         url: url,         method: 'post',         dataType: 'json',         data: data     })     .done(callback)     .fail(function(){ alert('网络故障，请稍后重试。'); })     .always(function(){ self.loading = false; }); }
```

其中，`this.loading`是用来控制 loading 层的显示和隐藏的。



