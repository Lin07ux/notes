如果需要将可能含有中文的字符串进行拆分成数组，可以使用 mb 系列的扩展方法很容易的解决：

```PHP
$str = "周梦康";

$array = [];
for ($i=0, $l = mb_strlen($str, "utf-8"); $i < $l; $i++) { 
    array_push($array, mb_substr($str, $i, 1));
}

var_export($array, true);
```

如果没有安装`mb`扩展时，可以使用如下的方式来处理：

```PHP
function str_split_utf8($str)  
{  
    $split = 1;  
    $array = array();
    
    for ($i = 0; $i < strlen($str);) {
        $value = ord($str[$i]);
        
        if ($value > 127) {
            if ($value >= 192 && $value <= 223) {  
                $split = 2;  
            } elseif ($value >= 224 && $value <= 239) {  
                $split = 3;  
            } elseif ($value >= 240 && $value <= 247) {  
                $split = 4;  
            }  
        } else {  
            $split = 1;  
        }
        
        $key = null;  
        for ($j = 0; $j < $split; $j++, $i++) {  
            $key .= $str[$i];  
        }
        
        array_push($array, $key);
    }
    
    return $array;  
}
```



