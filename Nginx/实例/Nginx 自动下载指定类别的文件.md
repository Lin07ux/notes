浏览器的功能越来越丰富，很多文件都支持在浏览器中自动打开了，但是有时候访问一个链接的时候，并不希望浏览器自动打开对应的文件，而是希望弹出下载框。此时可以考虑针为这些文件自动添加下载响应头，使浏览器能够自动弹出下载框：

```conf
location / {
   root      /usr/share/nginx/html/edp/web;

   if ($uri ~* \.(txt|doc|pdf|rar|gz|zip|docx|exe|xlsx|ppt|pptx)$) {
       add_header Content-Disposition 'attachment;';
   }

   try_files $uri $uri/  /index.php$uri$is_args$query_string;
}
```

有时候即便已经配置了这个响应头，Chrome 浏览器依旧会打开文件。比如对于 PDF 文件，Chrome 浏览器总是会尝试自动打开，此时可以编辑 Nginx 的 mime 配置，使 PDF 文件的类别对应为二进制流即可：

```
# /etc/nginx/mime.types
application/pdf                       octet-stream;
```


