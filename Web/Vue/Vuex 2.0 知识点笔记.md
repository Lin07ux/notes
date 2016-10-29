Vuex 是 Vue 中的状态管理插件，由官方维护，保持和 Vue 的同步更新。

Vuex 使用了单状态树（single state tree），一个 store 对象就存储了整个应用层的状态。它让我们可以更方便地定位某一具体的状态，并且在调试时能简单地获取到当前整个应用的快照。

## 使用
如果是模块化开发，那么需要先引入 Vuex，然后在调用`Vue.use()`方法来告知 Vue 加载并使用 Vuex，然后初始化一个状态对象，并注入到 Vue 根节点中：

```javascript
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.store({ ... })

new Vue({
    store
    ...
})
```

这样操作之后，就可以将 store 从根组件注入到所有子组件中，接着就可以在子组件中使用`this.$store`调用了。

如果不是模块化开发，而是直接引入 Vuex 代码文件的话，就不需要使用`Vue.use(Vuex)`这个操作了。

### state
如果需要在组件中使用多个 store 中的状态，可以使用 Vuex 2.0 提供的`mapState`方法来引入。可以使用对象扩展操作符(`Object spread operator`，也即是`...`)来实现导入多个状态。

比如，在组件中的计算属性中，添加一些本地的状态，并引入 store 中的部分状态，可以如下使用：

```javascript
computed: {
    loaclComputed () { ... },
    
    // 引入 store 中的状态
    ...mapState({
        message: state => state.message
    }),
    // 也可以使用数组方式
    ...mapState([
        'count'   // map this.count to store.state.count
    ])
}
```

### Getters
有时候我们需要从 store 的状态派生出其他状态，然后对这个状态（的方法）在多个组件中加以利用。通常我们的做法是复制这个方法，或者将它封装为一个公用的方法，然后在需要的时候导入，但是两者其实都不甚理想。

Vuex 提供了 getters 属性，用途类似 stores 中的计算属性。getters 中的方法接受两个参数，分别为`state`和`getters`（其他 getters），用法如下：

```javascript
getters: {
    doneTodosCount: (state, getters) =>{
        return getters.doneTodos.length
    }
}
```

在其他组件内部使用 getters 也变得十分简单：

```javascript
computed: {
    doneTodosCount () {
        return this.$store.doneTodosCount
    }
}
```

同样，如果要同时引入多个 getters，可以使用 mapGetters 方法。除了可以使用数组之外，还可以使用对象起别名。：

```javascript
computed: {
    ...mapGetters({
        count: 'anotherGetter'
    }),
    ...matGetters([
        'doneTodosCount',
        'anotherGetter',
    ])
}
```

### Mutations
能改变 Vuex store 中的 state 状态的唯一方法是提交 mutation 变更。mutation 和事件很像：都有字符串类型的 type 以及 handler 句柄。我们在 handler 中实际修改 state，state 为每个 mutation 的第一个参数。

```javascript
conststore =newVuex.Store({
state: {
count:1
 },
mutations: {
 increment (state) {
// mutate state
 state.count++
 }
 }
})

// call， 只有在使用 type increment 调用 mutation 时才能称为 handler
store.commit('increment')
```

commit 的第二个可选参数为 payload 有效载荷，可以为普通类型或对象类型等等。

commit 方法还可以通过对象形式调用，这种情况下，这个对象都会被当成 payload 。

```javascript
store.commit({
    type:'increment',
    amount:10
})
```

建议使用大写命名 Mutation，并将所有大写变量存放在一个文件中，需要的时候引入。使用 es6 的计算属性名新特性来使用常量作为方法名。这样可以避免后期修改 Mutation 名称的时候的麻烦。

* mutations 必须都是同步的，它的改变必须在调用之后立即执行。因为它是唯一可以修改 state 的，如果它使用了异步方法，将会使我们的 state 变得无法追踪，定位问题也变得很困难。
* 在组件中 commit mutation 时，可以使用`this.$store.commit()`或者使用`mapMutations`方法，后者可以将组件中的方法映射到`store.commit`调用（需要在根组件注入 store）。

```javascript
import { mapMutations } from 'vuex'

export default {
    // ...
    methods: {
        // 传入数组
        ...mapMutations([
            // map this.increment() to this.$store.commit('increment')
            'increment'
        ]),
        
        // 传入对象，可以使用 alias
        ...mapMutations({
            // map this.add() to this.$store.commit('increment')
            add: 'increment'
        })
    }
}
```

### Actions
Actions 是用来提交 Mutations 的，为什么有了 Mutations 还要用 Actions 呢？因为 Actions 可以有异步操作。

在一个 Actions 中，可以触发多个 Mutation，也可以根据不同的条件来触发不同的 Mutation。

