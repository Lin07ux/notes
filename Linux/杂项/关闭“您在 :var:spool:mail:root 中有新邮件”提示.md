安装完 Linux 后经常使用终端远程登录后经常出现`You have new mail in /var/spool/mail/root`的提示。这是 Linux 的邮年提示功能。Linux 会定时查看 Linux 各种状态做汇总，每经过一段时间会把汇总的信息发送的 root 的邮箱里，以供有需之时查看。

要想关闭 Linux 系统的邮件功能自动提示非常简单，只需要运行以下命令就可以：

```shell
echo "unset MAILCHECK" >> /etc/profile
```




