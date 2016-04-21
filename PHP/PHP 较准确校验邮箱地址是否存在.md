## 问题
一般情况下，我们验证邮箱都是验证其格式是否符合正常的邮箱的格式。如果符合格式，就说明不是一个正确的邮箱地址；而如果符合格式，则就认为是正确的邮箱。但是其实符合格式的邮箱地址也有可能不是真实存在的邮箱。

如果需要准确的判断邮箱地址是否存在，应该怎么办呢？

## 分析
分析一下要求，会发现其实很难能够判断一个邮箱地址是否存在，除非连接到 SMTP 服务器上去验证。但是可以通过判断邮箱的域名是否存在而能够比一般的格式验证准确一些。

## 方法
因为判断域名是否存在需要走一次网络请求，所以可以先结合格式判断，符合格式要求的再继续进行域名验证。

1. 首先，使用 PHP 内置的一个变量过滤函数`filter_var()`来进行邮箱的格式判断。如果通过判断则进行下一步；如果不通过直接返回失败；
2. 再使用`checkdnsrr()`对邮箱的域名进行判断，如果判断成功，则返回正确；否则返回失败。

```php
function chkEmail($email) {
    if (! filter_var($email,  FILTER_VALIDATE_EMAIL) )
        return false;
    
    if (! checkdnsrr(array_pop(explode("@",$email)),"MX") )
        return false;
        
    return true;
}
```

> 更多`filter_var()`函数的参数，可以参考 [PHP 官方文档](http://php.net/manual/zh/filter.filters.validate.php)