Actions 的第一个参数是 context，它向外暴露一组与 store 实例相同的方法/属性，所以可以直接调用`context.commit`或者访问`context.state`或者`context.getters`。我们通常可以使用 es6 的参数解构来简化我们的代码：

```javascript
actions: {
    increment ({ commit }) {
        commit('increment')
    }
}
```

Mutations 是通过`commit`来触发的，而 Actions 则需要通过`dispatch`来触发：`store.dispatch('actionName')`。触发的时候，同样可以采用 payload 或者对象的方式。二者等价。

```javascript
// dispatch with a payload
store.dispatch('increment', {
    amount: 10
})

// dispatch with an object
store.dispatch({
    type: 'increment',
    amount: 10
})
```

在组件中，可以使用`this.$store.dispatch()`触发 Actions，也可以通过`mapActions`将 Actions 映射到组件方法中进行调用，同`mapMutations`。

由于 Actions 是异步的，因此我们就很难知道一个 Action 什么时候完成，以及该怎么把多个 Action 组合起来，处理复杂的异步工作流。

好在`store.dispatch()`方法返回了我们定义的 Action handler 的返回值，所以我们可以直接在 Actions 中返回一个 Promise。比如下面这样：

```javascript
actions: {
    actionA ({ commit }) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                commit('someMutation')
                resolve()
            }, 1000)
        })
    }
}
```

然后就可以这么用：

```javascript
// 在组件中可以这样用
store.dispath('actionA').then(() => {
    // Do something
})

// 在另一个 Action 中可以这样用
actions: {
    actionB ({dispatch, commit }) {
        return this.dispatch('actionA').then(() => { 
            commit('someOtherMutation')
        })
    }
}
```

### Modules
由于 Vuex 使用了单状态树，所以随着我们应用的规模逐渐增大， store 也越来越膨胀。为了应对这个问题，Vuex 允许我们将 store 分成多个 modules。每个 module 有着自己的 state, mutations, actions, getters, 甚至可以有嵌套（ nested ）的 modules。

在每个 Modules 中，Mutations、Getters、Actions 的参数也会有所变化：

* Mutations 和 Getters 中，接受的第一个参数是该 Modules 的本地 state。
* Getters 的第三个参数才是根 state。
* 在 Actions 中，`context.state`为本地 state，而`context.rootState`为根 state。

```javascript
const moduleA = {
    // ...
    mutations: {
        increment (state) {
            // state is the local module state
            state.count++
        }
    },
    getters: {
        // state 都是本地 state，rootState 才是根 state
        sumWithRootCount (state, getters, rootState) {
            return state.count + rootState.count
        },
        doubleCount (state) {
            return state.count * 2
        }
    },
    actions: {
        incrementIfOdd ({ state, commit }) {
            if(state.count % 2 === 1) {
                commit('increment')
            }
        }
    }
}
```

