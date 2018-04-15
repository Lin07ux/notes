PHP 替换换行符有多种方法：

1. 使用`str_replace`来替换换行 

    `$str = str_replace(array("\r\n", "\r", "\n"), "", $str);`
  
2. 使用正则替换 

    `$str = preg_replace('//s*/', '', $str);`
  
3. 使用 php 定义好的变量（建议使用） 

    `$str = str_replace(PHP_EOL, '', $str);`

4. 还可以转换成前台可显示的字符串：使用[`nl2br`](http://www.php.net/nl2br)函数转为前台可显示的换行。

    ```php
    $str = "a
    b
    e
    f
    c";
    
    echo nl2br($str);
    
    /* 显示如下
    a<br />
    b<br />
    e<br />
    f<br />
    c
    */
    ```



