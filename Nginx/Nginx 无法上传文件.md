
### 问题
Nginx 搭建的 PHP 服务器，无法上传文件，准确的说，是无法上传常规大小的文件。
对于很小的文件则可以正常上传。

### 排查
php.ini 中已经开启了文件上传功能，大小限制在 2M。已经开启 PHP 的日志记录，但是并没有相应的错误日志出现。

php-fpm 的错误日志中也没有找到对应的错误日志。

Nginx 的错误日志(/var/log/nginx/error.log)中则出现相应的错误信息：
`[crit] 796#0: *2397 open() "/var/lib/nginx/client_body/0000000003" failed (13: Permission denied)`

很明显，这里指明了是权限问题。

### 解决
这里说明的是不能读取 /var/lib/nginx/client_body/ 中的文件。
查看 /var/lib/nginx/ 文件夹的权限：
`ls -l /var/lib/ | grep nginx`。
和运行 nginx 服务的用户-组并不相同，所以导致出现问题。

修改 /var/lib/nginx/ 文件夹的权限，使其和运行 nginx 服务的用户-组一致：
`chown -R www:www /var/lib/nginx/`。

### 备注
参考：[nginx cannot upload regular sized files permission denied](https://blog.lysender.com/2015/05/nginx-cannot-upload-regular-sized-files-permission-denied/)

前面提到过，在不能上传常规大小文件的时候，小文件还是能够正常上传的。
这可能是因为，Nginx 在文件很小(至少小于 200K，具体界限不清楚)的情况下，Nginx 并不将文件写入到临时目录中，
所以不会遇到权限问题。

而对于大文件，则必须要写入到临时目录中才可以，则必然要权限正常。

