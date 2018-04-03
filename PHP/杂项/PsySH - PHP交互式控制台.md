PsySH 是一个 PHP 的运行时开发平台，交互式调试器和 Read-Eval-Print Loop (REPL) 。就和 Chrome 的调试工具一样。

* [PsySH 官网](http://psysh.org/)
* [GitHub](https://github.com/bobthecow/psysh)
* [Packagist](https://packagist.org/packages/psy/psysh)

### 安装

**直接下载下来用**

```shell
wget https://git.io/psysh
chmod +x psysh
./psysh
```

**使用 composer 来安装**

```shell
composer g require psy/psysh:@stable
psysh
```

> 在这之前您已经将安装了 php 和 composer ，并且把加入了环境变量，并建议全局安装。


### 文档

PsySH 的文档存放在`~/.local/share/psysh/`。(windows 系统存放在`C:\Users\{用户名}\AppData\Roaming\PsySH\`。)

下载中文文档：

```shell
cd ~/.local/share 
mkdir psysh
cd psydh
wget http://psysh.org/manual/zh/php_manual.sqlite
```

> 需先安装了 wget，Mac 可以使用`brew install wget`。

> 参考：[PHP manual installation](https://github.com/bobthecow/psysh/wiki/PHP-manual)

### 特性

* PsySH 是一个交互式的 PHP 运行控制台，在这里你可以写 php 代码运行，并且可以清楚看到每次的返回值：

    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507335945.png" width="275"/>

* 它很智能地知道你的代码是否已经结束：

    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507352965.png" width="275"/>

* 自动完成。PsySH 可以像控制台那样，按下两次`[tab]`键自动补全，帮你自动完成变量名，函数，类，方法，属性，甚至是文件：

    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507383031.png" width="275"/>

    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507390034.png" width="275"/>

    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507404414.png" width="275"/>


### 功能

1. `show` 查看源代码
    轻松展现任何用户级的对象，类，接口，特质，常数，方法或属性的源代码：
    
    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507493658.png" width="275"/>

    <img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1488507501696.png" width="275"/>

2. `list` 反射列表
    list 命令知道所有关于你的代码 - 和其他人的。轻松地列出并搜索所有的变量，常量，类，接口，特点，功能，方法和属性。
    
3. `wtf` 获取最后的异常信息
    如果忘记`catch`异常，可以使用`wtf`命令查看异常的信息。
    
4. `history` 历史记录
    可以像类 Unix 系统的`history`命令一样，在 PsySH 可以查看你运行过的 PHP 代码或命令。

5. `exit` 退出 PsySH。
    
6. `doc` 查看函数文档，如`doc array_merge`。


