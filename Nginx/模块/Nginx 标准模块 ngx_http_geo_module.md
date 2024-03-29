[ngx_http_geo_module](https://nginx.org/en/docs/http/ngx_http_geo_module.html) 模块是 Nginx 自带的一个标准模块，只包含一个`geo`指令。该模块的作用是根据客户端 IP 来定义一个变量。

### 1. 语法

ngx_http_geo_module 模块只包含一个指令，`geo`，语法声明如下：

```
Syntax: geo [$address] $variable { ... }
Default: -
Context: http
```

可以看出，`geo`指令只能用在 http 模块中。

### 2. 参数

`geo`指令有一个可选参数`$address`。默认情况下，`$address`的值为`$remote_addr`，但`geo`命令也支持从指定的变量中获取 IP。

如果指定的变量的值不是一个有效的 IP 地址，那么`$address`的值就会被设置为`255.255.255.255`。

比如：

```conf
geo $arg_remote_addr $geo {
    ...
}
```

### 3. 声明体

`geo`指令的声明体中可以针对不同的 IP 值来决定变量的实际值。声明体中支持 IP 地址前缀（包含完整的 IP 地址）和 IP 范围段的格式。

> 从 1.2.7 和 1.3.10 开始支持 IPv6。

声明体中还支持以下的一些特殊参数：

* `delete` 删除指定的网络。

* `default` 如果客户端地址不能匹配声明体中的任何一个规则，那么变量的值就会设定为该参数指定的值。如果没有设置`default`参数，那么默认值就为空字符串。

    > 当 IP 地址指定为 CIDR 格式，那么`0.0.0.0/0`和`::/0`地址可以用来代替`default`参数。

* `include` 引入一个包含有 IP 地址和值的文件，而且能够引入多个文件。

* `proxy` 定义可信的 IP 地址。如果请求来源于可信地址，那么 Nginx 就会使用`X-Forwarded-For`请求头的最后地址来作为`$address`的值进行匹配。相较于普通地址，可信 IP 地址是按顺序检测的。

* `proxy_recursive` 开启递归查找可信地址。

    - 如果关闭递归查找，在客户端地址与某个可信地址匹配时，Nginx 将直接使用`X-Forwarded-For`请求头中的最后一个地址来代替原始客户端地址；
    - 如果开启递归查找，在客户端地址与某个可信地址匹配时，Nginx 将使用`X-Forwarded-For`中最后一个与所有可信地址都不匹配的地址来代替原始客户端地址。

* `ranges` 表示将使用地址区间段的方式进行定义。这个参数需要在`geo`命令声明体的最前面。同时，为了提升匹配的速度，IP 地址应该按照升序方式定义。

由于变量是在使用的时候才被赋值的，所以即便`geo`中声明了很多值，也不会引起额外的花销。

### 4. 示例

下面是使用 CIDR 标识匹配的方式定义的`geo`示例：

```conf
geo $country {
    default        ZZ;
    include        conf/geo.conf;
    delete         127.0.0.0/16;
    proxy          192.168.100.0/24;
    proxy          2001:0db8::/32;

    127.0.0.0/24   US;
    127.0.0.1/32   RU;
    10.1.0.0/16    RU;
    192.168.1.0/24 UK;
}
```

其中，`conf/geo.conf`文件的内容类似如下：

```conf
10.2.0.0/16    RU;
192.168.2.0/24 RU;
```

下面则是通过`ranges`参数指定区间匹配的`geo`示例：

```conf
geo $country {
    ranges;
    default                   ZZ;

    127.0.0.0-127.0.0.0       US;
    127.0.0.1-127.0.0.1       RU;
    127.0.0.1-127.0.0.255     US;
    10.1.0.0-10.1.255.255     RU;
    192.168.1.0-192.168.1.255 UK;
}
```

使用范围匹配的时候，如果同一个地址被包含在两个区间中，那么 Nginx 会使用更具体（也就是范围更小）的匹配规则来获取值。

比如，对于上面的示例中，`127.0.0.1`被指定了两个匹配规则，但是匹配后`$country`变量的值是`RU`而不是`US`，因为前者对应的区间更小更具体。

### 5. 其他使用

`geo`指令虽然是通过 IP 地址来决定变量的值，但是由于其支持`default`命令，所以也可以作为一种自定义变量声明的方式：

```conf
geo $variable {
    default "anonymous";
}
```

当然，一般情况下，还是应该直接使用`set`指令来定义和赋值自定义变量。不过，由于**`geo`模块不支持变量插入**，也就是说，`geo`指令的声明体中是没有变量这一说的，其中的变量值都是字面含义，不会进行变量替换。这一点对于想要得到包含`$`符号的文案的变量时是非常有用的。


