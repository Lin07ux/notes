## Webpack 是什么

Webpack 常被人们定义为“模块打包工具”（module bundler），它读取 JavaScript 模块，分析它们之间的依赖关系，然后用尽可能高效的方式将它们组织在一起，最后生成一个独立的 JS 文件。

Webpack 能读取的不光是原生的 JavaScript 文件，模块加载器的设计使得它能支持更丰富的格式。

## 安装

首先必须要安装的是 Node.js，然后用 npm 安装 webpack，建议全局安装：

```shell
# 全局安装
sudo npm install webpack -g
# 作为开发依赖安装
npm install --save-dev webpack
```

全局安装好之后，就能在系统的任何路径下执行 webpack 命令。

另外，为了自动化执行打包命令，可以安装`webpack-dev-server`模块：

```shell
# 全局安装
sudo npm install webpack-dev-server -g
# 作为开发依赖安装
npm install --save-dev webpack-dev-server
```

这个模块可以安装了一个基于 Node.js Express 的开发服务器，通过`webpack-dev-server`命令即可启动一个简单的 Web 服务器，以命令执行的路径为静态资源根目录。如果 Webpack 发现我们修改了一个文件，它会自动运行 webpack 命令打包我们的代码并刷新页面。这样我们就不用每次改变都去执行 webpack 命令并刷新页面了。

## 学习

