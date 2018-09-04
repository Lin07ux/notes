## IP 统计相关

* 统计 IP 访问量

```shell
awk '{print $1}' access.log | sort -n | uniq | wc -l
```

* 查看某一时间段的 IP 访问量(4-5点)

```shell
grep "07/Apr/2017:0[4-5]" access.log | awk '{print $1}' | sort | uniq -c| sort -nr | wc -l
```

* 查看访问最频繁的前 100 个 IP

```shell
awk '{print $1}' access.log | sort -n |uniq -c | sort -rn | head -n 100
```

* 查看访问 100 次以上的 IP

```shell
awk '{print $1}' access.log | sort -n |uniq -c |awk '{if($1 >100) print $0}'|sort -rn
```

* 查询某个 IP 的详细访问情况，按访问频率排序

```shell
grep '104.217.108.66' access.log |awk '{print $7}'|sort |uniq -c |sort -rn |head -n 100
```

## 页面统计

* 查看访问最频的页面(TOP100)

```shell
awk '{print $7}' access.log | sort |uniq -c | sort -rn | head -n 100
```

* 查看访问最频的页面([排除 php 页面】(TOP100)

```shell
grep -v ".php"  access.log | awk '{print $7}' | sort |uniq -c | sort -rn | head -n 100
```

* 查看访问次数超过100次的页面

```shell
cat access.log | cut -d ' ' -f 7 | sort |uniq -c | awk '{if ($1 > 100) print $0}' | less
```

* 查看最近 1000 条记录中访问量最高的页面

```shell
tail -1000 access.log |awk '{print $7}'|sort|uniq -c|sort -nr|less
```

## 时间统计

* 每秒请求量统计

统计每秒的请求数，top100 的时间点(精确到秒)

```shell
awk '{print $4}' access.log |cut -c 14-21|sort|uniq -c|sort -nr|head -n 100
```

* 每分钟请求量统计

统计每分钟的请求数，top100 的时间点(精确到分钟)

```shell
awk '{print $4}' access.log |cut -c 14-18|sort|uniq -c|sort -nr|head -n 100
```

* 每小时请求量统计

统计每小时的请求数，top100 的时间点(精确到小时)

```shell
awk '{print $4}' access.log |cut -c 14-15|sort|uniq -c|sort -nr|head -n 100
```

## 其他统计

* 性能分析

在 nginx log 中最后一个字段加入`$request_time`，然后就可以：

列出传输时间超过 3 秒的页面，显示前 20 条。

```shell
cat access.log|awk '($NF > 3){print $7}'|sort -n|uniq -c|sort -nr|head -20
```

列出 php 页面请求时间超过 3 秒的页面，并统计其出现的次数，显示前 100 条。

```shell
cat access.log|awk '($NF > 1 && $7~/\.php/){print $7}'|sort -n|uniq -c|sort -nr|head -100
```

* 蜘蛛抓取统计

统计蜘蛛抓取次数

```shell
grep 'Baiduspider' access.log |wc -l
```

统计蜘蛛抓取 404 的次数

```shell
grep 'Baiduspider' access.log |grep '404' | wc -l
```

* TCP 连接统计

查看当前 TCP 连接数

```shell
netstat -tan | grep "ESTABLISHED" | grep ":80" | wc -l
```

用 tcpdump 嗅探 80 端口的访问看看谁最高

```shell
tcpdump -i eth0 -tnn dst port 80 -c 1000 | awk -F"." '{print $1"."$2"."$3"."$4}' | sort | uniq -c | sort -nr
```