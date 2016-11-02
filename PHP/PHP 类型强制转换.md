### 浮点数据类型强制转换为整型数据

```php
$float = 2.7;
$int   = (int)$float;

var_dump($int); // int(2)
```

### 整型数据强制转换为浮点数据

```php
$int   = 2;
$float = (float)$int;

var_dump($float);  // float(2)
```

### 实数数据强制转换为字符串数据

```php
$int = 2;
$str = (string)$int;

var_dump($str);  // string(1) "2"

$float = 2.7;
$str = (string)$float;

var_dump($str);  // string(3) "2.7"
```

### 布尔型数据强制转换为字符串数据

```php
$bool1 = true;
$str1  = (string)$bool1;

$bool2 = false;
$str2  = (string)$bool2;

var_dump($str1, $str2);  string(1) "1" string(0) ""
```

### 布尔型数据强制转换为整型数据

```php
$bool1 = true;
$int1  = (int)$bool1;

$bool2 = false;
$int2  = (int)bool2;

var_dump($int1, $int2);  // int(1) int(0)
```

### 整型数据强制转换为布尔型数据
**只有是 0 时，返回 flase，其他都返回 true。**

```php
$int1  = 2;
$bool1 = (bool)$int1;

$int2  = -2;
$bool2 = (bool)$int2;

$int3  = 0;
$bool3 = (bool)$int3;

echo var_dump($bool1, $bool2, $bool3);
// bool(true) bool(true) bool(false)
```

### 字符串型数据强制转换为布尔型数据
**只有是 "0" 和空 "" 时，返回 flase，其他都返回 true。**

```php
$str1  = "1";
$bool1 = (bool)$str1;

$str2  = "0";
$bool2 = (bool)$str2;

$str3  = "00";
$bool3 = (bool)$str3;

$str4  = "HELLO!";
$bool4 =(bool)$str4;

$str5  = "0HELLO!";
$bool5 = (bool)$str5;

$str6  = "";
$bool6 = (bool)$str6;

$str7  = " ";
$bool7 = (bool)$str7;

$str8  = "FLASE";
$bool8 = (bool)$str8;

var_dump($bool1, $bool2, $bool3, $bool4, $bool5, $bool6, $bool7, $bool8);
// bool(true) bool(false) bool(true) bool(true) bool(true) bool(false) bool(true) bool(true)
```



