### 1. 三者关系

在 LAMP 组合中，Nginx 要将对 php 文件的访问转发给 php-fpm 来进行处理，而在这个过程中，涉及到 Nginx、php-fpm 和网站目录之间的访问权限的问题。

* `Nginx --> php-fpm`  Nginx 需要能够将访问请求转发给 php-fpm 的 sock 文件，就需要对该 sock 文件有访问权限，而这个可以通过在 php-fpm 的配置文件 www.conf 中进行配置，就是`listen.user`和`listen.group`，设置其为 Nginx 的运行用户或属组即可。否则会出现`connect() to unix:/var/run/php-fpm/php-cgi.sock failed (13: Permission denied) `的错误。web 页面会显示 500 错误。

    > 如果 php-fpm 是监听在 127.0.0.1 上，Nginx 就不需要通过 sock 文件来做中转，所以也就不需要设置 sock 文件的权限了。

* `php-fpm --> sock`  虽然 sock 文件是需要 php-fpm 来创建，但是这需要 php-fpm 对创建 sock 的目录有读写权限，也就是说，存放 sock 文件的目录的用户或属组需要设置为 php-fpm 的运行用户或属组(也就是`www.conf`文件中的`user`和`group`)，或者这个目录需要对任何用户都有读写权限。

* `php-fpm --> web root`  由于需要 php-fpm 来执行 php 文件，所以网站的根目录需要提供给 php-fpm 的运行用户或属组有读写和执行权限(写是因为需要 php 来设置缓存文件、上传文件等功能，如不需要使用缓存或上传文件则可以不需要写权限)。

    > 对于现在的 MVC 框架来说，一般都是通过入口文件来提供服务的，而其他的 PHP 代码都不需要放在网站根目录，那么 php-fpm 就只需要网站根目录的读写和运行权限，其他的非网站根目录的地方，如 MVC 的库文件、Controller、Model、View 等就只需要读权限。

* `Nginx --> web root`  Nginx 作为一个 web server，自然需要对网站目录有访问权限。不过可以仅仅赋予其读和执行的权限，而不必进行写。另外，目前 php 项目大都使用 MVC 框架，而框架中的文件夹并不需要被用户访问，只需要提供给用户一个公共目录即可。所以我们将网站的根目录(一般是 Public 目录)设置为能让 Nginx 读和执行，而项目中的其他目录和文件则禁止 Nginx 访问。

### 2. Unix socket vs TCP

> 转摘：[PHP-FPM 与 Nginx 的通信机制总结](https://juejin.im/post/5c7795246fb9a04a0b22dd08)

由于 Unix socket 不需要经过网络协议栈，不需要打包拆包、计算校验和、维护序号和应答等，只是将应用层数据从一个进程拷贝到另一个进程，所以其效率比 tcp 的方式要高，可减少不必要的 tcp 开销。不过，Unix socket 高并发时不稳定，连接数爆发时，会产生大量的长时缓存，在没有面向连接协议的支撑下，大数据包可能会直接出错不返回异常。而 tcp 这样的面向连接的协议，可以更好的保证通信的正确定和完整性。

如果是在同一台服务器上运行的 nginx 和 php-fpm，且并发量不高（不超过 1000），选择 Unix socket，以提高 nginx 和 php-fpm 的通信效率。如果是面临高并发业务，则考虑选择使用更可靠的 tcp，以负载均衡、内核优化等运维手段维持效率。

若并发较高但仍想用 Unix socket 时，可通过以下方式提高 Unix socket 的稳定性：

* 将 sock 文件放在`/dev/shm`目录下，此目录下将 sock 文件放在内存里面，内存的读写更快。

* 提高 backlog，默认为 128，最好换算成自己正常的 QPS。

    在`nginx.conf`中配置如下：

    ```conf
    server {
       listen 80 default backlog=1024;
    }
    ```
    
    php-fpm.conf 文件中：
    
    ```
    listen.backlog = 1024
    ```

* 增加 sock 文件和 php-fpm 实例

    在`/dev/shm`新建一个 sock 文件，在 nginx 中通过 upstream 模块将请求负载均衡到两个 sock 文件，并且将两个 sock 文件分别对应到两套 php-fpm 实例上。

