### 表单验证

表单验证使用的是一个第三方插件 [async-validator](https://github.com/yiminghe/async-validator)，基本上参考这个插件的说明，就可以生成对应的表单自动验证。

表单验证的规则可以设置为嵌套的对象属性，比如：

```html
<el-form-item label="跳转地址" prop="content.url" v-if="form.type === 'view'">
    <el-input v-model="form.content.url" type="url" placeholder="http(s)://"></el-input>
</el-form-item>
```

这里的代码，设置 input 关联的是`form.content.url`属性，而规则是通过`prop="content.url"`来引入规则对象中的`content`对象的`url`属性规则：

```js
rules: {
    content: {
        url: [{ required: true, message: '请设置跳转地址', trigger: 'blur' }]
    }
    // 还可以这样：
    // 'content.url': [{ required: true, message: '请设置跳转地址', trigger: 'blur' }]
}
```

### DatePicker 日期选择器

日期选择器会将值设置为一个 Date 对象，而不是一个日期字符串。在 vue-devtools 中显示成如下的样式：`2017-02-06T16:00:00.000Z`。

如果直接将该值作为最终的结果进行 ajax 提交的时候，对应的字段是不会被提交的。为了解决这个问题，我们可以使用该组件的`change`事件进行处理。

> 日期选择器的`change`事件需要在 1.1.6 版本之后才有效。

在`change`事件的回调函数中，默认传入的是格式化后的值，也就是日期字符串，比如：`2017-02-06`。

> 在一般的 Vue 组件的事件回调中，如果我们使用`@change="handleChange($event)"`方式绑定，那么`$event`会表示事件对象，而在日期选择器组件中，会将`$event`赋值为格式化后的字符串。这一特性可以帮助我们完成对组件最终值的调整。

比如，我们有一系列通过 v-for 生成的 DatePicker 组件，而每个组件绑定一个特定的对象上，那么我们就可以这样绑定 change 事件：`@change="handleChange($event, data)"`。这里的 data 就是当前组件绑定的对象，那么我们就可以在事件方法中进行`data.date = $event`，从而完成值的调整，因为这里`$event`被赋值成了格式化后的日期字符串，而不是原生事件对象。

还有一点需要注意：如果使用了表单验证，对这个日期选择器组件进行 date 类型的自动验证，那么直接这样处理可能会造成日期无法选中的，会报错。这是由于将其值从 Date 对象处理成 String 对象后，Date 相关的方法就无法使用。

这个问题目前仅能通过更改验证类别为 string 来解决。暂无更好的方法。


### 事件中的`$event`

在 Vue 中，绑定事件处理器的时候，可以使用一个特殊的变量名`$event`来表示触发的事件对象，但是在 ElementUI 中定义的元素中，事件处理器中使用`$event`时一般表示的是该元素的对应值。

比如，ElementUI 中的`el-select`元素，如果绑定了`@change="handleChange($event)"`事件处理函数，则其中的`$event`就表示的是该元素选中的值了。

### 上传组件手动上传

上传组件设置`auto-upload`为 false，就可以阻止其自动上传。此时，如果需要上传，可以使用该组件的引用来调用`submit()`方法。

比如，组件的引用为`ref="upload"`，那么可以使用如下的方法：

```JavaScript
this.$refs.upload.submit();
```


