### 基本信息

所有用户定义的 crontab 文件都被保存在`/var/spool/cron`目录中。其文件名与用户名一致。

使用者权限文件有两个：

* `/etc/cron.deny`  该文件中所列用户不允许使用 crontab 命令，相当于黑名单
* `/etc/cron.allow` 该文件中所列用户允许使用 crontab 命令，相当于白名单

常用的 Crontab 命令如下：

```shell
yum install crontab         # 安装 Crontab
/sbin/service crond start   # 启动服务
/sbin/service crond stop    # 关闭服务
/sbin/service crond restart # 重启服务
/sbin/service crond reload  # 重新载入配置
```

### 基本选项
`-u <user>`  define user
`-e`         edit user's crontab  编辑任务
`-l`         list user's crontab  列出任务
`-r`         delete user's crontab 删除任务
`-i`         prompt before deleting 确认后删除任务
`-n <host>`  set host in cluster to run users' crontabs
`-c`         get host in cluster to run users' crontabs
`-s`         selinux context
`-x <mask>`  enable debugging


### 时间格式
每个定时任务在 crontab 文件中，都会表示成一行内容，均包含六个参数(需要运行的任务算作一个参数)，其格式如下：

`minute hours day-of-month month day-of-week program`

* minute 分钟
* hour   小时
* day-of-month 一个月中的第几天
* month  月份
* day-of-week 星期几，0表示星期日，1表示星期一，6表示星期六

其中，前五个表示时间的参数，均有多种书写方式，每种书写方式的含义各不相同：

* `*` 代表所有可能的值，例如 month 字段如果是星号，则表示在满足其它字段的制约条件后每月都执行该命令操作。
* `,` 用逗号隔开的值指定一个列表范围，例如，“1,2,5,7,8,9”。
* `-` 整数之间的中杠表示一个整数范围，例如“2-6”表示“2,3,4,5,6”
* `/` 正斜线指定时间的间隔频率，例如“0-23/2”表示每两小时执行一次。同时正斜线可以和星号一起使用，例如`*/10`，如果用在 minute 字段，表示每十分钟执行一次。


### 示例
1. `* * * * * command` 每分钟执行一次
2. `3,15 * * * * command` 在每小时的第 3 分钟和第 15 分钟，各执行一次。
3. `30 21 * * * command` 每天的 21:30 执行一次。
4. `3,15 8-11 * * * command` 在每天的上午 8 - 11 点之间，每小时的第 3 分钟和第 15 分钟，各执行一次。
5. `3,15 8-11 */2 * * command` 每隔两天，在当天的上午 8 - 11 点之间，每小时的第 3 分钟和第 15 分钟，各执行一次。
6. `3,15 8-11 * * 1 command` 每隔星期一的上午 8 - 11 点之间，每小时的第 3 分钟和第 15 分钟，各执行一次。
7. `45 4 1,10,22 * * command` 每月的第 1 日、第 10 日、第 22 日，在当天的上午 8 - 11 点之间，每小时的第 3 分钟和第 15 分钟，各执行一次。
8. `10 1 * * 6,0 command` 每周六、周日的 1:10 执行一次。
9. `0,30 18-23 * * * command` 每天的 18 - 23 点之间，每小时的 0、30 分的时候各执行一次。也即：每天的 18 - 23 点之间，每隔半小时执行一次。
10. `0 23 * * 6 command` 每周六的 23:00 执行一次。
11. `* */1 * * *` 每隔一小时执行一次，也即是每小时执行一次。
12. `* 23-7/1 * * *` 每天的 23 点到第二天的 7 点之间，每小时执行一次。
13. `0 11 4 * mon-wed` 每月的 4 号与每周一到周三的 11 点执行一次。
14. `0 4 1 jan *` 一月一日的 4 点执行一次。
15. `0 * * * * root run-parts /etc/cron.hourly` 每小时执行`/etc/cron.hourly`目录内的脚本。`run-parts`这个参数如果去掉这个参数的话，后面就可以写要运行的某个脚本名，而不是目录名了。

### 注意事项
1. 手动可执行任务，但无法自动执行，需要注意环境变量

    这是由于，当我们 ssh 进服务器的时候，会自动加载`~/.bashrc`以及`/etc/profile`等环境变量配置文件。但是 crontab 任务运行的时候，不会做这个事情，而是由系统调用起一个最小的环境来做这些操作。即：**crontab不会加载环境变量配置文件**。
    
    * 脚本中涉及文件路径时写全局路径
    * 脚本执行要用到 java 或其他环境变量时，通过 source 命令引入环境变量

    ```shell
    #!/bin/sh
    source /etc/profile
    export RUN_CONF=/home/d139/conf/platform/cbp/cbp_jboss.conf
    /usr/local/jboss-4.0.5/bin/run.sh -c mev &
    ```

2. 新创建的 cron job，不会马上执行，至少要过 2 分钟才执行。如果重启 cron 则马上执行。

3. 当 crontab 突然失效时，可以尝试`/etc/init.d/crond restart`解决问题。或者查看日志看某个 job 有没有执行/报错`tail -f /var/log/cron`。

4. 注意清理系统用户的邮件日志。
    每条任务调度执行完毕，系统都会将任务输出信息通过电子邮件的形式发送给当前系统用户，这样日积月累，日志信息会非常大，可能会影响系统的正常运行。可以通过如下的命令来解决日志问题：
    
    ```
    0 */3 * * * /usr/local/apache2/apachectl restart >/dev/null 2>&1
    ```
    
    `/dev/null 2>&1`表示先将标准输出重定向到`/dev/null`，然后将标准错误重定向到标准输出，由于标准输出已经重定向到了`/dev/null`，因此标准错误也会重定向到`/dev/null`，这样日志输出问题就解决了。
    
5. 转义`%`。在 crontab 中`%`是有特殊含义的，表示换行的意思。如果要用的话必须进行转义`\%`，如经常用的`date '+%Y%m%d'`在 crontab 里是不会执行的，应该换成`date '+\%Y\%m\%d'`。

