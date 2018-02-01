## 简介

[Babel](https://babeljs.io/) 是一个广泛使用的转码器，可以将 ES6 代码转为 ES5 代码，从而在现有环境执行。

现在在使用 Babel 开发项目的时候，一般是通过构建工具来进行代码转换的，这就需要对 Babel 进行配置，而 Babel 的配置文件名称就是`.babelrc`。一般将这个文件放在项目的根目录即可。`.babelrc`文件是一个 JSON5 格式的文件。[点击查看官方文档](https://babeljs.io/docs/usage/babelrc/)。

```json
{
  "env": {"production": {}},
  "presets": [],
  "plugins": ["transform-react-jsx"],
  "ignore": ["foo.js"]
}
```

> 还可以在项目的`package.json`文件中指定 Babel 的配置，如下：
> 
>    ```json
>    {
>      "name": "my-package",
>      "version": "1.0.0",
>      "babel": {
>        // my babel config here
>      }
>    }
>    ```

## 路径查询

默认情况下，Babel 会从当前路径中寻找`.babelrc`文件，如果找不到的话，会继续往上级目录中查找，直到根目录。

当然，也可以在 Babel 的选项中设置`"babelrc": false`，或者在命令行中设置`--no-babelrc`选项来阻止这个查找行为。


## 配置项

babelrc 配置文件中，可以使用如下的配置选项：

### env

可以通过`env`选项为某个或某些特定的环境(如开发环境、产品环境)设置特定的配置。

```json
{
  "env": {
    "production": {
      "plugins": ["transform-react-constant-elements"]
    }
  }
}
```

在这个选项中为特定环境执行的配置会被合并到通用设置中，并覆盖通用配置，因为这里的配置的优先级比通用设置高。

判断是某个环境时，会先从`process.env.BABEL_ENV`来取值；如果该值不可用，则从`process.env.NODE_ENV`取值；如果这个值也不可用，则会取默认值`development`。

1. 路径

    默认情况下，Babel 会从`node_modules`文件中查找对应的 preset，当然也可以指定一个相对或者绝对路径，比如：`"plugins": ["./node_modules/asdf/plugin"]`。

2. 缩写

    如果 preset 的名称是以`babel-preset-`开头的，那么在配置的时候就可以缩写，比如，对于`"presets": ["babel-preset-myPreset"]`，可以写成`"presets": ["myPreset"]`；对于`"presets": ["@org/babel-preset-name"]`，可以写成`"presets": ["@org/name"]`。
    
3. 顺序

    presets 的执行顺序是和定义顺序相反的，也就是会从最后一个开始，再到第一个结束。比如，对于如下的配置，执行的顺序是先`stage-2`，再`react`，最后`es2015`：
    
    ```json
    {
      "presets": [
        "es2015",
        "react",
        "stage-2"
      ]
    }
    ```

### presets

> 官方文档：[](http://babeljs.io/docs/plugins#presets)

`presets`字段设定转码规则，官方提供以下的规则集，你可以根据需要安装：

```
env       # npm install --save-dev babel-preset-env
es2015    # npm install --save-dev babel-preset-es2015
es2016    # npm install --save-dev babel-preset-es2016
es2017    # npm install --save-dev babel-preset-es2017
latest    # npm install --save-dev babel-preset-latest
react     # npm install --save-dev babel-preset-react
flow      # npm install --save-dev babel-preset-flow
```

其中，`env`在没有其他配置的情况下，表示的是历年来的 presets，`latest`和`env`表示的意义相同，目前建议使用`env`。

在`env` presets 中，还可以指定需要兼容的浏览器，这样可以减少一定的转换和垫片的数量。比如，下面的配置表示仅需要兼容各个浏览器的最新的两个版本，并且要兼容不低于 Safari 7 的 Safari 浏览器：

```json
{
  "presets": [
    ["env", {
      "targets": {
        "browsers": ["last 2 versions", "safari >= 7"]
      }
    }]
  ]
}
```

而下面的配置表示需要兼容的是 Node 环境，而且兼容到 Node 6.10 版本：

```json
{
  "presets": [
    ["env", {
      "targets": {
        "node": "6.10"
      }
    }]
  ]
}
```

> 如果改成`"node": "current"`则表示兼容当前最新的 Node 即可。

### plugins

该字段用于配置一些插件，这些插件可以使 Babel 分析一些特定的语法。

> 插件不是用于 Babel 转换语法的。

比如，下面就是配置两个插件，使 Babel 能够分析理解`jsx`、`flow`相关的语法。

1. 路径

    如果所使用的插件存放于 npm 上，可以使用如下的方式进行配置`"plugins": ["babel-plugin-myPlugin"]`，这样 Babel 就会自动从`node_modules`文件夹中查找。
    
    当然，也可以指定具体的路径(相对路径或绝对路径)，如：`"plugins": ["./node_modules/asdf/plugin"]`。

2. 缩写

    如果插件的名称是以`babel-plugin-`开头的，那么就可以使用缩写形式，比如前面的那个配置就可以写成这样：`"plugins": ["myPlugin"]`。
    
3. 顺序

    对于代码而言，所有需要处理的文件都会按照顺序来使用插件，并且插件的使用会在 presets 之前。plugins 的使用顺序是按照定义时的顺序从前往后开始。
    
    比如，对于下面的配置，执行顺序就是先`transform-decorators-legacy`，再`transform-class-properties`：
    
    ```json
    {
      "plugins": [
        "transform-decorators-legacy",
        "transform-class-properties"
      ]
    }
    ```

### ignore

该选项用于指示 Babel 在进行分析转换时，忽略掉哪些路径和文件的。默认情况下，会自动忽略掉` node_modules`文件夹。当然，也可以添加其他的路径和文件被忽略：

```json
{
  "ignore": [
    "foo.js",
    "bar/**/*.js"
  ]
}
```

