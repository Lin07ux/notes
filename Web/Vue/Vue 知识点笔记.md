## 数据绑定
Vue.js 的模板是基于 DOM 实现的。这意味着所有的 Vue.js 模板都是可解析的有效的 HTML，且通过一些特殊的特性做了增强。Vue 模板因而从根本上不同于基于字符串的模板。

### 插值
* 文本
数据绑定最基础的形式是文本插值，使用 “Mustache” 语法（双大括号）：

```html
<span>Message: {{ msg }}</span>
```

Mustache 标签会被相应数据对象的 msg 属性的值替换。每当这个属性变化时它也会更新。

你也可以只处理单次插值，今后的数据变化就不会再引起插值更新了：

```html
<span>This will never change: {{* msg }}</span>
```

* 原始 HTML
双 Mustache 标签将数据解析为纯文本而不是 HTML。为了输出真的 HTML 字符串，需要用三 Mustache 标签：

```html
<div>{{{ raw_html }}}</div>
```

此时，内容以 HTML 字符串插入——数据绑定将被忽略。如果需要复用模板片断，应当使用 partials。

> 注：在网站上动态渲染任意 HTML 是非常危险的，因为容易导致 XSS 攻击。记住，只对可信内容使用 HTML 插值，永不用于用户提交的内容。

* HTML 特性
Mustache 标签也可以用在 HTML 特性 (Attributes) 内：

```html
<div id="item-{{ id }}"></div>
```

> 注意：在 Vue.js 指令和特殊特性内不能用插值。不过不必担心，如果 Mustache 标签用错了地方 Vue.js 会给出警告。


### 绑定表达式
放在 Mustache 标签内的文本称为绑定表达式。在 Vue.js 中，一段绑定表达式由一个简单的 JavaScript 表达式和可选的一个或多个过滤器构成。

* JavaScript 表达式
Vue.js 在数据绑定内支持全功能的 JavaScript 表达式，这些表达式将在所属的 Vue 实例的作用域内计算。

```html
{{ number + 1 }}
{{ ok ? 'YES' : 'NO' }}
{{ message.split('').reverse().join('') }}
```

绑定 JavaScript 表达式的一个限制是：**每个绑定只能包含单个表达式**。因此下面的语句是无效的：

```html
<!-- 这是一个语句，不是一个表达式： -->
{{ var a = 1 }}
<!-- 流程控制也不可以，可改用三元表达式 -->
{{ if (ok) { return message } }}
```

* 过滤器
Vue.js 允许在表达式后添加可选的“过滤器 (Filter) ”，以“管道符”指示：

```html
{{ message | capitalize }}
```

这里我们将表达式 message 的值“管输（pipe）”到内置的 capitalize 过滤器，这个过滤器其实只是一个 JavaScript 函数，返回大写化的值。

注意：**管道语法不是 JavaScript 语法，因此不能在表达式内使用过滤器，只能添加到表达式的后面。**

过滤器可以串联：

```html
{{ message | filterA | filterB }}
```

过滤器也可以接受参数：

```html
{{ message | filterA 'arg1' arg2 }}
```

过滤器函数始终以表达式的值作为第一个参数。带引号的参数视为字符串，而不带引号的参数按表达式计算。这里，字符串 'arg1' 将传给过滤器作为第二个参数，表达式 arg2 的值在计算出来之后作为第三个参数。


## 指令
指令 (Directives) 是特殊的带有前缀 v- 的特性。指令的值限定为绑定表达式，因此上面提到的 JavaScript 表达式及过滤器规则在这里也适用。指令的职责就是当其表达式的值改变时把某些特殊的行为应用到 DOM 上。

### 指令参数
有些指令可以在其名称后面带一个“参数” (Argument)，中间放一个冒号隔开。例如，v-bind 指令用于响应地更新 HTML 特性：

```html
<a v-bind:href="url"></a>
```

这里 href 是参数，它告诉 v-bind 指令将元素的 href 特性跟表达式 url 的值绑定。可能你已注意到可以用特性插值`href="{{url}}"`获得同样的结果：这样没错，并且实际上在内部特性插值会转为 v-bind 绑定。

另一个例子是 v-on 指令，它用于监听 DOM 事件，参数是监听的事件的名称：

```html
<a v-on:click="doSomething">
```

### 修饰符
修饰符 (Modifiers) 是以半角句号`.`开始的特殊后缀，用于表示指令应当以特殊方式绑定。例如`.literal`修饰符告诉指令将它的值解析为一个字面字符串而不是一个表达式：

```html
<a v-bind:href.literal="/a/b/c"></a>
```

当然，这似乎没有意义，因为我们只需要使用 href="/a/b/c" 而不必使用一个指令。这个例子只是为了演示语法。

### 缩写
`v-`前缀是一种标识模板中特定的 Vue 特性的视觉暗示。当你需要在一些现有的 HTML 代码中添加动态行为时，这些前缀可以起到很好的区分效果。但你在使用一些常用指令的时候，你会感觉一直这么写实在是啰嗦。而且在构建单页应用（SPA ）时，Vue.js 会管理所有的模板，此时`v-`前缀也没那么重要了。因此 Vue.js 为两个最常用的指令`v-bind`和`v-on`提供特别的缩写：

