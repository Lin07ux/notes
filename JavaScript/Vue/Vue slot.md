> 转摘：[vue 2.6 中 slot 的新用法](https://segmentfault.com/a/1190000019702966)

插槽是 Vue 组件的一种机制，它允许以一种不同于严格的父子关系的方式组合组件。插槽提供了一个将内容放置到新位置或使组件更通用的出口。最近发布不久的 Vue 2.6，使用插槽的语法变得更加简洁。

## 一、基础

插槽可用包裹外部的 HTML 标签或者组件，并允许其他 HTML 或组件放在具名插槽对应名称的插槽上。

### 1.1 基本插槽

下面是一个简单的示例：

```vue
// frame.vue
<template>
  <div class="frame">
    <slot></slot>
  </div>
</template>
```

这个组件最外层是一个`div`。假设`div`的存在是为了围绕其内容创建一个样式框架。这个组件可以通用地用于将框架包围在任何内容上，使用方式如下：

```vue
// app.vue
<template>
  <frame><img src="an-image.jpg"></frame>
</template>
```

在`<frame></frame>`标记之间的内容将插入到插槽所在的 frame 组件中，替换`slot`标记。这是最基本的方法。

### 1.2 插槽默认内容

还可以简单地通过填充指定要放入槽中的默认内容：

```vue
// frame.vue
<template>
  <div class="frame">
    <slot>如果这里没有指定任何内容，这就是默认内容</slot>
  </div>
</template>
```

现在如果这样使用它，那么它就会显示`如果这里没有指定任何内容，这就是默认内容`：

```vue
// app.vue
<template>
  <frame></frame>
</template>
```

而此时如果想前面那样，提供一个`img`，则就会用提供的`img`替换这段默认的内容。

### 1.3 多个/命名的插槽

可以向组件添加多个插槽，但是如果这样做了，那么除了其中一个之外，其他所有插槽都需要有名称。如果有一个没有名称的槽，它就是默认槽。

下面创建多个插槽：

```vue
// titled-frame
<template>
  <div class="frame">
    <header>
      <h2>
        <slot name="header">Title</slot>
      </h2>
    </header>
    <slot>如果这里没有指定任何内容，这就是默认内容</slot>
  </div>
</template>
```

这在原来的基础上添加了一个新的插槽，名称为`header`，可以用来设置标题。用法如下：

```vue
// app.vue
<template>
  <titled-frame>
    <template v-slot:header>
      <!-- The code below goes into the header slot -->
      My Image’s Title
    </template>
    <!-- The code below goes into the default slot -->
    <img src="an-image.jpg">
  </titled-frame>
</template>
```

就像之前一样，如果想将内容添加到默认槽中，只需将其直接放在`titled-frame`组件中。但是，要将内容添加到命名槽中，需要用`v-slot`指令将代码包裹在在`template`标记中。在`v-slot`之后添加冒号(`:`)，然后写出要传递内容的`slot`的名称。

> 注意，`v-slot`是 Vue 2. 6的新版本，如果使用的是旧版本，则需要阅读[关于不推荐的 slot 语法的文档](https://vuejs.org/v2/guide/components-slots.html#Named-Slots)。

### 1.4 作用域插槽

插槽组件可以将组件自身的*数据/函数*传递给插槽中的内容，具有如下特点：

* 可以使用`v-bind`指令为插槽绑定多个值。
* 也可以将函数传递到作用域槽。
* `v-slot`的别名是`#`，因此，可以用`#header="data"`来代替`v-slot:header="data"`，还可以使用`#header`来代替`v-slot:header`(前提：不是作用域插槽时)。对于默认插槽，在使用别名时需要指定默认名称，换句话说，需要这样写`#default="data"`，而不是`#="data"`。

下面创建一个组件，该组件将当前用户的数据提供给其插槽：

```vue
// current-user.vue
<template>
  <span>
    <slot v-bind:user="user">{{ user.lastName }}</slot>
  </span>
</template>

<script>
export default {
  data () {
    return {
      user: { ... }
    }
  }
}
</script>
```

该组件有一个名为`user`的属性，其中包含关于用户的详细信息。默认情况下，组件显示用户的姓，但请注意，它使用`v-bind`将用户数据绑定到`slot`。这样，就可以使用这个组件向它的后代提供用户数据。

为了访问传递给`slot`的数据，需要使用`v-slot`指令的值指定作用域变量的名称：

```vue
// app.vue
<template>
  <current-user>
    <template v-slot:default="slotProps">{{ slotProps.user.firstName }}</template>    
  </current-user>
</template>
```

这里有几点需要注意：

* 可以不为默认槽指定名称，这里也可以使用`v-slot="slotProps"`代替`v-slot:default="slotProps"`。
* 不是一定要使用`slotProps`作为名称，可以随便叫它什么。
* 如果只使用默认槽，可以跳过内部`template`标记，直接将`v-slot`指令放到组件`current-user`上。
* 可以使用对象解构来创建对作用域插槽数据的直接引用，而不是使用单个变量名。换句话说，可以使用`v-slot="{user}"`代替`v-slot="slotProps"`，然后可以直接使用`user`而不是`slotProps.user`。

所以，上面的例子可以这样重写：

```vue
// app.vue
<template>
  <current-user v-slot="{user}">{{ user.firstName }}</current-user>
</template>
```

## 二、使用

插槽不是为了一个目的而构建的，或者至少如果它们是，它们已经超越了最初的意图，成为做许多不同事物的强大工具。

### 2.1 可重用的模式

组件总是被设计为可重用的，但是某些模式对于使用单个“普通”组件来实施是不切实际的，因为为了自定义它，需要的`props`数量可能过多或者需要通过`props`传递大部分内容或其它组件。

假设开发中正在使用 Bootstrap。这就要求按钮通常与基本的`btn`类和指定颜色的类绑定在一起，比如`btn-primary`，还可以添加`size`类，比如`btn-lg`。

为了简单起见，现在假设应用经常使用`btn`、`btn-primary`和`btn-lg`。如果在任何地方都要写着三个类，那么会造成很多的不便和重复工作，而且新手可能也会忘记写下这三个类。

在这种情况下，可以创建一个自动包含所有这三个类的组件，但是如何允许自定义内容？`prop`不实用，因为允许按钮包含各种 HTML，因此应该使用一个插槽：

```vue
<!-- my-button.vue -->
<template>
  <button class="btn btn-primary btn-lg">
    <slot>Click Me!</slot>
  </button>
</template>
```

现在可以在任何地方使用它，无论想要展示什么内容：

```vue
<!-- 使用 my-button.vue -->
<template>
  <my-button>
    <img src="/img/awesome-icon.jpg"> 我是小智！
  </my-button>
</template>
```

当然，也可以使用类似的方式使用 Bootrap 中比按钮更复杂的东西，比如模态。

### 2.2 复用函数

Vue 组件并不完全是关于 HTML 和 CSS 的。它们是用 JavaScript 构建的，所以也是关于函数的。插槽对于一次性创建函数并在多个地方使用功能非常有用。

下面是使用 Bootstrap 模态封装的一个组件：

```vue
<!-- my-modal.vue -->
<template>
<div class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <slot name="header"></slot>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <slot name="body"></slot>
      </div>
      <div class="modal-footer">        
        <slot name="footer" :closeModal="closeModal"></slot>
      </div>
    </div>
  </div>
</div>
</template>

<script>
export default {
  //...
  methods: {
    closeModal () {
      // 关闭对话框时，需要做的事情
    }
  }
}
</script>
```

当使用此组件时，可以向`footer`添加一个可以关闭模态的按钮。通常，在 Bootstrap 模式的情况下，可以将`data-dismiss ="modal"`添加到按钮来进行关闭，但希望隐藏 Bootstrap 特定的东西。所以传递给插槽一个可以调用的函数，这样使用者就不会知道我们有使用 Bootstrap 的东西。

使用方式如下：

```vue
<!-- 使用 my-modal.vue -->
<template>
  <my-modal>
    <template #header>
      <h5>Awesome Interruption!</h5>
    </template>
    <template #body>
      <p>大家加油！</p>
    </template>
    <template #footer="{closeModal}">
      <button @click="closeModal">点我可以关闭烦人的对话框</button>
    </template>
  </my-modal>
</template>
```

### 2.3 无渲染组件

最后，可以利用插槽来传递可重用函数的特性，并剥离所有 HTML，只使用插槽。这就是无渲染组件的本质：一个只提供函数而不包含任何 HTML 的组件。

使组件真正无渲染可能有点棘手，因为需要编写`render`函数而不是使用模板来消除对根元素的依赖，但它可能并不总是必要的。来看看一个先使用模板的简单示例：

```vue
<template>
  <transition name="fade" v-bind="$attrs" v-on="$listeners">
    <slot></slot>
  </transition>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
```

这是一个无渲染组件的奇怪例子，因为它甚至没有任何 JavaScript。这主要是因为我们正在创建一个内置无渲染函数的预配置可重用版本`transition`。

是的，Vue 有内置的无渲染组件。这个特殊的例子取自 Cristi Jora的一篇关于[可重用transition](https://vuejsdevelopers.com/2018/02/26/vue-js-reusable-transitions/)的文章，展示了一种创建无渲染组件的简单方法，该组件可以标准化整个应用程序中使用的 transition。

另一个示例，将创建一个组件来处理切换 Promise 的不同状态中显示的内容：`pending`、`resolved`和`failed`。这是一种常见的模式，虽然它不需要很多代码，但是如果没有为了可重用性而提取逻辑，它会使很多组件变得混乱：

```vue
<!-- promised.vue -->
<template>
  <span>
    <slot  name="rejected"  v-if="error" :error="error"></slot>
    <slot  name="resolved"  v-else-if="resolved" :data="data"></slot>
    <slot  name="pending"  v-else></slot>
  </span>
</template>

<script>
export  default {
  props: {
    promise: Promise
  },

  data: () => ({
    resolved: false,
    data: null,
    error: null
  }),  

  watch: {
    promise: {
      handler (promise) {
        this.resolved = false
        this.error = null

        if (!promise) {
          return this.data = null
        }

        promise.then(data => {
          this.data = data
          this.resolved = true
        })
        .catch(err => {
          this.error = err
          this.resolved = true
        })
      },
      immediate: true
    }
  }
}
</script>
```

该组件接收一个 Promise 类型参数，在`watch`部分中，监听 promise 的变化，当 promise 发生变化时，清除状态，然后调用`then`并`catch` promise，当 promise 成功完成或失败时更新状态。

然后，在模板中根据状态显示一个不同的槽。请注意，没有保持它真正的无渲染，因为需要一个根元素来使用模板。同时，还将`data`和`error`传递到相关的插槽范围。

可以这样使用这个组件：

```vue
<template>
  <div>
    <promised :promise="somePromise">
      <template #resolved="{ data }">Resolved: {{ data }}</template>
      <template #rejected="{ error }">Rejected: {{ error }}</template>
      <template #pending>请求中...</template>
    </promised>
  </div>
</template>
```

这里，将`somePromise`传递给无渲染组件，然后等待它完成。对于`pending`插槽，显示“请求中...”。如果成功，显示`Resolved：对应的值`。如果失败，显示`已Rejected：失败的原因`。现在，不再需要跟踪此组件中的 promise 的状态，因为该部分被拉出到它自己的可重用组件中。

那么，可以做些什么来绕过`promised.vue`中的插槽？要删除它，需要删除`template`部分并向我们的组件添加`render`函数：

```vue
render () {
  if (this.error) {
    return this.$scopedSlots['rejected']({error: this.error})
  }

  if (this.resolved) {
    return this.$scopedSlots['resolved']({data: this.data})
  }

  return this.$scopedSlots['pending']()
}
```

这里没有什么太复杂的，只是使用一些`if`块来查找状态，然后返回正确的作用域`slot`(通过`this.$ scopedslot ['SLOTNAME'](…)`)，并将相关数据传递到`slot`作用域。

当不使用模板时，可以将 JavaScript 从 script 标记中提取出来，然后将其放入`.js`文件中。在编译这些 Vue 文件时，应该会带来非常小的性能提升。

