### Error: spawn cmd ENOENT

**问题描述**

在 Window 中进行编译，或者通过 npm script 打开开发服务器的时候，可能会出现`Error: spawn cmd ENOENT`的错误。

**问题原因**

这个一般是由于系统路径中没有设置好的缘故，从而造成无法打开相关软件(比如开发时会自动打开浏览器)。

**解决办法**

将`C:\Windows\System32\`添加到系统路径中。

> 参考：[StackOverflow](http://stackoverflow.com/questions/28624686/get-spawn-cmd-enoent-when-try-to-build-cordova-application-event-js85)


### 安装 phantomjs 时报错

**问题描述**

在 Window 中，当使用`npm i`来安装 phantomjs，的时候，总是会提示错误，无法安装。即便手动下载安装 phantomjs 到系统路径中，还是会跳过已安装版本，重新安装，继而报错：

```
Considering PhantomJS found at C:\npm\phantomjs-2.1.1-windows\bin\phantomjs.EXE
Looks like an `npm install -g` on windows; skipping installed version.
```

**问题原因**

*暂不清楚*

**解决方案**

这是一个临时的解决方案：

* 手动下载[phantomjs-2.1.1-windows.zip](https://github.com/Medium/phantomjs/releases/download/v2.1.1/phantomjs-2.1.1-windows.zip)；
* 拷贝下载的到的 phantomjs-2.1.1-windows.zip 到`C:\Users\<username>\AppData\Local\Temp\phantomjs\phantomjs-2.1.1-windows.zip`(`<username>`表示你当前登录的用户名)。

这样操作后，就可以解决重复下载继而报错的问题。但是如果清除了这个 Temp 路径中放入的 phantomjs 压缩包文件，这个问题将会继续出现。

> 参考：[StackOverflow](http://stackoverflow.com/questions/40992231/failed-at-the-phantomjs-prebuilt2-1-13-install-script-node-install-js)

### 安装 phantomjs-prebuild 报错

**问题描述**

在 Windows 中安装 phantomjs-prebuild 时会提示错误，类似如下：

```
npm ERR! phantomjs-prebuilt@2.1.13 install: `node install.js`  
npm ERR! Exit status 1  
npm ERR!  
npm ERR! Failed at the phantomjs-prebuilt@2.1.13 install script 'node   install.js'.  
npm ERR! Make sure you have the latest version of node.js and npm installed.
```

**问题描述**

*暂不清楚*

**解决方案**

使用如下的命令安装：

```bash
npm install phantomjs-prebuilt@2.1.13 --ignore-scripts
```

也可以加上`-g`来全局安装。

> 参考：[StackOverflow](http://stackoverflow.com/questions/40992231/failed-at-the-phantomjs-prebuilt2-1-13-install-script-node-install-js)


