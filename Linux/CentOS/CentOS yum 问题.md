## 问题

### 提示源重复出现

使用`yum`相关命令的时候，会提示类似`Repository updates-debuginfo is listed more than once in the configuration`一类的信息。

这是由于在`yum`的源中，有多个类似的源文件出现了，此时删除对应的文件即可。

通过`ls -l /etc/yum.repos.d/`命令可以看到具体的源文件，根据`yum`命令中的相关提示删除不需要的源文件即可。

> 删除之前做好备份保存工作。

> 参考：[updates is listed more than once in the configuration 的解决](http://blog.csdn.net/pknming/article/details/52574321)

