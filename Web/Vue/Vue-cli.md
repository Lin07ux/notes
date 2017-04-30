官方文档：[vuejs-template-webpack - GitBook](http://vuejs-templates.github.io/webpack/)

## 问题

### 资源路径

在项目中，默认会生成两个存放资源素材的文件夹：`static/`和`src/assets`。其中，前者是真正的静态文件目录，该目录下的所有文件在 build 的时候都会直接拷贝到 dist 目录中；后者则是需要先在项目中被引用，build 的时候才会处理到 dist 目录中。

在引用`static/`中的资源的时候，直接使用`/static/filename.ext`这种格式即可，而引用`src/asstes/`中的资源的时候则需要有一定的设置。

> 可以先查看官方文档：[Handing Static Assets](http://vuejs-templates.github.io/webpack/static.html)。

如果想通过`~assets/filename.ext`的方式进行访问，则先需要在 Webpack 的配置文件中设置对应的`resolve`项。比如，在 Vue-cli 2.8 版本中，需要在`build/webpack.base.conf.js`文件中，找到`resolve.alias`设置处，默认情况下，其配置如下：

```JavaScript
resolve: {
  extensions: ['.js', '.vue', '.json'],
  alias: {
   '@': resolve('src')
  }
}
```

可以看到，默认已经设置了一个`@`作为`src`文件的别名(所以我们可以在项目中使用`@`表示`src/`目录)。为了能够使 webpack 自动解析`~assets/`开头的资源，我们需要添加`'assets': resolve('src/assets')`到这个`alias`对象中，修改后如下：


```JavaScript
resolve: {
  extensions: ['.js', '.vue', '.json'],
  alias: {
    '@': resolve('src'),
    'assets': resolve('src/assets')
  }
}
```


