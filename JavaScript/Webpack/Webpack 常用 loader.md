### html-withimg-loader

Webpack 无法处理 HTML 文件中的图片引用问题，而这个 loader 就是为了解决该问题的，它会自动处理好 HTML 中的图片打包和引用路径问题。

[官方地址](https://github.com/wzsxyz/html-withimg-loader)

1. 安装

    ```shell
    npm install html-withimg-loader --save
    ```

2. 使用

    在 js 中引用：
    
    ```js
    var html = require('html-withimg-loader!../xxx.html');
    ```

    或者，写到配置中：
    
    ```js
    loaders: [
        {
            test: /\.(htm|html)$/i,
            loader: 'html-withimg-loader'
        }
    ]
    ```
    
默认情况下，该插件会去除 HTML 文档中的换行，可以添加一个参数来解决：`loader: 'html-withimg-loader?min=false'`。

### file-loader & url-loader

基于 Webpack 进行开发时，引入图片会遇到一些问题。其中一个就是引用路径的问题。拿`background`样式用`url`引入背景图来说，Webpack 最终会将各个模块打包成一个文件，因此样式中的 url 路径是相对入口 html 页面的，而不是相对于原始 css 文件所在的路径的。这就会导致图片引入失败。

这个问题可以用`file-loader`解决，它可以解析项目中的 url 引入（不仅限于 css），根据配置将图片拷贝到相应的路径，再修改打包后文件引用路径，使之指向正确的文件。

`url-loader`则是对`file-loader`的一个封装，从而提供了一个将文件编码成 dataURI 的功能，该功能一般是为了解决当页面中引入大量的图片时会发很多 http 请求，而降低页面性能的问题。可以通过给`url-loader`传入一个`limit`参数，使其将小于该参数值的图片都编码成 dataURI 数据，而大于该值的则会使用`file-loader`进行处理。

由于`url-loader`能完成`file-loader`的所有功能，所以一般情况下，只需要使用`url-loader`即可。

[file-loader](https://github.com/webpack-contrib/file-loader)

[url-loader](https://github.com/webpack-contrib/url-loader)

1. 安装

    ```shell
    npm install --save-dev url-loader
    ```

2. 使用

    ```js
    loaders: [
       {
           test: /\.(jpg|jpeg|png|gif)$/i,
           loader: 'url-loader?limit=8192'
       }
    ]
    ```

### css-loader & style-loader

`css-loader`使可以使用类似`@import`和`url(...)`的方法实现`require`的功能，`style-loader`将所有的计算后的样式加入页面中，二者组合在一起能够把样式表嵌入 Webpack 打包后的 js 文件中，并最终能够在页面上使用。

因此，遇到后缀为`.css`的文件，应先用`css-loader`加载器去解析这个文件，遇到`@import`等语句就将相应样式文件引入（所以如果没有`css-loader`就没法解析这类语句），最后计算完的 css 将会使用`style-loader`生成一个内容为最终解析完的 css 代码的 style 标签，放到 head 标签里。

需要注意的是，loader 是有顺序的，肯定是应该先将所有 css 模块依赖解析完得到计算结果再创建 style 标签。因此应该把`style-loader`放在`css-loader`的前面（webpack loader 的执行顺序是从右到左）。

[css-loader](https://github.com/webpack-contrib/css-loader)

[style-loader](https://github.com/webpack-contrib/style-loader)

1. 安装

    ```shell
    npm install --save-dev css-loader style-loader
    ```

2. 使用

    ```js
    loaders: [
        {  
            test: /\.css$/,  
            use: ['style-loader', 'css-loader']  
        }
    ]
    ```

还有其他的一些 css 预处理器，都可以应用到 webpack 中，比如：

- [less loader](https://github.com/webpack/less-loader)
- [sass loader](https://github.com/jtangelder/sass-loader)
- [stylus loader](https://github.com/shama/stylus-loader)


### json-loader

`json-loader`加载和处理 json 文件。