1. [Webpack 介绍：第一部分](https://yufan.me/introduction-to-webpack-part-1/)
2. [【译】webpack入门指南](http://sugarball.me/yi-webpackru-men-zhi-nan/)
2. [Webpack傻瓜式指南（一）](https://github.com/vikingmute/webpack-for-fools/blob/master/entries/chapter-1.md)
3. [Webpack傻瓜指南（二）开发和部署技巧](https://github.com/vikingmute/webpack-for-fools/blob/master/entries/chapter-2.md)
4. [Webpack傻瓜指南（三）和React配合开发](https://github.com/vikingmute/webpack-for-fools/blob/master/entries/chapter-3.md)
3. [一小时包教会 —— webpack 入门指南](http://www.w2bc.com/Article/50764)

## 使用

Webpack 的基本用法是`webpack [entry file] [destination for bundled file]`。需要你指定一个输入文件(entry file)，然后通过这个输入文件来分析整个项目，然后打包为一个最终的文件。

相关的运行指令可以在命令行中加上，也可以更方便的在 Webpack 的配置文件中设置，在执行的时候，会自动的寻找`webpack.config.js`这个配置文件。

如果没有把 Webpack 全局安装，需要通过调用项目中 node_modules 文件夹中的 package 来实现打包，例如：

`node_modules/.bin/webpack app/main.js publi/bundle.js`

另外，我们还可以将每次项目开发、构建、打包过程中需要使用的指令直接写入到项目的 package.js 文件中的 script 部分，之后就能方便的使用 npm 来运行指定的指令，比如配置了如下指令：

```json
"scripts": {
  "start": "webpack" 
}
```

然后我们就可以在命令行中，进入项目根目录，执行`npm run start`来执行`webpack`指令。

> 注意：`start`在 package.json 中是一个特别的关键字，可以通过`npm start`来执行。当然你可以创建任何你想到的命令，但需通过`npm run <action>`来执行(`action`是你所建立的命令)。

## 配置文件

在项目根目录中，可以新建一个`webpack.config.js`文件，作为 Webpack 的配置文件，从多个方面控制 Webpack 的行为。

配置文件的主体结构如下：

```js
module.exports = {
    entry: __dirname + 'path/to/entry/file/',
    output: {
        path: __dirname + '/public', // 输出目录
        filename: "bundle.js"        // 输出文件名
    },
    devtool: '',
    module: {},
    plugins: {},
    devServer: {},
    resolve: {}
}
```

> 备注：`__dirname`是一个 nodejs 的 global 变量，指代当前执行文件所在的文件夹。

配置文件中必须要配置的两大块就是入口文件和出口文件的配置：

- `entry` 入口文件 让 webpack 用哪个文件作为项目的入口
- `output` 出口 让 webpack 把处理完成的文件放在哪里

另外，还有其他的一些可选配置项：

- `module` 模块 要用什么不同的模块来处理各种类型的文件
- `plugins` 插件 指定在 webpack 中要使用的插件
- `devtool` source map 配置
- `devServer` 开发服务器的设置

### source map

当把所有的文件都打包到一个文件中，在浏览器中调试，是不是非常不爽，不知道在源码中的哪个文件的哪行出了 bug，再加上如果有编译的过程就更苦逼了，比如 es6 或 coffeescript 转 js。

source map 的作用就是解决这个困境的，就是在浏览器中出现问题时能够自动映射到源文件中，知道是哪个文件的哪一行出了问题。

在 Webpack 配置中是通过`devtool`来设置 sourcemap 的，可以有如下几个值，分别对应不同的情况：

- `source-map` 产生一个完整的全面的 source map，这个选项的效果最好，但是它会降低 build 的效率。
- `cheap-module-source-map` 单独产生一个 source map 文件，但是去掉了具体列的信息，所以会降低调试的方便，却提高了 build 的效率。
- `eval-source-map` bundle 源代码是利用'eval'，source map 完整的和 bundle 的结果在同一个文件中。这个有和好的调试效果，同时不影响 build 的效率，但是可能会有执行效率和安全的缺点，但是在开发的过程是一个很好的选择。
- `cheap-module-eval-source-map` 这个是 build 效率最高的方式，和 eval-source-map 相似，但是去掉了具体的列的信息。和 eval-source-map 相似，它有执行效率和安全性的缺点，所以不适合用在生产环境中。

这四个选项是从上而下 build 速度越来越快，但是相应的产生的 source map 的缺点越多。

在中小型的项目中，`eval-source-map`是一个不错的选择，它 build 效率比较高，同时调试比较方便，同时我们可以写一个专门用于生产环境的 webpack 配置文件：`devtool: 'eval-source-map'`。

### module

#### loaders

Webpack 通过 loader 来加载各种各样的资源，不同的资源应用的不同的 loader，比如：打包 es6 会用到 babel-loader，打包 css 用到 style-loader 和 css-loader 等等。

loaders 是通过单独的 npm 来安装的，然后在 webpack.config.js 中通过`module`来配置。loader 的配置包括：

- `test` 一个正则表达式，用于检测不同的文件的后缀名，然后配置配置不同的 loader 。
- `loader` loader 的名字，比如'babel-loader'。
- `include/exclude` 配置哪些目录和文件需要包含或者排除。
- `query` 可以用于传递不同的参数给 loader。

举个例子：我们在项目中需要使用`json`文件来加载数据，可以使用`json-loader`来加载和处理 json 文件，`webpack.config.js`可以如下配置：

```js
module.exports = {
    module: {
        loaders: [
            {   // json-loader 处理 json 文件
                test: /\.json$/,
                loader: "json"
            }
        ]
    }
}
```


## 模块&插件

Webpack 本身能做的事情并不多，但是通过加载各种各样的插件模块，或者自行编写相应的插件模块能够实现各种想要的功能。下面就列举一些常用的插件，并解释其作用。

**参考**：
[webpack 看我就够了（三）](http://www.jianshu.com/p/b5248d441d9e)

### webpack-dev-server

Webpack development server 是一个 Webpack 可选的本地开发的 server。它通过 nodejs 的 express 来起一个 server 提供静态文件服务，同时它根据配置信息（webpack.config.js）来打包资源，存在内存中，同时当你的代码发生改变的时候，它还可以刷新你的浏览器。

`webpack-dev-server`是一个单独的 npm module，通过`npm install webpack-dev-server --save-dev`来给项目安装依赖，当然也可以全局安装。可以通过 webpack.config.js 的`devServer`选项来配置：

```js
module.exports = {
  devServer: {
    contentBase: './public',
    color: true,
    historyApiFallback: true,
    inline: true
  }
}
```


具体配置包括：

- `contentBase` 设置 webpack-dev-server 服务器的根目录。默认是从项目的根目录提供服务，如果要从不同的目录提供服务，可以通过 contentBase 来配置，比如 rails 中可以把 contentBase 配置成`'./public'`。
- `port` 默认 webpack 是用 8080 端口起的，通过 port 可以配成其他的端口。
- `inline` 设置为 true 时，代码有变化，浏览器端就会自动刷新。
- `colors` server 运行的时候，terminal 输出带颜色。
- `historyApiFallback` 对于单页面程序，浏览器的 brower histroy 可以设置成 html5 history api 或者 hash，而设置为 html5 api 的，如果刷新浏览器会出现 404 not found，原因是它通过这个路径（比如`/activities/2/ques/2`）来访问后台，所以会出现 404，而把`historyApiFallback`设置为 true 那么所有的路径都执行 index.html。
> 利用 html5 的 history，生产环境的 nginx 可以这么配置，可以参考下：
> ```nginx
> location / { 
    expires -1; 
    add_header Pragma "no-cache"; 
    add_header Cache-Control "no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
    root /var/web;
    try_files $uri $uri/ /index.html =404; 
}
```

使用了`webpack-web-server`模块后，就不需要执行 webpack 指令，而是执行`webpack-web-server`指令来开启一个自动打包刷新的开发服务器。


### babel

babel 是一个编译 javascript 的工具，它可以实现：

    * 让你用下一代 javascript（es6/es7/es2015/es2016）来写代码。
    * 可以使用 javascript 的扩展语法，比如 react jsx。
    * babel 是一个单独的工具，但是我们可以通过`babel-loader`在 webpack 中应用它。

babel 是一个模块化的并且分发到不同的 npm modules。核心的功能`babel-core`是通过`babel-loader`安装来直接使用的。但是，对于一些其他的功能和扩展要另外的安装（最常用的是`babel-preset-es2015`和`babel-preset-react`分别用于支持 es6 和 react jsx）：

`npm install --save-dev babel-core babel-loader babel-preset-es2015 babel-preset-react`

像其他的 laoder 一样，可以通过配置文件来配置：

```js
module.exports = {
    module: {
        loaders: [
            {   // 使用 babel-loader
                test: /\.js$/,
                loader: 'babel',
                exclude: /node_modules/,
                query: {
                    // 项目可以利用 es6 的属性和 react 的 jsx
                    presets: ['es2015','react']
                }
            }
        ]
    }
}
```

babel 可以通过 webpack 的配置文件直接配置，但是它有很多的配置信息，都放到同一个 webpack 的配置文件中会使得配置文件不好维护。因为这个原因很多的开发者选择了单独的 babel 配置文件`.babelrc`，来配置 bebel 的选项等等。目前我们对 babel 的配置只是`presets`，所以可以先把这个配置放到`.babelrc`中：

```js
// webpack.config.js 中的 babel loader 配置
loaders: [
  {   // 使用 babel-loader
      test: /\.js$/,
      loader: 'babel',
      exclude: /node_modules/
  }
]
```

```
# .babelrc 文件
{
    'presets': ['es2015','react']
}
```

### PostCSS

一个新的趋势更加宽松的 css 工作流程是通过应用`PostCSS`来实现的。不是通过一个完整的，固定的 css 语言，PostCSS 是一个 css 转译工具。通过连接不同的插件，来应用不用的转译到你的 css 文件。你可以通过[这个](https://github.com/postcss/postcss)了解更多。

这里我们通过 PostCSS 和`autoprefixer`插件来举例子，其中`autoprefixer`是给我们的 css 自动添加浏览器供应商前缀。

首先安装这些包：

`npm install --save-dev postcss-loader autoprefixer`

然后配置 webpack：

```js
// webpack.config.js
module.exports = {
    ...
    module: {
        loaders: [
            {
                test: /\.css$/,
                loader: 'style!css?modules!postcss'
            }
        ]
    },
    postcss: [ require('autoprefixer') ]
```


