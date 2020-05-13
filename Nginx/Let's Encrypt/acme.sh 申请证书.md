Let's Encrypt 官方推荐的 Certbot 工具能够相对简单的实现安装和证书申请、配置，一般使用 Certbot 工具即可。但是对于泛域名证书的申请，Certbot 还不是很方便，而 acme.sh 则可以顺利的实现泛域名证书的申请和续签。

## 一、acme.sh

### 1.1 简介

acme.sh 是一个支持 ACME 协议的工具，能够实现 Let's Encrypt 证书的申请和自动更新，而且具有中文文档：

* acme.sh 的官方为：[https://acme.sh](https://acme.sh)。

* acme.sh 的 Github 为：[https://github.com/acmesh-official/acme.sh](https://github.com/acmesh-official/acme.sh)。

### 1.2 安装

acme.sh 的安装相对简单，使用下面的两个命令中的任何一个即可：

```shell
curl https://get.acme.sh | sh
# Or
wget -O - https://get.acme.sh | sh
```

在国内服务器中可能无法正常的下载安装文件，可以考虑先使用 VPN 下载对应的安装 shell 文件，然后拷贝到服务器之后手动安装：

```shell
wget -O - https://raw.githubusercontent.com/acmesh-official/acme.sh/master/acme.sh
chmod +x acme.sh
acme.sh --install
```

安装过程进行了以下几步：

1. 把 acme.sh 安装到 home 目录下：`~/.acme.sh/`。

    而且还创建了一个 bash 的 alias，以方便在整个系统中使用：`alias acme.sh=~/.acme.sh/acme.sh`。

    > 安装后这个 alias 可能并没有立即生效，切换到其他目录下就无法执行了，需要执行`source ~/.bashrc`来重载配置即可。

2. 自动创建一个 cron job，每天 0:00 点自动检测所有的证书，如果快过期了，需要更新，则会自动更新证书。

### 1.3 更新

目前由于 acme 协议和 Letsencrypt CA 都在频繁的更新，因此 acme.sh 也经常更新以保持同步。

```shell
# 升级 acme.sh 到最新版
acme.sh --upgrade

# 开启自动升级
acme.sh  --upgrade  --auto-upgrade

# 关闭自动更新
acme.sh --upgrade  --auto-upgrade  0
```

### 1.4 安装问题

如果安装过程中提示需要 socat，那么就用系统工具安装即可。比如：

```shell
yum install -y socat
```

## 二、证书申请

acme.sh 可以一次对一个或者多个一般域名进行证书的申请，也支持对泛域名进行证书申请。

验证方式上，acme.sh 可以通过 http 和 dns 方式来验证域名的所有权，但是对于泛域名的证书申请，则必须使用 dns 验证。

### 2.1 http 验证申请

http 方式需要在网站根目录下放置一个文件来验证域名所有权。完成验证后就可以生成证书了。这个步骤是 acme.sh 自动完成的，而且在完成验证之后，会恢复到之前的状态，不会更改配置和文件。

```shell
# 手动指定网站根目录
acme.sh  --issue  -d mydomain.com -d www.mydomain.com  --webroot  /home/wwwroot/mydomain.com/

# 从 apache 的配置中自动完成验证
acme.sh --issue  -d mydomain.com   --apache

# 从 nginx 的配置中自动完成验证
acme.sh --issue  -d mydomain.com   --nginx

# 没有运行任何 web 服务且 80 端口是空闲的，那么 acme.sh 能假装自己是一个 webserver，临时听在 80 端口完成验证
acme.sh  --issue -d mydomain.com   --standalone
```

### 2.2 dns 验证申请

dns 方式需要在域名上添加一条 txt 解析记录以验证域名所有权。这种方式不需要任何服务器、不需要任何公网 ip、只需要 dns 的解析记录即可完成验证。坏处是，如果不同时配置 Automatic DNS API，使用这种方式 acme.sh 将无法自动更新证书，每次都需要手动再次重新解析验证域名所有权。

申请步骤如下：

1. 指定 dns 验证方式
    
    ```shell
    acme.sh  --issue  --dns   -d mydomain.com
    ```

2. 配置域名的 txt 记录

     执行前面的命令后，acme.sh 会生成相应的解析记录显示出来，只需要在域名管理面板中添加这条 txt 记录即可。

3. 等待 txt 记录解析完成之后重新生成证书

    ```shell
    acme.sh  --renew  -d mydomain.com
    ```
    
    > 注意第这里用的是`--renew`。

### 2.3 dns 服务器支持

acme.sh 目前支持 cloudflare、dnspod、cloudxns、godaddy 以及 ovh 等数十种解析商的自动集成。每种集成的步骤可以参考：[dnsapi 文档](https://github.com/acmesh-official/acme.sh/wiki/dnsapi)。

下面以阿里云的域名/DNS 集成为例进行说明：

1. 申请阿里云的 access key 和 access secret，并为该账号添加`AliyunDNSFullAccess`权限。

2. 申请好之后，分别将 access key 和 access secret 的值替换下面的两个命令的值：

    ```shell
    export Ali_Key="access key"
    export Ali_Secret="access secret"
    ```
    
    > `Ali_Key`和`Ali_Secret`的值将会被保存在`~/.acme.sh/account.conf`文件中，以供下次使用。

3. 申请证书

    ```shell
    acme.sh --issue --dns dns_ali -d example.com -d www.example.com
    ```

### 2.4 安装证书

前面的`--issue`命令安装证书时，生成的证书都放在安装`~/.acme.sh/`目录下。一般不建议直接使用这个目录下的文件，因为这些都是内部文件，可能会发生变更。

正确的使用方法是使用`--installcert`命令，并指定目标位置，然后证书文件会被自动拷贝到到指定的位置中。例如：

```shell
acme.sh --installcert -d example.com \
--key-file       /path/to/keyfile/in/nginx/key.pem  \
--fullchain-file /path/to/fullchain/nginx/cert.pem \
--reloadcmd     "service nginx force-reload"
```

这的`--reloadcmd`参数是用来在生成证书后自动重启 Nginx 用的。

`--installcert`参数的详细说明参考：[https://github.com/Neilpang/acme.sh#3-install-the-issued-cert-to-apachenginx-etc](https://github.com/Neilpang/acme.sh#3-install-the-issued-cert-to-apachenginx-etc)。

## 三、参考

* [acme.sh 安装说明](https://github.com/acmesh-official/acme.sh/wiki/%E8%AF%B4%E6%98%8E)
* [Let's Encrypt 泛域名证书申请及配置](https://learnku.com/articles/13496/lets-encrypt-pan-domain-name-application-and-configuration)

