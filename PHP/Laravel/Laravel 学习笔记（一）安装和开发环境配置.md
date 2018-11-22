## 安装

Laravel 的安装和开发都需要 [Composer](https://getcomposer.org/) 工具，所以在安装之前，需要先确保已经安装好了 Composer，具体的安装过程可以参考网上相关的文档。

安装好 Composer 之后，有两种方式来安装 Laravel：

### 1. 通过 Laravel Installer 安装

这是比较建议的方式。首先需要通过 Composer 全局安装 Laravel Installer，然后就可以使用`laravel`命令来新建项目。

```shell
composer global require "laravel/installer"
laravel new blog
```

这种方式需要注意的是，要确保 Composer 的`$HOME/.composer/vendor/bin`目录在系统的环境路径中，这样我们才能全局的使用`laravel`命令。

默认情况下，这会用最新的稳定版的 Laravel 来创建项目。如果要安装最新版的，则可以加上`--dev`选项，如下所示：

```shell
laravel new blog --dev
```

如果要更新 Laravel Installer，就使用`composer g up laravel/installer`即可。

### 2. 通过 Composer Create-Project 命令安装

另外，我们也可以选择通过 Composer 自带的命令来新建项目：

```shell
composer create-project --prefer-dist laravel/laravel blog
```

## 开发环境

Laravel 官方推荐的开发环境主要有两种：Homestead、Valet。其中，前者是一个通用的开发环境，后者则主要针对 Mac 系统的用户。

### Homestead

Homestead 是一个基于 Vagrant 的开发环境，简单说就是一个虚拟机，但是已经配置好了 Laravel 开发中会用到的多种服务需求，并对 Vagrant 的 API 做了一些封装，使用户能更方便的配置虚拟机。

具体的安装过程可以查看官方网站和其他资料。

### Valet

Valet 是一个针对 Mac 用户的轻量级开发环境，但是需要在本机中安装一系列的服务，也就是需要本机的支持。

使用 Valet 除了能够在本机就开启网站支持之外，还能够将本地的网站分享出去供别人临时查看。

安装过程：

```shell
# Install or update Homebrew to the latest version
brew update
# Install PHP 7.1 
brew install homebrew/php/php71
# Install Valet with Composer
composer global require laravel/valet
# Configure and install Valet and DnsMasq, and register
# Valet's daemon to launch when your system starts
valet install
```

> 安装 Valet 需要使用 Homebrew 和 Composer。

安装好 Valet 之后，在电脑启动的时候该服务就会自动启动。并且，Valet 会代理所有的`*.dev`域名。除了 Valet 之外，还需要安装必要的数据库服务等。

具体的使用可以查看[官方文档](https://laravel.com/docs/5.5/valet)。下面就介绍下基础的使用。

**park**

为了能够开启 Laravel 项目网站，我们需要运行如下命令：

```shell
# 创建存放项目的目录
mkdir ~/sites
cd ~/sites
# 将当前目录路径加入到 Valet 服务的网站搜索路径中
valet park
```

这样操作之后，所有存在与`~/sites`目录中的子目录都会被当做一个网站来对待，网站的域名就是` floder-name.dev`。

比如，我们在该目录下新建一个 Laravel 项目：`laravel new blog`，创建好之后，就可以通过`http://blog.dev`来访问了。

**link**

如果想把指定的目录直接作为一个网站来访问，则可以使用`valet link app-floder`命令实现。该命令是将指定的`app-floder`目录通过创建软连接的方式放入到`~/.valet/Sites`目录中，从而使得 Valet 能够搜索到该目录。

`link`过去之后，就可以使用`http://app-floder.dev`来访问该项目了。

**secure**

如果要使用 HTTPS 协议，可以通过`valet secure`命令来实现。该命令有两种使用方式：

1. 指定项目目录名：`valet secure app-name`，
2. 进入到项目目录后运行：`cd app-nme`、`valet secure`。

这样操作之后就可以通过`https://app-name.dev`来访问该项目了。


## 开发工具

Laravel 乃至 PHP 的开发工具中，IDE 首选的自然就是 PHPStorm 了，其他的非 IDE 则推荐使用 Sublime Text。不过，不论是使用 PHPStorm，还是 Sublime Text，都需要一定的配置才能加快我们的开发效率。下面就以 PHPStorm 的配置为例说明(Sublime Text 的配置可以查看这里[Sublime Text 3 开发 Laravel 必备插件](https://github.com/Lin07ux/notes/blob/master/PHP/%E6%9D%82%E9%A1%B9/Sublime%20Text%203%20%E5%BC%80%E5%8F%91%20Laravel%20%E5%BF%85%E5%A4%87%E6%8F%92%E4%BB%B6.md))。

### Laravel Plugin

这个插件需要从 PHPStorm 中直接进行搜索安装：`Settings -- Plugins -- Browse repositories...`，然后在打开的窗口中，输入`laravel`就可以搜索到需要的插件了。点击右侧的`Install`就可以安装了。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505650602682.png" width="414"/>
</div>

安装完成之后，就需要对当前的项目启用插件了：`Settings -- Laravel Plugin`，选中`Enable plugin for this project`。

<div align="center">
    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1505651007674.png" width="490"/>
</div>

### laravel-ide-helper

仅仅安装 Laravel Plugin 并不能让 PHPStorm 出现相关的提示。还需要安装`laravel-ide-helper`这个依赖。这个是通过 Composer 来安装的，所以你需要确保 Composer 已经安装好了。

```shell
composer require --dev barryvdh/laravel-ide-helper
```

也可以去掉`--dev`选项实现正式性的依赖。

安装好`laravel-ide-helper`之后，有两种方式来启用它：

1. 直接添加到系统的 providers 中。
    需要在`config/app.php`文件中的`providers`中添加下面的代码：

    ```php
    Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
    ```

2. 在 APP 启动服务中设置。
    需要在`app/Providers/AppServiceProvider.php`文件中的`register()`方法中添加下面的代码：
    
    
    ```php
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        // ...
    }
    ```
    
### 其他相关操作

安装好上面的一个插件和一个依赖之后，接下来还需要使用 Artisan 命令生成一些配置文件。生成方式如下：

```shell
# 首先要清除 bootstrap/compiled.php 文件
php artisan clear-compiled
# 然后就可以生成相关的配置文件
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:model
```

我们也可以在 composer.json 文件中添加如下的脚本命令，就可以在每次提交版本库之后自动进行这些脚本的执行：

```json
"scripts":{
    "post-update-cmd": [
        "Illuminate\\Foundation\\ComposerScripts::postUpdate",
        "php artisan ide-helper:generate",
        "php artisan ide-helper:meta",
        "php artisan optimize"
    ]
},
```

参考：[Laravel Development using PhpStorm](http://confluence.jetbrains.com/plugins/servlet/mobile#content/view/57288110)