```html
<!-- 完整语法 -->
<a v-bind:href="url"></a>
<!-- 缩写 -->
<a :href="url"></a>
<!-- 完整语法 -->
<button v-bind:disabled="someDynamicCondition">Button</button>
<!-- 缩写 -->
<button :disabled="someDynamicCondition">Button</button>
v-on 缩写
<!-- 完整语法 -->
<a v-on:click="doSomething"></a>
<!-- 缩写 -->
<a @click="doSomething"></a>
```

它们看起来跟“合法”的 HTML 有点不同，但是它们在所有 Vue.js 支持的浏览器中都能被正确地解析，并且不会出现在最终渲染的标记中。缩写语法完全是可选的。

### 计算属性
在模板中绑定表达式是非常便利的，但是它们实际上只用于简单的操作。模板是为了描述视图的结构。在模板中放入太多的逻辑会让模板过重且难以维护。这就是为什么 Vue.js 将绑定表达式限制为一个表达式。如果需要多于一个表达式的逻辑，应当使用计算属性。

**基础示例**

```html
<div id="example">
  a={{ a }}, b={{ b }}
</div>
```

```javascript
var vm = new Vue({
  el: '#example',
  data: {
    a: 1
  },
  computed: {
    // 一个计算属性的 getter
    b: function () {
      // `this` 指向 vm 实例
      return this.a + 1
    }
  }
})
```

这里我们声明了一个计算属性 b，我们提供的函数将用作属性 vm.b 的`getter`。

**计算属性 vs. $watch**

Vue.js 提供了一个方法`$watch`，它用于观察 Vue 实例上的数据变动。当一些数据需要根据其它数据变化时，`$watch`很诱人 —— 特别是如果你来自 AngularJS。不过，通常更好的办法是使用计算属性而不是一个命令式的`$watch`回调。

考虑如下的一个示例：

```html
<div id="demo">{{fullName}}</div>
```
```javascript
var vm = new Vue({
  el: '#demo',
  data: {
    firstName: 'Foo',
    lastName: 'Bar',
    fullName: 'Foo Bar'
  }
})
vm.$watch('firstName', function (val) {
  this.fullName = val + ' ' + this.lastName
})
vm.$watch('lastName', function (val) {
  this.fullName = this.firstName + ' ' + val
})
```

可以看到使用`$watch`明显复杂的多，而使用`computed`则就简单多了：

```javascript
var vm = new Vue({
  el: '#demo',
  data: {
    firstName: 'Foo',
    lastName: 'Bar'
  },
  computed: {
    fullName: function () {
      return this.firstName + ' ' + this.lastName
    }
  }
})
```

**计算属性 setter**

计算属性默认只是`getter`，不过在需要时你也可以提供一个`setter`：

```javascript
// ...
computed: {
  fullName: {
    // getter
    get: function () {
      return this.firstName + ' ' + this.lastName
    },
    // setter
    set: function (newValue) {
      var names = newValue.split(' ')
      this.firstName = names[0]
      this.lastName = names[names.length - 1]
    }
  }
}
// ...
```

现在在调用`vm.fullName = 'John Doe'`时，`setter`会被调用，`vm.firstName`和`vm.lastName`也会有相应更新。

### Class 与 Style 绑定
数据绑定一个常见需求是操作元素的 class 列表和它的内联样式。因为它们都是 attribute，我们可以用 v-bind 处理它们：只需要计算出表达式最终的字符串。不过，字符串拼接麻烦又易错。因此，在 v-bind 用于 class 和 style 时，Vue.js 专门增强了它。表达式的结果类型除了字符串之外，还可以是对象或数组。

#### 绑定 HTML Class
尽管可以用 Mustache 标签绑定 class，比如`class="{{ className }}"`，但是我们不推荐这种写法和`v-bind:class`混用。两者只能选其一！

**对象语法**

我们可以传给`v-bind:class`一个对象，以动态地切换 class。注意`v-bind:class`指令可以与普通的 class 特性共存：

```html
<div class="static" v-bind:class="{ 'class-a': isA, 'class-b': isB }"></div>

data: {
  isA: true,
  isB: false
}
```

渲染的结果为：

```html
<div class="static class-a"></div>
```

当 isA 和 isB 变化时，class 列表将相应地更新。例如，如果 isB 变为 true，class 列表将变为 "static class-a class-b"。

你也可以直接绑定数据里的一个对象：

```html
<div v-bind:class="classObject"></div>

data: {
  classObject: {
    'class-a': true,
    'class-b': false
  }
}
```

我们也可以在这里绑定一个返回对象的计算属性。这是一个常用且强大的模式。

**数组语法**

我们可以把一个数组传给`v-bind:class`，以应用一个 class 列表：

```html
<div v-bind:class="[classA, classB]">

data: {
  classA: 'class-a',
  classB: 'class-b'
}
```

渲染为：

```html
<div class="class-a class-b"></div>
```

如果你也想根据条件切换列表中的 class，可以用三元表达式：

```html
<div v-bind:class="[classA, isB ? classB : '']">
```

此例始终添加 classA，但是只有在 isB 是 true 时添加 classB。

当有多个条件 class 时这样写有些繁琐。在 1.0.19+ 中，可以在数组语法中使用对象语法：

