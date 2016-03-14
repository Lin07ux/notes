
### Nginx 有权限但是无法访问文件
Nginx 配置的 root 目录有对应的权限，但是无法访问，提示 404 错误。
日志中记载的是权限不足，无法访问。

此时一般是由于使用 selinux 服务导致的。关闭该服务即可恢复。

查看 selinux 状态：`getenforce`。

临时关闭：

```shell
setenforce 0 #设置SELinux 成为permissive模式
#setenforce 1 设置SELinux 成为enforcing模式
```

永久关闭(需重启)：

```shell
# 修改 /etc/selinux/config 文件
# 将 SELINUX=enforcing 改为 SELINUX=disabled
SELINUX=disabled
```

### 清理垃圾

```shell
find / -name *~ -exec rm -rf {} \; 
```