## Vuex 2.x 与 1.x 的区别
[Vuex 2.0 设计理念](https://github.com/vuejs/vuex/issues/236)
### 语义化
这个语义化说的是触发`action`和`mutation`的 API 上。

在使用 action 的时候，我们一般是从 vue 组件本身 dispatch 派发一个 action，这个其实只是一个命令，并没有实际改变什么；而 dispatch 一个 mutation 其实是改变了 vuex 本身的数据，所以一般从数据角度理解，这种应该属于事物提交。那么变化之后的命名就是 commit 这样更加语义化 也更好的理解职责。

新的用法如下：

```javascript
// dispatch --> actions
methods:{
  Add () {
    this.$store.dispatch('ADD',2).then(function(resp){
      console.log(resp)
    })
  }
}

// commit --> mutations
actions: {
  ADD (store, payload) {
    store.commit('ADD', payload)
  }
}
```

### 更灵活
1.x 之前的版本 action 是不定义在 vuex 里的，而 2.x actions 可以直接在 store 中定义了。也就是可以在 store 实例中直接 dispatch。

```javascript
var store =  new Vuex.Store({
    state: {
        messages: 0
    },
    mutations:{
        "ADD": function(state, msg) {
            state.messages += msg
        }
    },
    // action不用再去外面定义 可以直接写在构建参数里
    actions:{
        "ADD" : function(store , param){
            store.commit('ADD', param)
        },
    }
})
store.dispatch('ADD', 2)
```

而 getter 也是如此，在 vue 中可以直接取 getters：

```javascript
computed:{
   msg : function(){
      return this.$store.getters.getMessage
   }
}
```

### Promise Action
> 原文`Composable Action Flow`直译`可组合的 action 流`。

由于现在 actions 是被放在了 vuex 中的，需要通过 dispatch 来分发，所以在一定情况下，我们不能直接如同调用方法一样来调用 actions 了，也就不能自由组合操作了。但是由于 dispatch 会返回 actions 方法的返回，所以我们可以在 actions 中返回一个 Promise 来实现异步操作的可组合行。

```javascript
// action我们定义一个返回promise的add action
actions:{
    "ADD" : function(store , param){
        return new Promise(function(resolve, reject) {
            store.commit('ADD',param)
            resolve("ok")
        })
    }
}

// 这里可以在dispatch之后直接处理异步
this.$store.dispatch('ADD',2).then(function(resp){
   console.log(resp) // ok
})
```

### mapState/mapMutations/mapGetters/mapActions
新版 vuex 提供了几个封装方法`mapState`、`mapMutations`、`mapGetters`、`mapActions`。

在 1.x 中，如果我们需要用到 vuex 中的某些 state、getters、mutations、actions，那么就需要将其一个个的导入进来(`import { ... } from store`)。而在 2.x 中就可以使用这些方法来引入。

```javascript
// es6写法 支持rest参数这种写法 也可以直接完全使用map套装注入
import { mapGetters, mapActions } from 'vuex'
export default {
  computed: {
    someComuted () { … },
    ...mapGetters(['getMessage', 'getName'])
  },
  methods: {
    someMethod () { … },
    ...mapActions(['ADD','EDIT'])
  }
}
```

### Silent
使用这个选项，可以设置在 commit 一个 mutation 的时候是否触发订阅的插件。

默认 silent 为 false。如果 silent 设置为 true，则表示不触发注册的 subscribe，一般订阅的插件都不会触发了，包括 dev-tools。

```javascript
// 实例代码
store.commit('ADD', param, { silent: true})
```

```javascript
// 源码 
if (!options || !options.silent) {
  this._subscribers.forEach(sub => sub(mutation, this.state))
}
```

### 其他变动

```javascript
// 这个就是换个名字
store.middlewares -> store.plugins
    
// 这货貌似干掉又被还原了   
store.watch
   
// 使用 subscribe 监听 vuex 的变化
store.subscribe((mutation, state) => { ... })

// 注册模块
registerModule

// 注销模块
unregisterModule
```


## 错误
### 使用 mapState/mapGetters/mapActions 报错
vuex2 中增加了`mapState`、`mapGetters`和`mapActions`方法，借助 ES6-stage2 的`Object Rest Operator`特性可以写出下面代码：

```javascript
methods: {
  marked,
  ...mapActions([
    'getArticles'
  ])
}
```

但是在借助 babel 编译转换时发生了报错：`BabelLoaderError: SyntaxError: Unexpected token`。

首先需要考虑的就是 babel 的配置。babel 预置的转换器是`babel-preset-es2015`，并不能转换`Object Rest Operator`特性。所以我们需要安装对应的转换器。有两种方法：

* 安装整个 stage2 的预置器`babel-preset-stage-2`，然后对应的 babel 配置为：

```json
{
    "presets": ["es2015", "stage-2"]
}
```

* 安装`Object Rest Operator`的 babel 插件`babel-plugin-transform-object-rest-spread`。对应的 babel 配置如下：

```json
{
  "presets": [
    ["es2015", { "modules": false }]
  ],
  "plugins": ["transform-object-rest-spread"]
}
```

### 严格模式下，表单元素的 v-model 指令同步 Vuex state 会报错
严格模式下，如果在 Mutation handler 之外修改了 Vuex 的 state，应用就会抛错。当我们将 Vuex 中的某个数据，用 Vue 的 v-model 绑定到 input 时，一旦感应到 input 改动，就会尝试去直接修改这个数据，严格模式下就会报错。

所以建议是绑定 value 值，然后在 input 时间中调用 action。

```html
<input :value="message" @input="updateMessage">
```

```javascript
export default {
    // ...
    computed: {
        ...mapState({
            message: state.obj.message
        })
    },
    methods: {
        updateMessage (e) {
            // 调用 Vuex 状态中的 updateMessage Mutation
            this.$store.commit('updateMessage', e.target.value)
        }
    }
}
```

当然，我们也还是可以使用 v-model 指令的，只是要配合使用*双向计算属性和 setter*：

```javascript
export default {
    // ...
    computed: {
        message: {
            get () {
                return this.$store.state.obj.message
            },
            set (value) {
                // 直接 commit 到 mutation，type 为 updateMessage
                this.$store.commit('updateMessage', value)
            }
        }
    }
}
```

## 转摘
[Vuex 入门](https://jothy1023.github.io/2016/10/05/index/)
[vuex入门实例(3/3) - end](https://segmentfault.com/a/1190000006988584)

