### 隐藏 Mustache 标签(避免页面闪烁)
问题：Vue 还未实例化前， HTML 模板中的`{{ }}`( Mustache 标签) 会暴露在用户界面上，也就是说页面有那么一瞬间会将所有的`{{ }}`都显示出来，如何解决？

解决：

* 方法一：使用`v-cloak`指令，这个指令保持在元素上直到关联实例结束编译。和 CSS 规则如`[v-cloak] { display: none; }`一起用时，这个指令可以隐藏未编译的 Mustache 标签直到实例准备完毕。

```css
[v-cloak] { 
  display: none;
}
```

```html
<div v-cloak>
  {{ message }}
</div>
```

* 方法二：使用`v-text`

```html
<span v-text="msg"></span>
<!-- 等同于 -->
<span>{{msg}}</span>
```

### 同步新增的数据
问题：新增的 data 数据没法同步响应到页面？

解决：这涉及到 Vue 的响应式原理，可以先看下官方文档中的 [深入响应式原理](http://vuejs.org.cn/guide/reactivity.html)。

在实例创建之后添加属性并且让它是响应的，需要分情况对待：

* 对于 Vue 实例，可以使用`$set(key, value)`实例方法。

```javascript
vm.$set('b', 2)
// `vm.b` 和 `data.b` 现在是响应的
```

* 对于普通数据对象，可以使用全局方法`Vue.set(object, key, value)`。

```javascript
Vue.set(data, 'c', 3)
// `vm.c` 和 `data.c` 现在是响应的
```

### JavaScript 更改 input 值不引起响应

使用其他的插件来丰富 input 的表现的时候，可能会导致 Vue 的`v-model`在 input 等元素上不起作用的情况。这可能是由于引入的其他插件是使用 JavaScript 来更改表单元素的值导致的。比如`bootstrap-datetimepicker`插件，就会导致这个问题。

此时可以考虑不使用`v-model`来实现动态绑定，而是使用在`mounted`的时候将值赋值给表单元素，然后在提交或者其他必要的时候，再同步表单元素的值到 Vue 的 data 属性中。

> 可以借助`ref`指令和`$refs`属性来完成。


