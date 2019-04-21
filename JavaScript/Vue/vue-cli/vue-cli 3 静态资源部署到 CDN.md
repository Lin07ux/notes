vue-cli 3 中，可以通过在配置文件`vue.config.js`文件中增加`publicPath`来设置生成的静态资源的路径，类似如下：

```JavaScript
module.exports = {
    publicPath: isProduction ? 'https://cdn.example.com' : '/',
}
```

> `publicPath`替代了之前的`baseUrl`。

需要注意的是，`publicPath`会在多个地方使用，而且会将`process.env.VUE_APP_BASE_URL`的值也设置成这个值。而默认的`vue-router`的配置中，有`base: process.env.VUE_APP_BASE_URL`。也就是说，默认情况下，`publicPath`的值会被用于路由的前缀。这就会导致，编译出来的结果中，每次路由切换都会带有 CDN 的域名信息，所以为了配置更准确，还需要修改 router 的`base`项设置。比如，可以在`.env`配置文件中增加一个 router base 值选项，并在 router 定义的时候引用即可。