```html
<div v-bind:class="[classA, { classB: isB, classC: isC }]">
```

#### 绑定内联样式
**对象语法**

`v-bind:style`的对象语法十分直观——看着非常像 CSS，其实它是一个 JavaScript 对象。CSS 属性名可以用驼峰式（camelCase）或短横分隔命名（kebab-case）：

```html
<div v-bind:style="{ color: activeColor, fontSize: fontSize + 'px' }"></div>

data: {
  activeColor: 'red',
  fontSize: 30
}
```

直接绑定到一个样式对象通常更好，让模板更清晰：

```html
<div v-bind:style="styleObject"></div>

data: {
  styleObject: {
    color: 'red',
    fontSize: '13px'
  }
}
```

同样的，对象语法常常结合返回对象的计算属性使用。

**数组语法**

`v-bind:style`的数组语法可以将多个样式对象应用到一个元素上：

```html
<div v-bind:style="[styleObjectA, styleObjectB]">
```

**自动添加前缀**

当`v-bind:style`使用需要厂商前缀的 CSS 属性时，如`transform`，Vue.js 会自动侦测并添加相应的前缀。

### 条件渲染
#### v-if
在字符串模板中，如 Handlebars，我们得像这样写一个条件块：

```html
<!-- Handlebars 模板 -->
{{#if ok}}
  <h1>Yes</h1>
{{/if}}
```

在 Vue.js，我们使用`v-if`指令实现同样的功能：

```html
<h1 v-if="ok">Yes</h1>
```

#### template v-if
因为`v-if`是一个指令，需要将它添加到一个元素上。但是如果我们想切换多个元素呢？此时我们可以把一个`<template>`元素当做包装元素，并在上面使用`v-if`，最终的渲染结果不会包含`<template>`元素，而仅包含其中的子元素。

```html
<template v-if="ok">
  <h1>Title</h1>
  <p>Paragraph 1</p>
  <p>Paragraph 2</p>
</template>
```

#### v-show
另一个根据条件展示元素的选项是`v-show`指令。用法大体上一样：

```html
<h1 v-show="ok">Hello!</h1>
```

不同的是有`v-show`的元素会始终渲染并保持在 DOM 中。`v-show`是简单的切换元素的 CSS 属性`display`。

注意：`v-show`不支持`<template>`语法。

#### v-else
可以用`v-else`指令给`v-if`或`v-show`添加一个 “else 块”：

```html
<div v-if="Math.random() > 0.5">
  Sorry
</div>
<div v-else>
  Not sorry
</div>
```

注意：`v-else`元素必须立即跟在`v-if`或`v-show`元素的后面——否则它不能被识别。

将`v-show`用在组件上时，因为指令的优先级`v-else`会出现问题。因此不要这样做：

```html
<custom-component v-show="condition"></custom-component>

<p v-else>这可能也是一个组件</p>
```

可以使用另一个`v-show`替换`v-else`：

```html
<custom-component v-show="condition"></custom-component>
<p v-show="!condition">这可能也是一个组件</p>
```

#### v-if vs. v-show
在切换`v-if`块时，Vue.js 有一个局部编译/卸载过程，因为`v-if`之中的模板也可能包括数据绑定或子组件。`v-if`是真实的条件渲染，因为它会确保条件块在切换当中合适地销毁与重建条件块内的事件监听器和子组件。

`v-if`也是惰性的：如果在初始渲染时条件为假，则什么也不做——在条件第一次变为真时才开始局部编译（编译会被缓存起来）。

相比之下，`v-show`简单得多——元素始终被编译并保留，只是简单地基于 CSS 切换。

一般来说，`v-if`有更高的切换消耗而`v-show`有更高的初始渲染消耗。因此，如果需要频繁切换 `v-show`较好，如果在运行时条件不大可能改变`v-if`较好。

### 列表渲染
#### v-for
可以使用`v-for`指令基于一个数组渲染一个列表。这个指令使用特殊的语法，形式为`item in items`，`items`是数据数组，`item`是当前数组元素的别名。

```html
<ul id="example-1">
  <li v-for="item in items">
    {{ item.message }}
  </li>
</ul>

var example1 = new Vue({
  el: '#example-1',
  data: {
    items: [
      { message: 'Foo' },
      { message: 'Bar' }
    ]
  }
})
```

渲染结果为：

```
·Foo
·Bar
```

在`v-for`块内我们能完全访问当前组件作用域内的属性，另有一个特殊变量`$index`，表示当前数组元素的索引。

```javascript
<ul id="example-2">
  <li v-for="item in items">
    {{ parentMessage }} - {{ $index }} - {{ item.message }}
  </li>
</ul>

var example2 = new Vue({
  el: '#example-2',
  data: {
    parentMessage: 'Parent',
    items: [
      { message: 'Foo' },
      { message: 'Bar' }
    ]
  }
})
```

渲染结果为：

```
·Parent-0-Foo
·Parent-1-Bar
```

当然，你可以为索引指定一个别名（如果`v-for`用于一个对象，则可以为对象的键指定一个别名）：

```html
<div v-for="(index, item) in items">
  {{ index }} {{ item.message }}
</div>
```

