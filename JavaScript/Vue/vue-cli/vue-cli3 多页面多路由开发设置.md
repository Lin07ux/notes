当使用 vue-cli 3 开发多页面时，如果引入了 vue-router 设置多路由，并使用 history 模式，在默认情况下，会出现页面无法显示或找不到路由的情况。有两种方式能够修正这个问题。

### 1. 改用 hash 模式

在 history 模式下，需要有服务器方面的配合才能正常访问，而 hash 模式因为是在前端改写 url 的 hash 来实现路由切换，则不会存在这个问题。所以在开发模式下，可以使用 hash 模式，而在发布模式下，再考虑切换成 history 模式，并在服务器端配置相关的跳转规则。

### 2. 修改 devServer

由于 history 模式下，是需要服务器端进行配合才能正常访问，那么就可以对 vue-cli 中的 devServer 进行配置，使其可以正常对 vue-router 路由进行匹配。

在 vue-cli 的配置文件 vue.config.js 中添加类似如下配置：

```js
module.exports = {
  pages: {
    home: {
      entry: 'src/pages/home/main.js',
      template: 'public/index.html',
      filename: 'home.html',
      title: 'Home Page',
      chunks: ['chunk-vendors', 'chunk-common', 'home']
    },
    admin: {
      entry: 'src/pages/admin/main.js',
      template: 'public/index.html',
      filename: 'admin.html',
      title: 'Admin Page',
      chunks: ['chunk-vendors', 'chunk-common', 'admin']
    },
  },
  devServer: {
    historyApiFallback: {
      rewrites: [
        { from: /\/admin/, to: '/admin.html' },
        { from: /\/home/, to: '/home.html' }
      ]
    }
  }
}
```

这样，对`home`和`admin`两个模块就可以单独使用其 vue-router 路由了。但为了更好的使用，还需要将各个 router 设置相应的`base`，否则会造成 vue-router 将连接导向不合适的地方。

