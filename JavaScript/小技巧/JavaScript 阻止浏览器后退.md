浏览器的前进后退是属于 BOM 层面的操作，通过 JavaScript 并不能真正意义上的禁止浏览器的后退操作，但是可以通过一些方法来事实上实现即便触发了浏览器的后退操作，也不会退回到上一页的效果。

可以借助`history.pushState`来实现这种效果。思路如下：

1. 进入当前页面之后，向浏览历史中压入一个空的浏览历史；
2. 浏览器后退时触发的`popstate`事件处理函数中，再次压入一个空的浏览历史。

这样，浏览器就总会处于退出当前浏览历史，再次压入新的浏览历史的循环中。

> 这种方式是需要浏览器的支持的。

具体的代码示例如下：

```JavaScript
<script language="javascript">
   // 防止页面后退
   history.pushState(null, null, document.URL);
   window.addEventListener('popstate', function () {
       history.pushState(null, null, document.URL);
   });
</script>
```

> 参考：[利用js实现 禁用浏览器后退](https://blog.csdn.net/zc474235918/article/details/53138553)

