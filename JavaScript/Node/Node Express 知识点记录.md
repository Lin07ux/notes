### 1. 全局 404 响应

在 Express 中，404 响应不是错误的结果，所以错误处理程序中间件不会将其捕获。此行为是因为 404 响应只是表明缺少要执行的其他工作；换言之，Express 执行了所有中间件函数和路由，且发现它们都没有响应。

需要做的只是在堆栈的最底部（在其他所有函数之下）添加一个中间件函数来处理 404 响应：

```JavaScript
app.use(function(req, res, next) {
    res.status(404).send('Sorry cant find that!');
});
```

需要注意的是，这个中间件需要放在**最底部**，否则会造成在其下方的路由和中间件不被处理。

### 2. res.sendfile() 出现 Forbidden 错误

当在 Express 的路由中使用相对路径(`../`)输出文件内容的时候，由于这可能存在被攻击的风险(恶意访问者通过构造特定的相对路径使得可以获取到服务器的私密文件)，所以 Express 是禁止在`sendfile()`方法中使用相对路径的。

要解决这个问题有三个方法：

1. 对于较多的文件直接输出，可以考虑使用`express.static()`方法将文件的路径设置为静态路径，这样就可以直接访问了：

    ```JavaScript
    app.use(express.static(dir);
    ```

2. 先使用`path.resolve()`得到对应的文件路径，然后再调用`sendfile()`方法：

    ```JavaScript
    const path = require('path');
    res.sendfile(path.resolve(__dirname + '/../' + path));
    ```

3. 调用`sendfile()`方法时，传入`root`选项，避免构造相对路径：

    ```JavaScript
    res.sendfile(path, {'root': '/path/to/root/directory'});
    ```

