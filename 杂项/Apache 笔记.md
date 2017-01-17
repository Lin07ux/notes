## 基本操作
Mac OS 上默认安装了 Apache，可以使用如下的命令对其进行操作：

- 重启 Apache：`sudo /usr/sbin/apachectl restart`

- 关闭 Apache：`sudo /usr/sbin/apachectl stop`

- 开启 Apache：`sudo /usr/sbin/apachectl start`


## 配置
主配置文件的路径为：`/etc/apache2/httpd.conf `。

### rewrite 重写
Apache 中可以通过设置一定的规则，将访问重写到其他的 URL 上。

#### 开启重写功能
使用路径重写前，需要确保 Apache 加载了`mod_rewrite`模块

* Apache 1.x 的用户请检查`conf/httpd.conf`中是否存在如下两段代码：
    
    ```
    LoadModule rewrite_module libexec/mod_Rewrite.so
    AddModule mod_Rewrite.c
    ```
  
* Apache 2.x 的用户请检查`conf/httpd.conf`中是否存在如下一段代码：
 
    ```
    LoadModule rewrite_module modules/mod_Rewrite.so
    ```

> 注：如果前面有`#`，将其去掉。并且保证你的 Apache 文件里有`mod_Rewrite.so`文件（1.X版的要有`mod_Rewrite.c`）。

#### 配置重写规则
重写规则可以直接写在主配置文件`conf/httpd.conf`中对应的`Directory`或`VirtualHost`指令块中，也可以单独放在项目相应目录中的`.htaccess`文件中。

一般为如下的格式：

```conf
<IfModule rewrite_module>
  RewriteEngine On
  RewriteRule ^(.*)$  test.php?$1
</IfModule>
```

这里需要解释下：

- `rewrite_module` 就是上面开启重写规则时设置的模块名称。
- `RewriteEngine`  表示开启重写引擎。
- `RewriteRule`    表示重写规则。

其中，最重要的就是`RewriteRule`，其可以根据访问的 URI 来设计不同的重写路径。其第一个参数表示对请求的 URI 的过滤，第二个参数表示重写后的 URI。具体可以查看 [RewriteRule Directive - Apache](http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewriterule)。

`RewriteRule`还有第三个可选参数，表示对重写后的规则进行修饰。详细介绍可以查看 [RewriteRule Flags - Apache](http://httpd.apache.org/docs/current/rewrite/flags.html)。其中，常用的几个命令如下：

- QSA(Query String Append) 表示追加原 URI 中查询参数到重写后的 URI 中。

    > 比如，对于如下的重写规则：
    > ```RewriteRule "/pages/(.+)" "/page.php?page=$1" [QSA]```
    > 当我们访问`/pages/123?one=two`时候，Apache 会将其重定向到`/page.php?page=123&one=two`。

- PT(Pass Through to next handler) 表示将重写后的 URI 重新放到路由匹配中进行下一步的处理。
- L(Last)  表示该重写规则匹配后，重写后的 URI 不会继续被下一个重写规则进行处理了。


另外一个与路径重写很相关的指令是`RewriteCond`。该指令会定义一些条件，只有符合这些条件的请求才会被其后面的重写规则重写。

下面给出一个综合的示例：

```conf
<IfModule rewrite_module>   AcceptPathInfo On   RewriteEngine on   RewriteCond %{REQUEST_FILENAME} !-d   RewriteCond %{REQUEST_FILENAME} !-f   RewriteRule ^/admin/(.*)$ admin.php?/$1 [QSA,PT,L]    RewriteCond %{REQUEST_FILENAME} !-d   RewriteCond %{REQUEST_FILENAME} !-f   RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L] </IfModule>
```

这个示例中，前面的一个重写指令会将 URI 以`/admin/`开头的请求重写到`admin.php`入口上；后面的一个重写指令会将不满足前面的重写规则的 URI 全部重写到 index.php 入口上。

另外，示例中的`RewriteCond`分别表示请求的资源不是一个已存在的文件或者目录，才可以去匹配下面的重写规则。

#### .htaccess 中配置的规则不起作用
1. 检查是否开启了重写模块。也就是确认`httpd.conf`配置文件中是否有`LoadModule rewrite_module modules/mod_rewrite.so`，并且其前面的`#`已经去除了。

2. 检查是否打开了允许文件重载。在对应的指令块中找到`AllowOverride`，设置其值为`On`。需要注意的是，这个指令在配置文件中会有多处出现，需要修改正确位置的该指令才行。

3. 在主配置文件中搜索`AccessFileName`，看看其设置的名称是否为`.htaccess`。如果没有该指令，可以考虑添加该指令，并设置值为`.htaccess`。(也可以设置为其他的值，但是项目中的配置文件也要改成对应的名称。)

4. 检查完前三步，重启 Apache，看看是否有作用。如果仍然不起作用，请检查你的重写语法。或者查看 Apache 的 Error Log。

