Wget 有一个缓冲区溢出漏洞，需要更新到 1.19 版本以上来修复这个漏洞。更新步骤如下：

### 1. 下载 wget 压缩包

从 [http://mirrors.ustc.edu.cn/gnu/wget/](http://mirrors.ustc.edu.cn/gnu/wget/) 中获取到 Wget 1.19 版本的 tar 包：

```shell
wget http://mirrors.ustc.edu.cn/gnu/wget/wget-1.19.tar.gz
```

### 2. 解压

```shell
tar -zxvf wget-1.19.tar.gz
```

### 3. 编译

进入解压缩之后的文件夹，进行编译：

```shell
cd wget-1.19
./configure --prefix=/usr --sysconfdir=/etc --with-ssl=openssl
```

在这个过程中可能会提示检查失败，缺少某些包，例如 openssl。使用`yum install`命令安装之后**重新编译**。

需要**注意**的是：openssl 安装之后，编译是如果还是提示未安装，则需要安装`openssl-devel`包。

### 4. 安装

编译通过之后，就可以进行安装了：

```shell
make && make install
```

安装完成之后，用`wget -V`就可以看到 Wget 版本已经更新了。

### 5. 转摘

[centos中wget更新到1.18版本以上](https://blog.csdn.net/rczrj/article/details/78931007)