从 1.0.17 开始可以使用`of`分隔符，更接近 JavaScript 遍历器语法：

```html
<div v-for="item of items"></div>
```

#### template v-for
类似于`template v-if`，也可以将`v-for`用在`<template>`标签上，以渲染一个包含多个元素的块。例如：

```html
<ul>
  <template v-for="item in items">
    <li>{{ item.msg }}</li>
    <li class="divider"></li>
  </template>
</ul>
```

### 数组变动检测
#### 变异方法
Vue.js 包装了被观察数组的变异方法，故它们能触发视图更新。被包装的方法有：

```
push()
pop()
shift()
unshift()
splice()
sort()
reverse()
```

#### 替换数组
变异方法，如名字所示，修改了原始数组。相比之下，也有非变异方法，如`filter()`，`concat()`和`slice()`，不会修改原始数组而是返回一个新数组。在使用非变异方法时，可以直接用新数组替换旧数组：

```javascript
example1.items = example1.items.filter(function (item) {
  return item.message.match(/Foo/)
})
```

可能你觉得这将导致 Vue.js 弃用已有 DOM 并重新渲染整个列表——幸运的是并非如此。 Vue.js 实现了一些启发算法，以最大化复用 DOM 元素，因而用另一个数组替换数组是一个非常高效的操作。

#### track-by
有时需要用全新对象（例如通过 API 调用创建的对象）替换数组。因为 v-for 默认通过数据对象的特征来决定对已有作用域和 DOM 元素的复用程度，这可能导致重新渲染整个列表。但是，如果每个对象都有一个唯一 ID 的属性，便可以使用`track-by`特性给 Vue.js 一个提示，Vue.js 因而能尽可能地复用已有实例。

例如，假定数据为：

```json
{
  items: [
    { _uid: '88f869d', ... },
    { _uid: '7496c10', ... }
  ]
}
```

然后可以这样给出提示：

```html
<div v-for="item in items" track-by="_uid">
  <!-- content -->
</div>
```

然后在替换数组`items`时，如果 Vue.js 遇到一个包含`_uid: '88f869d'`的新对象，它知道它可以复用这个已有对象的作用域与 DOM 元素。

#### track-by $index
如果没有唯一的键供追踪，可以使用`track-by="$index"`，它强制让`v-for`进入原位更新模式：片断不会被移动，而是简单地以对应索引的新值刷新。这种模式也能处理数据数组中重复的值。

这让数据替换非常高效，但是也会付出一定的代价。因为这时 DOM 节点不再映射数组元素顺序的改变，不能同步临时状态（比如 <input> 元素的值）以及组件的私有状态。因此，如果`v-for`块包含 <input> 元素或子组件，要小心使用`track-by="$index"`。

#### 使用 Object.freeze()
在遍历一个数组时，如果数组元素是对象并且对象用`Object.freeze()`冻结，你需要明确指定 `track-by`。在这种情况下如果 Vue.js 不能自动追踪对象，将给出一条警告。

#### 边里对象
也可以使用`v-for`遍历对象。除了`$index`之外，作用域内还可以访问另外一个特殊变量`$key`。

```html
<ul id="repeat-object" class="demo">
  <li v-for="value in object">
    {{ $key }} : {{ value }}
  </li>
</ul>

new Vue({
  el: '#repeat-object',
  data: {
    object: {
      FirstName: 'John',
      LastName: 'Doe',
      Age: 30
    }
  }
})
```

渲染结果为：

```
·FirstName:'John'
·LastName:'Doe'
·Age:30
```

也可以给对象的键提供一个别名：

```html
<div v-for="(key, val) in object">
  {{ key }} {{ val }}
</div>
```

在遍历对象时，是按`Object.keys()`的结果遍历，但是不能保证它的结果在不同的 JavaScript 引擎下是一致的。

#### 遍历数值
`v-for`也可以接收一个整数，此时它将重复模板数次。

```html
<div>
  <span v-for="n in 10">{{ n }} </span>
</div>
```

会渲染出 10 个 span 标签，内容分别是从 1 ~ 10。

#### 显示过滤/排序的结果
有时我们想显示过滤/排序过的数组，同时不实际修改或重置原始数据。有两个办法：

* 创建一个计算属性，返回过滤/排序过的数组；
* 使用内置的过滤器`filterBy`和`orderBy`。

计算属性有更好的控制力，也更灵活，因为它是全功能 JavaScript。但是通常过滤器更方便，详细见 API。

#### 注意问题
因为 JavaScript 的限制，Vue.js 不能检测到下面数组变化：

* 直接用索引设置元素，如`vm.items[0] = {}`；
* 修改数据的长度，如`vm.items.length = 0`。

为了解决第一个问题，Vue.js 扩展了观察数组，为它添加了一个`$set()`方法：

```javascript
// 与 example1.items[0] = ... 结果相同，但是能触发视图更新
example1.items.$set(0, { childMsg: 'Changed!'})
```

至于第二个问题，只需用一个空数组替换 items，但是要修改为其他的长度，则不行。

除了`$set()`， Vue.js 也为观察数组添加了`$remove()`方法，用于从目标数组中查找并删除元素，在内部它调用`splice()`。因此，不必这样：

