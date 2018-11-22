在 Vue 里面困扰开发者的事情之一是如何在组件之间共享状态。对于刚刚接触响应式编程的开发者来说，像 Vuex 这种库，有着繁多的新名词及其关注点分离的方式，往往令人望而生畏。特别是当你只希望分享一两个数据片段时，这一套逻辑的复杂性就显得有点过分了。但是如果理解了其中的原理就会发现其实事情都很简单。

下面通过三个简单的示例来对比说明：

* 第一个通过使用一个简单的 JavaScript 对象，在每个新组件当中引用来实现共享状态。[Using shared object](https://benjaminlistwon.com/demo/dataflow/shared/index.html)
* 第二个就使用 Vuex 来实现同样的功能。[Using vuex](https://benjaminlistwon.com/demo/dataflow/vuex/index.html)
* 第三个虽然实现了和上面相同的功能，但这是一个你绝对不应该做的事情的示例（我们将在最后看看为什么）。[Using evil bindings](https://benjaminlistwon.com/demo/dataflow/evil/index.html)

> 上面三个示例的代码可以在[这个GitHub仓库](https://github.com/BenjaminListwon/vue-data-flow)获得。

代码里很多地方是 Vue 2.0 版本的特性，但我接下来想讲的数据流概念在任何版本里都是相关的，并且它可以通过一些改变很轻易地向下兼容到 1.0。

这些演示都是一样的功能，只是实现的方法不同。应用程序由两个独立的聊天组件实例组成。当用户在一个实例里提交一个消息的时候，它应该在两个聊天窗口都出现，因为消息状态是共享的，下面是一个截图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476844886112.png)


### 用一个对象共享状态
在这个演示里，我们将使用一个简单的 JavaScript 对象：`var store = {...}`，在 Client.vue 组件的实例之间共享状态。

数据在示例的应用程序当中流转的的过程如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476844949387.png)

index.html 关键代码：

```html
<div id="app"></div>
<script>
  var store = {
    state: {
      messages: []
    },
    newMessage (msg) {
      this.state.messages.push(msg)
    }
  }
</script>
```

这里有两个关键的地方：

1. 我们通过把这个对象直接添加到 index.html 里来让其对整个应用程序可用，也可以将它注入到应用程序里更下一层的作用链，但目前直接添加显然更快捷简单。

2. 我们在这里保存状态，但同时也提供了一个函数来调用它。相比起分散在组件各处的函数，我们更倾向于让它们保持在一个地方（便于维护），并在任何需要它们的地方简单使用。 

App.vue 关键代码：

```html
<template>
  <div id="app">
    <div class="row">
      <div class="col">
        <client clientid="Client A"></client>
      </div>
      <div class="col">
        <client clientid="Client B"></client>
      </div>
    </div>
  </div>
</template>

<script>
import Client from './components/Client.vue'

export default {
  components: {
    Client
  }
}
</script>
```

这里我们引入了 Client 组件，并创建了两个它的实例，使用一个属性：`clientid`，来对每个实例进行区分。事实上，你应该更动态地去实现这些，但别忘了，目前快捷简单更重要。

注意一点，到这里我们还完全没有同步任何状态。

Client.vue 关键代码：

```html
<template>
  <div>
    <h1>{{ clientid }}</h1>
    <div class="client">
      <ul>
        <li v-for="message in messages">
          <label>{{ message.sender }}:</label> {{ message.text }}
        </li>
      </ul>
      <div class="msgbox">
        <input v-model="msg" placeholder="Enter a message, then hit [enter]" @keyup.enter="trySendMessage">
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      msg: '',
      messages: store.state.messages
    }
  },
  props: ['clientid'],
  methods: {
    trySendMessage() {
      store.newMessage({
        text: this.msg,
        sender: this.clientid
      })
      this.resetMessage()
    },
    resetMessage() {
      this.msg = ''
    }
  }
}
</script>
```

Client 组件的主要内容为：

1. 在该模板里，设置一个`v-for`循环去遍历`messages`集合。 
2.	绑定在文本输入框上的`v-model`简单地存储了组件的本地数据对象`msg`。 
3.	同样在数据对象里，我们创建了一个对`store.state.messages`的引用，它将触发组件的更新。 
4.	最后，将`enter`键绑定到`trySendMessage`函数，这个函数包含了以下几个功能：
    * 准备好需要存储的数据（发送者和消息的一个字典对象）。
    * 调用定义在共享存储里的`newMessage`函数。
    * 调用一个清理函数：`resetMessage`，重置输入框。通常你更应该在一个`promise`完成之后再调用它。 
这样就完成了通过共享对象来同步数据的目的。

### 用 Vuex 共享状态
现在来试试看用 Vuex 实现。同样的，先上图，也便于我们将 Vuex 的术语（actions，mutations等等）对应到我们刚刚完成的示例中。

![](http://cnd.qiniu.lin07ux.cn/markdown/1476845404272.png)

正如你所看到的，Vuex 简单地形式化了我们刚刚完成的过程。使用它的时候，所做的事情其实和我们上面做过的非常像：

1.	创建一个用来共享的存储，在这个例子中它将通过 vue/vuex 注入到组件当中。 
2.	定义组件可以调用的`actions`，它们仍然是集中定义的。 
3.	定义实际接触存储状态的`mutations`。我们这么做，`actions`就可以形成不止一个`mutation`，或者执行逻辑去决定调用哪一个`mutation`。这意味着你再也不用担心组件当中的业务逻辑了！ 
4.	当状态更新时，任何拥有`getter`，动态属性和映射到`store`的组件都会被立即更新。 
同样再来看看代码：

main.js

```javascript
import store from './vuex/store'

new Vue({
  el: '#app',
  render: (h) => h(App),
  store: store
})
```

这次，我们用 Vuex 创建了一个存储并将其直接传入应用程序当中，替代掉了之前 index.html 中的`store`对象。在继续之前，先来看一下这个存储：

store.js

```javascript
export default new Vuex.Store({

  state: {
    messages: []
  },

  actions: {
    newMessage ({commit}, msg) {
      commit('NEW_MESSAGE', msg)
    }
  },

  mutations: {
    NEW_MESSAGE (state, msg) {
      state.messages.push(msg)
    }
  },

  strict: debug

})
```

和我们自己创建的对象非常相似，但是多了一个 mutations 对象。

App.vue 的代码保持不变，Client.vue 中的模板不变，只改变一些 JavaScript 代码：

```JavaScript
import { mapState, mapActions } from 'vuex'

export default {
  data() {
    return {
      msg: ''
    }
  },
  props: ['clientid'],
  computed: {
    ...mapState({
      messages: state => state.messages
    })
  },
  methods: {
    trySendMessage() {
      this.newMessage({
        text: this.msg,
        sender: this.clientid
      })
      this.resetMessage()
    },
    resetMessage() {
      this.msg = ''
    },
    ...mapActions(['newMessage'])
  }
}
```

这里最大的不同在于`mapState`和`mapActions`，这是 Vue 2.0 的特性。

可以看到：*自己进行简单的状态共享和使用 Vuex 进行共享并没有多大区别*。而 Vuex 最大的 优点在于它为你形式化了集中处理数据存储的过程，并提供了所有功能方法去处理那些数据。

最初，当你阅读 Vuex 的文档和示例的时候，它那些针对`mutations`，`actions`和`modules`的单独文档很容易让人感觉困扰。但是如果你敢于跨出那一步，简单地在 store.js 文件里写一些关于它们的代码来开始学习。随着这个文件的大小增加，你就将找到正确的时间移步到 actions.js 里，或者是把它们更进一步地分离开来。


### “不好的”方式
下面这个例子和第一个例子类似，都是使用一个共享的 JavaScript 对象来同步数据。不过这利用了 Vue 2.0 里单向绑定的特性来注入回调函数，从而允许了父子模板之间顺序的双向绑定。

代码改动的不多，下面看看改动的地方：

App.vue 关键代码如下：

```html
<div class="row">
  <div class="col">
    <client clientid="Client A" :messages="messages" :callback="newMessage"></client>
  </div>
  <div class="col">
    <client clientid="Client B" :messages="messages" :callback="newMessage"></client>
  </div>
</div>
```

这里，我在组件上使用了一个属性将一个动态绑定传递到`messages`集合里。 但是，我同时还传递了一个动作函数，所以可以在子组件里调用它。

Client.vue 关键代码如下：

```javascript
export default {
  data() {
    return {
      msg: ''
    }
  },
  props: ['clientid', 'messages', 'callback'],
  methods: {
    trySendMessage() {
      this.callback({
        text: this.msg,
        sender: this.clientid
      })
      this.resetMessage()
    },
    resetMessage() {
      this.msg = ''
    }
  }
}
```

这就是不好的做法。要问为什么不好呢？

1.	我们正在破坏之前图中所展示的单向循环。 
2.	我们创建了一个在组件及其父组件之间的紧密耦合。 
3.	这将变得**不可**维护。如果你在组件里需要 20 个函数，你就将添加 20 个属性，管理它们的命名等等，然后，如果任何东西发生改变，呃！ 
所以为什么还要再展示这段？因为我和其他人一样很懒。有时我就会做这样的事情，仅仅想知道再继续做下去会有多么糟糕，然后我就会咒骂自己的懒惰，因为我可能要花上一小时或者一天的时间去清理它们。鉴于这种情况，我希望我可以帮助你尽早避免无谓的决定和错误，**千万不要**传递任何你不需要的东西。99% 的情况下，一个单独的共享状态已经足够完美。


转摘：[【译】VUE 和 VUEX 中的数据流](https://segmentfault.com/a/1190000007168699)
原文：[DATA FLOW IN VUE AND VUEX](https://benjaminlistwon.com/blog/data-flow-in-vue-and-vuex/)

