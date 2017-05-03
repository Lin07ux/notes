在使用 HAProxy 做负载均衡服务的时候，HAProxy 不处理已有的`X-Forwarded-For`，只是简单地在 header 末尾加入一个新的`X-Forwarded-For`。但是其他后端语言可能不会正确处理多个同名的 header。比如 php-fpm 对 header 的处理方式是同名的 header 只保留最后一个，这样就会导致无法取得正确的用户 IP。

HAProxy 的这种处理方式是正确的，根据 RFC2616 ，多个同名的 header 和单个逗号分隔列表构成的 header 是等价的：

> Multiple message-header fields with the same field-name MAY be present in a message if and only if the entire field-value for that header field is defined as a comma-separated list [i.e., #(values)]. It MUST be possible to combine the multiple header fields into one "field-name: field-value" pair, without changing the semantics of the message, by appending each subsequent field-value to the first, each separated by a comma. The order in which header fields with the same field-name are received is therefore significant to the interpretation of the combined field value, and thus a proxy MUST NOT change the order of these field values when a message is forwarded.

这个问题可以通过让 Nginx 来合并多个`X-Forwarded-For`记录来解决。在 Nginx 配置中加入以下选项（一般在`location ~ \.php$`部分或者在`fastcgi_params`配置文件里）：

```conf
fastcgi_param HTTP_X_FORWARDED_FOR $http_x_forwarded_for if_not_empty;
```

配置好之后 Nginx 服务器就会预先合并多个`X-Forwarded-For`请求 header 记录为逗号分隔格式，然后再传给 php-fpm。