```javascript
var index = this.items.indexOf(item)
if (index !== -1) {
  this.items.splice(index, 1)
}
```

只需要这样：

```javascript
this.items.$remove(item)
```

## 方法与事件处理器
### 方法处理器
可以使用`v-on`监听 DOM 事件：

```html
<div id="example">
  <button v-on:click="greet">Greet</button>
</div>
```

我们绑定了一个单击事件处理器到一个方法 greet。下面在 Vue 实例中定义这个方法：

```javascript
var vm = new Vue({
  el: '#example',
  data: {
    name: 'Vue.js'
  },
  // 在 `methods` 对象中定义方法
  methods: {
    greet: function (event) {
      // 方法内 `this` 指向 vm
      alert('Hello ' + this.name + '!')
      // `event` 是原生 DOM 事件
      alert(event.target.tagName)
    }
  }
})

// 也可以在 JavaScript 代码中调用方法
vm.greet() // -> 'Hello Vue.js!'
```

### 内联语句处理器
除了直接绑定到一个方法，也可以用内联 JavaScript 语句，但类似于内联表达式，事件处理器限制为一个语句：

```html
<div id="example-2">
  <button v-on:click="say('hi')">Say Hi</button>
  <button v-on:click="say('what')">Say What</button>
</div>

new Vue({
  el: '#example-2',
  methods: {
    say: function (msg) {
      alert(msg)
    }
  }
})
```

有时也需要在内联语句处理器中访问原生 DOM 事件。可以用特殊变量 $event 把它传入方法：

```html
<button v-on:click="say('hello!', $event)">Submit</button>  
// ...
methods: {
  say: function (msg, event) {
    // 现在我们可以访问原生事件对象
    event.preventDefault()
  }
}
```

### 事件修饰符
在事件处理器中经常需要调用 event.preventDefault() 或 event.stopPropagation()。尽管我们在方法内可以轻松做到，不过让方法是纯粹的数据逻辑而不处理 DOM 事件细节会更好。

为了解决这个问题，Vue.js 为`v-on`提供两个事件修饰符：`.prevent`与`.stop`。

```html
<!-- 阻止单击事件冒泡 -->
<a v-on:click.stop="doThis"></a>

<!-- 提交事件不再重载页面 -->
<form v-on:submit.prevent="onSubmit"></form>

<!-- 修饰符可以串联 -->
<a v-on:click.stop.prevent="doThat">

<!-- 只有修饰符 -->
<form v-on:submit.prevent></form>
```

1.0.16 添加了两个额外的修饰符：

```html
<!-- 添加事件侦听器时使用 capture 模式 -->
<div v-on:click.capture="doThis">...</div>

<!-- 只当事件在该元素本身（而不是子元素）触发时触发回调 -->
<div v-on:click.self="doThat">...</div>
```

### 按键修饰符
在监听键盘事件时，我们经常需要检测 keyCode。Vue.js 允许为`v-on`添加按键修饰符：

```html
<!-- 只有在 keyCode 是 13 时调用 vm.submit() -->
<input v-on:keyup.13="submit">

记住所有的 keyCode 比较困难，Vue.js 为最常用的按键提供别名：

<!-- 同上 -->
<input v-on:keyup.enter="submit">

<!-- 缩写语法 -->
<input @keyup.enter="submit">
```

全部的按键别名：

```
enter
tab
delete
esc
space
up
down
left
right
```

1.0.8+： 支持单字母按键别名。

1.0.17+： 可以自定义按键别名：

```javascript
// 可以使用 @keyup.f1
Vue.directive('on').keyCodes.f1 = 112
```

### 为什么在 HTML 中监听事件?
你可能注意到这种事件监听的方式违背了传统理念 “separation of concern”。不必担心，因为所有的 Vue.js 事件处理方法和表达式都严格绑定在当前视图的 ViewModel 上，它不会导致任何维护困难。实际上，使用 v-on 有几个好处：

* 扫一眼 HTML 模板便能轻松定位在 JavaScript 代码里对应的方法。
* 因为你无须在 JavaScript 里手动绑定事件，你的 ViewModel 代码可以是非常纯粹的逻辑，和 DOM 完全解耦，更易于测试。
* 当一个 ViewModel 被销毁时，所有的事件处理器都会自动被删除。你无须担心如何自己清理它们。


## 表单控件绑定
### 基础用法
可以用`v-model`指令在表单控件元素上创建双向数据绑定。根据控件类型它自动选取正确的方法更新元素。尽管有点神奇，`v-model`不过是语法糖，在用户输入事件中更新数据，以及特别处理一些极端例子。

**(1) Text**

```html
<span>Message is: {{ message }}</span>
<br>
<input type="text" v-model="message" placeholder="edit me">
```

**(2) Multiline text**

```html
<span>Multiline message is:</span>
<p>{{ message }}</p>
<br>
<textarea v-model="message" placeholder="add multiple lines"></textarea>
```

**(3) Checkbox**

* 单个勾选框，逻辑值：

```html
<input type="checkbox" id="checkbox" v-model="checked">
<label for="checkbox">{{ checked }}</label>
```

* 多个勾选框，绑定到同一个数组：

