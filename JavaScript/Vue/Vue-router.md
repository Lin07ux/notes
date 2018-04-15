### beforeRouteLeave
该导航钩子在页面进行跳转之前被触发，所以可以在该钩子中做相应的处理工作。

但是`beforeRouteLeave`钩子可能不会被触发，一般是因为在 SPA 项目中，该组件并未直接在路由定义中被引用，而是用在某个路由对应的组件中，作为子组件存在。

此时可以在父组件中注册`beforeRouteLeave`钩子，来做相应的处理。

参考：[Vue Router beforeRouteLeave doesn't stop subcomponents](http://stackoverflow.com/questions/42045433/vue-router-beforerouteleave-doesnt-stop-subcomponents)

### next
在导航钩子中，`next`会作为第三个参数传递进去，而且可以通过这个方法来决定是取消导航，还是继续导航，抑或导航到指定路由去。

不过需要注意的是，在导航钩子中必须使用这个方法，否则路由不会做改变，而且改方法还不能在 Promise 中被使用，只能在正常的同步代码中调用才有效。

