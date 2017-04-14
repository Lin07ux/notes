## 0x00 Grunt 与 gulp

目前，`Grunt`依然是前端领域构建工具的王者，拥有非常大的插件库，使用者和开发者也非常多，但是它也不是完美无缺的，而最近正在快速崛起的`gulp`正是为了解决`Grunt`的一些缺陷而出现的。

### 1、Grunt 之殇

`gulp.js`的作者 Eric Schoffstall 总结了`Grunt`的几点不足之处：

1. **插件很难遵守单一责任原则。** 因为`Grunt`的API设计缺陷，使得很多插件不得不负责一些和其主要任务无关的事情。比如说要对处理后的文件进行更名操作。

2. **用插件做一些本来不需要插件来做的事情。** 因为`Grunt`提供了统一的`CLI`入口，子任务由插件定义，由`CLI`命令来调用执行，因此哪怕是很简单的外部命令(比如说运行`karma start`)都得拥有一个插件来负责封装它，然后再变成`Grunt CLI`命令的参数来运行，多此一举。

3. **试图用配置文件完成所有的事情，结果就是混论不堪。** 规模较大，构建/分发/部署流程较为负责的项目，其`Gruntfile`有多庞杂相信有经历的人都有所体会。而`gulp.js`奉行的是“写程序而不是写配置”，它走的是一种 _node way_。

4. **落后的流程控制产生了让人头痛的临时文件/文件夹所导致的性能滞后。** 这是`gulp.js`下刀子的重点，也是其说明中的`The streaming build system`的含义所在。流式构建改变了底层的流程控制，大大提高了构建工作的效率和性能，给用户的直观感觉就是：更快！

### 2、Gulp 的五大特点

作为对比和总计，下面列出`gulp`的五大特点：

1. 使用`gulp`，你的构建脚本是代码，而不是配置文件；
2. 使用标准库(node.js standard library)来编写脚本；
3. 插件都很简单，只负责完成一件事情-基本上都是20行左右的函数；
4. 任务都以最大的并发数来执行；
5. 输入/输出(I/O)是基于“流式”的。

## 0x01 Gulp 简单入门

