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
* day-of-week 星期几，0表示星期日，1表示星期一，6小时星期六

其中，前五个表示时间的参数，均有多种书写方式，每种书写方式的含义各不相同，下面以 minute 参数为例，说明情况：

* `a-b` 在小时内的第 a 分钟到第 b 分钟内执行。
* `a,b[,...]` 在小时内的第 a 分钟，第 b 分钟，各执行一次。这种设置情况下，可以设置多个值，每个值之间用逗号分隔。
* `*/n` 表示每隔 n 分钟就执行一次。

上面的三种书写方式，在其他四个时间参数上均适用，含义类似。

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

* 脚本中涉及文件路径时写全局路径
* 脚本执行要用到 java 或其他环境变量时，通过 source 命令引入环境变量

```shell
cat start_cbp.sh
#!/bin/sh
source /etc/profile
export RUN_CONF=/home/d139/conf/platform/cbp/cbp_jboss.conf
/usr/local/jboss-4.0.5/bin/run.sh -c mev &
```

2. 新创建的 cron job，不会马上执行，至少要过 2 分钟才执行。如果重启 cron 则马上执行。

3. 当 crontab 突然失效时，可以尝试`/etc/init.d/crond restart`解决问题。或者查看日志看某个 job 有没有执行/报错`tail -f /var/log/cron`。

