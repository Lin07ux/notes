### 将对某文件夹的访问进行重写
比如，网站的 Api 放在根目录下，但是需要将对 Api 的访问转向到统一的入口文件上进行处理。

```conf
location ^~ /api/ {
   rewrite ^\/api\/(.*)$ /api.php/$1 last;

   # 如果要有 api 目录，并且其下有可以直接访问的文件，可以如下配置
   # if (!-e $request_filename){
   #   rewrite ^\/api\/(.*)$ /api.php/$1 last;
   # }
}
```

