Vue 前端页面中，对用户的权限信息进行校验往往体现在两个方面：

* 路由的可见性
* 元素的可见性

在这两个方面来将用户权限之外的内容隐藏掉，从而达到更好的交互体验。

### 1. 路由可见性

Vue 的官方路由组件 vue-router 提供了一个`beforeEach`控制守卫，可以在该控制守卫中判断用户权限，从而决定当前用户是否可以进入相关路由中。

首先，在路由配置的`meta`字段中加入该路由的访问权限列表(如`auths`字段)：

```JavaScript
{
    path: 'edit',
    name: 'edit',
    meta: {
        title: '编辑账户',
        auths:['edit_account'],
    },
    component: () => import('pathToComponent/component.vue'),
},
```

然后，在路由的控制守卫中，使用全局前置守卫对路由跳转进行权限校验：

```JavaScript
const hasAuth = function (needAuths, haveAuths) {
   // TODO 判断用户是否拥有权限
};

router.beforeEach((to, from, next) => {
    const havaAuths = []; // 用户拥有的权限列表，可以从 Vuex store 中获取
    
    if (!hasAuth(to.meta.auths, haveAuths)) {
        // 没有权限重定位到其他页面，往往是 401 页面
        next({ replace: true, name: 'otherRouteName' })
    }
    
    // 权限校验通过,跳转至对应路由
    next();
})
```

最后，为了更好的体验，还需将页面中的导航进行一定的隐藏，避免用户可以直接点击进入到没有权限的路由中。

### 2. 元素的可见性

除了在全局上的路由控制，在特定页面上经常还需要将个别的元素(如按钮)根据用户的权限进行显示和隐藏，这时可以通过建立一个全局的指令来实现：

```JavaScript
// acl.js
const aclDirective = {
    // 在被绑定的元素插入到 dom 中时进行相关判断
    inserted: function(el, binding) {
        const hasAuth = function(needAuths, haveAuths) {
            // TODO 判断用户是否拥有权限 
        }
        
        const havaAuths = []; // 用户拥有的权限列表
        
        // binding.value 可以获得绑定指令时传入的参数
        if(!hasAuth(binding.value, haveAuths)) {
            el.style = "display:none"; // 修改元素的可见状态
        }
    }
}

// main.js 全局注册指令
Vue.directive('acl', aclDirective);
```

在需要控制显示的组件上就可以通过`v-acl`指令进行权限控制：

```Vue
<button v-acl="['edit_access']">编辑账户</button>
```


