## 介绍

Let's Encrypt 是一个免费的 HTTPS 证书提供机构，可以申请其证书，为自己的网站开启 HTTPS 功能。

相关链接如下：

1. [Let's Encrypt - 官网](https://letsencrypt.org/getting-started/)
2. [Certbot ACME Client](https://certbot.eff.org/)


## 申请

Let's Encrypt 的证书可以通过命令行来进行申请，当然也可以不使用命令行申请。

通过命令行申请时可以使用如 [Certbot ACME Client](https://certbot.eff.org/) 这样的客户端进行，而非命令行情况下，则需要通过站点或域名商网站上进行处理，具体使用会因情况而有所不同。

下面就通过 Certbot 来进行申请。

### Certbot ACME Client

> 下面是在 CentOS7 上进行的操作。其他更多的操作，可以查看 [官方文档](https://certbot.eff.org/docs/)。

#### 申请

1. 首先需要安装`certbot`工具

    ```shell
    sudo yum install certbot-nginx
     ```

2. 进行申请

    下面的两个命令都可以进行申请，不过他们有所不同：
    
    ```shell
    sudo certbot --nginx
    # 或者使用下面的命令
    sudo certbot --nginx certonly
    ```

    前者表示生成证书的时候，还要对 Nginx 的配置文件进行修改，以方便进行 Nginx SSL 的配置工作。而后者则仅生成证书文件，Nginx 则需要手动进行配置。
    
    > 注意：这个命令执行的时候，会自动查看当前 Nginx 的全部配置文件，找到已经设置的域名，然后就可以从中选择你需要申请证书的域名了。所以在执行前请先配置 Nginx 的域名。

#### 更新证书

因为 Let's Encrypt 提供的证书只有三个月的有效期，过期前如果需要继续使用，就需要进行刷新续期。

Certbot 也提供了刷新命令：

```shell
cretbot renew
```

该命令会自动检查已经生成的 Let's Encrypt 证书，并将其中剩余有效期不足 30 天的证书刷新，从而延长其有效期。

为了能够自动更新，可以使用服务器中的`crontab`或`systemd timer`定时器服务，设定自动执行该命令的时间。

在设置定时任务前，可以通过下面的命令来检查是否能够成功执行`certbot renew`命令：

```shell
certbot renew --dry-run
```

如果上面的命令执行成功，就说明一切正常。然后就可以设置定时任务了。

下面是在 crontab 中定时器的设置：

```
0 0,12 * * * python -c 'import random; import time; time.sleep(random.random() * 3600)' && certbot renew
```

表示在每天的 0 点和 12 点时，延迟一定的时间后，执行`certbot renew`命令，检查和更新即将过期的证书。

> 建议定时任务每天执行两次。当然，也可以设置一天多次，这样并不会频繁的刷新证书。

#### 备份

建议将服务器中 Let's Encrypt 的文件路径进行备份：`/etc/letsencrypt/`。

## Nginx 配置

申请完成后，就需要配置 Nginx 了。

在执行`certbot --nginx`命令的时候，会有提示询问是否需要将 Nginx 配置为将 HTTP 请求转发到 HTTPS 请求上。需要转发时，不会修改原本的 HTTP 配置，而是会插入包含如下内容的配置：

```conf
if ($scheme != "https") {
    return 301 https://$host$request_uri;
}
```

这样当用户通过 HTTP 方式访问的时候，就会返回 301 永久重定向到 HTTPS 协议上。


的Certbot 会自动的更新对应域名的配置。