```html
<input type="checkbox" id="jack" value="Jack" v-model="checkedNames">
<label for="jack">Jack</label>
<input type="checkbox" id="john" value="John" v-model="checkedNames">
<label for="john">John</label>
<input type="checkbox" id="mike" value="Mike" v-model="checkedNames">
<label for="mike">Mike</label>
<br>
<span>Checked names: {{ checkedNames | json }}</span>

new Vue({
  el: '...',
  data: {
    checkedNames: []
  }
})
```

**(4) Radio**

```html
<input type="radio" id="one" value="One" v-model="picked">
<label for="one">One</label>
<br>
<input type="radio" id="two" value="Two" v-model="picked">
<label for="two">Two</label>
<br>
<span>Picked: {{ picked }}</span>
```

**(5) Select**

* 单选

```html
<select v-model="selected">
  <option selected>A</option>
  <option>B</option>
  <option>C</option>
</select>
<span>Selected: {{ selected }}</span>
```

* 多选（绑定到一个数组）

```
<select v-model="selected" multiple>
  <option selected>A</option>
  <option>B</option>
  <option>C</option>
</select>
<br>
<span>Selected: {{ selected | json }}</span>
```

* 动态选项，用`v-for`渲染：

```html
<select v-model="selected">
  <option v-for="option in options" v-bind:value="option.value">
    {{ option.text }}
  </option>
</select>
<span>Selected: {{ selected }}</span>

new Vue({
  el: '...',
  data: {
    selected: 'A',
    options: [
      { text: 'One', value: 'A' },
      { text: 'Two', value: 'B' },
      { text: 'Three', value: 'C' }
    ]
  }
})
```

* 动态选项，用`options`特性

当你需要为一个`<select>`元素动态渲染列表选项时，推荐将`options`特性和`v-model`指令配合使用，这样当选项动态改变时，`v-model`会正确地同步。

`<select v-model="selected" options="myOptions"></select>`

数据中，myOptions 应该是一个指向选项数组的路径或是表达式。这个可选的数组可以包含简单的数组：

`myOptions = ['a', 'b', 'c']`

或者可以包含格式如`{text:'', value:''}`的对象。该对象格式允许你设置可选项，让文本展示不同于背后的值：

```javascript
options = [
  { text: 'A', value: 'a' },
  { text: 'B', value: 'b' }
]
```

会被渲染成为：

```html
<select>
  <option value="a">A</option>
  <option value="b">B</option>
</select>
```

* 选项组

我们传递给 select 元素的选项数组，里面的对象的格式也可以是`{label:'', options:[...]}`。这样的数据会被渲染成为一个`<optgroup>`。

每个数据对象中的`options`项可以是一个简单的字符串数组，也可以是一个`{text:'', value:''}`的对象：前者的选项的值和显示的内容是相同的，后者则显示的是 text，值是 value。

```json
[
  { label: 'A', options: ['a', 'b']},
  { label: 'B', options: ['c', 'd']}
]
```

```html
<select>
  <optgroup label="A">
    <option value="a">a</option>
    <option value="b">b</option>
  </optgroup>
  <optgroup label="B">
    <option value="c">c</option>
    <option value="d">d</option>
  </optgroup>
</select>
```

* 选项过滤

原始数据很有可能不是这里所要求的格式，因此在动态生成选项时必须进行一些数据转换。为了简化这种转换，`options`特性支持过滤器。将数据的转换逻辑做成一个可复用的*自定义过滤器*通常来说是个好主意。

比如，我们定义一个过滤器：

```javascript
Vue.filter('extract', function (value, keyToExtract) {
  return value.map(function (item) {
    return item[keyToExtract]
  })
})
```

然后可以在`options`特性上使用这个过滤器：

`<select v-model="selectedUser" options="users | extract 'name'"></select>`

上面的示例，将格式为`[{ name: 'Bruce' }, { name: 'Chuck' }]`这样的原始数据转化为 `['Bruce', 'Chuck']`，从而符合动态选项的格式要求。

* 静态默认选项

除了动态生成的选项之外，你还可以提供一个静态的默认选项：

```html
<select v-model="selectedUser" options="users">
  <option value="">Select a user...</option>
</select>
```

基于`users`动态生成的选项将会被添加到这个静态选项后面。如果`v-model`的绑定值为除 0 之外的伪值，则会自动选中该默认选项。


### 绑定 value
对于单选按钮，勾选框及选择框选项，`v-model`绑定的 value 通常是静态字符串（对于勾选框是逻辑值）：

```html
<!-- 当选中时，`picked` 为字符串 "a" -->
<input type="radio" v-model="picked" value="a">  
<!-- `toggle` 为 true 或 false -->
<input type="checkbox" v-model="toggle">
<!-- 当选中时，`selected` 为字符串 "abc" -->
<select v-model="selected">
  <option value="abc">ABC</option>
</select>
```

但是有时我们想绑定 value 到 Vue 实例的一个动态属性上，这时可以用`v-bind`实现，并且这个属性的值可以不是字符串。

* checkbox

`<input type="checkbox" v-model="toggle" v-bind:true-value="a" v-bind:false-value="b">`

当选中时`vm.toggle === vm.a`，当没有选中时`vm.toggle === vm.b`。

* Radio

`<input type="radio" v-model="pick" v-bind:value="a">`

当选中时，`vm.pick === vm.a`。