`gulp`的文档目前基本都很完善了，[官方文档](https://github.com/gulpjs/gulp/blob/master/docs/README.md)在 Github上，有多种语言，或者也可以直接查看[中文官网](http://www.gulpjs.com.cn/docs/)。

### 1、基础入门

**第一步： 全局安装 gulp**

```shell
npm install -g gulp
```

> 由于`gulp`是基于`Node.js`的，所以你需要确保你已经安装好了`node`和`npm`。这个是全局安装的，对于所有的项目，都只需要安装这一次。

**第二步： 作为项目的开发依赖(devDependencies)安装**

```shell
npm install --save-dev gulp
```

> 这一步也是必不可少的，对每个项目都需要这样安装一次。需要注意点是，即使项目中没有`package.json`文件也是能正常运行的，但是还是建议创建这个文件。

**第三步： 在项目根目录下创建一个名为 gulpfile.js 的文件，初始内容如下**

```JavaScript
var gulp = require('gulp');

gulp.task('default', function() {
    // 将你的默认的任务代码放在这里
});
```

**第四步： 运行 gulp**

```shell
gulp
```

默认的名为 default 的任务(task)将会被运行，即命令`gulp`等同于`gulp default`命令。在这里，这个任务并未做任何事情。

想要单独执行特定已定义的任务(task)，需要运行如下的命令：`gulp <task> <othertask>`。

### 2、Gulp 四大基本 API

`gulp`和`Grunt`在使用上并没有太大的区别，从上面的基础入门中我们已经能够看出了。而为了能够顺利的编写构建脚本文件，我们也只需要了解`gulp`四个最核心的 API 函数即可。（具体可以查看 [gulp API 的中文文档](http://www.gulpjs.com.cn/docs/api/)。`gulp`还有一个基本 API： gulp.run(tasks...)，用于运行 tasks。）

#### 2.1 gulp.task(name[, deps], fn)

注册一个任务。定义的任务可以在 CLI 中直接调用运行，也可以在脚本中被引用。

- `name`是任务的名称，如果你需要在命令行中运行这个任务，那么就不要再这个任务的名称中使用空格；
- `deps`是可选数组，其中列出需要在本任务运行中执行的任务，这些任务会在当前任务运行之前完成；

> 注意：你的任务是否在这些前置依赖的任务完成之前运行了？请一定要确保你所以来的任务列表中的任务都使用了正确的异步执行方式：使用一个 callback， 或返回一个 promise 或 stream。

- `fn`定义任务要执行的一些操作，通常来说，他会是这种形式的：`gulp.src().pipe(someplugin())`。

> 如果任务的`fn`满足下面三种情况的任意一种，这个任务就是异步执行任务：
 
 - 接受一个回调函数 callback
  
  ```JavaScript
  // 在 shell 中执行一个命令
  var exec = require('child_process').exec;
  gulp.task('jekyll', function(cb) {
    // 编译 Jekyll
    exec('jekyll build', function(err) {
      if (err)
        return cb(err);   // 返回 error
      cb();   // 完成 task
    });
  });
  ```
 
 - 返回一个 stream
  
  ```JavaScript
  gulp.task('someName', function() {
    var stream = gulp.src('client/*.js')
      .pipe(minify())
      .pipe(gulp.dest('build'));
    return stream;
  });
  ```
 
 - 返回一个 promise
  
  ```JavaScript
  var Q = require('q');
  gulp.task('someName', function() {
    var deferred = Q.defer();
    // 执行异步的操作
    setTimeout(function() {
      deferred.resolve();
    }, 1);
    return deferred.promise;
  });
  ```

默认情况下，task 将已最大的并发数执行，也就是说`gulp`会一次性运行所有的 task，并且不会做任何等待。如果你想要创建一个序列化的 task 队列，并以特定的顺序执行，你需要做两件事情：

- 给出一个提示，来告诉 task 什么时候执行完毕。（可以在完成的时候返回一个 callback，或者返回一个 promise / stream。）
- 并且再给出一个提示，来告诉一个 task 依赖另外的 task 的完成。（在 gulp.task() 中设置参数 devps。）

```JavaScript
var gulp = require('gulp');

// 定义一个名称为'one'的任务
gulp.task('one', function() {
    // 完成一些事情
});

// 定义一个需要在任务'one'完成之后才能执行的任务'two'
gulp.task('two', ['one'], function() {
    // 在任务'one'完成之后，做另一些事情
});

gulp.task('default', ['one', 'two']);
```

#### 2.2 gulp.src(globs[, options])

指明源文件的路径。

- `globs`所要读取的 glob 或者包含 globs 的数组，类型是`string`或`Array`。`glob`请参考 [node-glob 语法](https://github.com/isaacs/node-glob)，或者也可以直接写成源文件的路径；
- `options`可选`Object`参数，详情参见 [gulp API](https://github.com/gulpjs/gulp/blob/master/docs/API.md#options)。

> - `options.buffer`: Boolean布尔值，默认为`true`。如果该项被设置为`false`，那么将会以 stream 方式返回`file.contents`而不是文件 buffer 的形式。这在处理一些大文件的时候将会很有用。但是需要注意，有些差距可能并不会实现对 stream 的支持。
> 
> - `options.read`: `Boolean`布尔值，默认为`true`。如果该项被设置为`false`，那么就不会去读取文件，返回空值（null）。
>
> - `options.base`: `String`字符串，默认值是`glob`起始位置的内容。可以参见 [glob2base](https://github.com/contra/glob2base)。

```JavaScript
// 匹配 'client/js/**/*.js'，并且将`base`解析为`client/js/`
gulp.src('client/js/**/*.js')
    .pipe(minify())
    .pipe(gulp.dest('build'));  // 写入 'build/somedir/somefile.js'

// 匹配 'client/js/**/*.js'，将`base`解析成`client`
gulp.src('client/js/**/*.js', { base: 'client' })
    .pipe(minify())
    .pipe(gulp.dest('build'));
```

#### 2.3 gulp.dest(path[, options])

指明任务处理后的目标输出路径。

能被 pipe 进来，并且会写文件，将输出所有的数据。可以将它 pipe 到多个文件夹，如果文件夹不存在，将会自动创建。

文件被写入的路径是以所给的相对路径路径根据所给的源文件目标目录计算出来的。类似的，相对路径也可以根据所给的 base 来计算。

- `path`文件奖杯写入的路径（即输出目录）。可以传递一个`String`的路径，也可以传入一个`function`函数，在函数中返回相应的路径；

- `options`可选的`Object`对象。

> - `options.cwd`: `String`字符串，默认值为`process.cwd()`，表示输出目录的`cwd`参数，只在所给的输出目录是相对路径的时候有效。
>
> - `options.mode`: `String`字符串，默认值为`0777`，八进制权限字符串，用于定义所在输出目录中所创建的目录的权限。

```JavaScript
gulp.src('./client/templates/*.jade')
    .pipe(jade())
    .pipe(gulp.dest('./build/templates'))
    .pipe(minify())
    .pipe(gulp.dest('./build/minified_templates'));
```

#### 2.4 gulp.watch()

监视文件的变化，并运行相应的任务。

这个 API 有两种写法： gulp.watch(glob[, options], tasks) / gulp.watch(glob[, options, callback])。

- `glob`一个 glob 字符串，或者一个包含多个 glob 字符串的数组，用来指定具体监控哪些文件的变动；

- `options`可选的`Object`对象。传给 [gaze](https://github.com/shama/gaze) 的参数；

- `tasks`需要在文件变动后执行的一个或多个通过`gulp.task()`创建的 task 的名字。

- `callback`每次变动需要执行的 callback。callback 会被传入一个名为 event 的对象。这个对象描述了所监控到的变动。

> - `event.type`  发生变动的类型：`added`、`changed`、`deleted`。
>
> - `event.path`  触发了改事件的文件路径。

```JavaScript
var watcher = gulp.watch('js/**/*.js', ['uglify', 'reload']);

watcher.on('change', function(event) {
    console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
});


gulp.watch('js/**/*.js', function(event) {
    console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
});
```

### 3、范例

展示一个最常见的范例，写一个最常见的构建脚本，做三件事情（括号中列出对应的插件的名字，更多插件请在 [gulp Plugins 网站](http://gulpjs.com/plugins/)查找）：

1. 语法检查（`gulp-jshint`）
2. 合并文件（`gulp-concat`）
3. 压缩代码（`gulp-uglify`）

另外，我们可能还会需要文件更名操作，所以`gulp-rename`也会有用。

首先，我们需要先在项目下安装这些插件（安装之前，请先确保已经在项目中安装了`gulp`）：

```shell
npm install --save-dev gulp-jshint gulp-concat gulp-uglify gulp-rename
```

然后，我们需要完成相关任务的编写，完整代码如下：

```JavaScript
var gulp = require('gulp'),
    jshint = require('gulp-jshint'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename');

// 语法检查task
gulp.task('jshint', function() {
    return gulp.src('src/*.js')
        .pipe(jshint)
        .pipe(jshit.reporter('default'));
});

// 合并文件之后压缩代码
gulp.task('minify', function() {
    return gulp.src('src/*.js')
        .pipe(concat('all.js'))
        .pipe(gulp.dest('dest'))
        .pipe(uglify())
        .pipe(rename('all.min.js'))
        .pipe(gulp.dest('dist'));
});

// 监视文件的变动
gulp.task('watch', function() {
    gulp.watch('src/*.js', ['jshint', 'minify']);
});

// 注册缺省任务
gulp.task('default', ['jshint', 'minify', 'watch']);
```

可以看出，基本所有的任务体都是下面的这种模式：

```JavaScript
gulp.task('任务名称', function() {
    return gulp.src('文件')
        .pipe(...)
        .pipe(...)
        // 直到任务的最后一步
        .pipe(...);
});
```

获取要处理的文件，传递给下一个环节处理，然后把返回的结果继续传递个一下一个环节，一直到所有的环节完成。`pipe`就是`stream`模块里负责传力流数据的方法而已，至于最开始的`return`则是把整个任务的`stream`对象返回出现，以便任务和任务可以依次传递执行。下面的写法会更加直观：

```JavaScript
gulp.task('任务名称', function() {
    var stream = gulp.src('...')
        .pipe(...)
        .pipe(...)
        // 直到任务的最后一步
        .pipe(...);

    return stram;
});
```

### 4、Glup CLI

#### 4.1 参数

- `-v`或`--version`  显示全局和项目本地所安装的`gulp`的版本号

- `--require <moudle path>`  将会在执行之前 require 一个模块。这对于一些语言编译器或者需要其他应用的情况来说很有用。你可以使用多个`--require`。

- `--gulpfile <gulpfile path>`  手动指定一个 gulpfile 的路径，这在你有很多个 gulpfile 的时候很有用。这也会将 CWD 设置打该 gulpfile 所在的目录。

- `cwd <dir path>`  手动指定 CWD。定义 gulpfile 查找的位置，此外，所有的相应的依赖（require）会从这里开始计算相对路径。

- `-T`或`--tasks`  会显示所指定 gulpfile 的 task 依赖树。

- `--tasks-simple`  会以纯文本的方式显示所载入的 gulpfile 中的 task 列表。

- `--color`  强制 gulp 和 gulp 插件显示颜色，即便没有颜色支持。

- `--no-color`  强制不显示颜色，即便检测到有颜色支持。

- `--silent`  禁止所有的 gulp 日志。

命令行会在`process.env.INIT_CW`中记录它是从哪里被运行的。

#### 4.2 Task 特定的参数标记

请参考 [StackOverflow](http://stackoverflow.com/questions/23023650/is-it-possible-to-pass-a-flag-to-gulp-to-have-it-run-tasks-in-different-ways) 了解如何增加任务特定的参数标记。

#### 4.3 Tasks

Task 可以通过`gulp <task> <othertask>`方式来执行。

如果只运行`gulp`命令，则会执行所注册的名为`default`的 task。如果没有这个 task，那么 gulp 会报错。

#### 4.4 编译器

你可以在 [interpret](https://github.com/js-cli/js-interpret) 找到所支持的语言列表。


