### 需求

拆分 Nginx 生成的 log 文件，最好是按天生成。

### 解决

使用`map`定义一个时间结构，并且在`access_log`的配置名中加上这个结构，类似下面这样：

```conf
# nginx.conf
map $time_iso8601 $logdate {
    '~^(?\d{4}-\d{2}-\d{2})' $ymd; default 'nodate'; 
}

accesslog '/var/log/nginx/access${logdate}.log'
```