* Select Options

```html
<select v-model="selected">
  <!-- 对象字面量 -->
  <option v-bind:value="{ number: 123 }">123</option>
</select>
```

当选中时`typeof vm.selected`为'object'，`vm.selected.number`为 123。

### 参数特性
1. lazy

    在默认情况下，`v-model`在 input 事件中同步输入框值与数据，可以添加一个特性`lazy`，从而改到在 change 事件中同步：

    ```html
    <!-- 在 "change" 而不是 "input" 事件中更新 -->
    <input v-model="msg" lazy>
    ```

2. number

    如果想自动将用户的输入转为 Number 类型（如果原值的转换结果为 NaN 则返回原值），可以添加一个特性 number：

    `<input v-model="age" number>`

3. debounce

    debounce 设置一个最小的延时，在每次敲击之后延时同步输入框的值与数据。如果每次更新都要进行高耗操作（例如在输入提示中 Ajax 请求），它较为有用。

    `<input v-model="msg" debounce="500">`

    > 注意：debounce 参数不会延迟 input 事件：它延迟“写入”底层数据。因此在使用 debounce 时应当用`vm.$watch()`响应数据的变化。若想延迟 DOM 事件，应当使用 debounce 过滤器。
    
## 过渡
通过 Vue.js 的过渡系统，可以在元素从 DOM 中插入或移除时自动应用过渡效果。Vue.js 会在适当的时机为你触发 CSS 过渡或动画，你也可以提供相应的 JavaScript 钩子函数在过渡过程中执行自定义的 DOM 操作。

为了应用过渡效果，需要在目标元素上使用 transition 特性：

```html
<div v-if="show" transition="my-transition"></div>
```

transition 特性可以与下面资源一起用：

* v-if
* v-show
* v-for （只在插入和删除时触发，使用 vue-animated-list 插件）
* 动态组件 （介绍见组件）
* 在组件的根节点上，并且被 Vue 实例 DOM 方法（如 vm.$appendTo(el)）触发。

当插入或删除带有过渡的元素时，Vue 将：

* 尝试以 ID "my-transition" 查找 JavaScript 过渡钩子对象——通过`Vue.transition(id, hooks)`或`transitions`选项注册。如果找到了，将在过渡的不同阶段调用相应的钩子。
* 自动嗅探目标元素是否有 CSS 过渡或动画，并在合适时添加/删除 CSS 类名。
* 如果没有找到 JavaScript 钩子并且也没有检测到 CSS 过渡/动画，DOM 操作（插入/删除）在下一帧中立即执行。

### CSS 过渡
典型的 CSS 过渡像这样：

```html
<div v-if="show" transition="expand">hello</div>
```

然后为`.expand-transition`、`.expand-enter`和`.expand-leave`添加 CSS 规则：

```css
/* 必需 */
.expand-transition {
  transition: all .3s ease;
  height: 30px;
  padding: 10px;
  background-color: #eee;
  overflow: hidden;
}

/* .expand-enter 定义进入的开始状态 */
/* .expand-leave 定义离开的结束状态 */
.expand-enter, .expand-leave {
  height: 0;
  padding: 0 10px;
  opacity: 0;
}
```

你可以在同一元素上通过动态绑定实现不同的过渡：

```html
<div v-if="show" :transition="transitionName">hello</div>

new Vue({
  el: '...',
  data: {
    show: false,
    transitionName: 'fade'
  }
})
```

### 过渡钩子
可以提供 JavaScript 钩子:

```javascript
Vue.transition('expand', {

  beforeEnter: function (el) {
    el.textContent = 'beforeEnter'
  },
  enter: function (el) {
    el.textContent = 'enter'
  },
  afterEnter: function (el) {
    el.textContent = 'afterEnter'
  },
  enterCancelled: function (el) {
    // handle cancellation
  },

  beforeLeave: function (el) {
    el.textContent = 'beforeLeave'
  },
  leave: function (el) {
    el.textContent = 'leave'
  },
  afterLeave: function (el) {
    el.textContent = 'afterLeave'
  },
  leaveCancelled: function (el) {
    // handle cancellation
  }
})
```

### 过渡的 CSS 类名
类名的添加和切换取决于 transition 特性的值。比如 transition="fade"，会有三个 CSS 类名：

* `.fade-transition`始终保留在元素上。
* `.fade-enter`定义进入过渡的开始状态。只应用一帧然后立即删除。
* `.fade-leave`定义离开过渡的结束状态。在离开过渡开始时生效，在它结束后删除。

如果 transition 特性没有值，类名默认是`.v-transition`、`.v-enter`和`.v-leave`。

### 自定义过渡类名
我们可以在过渡的 JavaScript 定义中声明自定义的 CSS 过渡类名。这些自定义类名会覆盖默认的类名。当需要和第三方的 CSS 动画库，比如 Animate.css 配合时会非常有用：

```html
<div v-show="ok" class="animated" transition="bounce">Watch me bounce</div>

Vue.transition('bounce', {
  enterClass: 'bounceInLeft',
  leaveClass: 'bounceOutRight'
})
```

> 这是 1.0.14 新增的特性。

