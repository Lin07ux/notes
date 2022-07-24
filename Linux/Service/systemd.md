## 一、简介

systemd 可以将将一个程序配置成服务，通过调整服务化配置参数，可以使得程序保持持续执行、自动重启等。

> supervisor 也能做使一个程序保持运行和自动重启，不过它并不是 Linux 预装软件，而且是用 Python 写的，需要安装较多的依赖包，相对来说安装会更复杂一些。

## 二、服务化参数

### 2.1 Unit

#### 2.1.1 通用参数

* `Description` 设置描述信息
* `After` 指定依赖关系，表示当前服务要在指定的服务启动之后才能启动

#### 2.1.2 启动频率限制

* `StartLimitBurst` 设置在一段给定的时长内，最多允许启动多少次，默认值等于`DefaultStartLimitBurst`的值（默认为 5）。该限制会作用于任何方式的启动（包括手动启动），而不仅仅是由`Restart`触发的启动。

* `StartLimitIntervalSec` 设置启动时长，也就是为`StartLimitBurst`设置的时长，单位为 s。默认情况下为`DefaultStartLimitIntervalSec`的值（默认为 10s）。

注意：一单某个设置了`Restart`自动重启逻辑的服务触碰到了启动频率限制，那么该单元将再也不会尝试自动重启。不过，如果该服务后来又被手动重启成功的话，那么它的自动重启逻辑将会被再次激活。

`systemctl reset-failed`命令能够重置服务的失败频率计数器。在手动启动某个已经触碰到了启动频率限制的服务之前，可以使用这个命令来清除计数器。

因为启动频率限制位于所有单元条件检查之后，所有基于失败条件的启动不会计入启动频率限制的启动次数之中。

### 2.2 Service

* `Type` 服务类别，一般设置为`simple`。
* `Restart` 重启方式，`always`表示一直自动重启
* `RestartSec` 设置重启的时间间隔
* `User` 服务运行的用户
* `ExecStart` 启动服务的命令

### 2.3 Install

* `WantedBy`

## 三、其他

### 3.1 服务化实例

> 转摘：[使用systemd，把服务装进 Linux 心脏里～](https://mp.weixin.qq.com/s/VdBHKC2X0-drQvJ6dtRoAQ)

比如，使用 Java 写一个小小的 HTTP 服务程序，然后在`/etc/systemd/system/`目录下创建一个服务化配置文件，假设命名为`java-http.service`，设置如下一些配置：

```conf
[Unit]
Description=My First Java Service
After=network.target
StartLimitIntervalSec=0

[Service]
Type=simple
Restart=always
RestartSec=1
User=root
ExecStart=/usr/bin/env java /path/to/java/http/runner

[Install]
WantedBy=multi-user.target
```

这里比较重要的就是配置就是`ExecStart`，它配置了确切要执行的命令。

要启动这个服务的话，直接执行下面的命令就可以了：

```shell
systemctl start java-http
```

如果找不到刚刚创建的服务，可以 reload 一下：

```shell
systemctl daemon-reload
```

如果想随系统开机自启动，可以 enable 这个服务，创建一个链接即可：

```shell
systemctl enable java-http
```


