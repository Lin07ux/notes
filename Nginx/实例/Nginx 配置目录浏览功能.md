使用 Nginx 自带的`ngx_http_autoindex_module`模块可以将服务器上的某些目录共享出来，让其他人可以直接通过浏览器去访问、浏览或者下载这些目录里的一些文件。而且还可以配置简单的密码保护，并对索引页面进行美化。


### 配置目录浏览
要开启 Nginx 的目录浏览功能很简单，只需要打开`nginx.conf`或者对应的虚拟主机配置文件，在`server`或`location`段里面中上`autoindex on;`就可以了。

除了`autoindex`外，该模块还有两个可用的字段：

```conf
autoindex_exact_size on;
# 默认为 on，以 bytes 为单位显示文件大小；
# 切换为 off 后，以可读的方式显示文件大小，单位为 KB、MB 或者 GB。

autoindex_localtime on;
# 默认为 off，以 GMT 时间作为显示的文件时间；
# 切换为 on 后，以服务器的文件时间作为显示的文件时间。
```

除此之外，如果二级目录使用的是虚拟目录，则需要使用`alias`字段进行配置。为了避免中文乱码，还可以设置`charset`字段。

> 关于`ngx_http_autoindex`模块的更详细信息可以参考 [官方文档](http://nginx.org/en/docs/http/ngx_http_autoindex_module.html)。

下面是一个完整的配置文件：

```conf
location /download {
    alias /home/user/static_files/;
    
    charset utf-8,gbk; # 两个字符集间不要加空格
    
    autoindex on;
    autoindex_exact_size off;
    autoindex_localtime on;
}
```

### 添加目录密码保护
如果该目录是隐私目录，就需要为其增加密码保护。方法如下：

```conf
location /download {
    # ... 其它同上
    
    auth_basic "Enter your name and password";
    auth_basic_user_file /var/www/html/.htpasswd;
}
```

其中：

* `auth_basic`字段是用户名、密码弹框上显示的文字（貌似在 Chrome 和 Safari 上面都没有用到）
* `auth_basic_user_file`指定了记录登录用户名与密码的文件`.htpasswd`，这个文件需要使用`htpasswd`命令或者 [在线工具](http://tool.oschina.net/htpasswd) 来生成。

> `htpasswd`命令是类 Linux 系统自带的命令，如果是 Windows 系统，建议直接使用在线生成工具比较方便。
> 
> ```shell
> # 创建一个全新的文件，会清除文件里的全部用户
> $ htpasswd -c /var/www/html/.htpasswd user1  
> # 添加一个用户，如果用户已存在，则修改密码
> $ htpasswd -b /var/www/html/.htpasswd user2 password
> # 删除一个用户
> $ htpasswd -D /var/www/html/.htpasswd user2
> ```
> 
> 具体可查看 [官方文档](https://httpd.apache.org/docs/current/programs/htpasswd.html)

### 使用 FancyIndex 进行美化
经过上面的配置，已经能够安全使用目录浏览功能了，不过我们还可以对其进行页面美化。

#### 安装
可以在编译 nginx 时，添加这个插件。如果系统使用的仓库源中有对应的插件可以直接安装，则只需要直接安装插件即可，不需要重新编译 Nginx。

比如，在 ubuntu 系统上，我们可以通过安装`nginx-extras`来安装 FancyIndex 插件：

```shell
$ sudo apt-get install nginx-extras
```

#### 配置
安装完成之后，就要对页面进行美化了。如果偷懒，可以直接使用这个 [主题](https://github.com/TheInsomniac/Nginx-Fancyindex-Theme)。

首先，将这个主题克隆下来。

然后在网站根目录（如`/var/www/html`）下新建一个`fancyindex`目录，然后将主题中的下述文件复制到该目录中：

* header.html
* footer.html
* css/fancyindex.css
* fonts/*
* images/breadcrumb.png

最后重新修改 nginx 配置文件，下面是完整的配置文件：

```conf
location /download {
	alias /home/user/static_files/;
	charset utf-8,gbk;

	auth_basic "Enter your name and password";
	auth_basic_user_file /var/www/html/.htpasswd;

	fancyindex on;
	fancyindex_exact_size off;
	fancyindex_localtime on;
	fancyindex_header "/fancyindex/header.html";
	fancyindex_footer "/fancyindex/footer.html";
	fancyindex_ignore "fancyindex";
}
```

> 注意：使用 fancyindex 之后需要将 autoindex 相关的字段去掉，否则可能会造成冲突。

[FancyIndex 文档](https://github.com/aperezdc/ngx-fancyindex#directives) 上面说明了有两个字段`fancyindex_default_sort`和`fancyindex_name_length`可以分别用来指定文件排序和文件名的最大长度，但是我试过之后都不起作用，可能是由于`nginx-extras`里面的`FancyIndex`版本比较低的缘故。

下图是配置完后的最终效果：

![](http://cnd.qiniu.lin07ux.cn/markdown/1481253081572.png)

### 转摘
[配置 Nginx 的目录浏览功能](http://www.swiftyper.com/2016/12/08/nginx-autoindex-configuration/)