### 显式声明 CSS 过渡类型
Vue.js 需要给过渡元素添加事件侦听器来侦听过渡何时结束。基于所使用的 CSS，该事件要么是 transitionend，要么是 animationend。如果你只使用了两者中的一种，那么 Vue.js 将能够根据生效的 CSS 规则自动推测出对应的事件类型。但是，有些情况下一个元素可能需要同时带有两种类型的动画。比如你可能希望让 Vue 来触发一个 CSS animation，同时该元素在鼠标悬浮时又有 CSS transition 效果。这样的情况下，你需要显式地声明你希望 Vue 处理的动画类型 (animation 或是 transition)：

```javascript
Vue.transition('bounce', {
  // 该过渡效果将只侦听 `animationend` 事件
  type: 'animation'
})
```

> 这是 1.0.14 新增的特性。

### 过渡流程详解
当 show 属性改变时，Vue.js 将相应地插入或删除 <div> 元素，按照如下规则改变过渡的 CSS 类名：

* 如果 show 变为 false，Vue.js 将：
    1. 调用`beforeLeave`钩子；
    2. 添加`v-leave`类名到元素上以触发过渡；
    3. 调用`leave`钩子；
    4. 等待过渡结束（监听`transitionend`事件）；
    5. 从 DOM 中删除元素并删除`v-leave`类名；
    6. 调用 afterLeave 钩子。

* 如果 show 变为 true，Vue.js 将：
    1. 调用`beforeEnter`钩子；
    2. 添加`v-enter`类名到元素上；
    3. 把它插入 DOM；
    4. 调用`enter`钩子；
    5. 强制一次 CSS 布局，让`v-enter`确实生效。然后删除`v-enter`类名，以触发过渡，回到元素的原始状态；
    6. 等待过渡结束；
    7. 调用`afterEnter`钩子。

另外，如果在它的进入过渡还在进行中时删除元素，将调用`enterCancelled`钩子，以清理变动或`enter`创建的计时器。反过来对于离开过渡亦如是。

上面所有的钩子函数在调用时，它们的 this 均指向其所属的 Vue 实例。编译规则：过渡在哪个上下文中编译，它的 this 就指向哪个上下文。

最后，`enter`和`leave`可以有第二个可选的回调参数，用于显式控制过渡如何结束。因此不必等待 CSS `transitionend`事件， Vue.js 将等待你手工调用这个回调，以结束过渡。例如：

```javascript
enter: function (el) {
  // 没有第二个参数
  // 由 CSS transitionend 事件决定过渡何时结束
}

/* vs. */

enter: function (el, done) {
  // 有第二个参数
  // 过渡只有在调用 `done` 时结束
}
```

当多个元素一起过渡时，Vue.js 会批量处理，只强制一次布局。

### CSS 动画
CSS 动画用法同 CSS 过渡，区别是在动画中`v-enter`类名在节点插入 DOM 后不会立即删除，而是在`animationend`事件触发时删除。

```html
<span v-show="show" transition="bounce">Look at me!</span>

.bounce-transition {
  display: inline-block; /* 否则 scale 动画不起作用 */
}
.bounce-enter {
  animation: bounce-in .5s;
}
.bounce-leave {
  animation: bounce-out .5s;
}
@keyframes bounce-in {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.5);
  }
  100% {
    transform: scale(1);
  }
}
@keyframes bounce-out {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.5);
  }
  100% {
    transform: scale(0);
  }
}
```

### JavaScript 过渡
也可以只使用 JavaScript 钩子，不用定义任何 CSS 规则。当只使用 JavaScript 过渡时`enter`和`leave`钩子需要调用`done`回调，否则它们将被同步调用，过渡将立即结束。

为 JavaScript 过渡显式声明`css: false`是个好主意，Vue.js 将跳过 CSS 检测。这样也会阻止无意间让 CSS 规则干扰过渡。

在下例中我们使用 jQuery 注册一个自定义的 JavaScript 过渡：

```javascript
Vue.transition('fade', {
  css: false,
  enter: function (el, done) {
    // 元素已被插入 DOM
    // 在动画结束后调用 done
    $(el)
      .css('opacity', 0)
      .animate({ opacity: 1 }, 1000, done)
  },
  enterCancelled: function (el) {
    $(el).stop()
  },
  leave: function (el, done) {
    // 与 enter 相同
    $(el).animate({ opacity: 0 }, 1000, done)
  },
  leaveCancelled: function (el) {
    $(el).stop()
  }
})
```

然后用`transition`特性中：

```html
<p transition="fade"></p>
```

### 渐近过渡
`transition`与`v-for`一起用时可以创建渐近过渡。给过渡元素添加一个特性`stagger`、`enter-stagger`或`leave-stagger`：

```html
<div v-for="item in list" transition="stagger" stagger="100"></div>
```

或者，提供一个钩子`stagger`、`enter-stagger`或`leave-stagger`，以更好的控制：

```javascript
Vue.transition('stagger', {
  stagger: function (index) {
    // 每个过渡项目增加 50ms 延时
    // 但是最大延时限制为 300ms
    return Math.min(300, index * 50)
  }
})
```

## 转摘
[vue.js 起步式（一）](https://segmentfault.com/a/1190000006708721)
[vue.js起步式（二）](https://segmentfault.com/a/1190000006711739)

