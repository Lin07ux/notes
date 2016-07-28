## 检查日期是否是指定的格式
```php
function checkDatetime($str, $format="Y-m-d H:i:s"){
    $unixTime  = strtotime($str);
    $checkDate = date($format, $unixTime);

    return $checkDate == $str;
}
```

