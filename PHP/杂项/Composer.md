Composer 是 PHP 用来管理依赖（dependency）关系的工具。你可以在自己的项目中声明所依赖的外部工具库（libraries），Composer 会帮你安装这些依赖的库文件。

> 和 Node 中的 NPM 的功能相同。

## 一、 安装

> 安装前请务必确保已经正确安装了 PHP。打开命令行窗口并执行 php -v 查看是否正确输出版本号。

### 1. 下载 composer.phar

打开命令行并执行下列命令安装最新版本的 Composer (以下指令摘自 [getcomposer.org](https://getcomposer.org/download/)，请前往查看最新的安装指令)：

```shell
# 下载安装脚本（composer-setup.php）到当前目录
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php

# 执行安装过程：检测设置，下载 composer.phar 文件
php composer-setup.php

# 删除安装脚本 -- composer-setup.php
php -r "unlink('composer-setup.php');"
```

执行第一条命令下载下来的`composer-setup.php`脚本将简单的检测`php.ini`中的参数设置，如果某些参数未正确设置则会给出警告；然后下载最新版本的`composer.phar`文件到当前目录。

> 注意：由于`getcomposer.org`被墙了，所以很有可能我们根本不能正常执行这段代码以便下载文件，那就需要使用科学上网工具，或者从其他开发者那里直接获取`composer.phar`文件，再继续下面的步骤。

### 2. 局部安装

上述下载 Composer 的过程正确执行完毕后，可以将`composer.phar`文件复制到任意目录（比如项目根目录下），然后通过`php composer.phar`指令即可使用 Composer 了！

### 3. 全局安装

全局安装是将 Composer 安装到系统环境变量 PATH 所包含的路径下面，然后就能够在命令行窗口中直接执行 composer 命令了。

**Mac 或 Linux 系统**：打开命令行窗口并执行如下命令将前面下载的`composer.phar`文件移动到`/usr/local/bin/`目录下面：

```shell
sudo mv composer.phar /usr/local/bin/composer
```

**Windows 系统**：

1. 找到并进入 PHP 的安装目录（和你在命令行中执行的 php 指令应该是同一套 PHP）。
2. 将`composer.phar`复制到 PHP 的安装目录下面，也就是和`php.exe`在同一级目录。
3. 在 PHP 安装目录下新建一个 composer.bat 文件，并将下列代码保存到此文件中：`@php "%~dp0composer.phar" %*`

最后重新打开一个命令行窗口试一试执行`composer --version`看看是否正确输出版本号。能正常显示，就说明已经安装成功了。

### 4. 升级

可以通过执行下面的任意一条命令来保持 Composer 一直是最新版本：

```shell
composer selfupdate

# 或者
composer self-update
```

> 如果通过自身的命令更新失败，那么可以尝试重新执行安装过程。比如，在 Mac 中，可以从官网上下载最新版本，然后移动下载的文件为`/usr/local/bin/composer`即可。


## 二、使用

作为一个包管理工具，composer 和其他语言中的包管理工具的用法基本相同，只是部分命令区别。

更多命令行操作，请查看 [命令行 | Composer 中文文档](http://docs.phpcomposer.com/03-cli.html)。

### 1. 依赖管理文件

在`composer.json`文件中的`require`字段中，可以添加多个需要的依赖模块。每个模块依赖需要一个确定的*包名*(如'monolog/monolog')和一个*包版本*(不需要确定的版本号，可以使用通配版本，如'1.2.*'表示使用版本'1.2'中的最新子版本)。

**包名**

包名称由供应商名称和其项目名称构成。通常容易产生相同的项目名称，而供应商名称的存在则很好的解决了命名冲突的问题。它允许两个不同的人创建同样名为`json`的库，而之后它们将被命名为`igorw/json`和`seldaek/json`。

**包版本**

版本约束可以用几个不同的方法来指定：

    名称    |   	实例       |      描述               
-----------|---------------|--------------------------
确切的版本号 | `1.0.2`	    | 指定包的确切版本。
范围        | `>=1.0`<br>`>=1.0,<2.0`<br>`>=1.0,<1.1|>=1.2` | 使用比较操作符指定有效的版本范围。
有效的运算符 | `>` `>=` `<` `<=` `!=`  | 定义多个范围，用逗号隔开，被视为一个逻辑 AND 处理。<br>一个管道符号`|`将作为逻辑 OR 处理。<br>AND 的优先级高于 OR。
通配符      | `1.0.*`       | 通配符`*`指定一个范围。`1.0.*`与`>=1.0,<1.1`是等效的。
赋值运算符   | `~1.2`	       | `~1.2` 相当于`>=1.2,<2.0`。<br>`~`作用在主版本号上只能增加小版本，不能增加主版本。<br>如`~1`会被当作`~1.0`对待。
折音号(^)   | `^1.2.3`      | 允许升级版本到安全的版本。<br>如，`^1.2.3`相当于`>=1.2.3 <2.0.0`，因为在 2.0 版本前的版本应该都没有兼容性的问题。<br>对 1.0 之前的版本，这种约束方式也考虑到了安全问题，<br>如`^0.3`会被当作`>=0.3.0 <0.4.0`对待。

*注意*： 虽然`2.0-beta.1`严格地说是早于`2.0`，但是，根据版本约束条件，例如`~1.2`却不会安装这个版本。就像前面所讲的`~1.2`只意味着`.2`部分可以改变，但是`1.`部分是固定的。

> 参考：[Composer进阶使用 —— 常用命令和版本约束](https://segmentfault.com/a/1190000005898222)

### 2. 模块安装目录

Composer 会将安装的依赖下载到项目根目录目录中的`vendor`目录中的对应位置。

比如，`monolog/monolog`将会被安装在`vendor/monolog/monolog`目录中。

> 如果使用 Git 来管理项目，可能要添加`vendor`到`.gitignore`文件中。因为这些模块并不需要全放进仓库中。

### 3. 安装项目依赖

如何在项目目录下有一个`composer.json`文件，并指明了依赖，比如，依赖`monolog`：

```json
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
```

那么安装依赖非常简单，只需在项目目录下运行下面的命令即可：

```shell
# 全局安装 composer 了
composer install

# 如果没有全局安装
php composer.phar install
```

### 4. 安装模块并添加依赖

如果项目中的`composer.json`文件中没有包含需要的模块，那么可以使用下面的命令安装模块，并添加到`composer.json`文件中的依赖中：

```shell
composer require "<module-name>"
```

如，可以通过`composer require "monolog/monolog"`命令安装并将`monolog`模块添加到项目依赖中。

### 5. 自动加载

对于库的自动加载信息，Composer 生成了一个`vendor/autoload.php`文件。所以只需在代码的初始化部分中加入下面一行即可实现自动加载：

```php
require 'vendor/autoload.php';  
```

### 6. composer.lock - 锁文件

在安装依赖后，Composer 将把安装时确切的版本号列表写入`composer.lock`文件。这将锁定改项目的特定版本。

**请提交应用程序的`composer.lock`（包括`composer.json`）到你的版本库中**

这是非常重要的，因为`install`命令将会检查锁文件是否存在，如果存在，它将下载指定的版本（忽略`composer.json`文件中的定义）。

这意味着，任何人建立项目都将下载与指定版本完全相同的依赖。你的持续集成服务器、生产环境、团队中的其他开发人员、每件事、每个人都使用相同的依赖，从而减轻潜在的错误对部署的影响。即使独自开发项目，在六个月内重新安装项目时，也可以放心的继续工作，即使从那时起依赖已经发布了许多新的版本。

这也意味着如果依赖更新了新的版本，将不会获得任何更新。此时要更新依赖版本请使用`composer update`命令。这将获取最新匹配的版本（根据`composer.json`文件）并将新版本更新进锁文件。

如果只想安装或更新一个依赖，可以白名单它们：`composer update monolog/monolog [...]`

### 7. 生产环境优化

在部署代码到生产环境的时候，别忘了优化一下自动加载：

```shell
composer dump-autoload --optimize  
```

安装包的时候可以同样使用`--optimize-autoloader`。不加这一选项，可能会发现 [20%到25%的性能损失](http://www.ricardclau.com/2013/03/apc-vs-zend-optimizer-benchmarks-with-symfony2/)。


## 三、命令

### 1. update 更新包

```shell
# 更新所有依赖
composer update

# 更新指定的包
composer update monolog/monolog

# 更新指定的多个包
composer update monolog/monolog symfony/dependency-injection

# 还可以通过通配符匹配包
composer update monolog/monolog symfony/*
```

需要注意的时，包能升级的版本会受到版本约束的约束，包不会升级到超出约束的版本的范围。例如如果`composer.json`里包的版本约束为`^1.10`，而最新版本为`2.0`。那么`update`命令是不能把包升级到`2.0`版本的，只能最高升级到`1.x`版本。

### 2. remove 移除包及其依赖

该命令会移除一个包及其依赖（在依赖没有被其他包使用的情况下）：

```shell
composer remove monolog/monolog
```

### 3. search 搜索包

```shell
composer search monolog
# 输出：monolog/monolog Sends your logs to files, sockets, inboxes, databases and various web services

# 如果只是想匹配名称可以使用--only-name选项
composer search --only-name monolog
```

### 4. show 列出包

使用`show`命令可以列出项目目前所安装的包的信息：

```shell
# 列出所有已经安装的包
composer show

# 可以通过通配符进行筛选
composer show monolog/*

# 显示具体某个包的信息
composer show monolog/monolog
```

### 4. outdated 查看需要升级的依赖

```shell
# 查看需要升级的依赖，会列出全部的依赖(包括依赖中的依赖)
composer outdated

# 查看 composer.json 指定的需要升级的依赖 （--direct）
composer outdated -D

# 只看有次版本升级的依赖
composer outdated -m
```

## 四、其他

### 1. 模块仓库

[packagist.org](https://packagist.org/) 是Composer的仓库，很多著名的 PHP 库都能在其中找到。每个人都可以提交自己的作品。

### 2. 镜像配置

由于官方的镜像被墙了，所以需要使用科学上网，或者使用国内的镜像 [Laravel China 镜像](https://packagist.laravel-china.org)。

> 详情查看：[Composer 中文镜像](https://laravel-china.org/composer)

**全局修改**

可以通过如下的配置来改用国内的镜像：

```shell
composer config -g repo.packagist composer https://packagist.laravel-china.org
```

**当前项目修改**

也可以修改当前项目的`composer.json`配置文件，来在当前项目中启用国内的镜像：

```shell
composer config repo.packagist composer https://packagist.laravel-china.org
```

这个命令将会在当前项目中的`composer.json`文件的末尾自动添加镜像的配置信息（也可以自己手工添加）：

```json
{
    "repositories": {
        "packagist": {
            "type": "composer",
            "url": "https://packagist.phpcomposer.com"
        }
    }
}
```

### 3. Composer install 原理

Composer 安装其他的扩展的原理如下图：

![Composer install 原理](http://cnd.qiniu.lin07ux.cn/markdown/1463823325781.png)

Composer 就是安装在系统上的`composer`工具。所有 package 元数据和 zip 文件的下载、安装工作都是它来完成的。

从图上可以看到，不管是`Packagist.org`还是`Github.com`出现故障或者被墙，都将无法正常安装 package，即便能安装的时候，也是龟速。

### 4. 小技巧

#### 4.1 按名称对 require 和 require-dev 中的包排序

按名称对`require`及`require-dev`中的包排序是非常好的实践。这在衍合一个分支时可以避免不必要的合并冲突。假如把一个包添加到两个分支文件中的列表末尾，那每次合并都可能遇到冲突。在`composer.json`中设置如下配置即可自动排序：

```json
{
    "config": {
        "sort-packages": true
    }
}
```

以后再要`require`一个新的包，它会自动添加到一个正确位置（不会跑到尾部）。

#### 4.2 在 composer.json 中指明生产环境的 PHP 版本号

在`composer.json`中可以使用定义应用程序和库所支持的 PHP 版本：

```json
{
    "require": {
        "ext-mbstring": "*",
        "ext-pdo_mysql": "*",
    }
}
```

如果开发环境和生产环境的 PHP 版本不同，那么可以使用如下的方式来指明生产环境的 PHP 版本，这将会使依赖的升级版本要和指定的平台（生产）版本保持兼容：

```json
{
    "require": {
        "php": "7.1.* || 7.2.*"
    },
    "config": {
        "platform": {
            "php": "7.1"
        }
    }
}
```

#### 4.3 使用自有托管 Gitlab 上的私有包

对于有私有的 vcs 仓库中的包，可以在`composer.json`中进行设置，从而使得 Composer 从指定的私有仓库中下载对应的包。（可能需要相关的身份认证设置。）

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "git@gitlab.mycompany.cz:package-namespace/package-name.git"
        }
    ],
    "require": {
        "package-namespace/package-name": "1.0.0"
    }
}
```

#### 4.4 临时使用 fork 下 bug 修复分支的方法

如果在某个公共的库中找到一个 bug，并且在Github上自己的 fork 中修复了它， 这就需要从自己的版本库里安装这个库，而不是官方版本库（要到修复合并且修复的版本释出才行）。使用内嵌别名可轻松搞定：

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/you/monolog"
        }
    ],
    "require": {
        "symfony/monolog-bundle": "2.0",
        "monolog/monolog": "dev-bugfix as 1.0.x-dev"
    }
}
```

## 转摘

1. [你必须知道的 17 个 Composer 最佳实践（已更新至 22 个）](https://www.tuicool.com/articles/Y7JFfq6)


