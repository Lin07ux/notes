
### html-webpack-plugin

该插件可以自动把打包好的 js 文件自动引入到模板 HTML 中，并放在页面的最底部。这在输出的 js 名称含有 hash 的时候是很有用的，因为文件的 hash 会经常变化，使用该插件可以很方便的引入 js。

[官方地址](https://github.com/jantimon/html-webpack-plugin)

1. 安装：

    ```shell
    npm install --save-dev html-webpack-plugin
    ```

2. 使用

    ```js
    var HtmlWebpackPlugin = require('html-webpack-plugin');
    var webpackConfig = {
      entry: 'index.js',
      output: {
        path: __dirname + '/dist',
        filename: 'index_bundle.js'
      },
      plugins: [new HtmlWebpackPlugin()]
    };
    ```

另外，还可以使用该插件来压缩 html：

```js
new HtmlWebpackPlugin({
    template: './index.html',
    favicon: path.resolve(__dirname, './favicon.ico'),
    minify: {
        removeAttributeQuotes: true,
        removeComments: true,
        removeEmptyAttribute: true,
        collapseWhitespace: true
    }
})
```

### extract-text-webpack-plugin

对于 css，我们可以通过`css-loader`和`style-loader`处理好，并且都打包到最终的 js 文件中。但是，这个 js 是在页面最后面才加载进来的。也就是说，样式被放在了页面的底部被加载，而且要在 js 加载好执行后才会有。而希望的是样式在`head`中加载，而 js 脚本才放在页面底部加载。所以就不能把 css 和 js 一起打包进最终的 js 中了。

`extract-text-webpack-plugin`插件就是为了解决该问题的。这个插件可以将文本内容从打包文件中分离，并导入到一个单独的文件中。所以就可以使用该插件来分离 css。

[extract-text-webpack-plugin](https://github.com/webpack-contrib/extract-text-webpack-plugin)

1. 安装

```shell
# for webpack 3
npm install --save-dev extract-text-webpack-plugin
# for webpack 2
npm install --save-dev extract-text-webpack-plugin@2.1.2
# for webpack 1
npm install --save-dev extract-text-webpack-plugin@1.0.1
```

2. 使用

    ```js
    const ExtractTextPlugin = require("extract-text-webpack-plugin");
    
    module.exports = {
      module: {
        rules: [
          {
            test: /\.css$/,
            use: ExtractTextPlugin.extract({
              fallback: "style-loader",
              use: "css-loader"
            })
          }
        ]
      },
      plugins: [
        new ExtractTextPlugin("styles.css"),
      ]
    }
    ```

### UglifyJsPlugin

该插件可以用于压缩 js 文件，一般在最终生成打包的时候，需要压缩 js 文件，以便去除不必要的调试信息，减小 js 体积，增加加载速度。

该插件是 Webpack 自带的一个优化功能，所以不需要单独的安装即可使用。

```js
plugins: [
    new webpack.optimize.UglifyJsPlugin({compress: {warnings: false}})
]
```


