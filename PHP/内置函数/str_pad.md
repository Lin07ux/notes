### 1. 基本

**语法**

```php
str_pad ( string $input , int $pad_length [, string $pad_string = " " [, int $pad_type = STR_PAD_RIGHT ]] ) : string
```

**参数**

* `$input` 需要被填充的字符串。
* `$pad_length` 填充后字符串的长度。如果其值是负数、小于或者等于输入字符串的长度，不会发生任何填充，并会返回`$input`。
* `$pad_string` 填充字符串，使用该参数列出的字符串对`$input`进行填充。如果填充字符的长度不能被`$pad_string`整除，那么最终结果中`pad_string`可能会被缩短。
* `$pad_type` 填充类别，可选的`$pad_type`参数的可能值为`STR_PAD_RIGHT`、`STR_PAD_LEFT`或`STR_PAD_BOTH`，分别表示在`$input`右侧填充、左侧填充、两侧填充。如果没有指定`$pad_type`，则假定它是`STR_PAD_RIGHT`。

**效果**

使用另一个字符串填充字符串为指定长度，并返回被从左端、右端或者同时两端被填充到指定长度后的结果。

**示例**

```php
$input = "Alien";
echo str_pad($input, 10);                      // 输出 "Alien     "
echo str_pad($input, 10, "-=", STR_PAD_LEFT);  // 输出 "-=-=-Alien"
echo str_pad($input, 10, "_", STR_PAD_BOTH);   // 输出 "__Alien___"
echo str_pad($input,  6, "___");               // 输出 "Alien_"
echo str_pad($input,  3, "*");                 // 输出 "Alien"
```

### 2. 注意点

#### 2.1 填充后的长度

当填充后的长度`$pad_length`小于原字符串长度时，会直接返回原字符串，对应的 PHP 源码中，有如下代码：

```c
if (pad_length < 0 || (size_t)pad_length <= ZSTR_LEN(input)) {
    RETURN_STRING(ZSTR_VAL(input), ZSTR_LEN(input));
}
```

根据如下的源码，可以看到，`$pad_length`的最大取值应该是一个不大于`INT_MAX + ZSTR_LEN(input)`的值：

```c
num_pad_chars = pad_length - ZSTR_LEN(input);

if (num_pad_chars >= INT_MAX) {
    php_error_docref(NULL, E_WARNING, "Padding length is too long");
    return;
}
```

#### 2.2 填充字符串

可以不传入填充字符串`$pad_string`，此时会使用空格进行填充。但是如果传入的值是空字符串(`""`)，则会抛出警告。对应的 PHP 源码如下：

```c
if (pad_str_len == 0) {
    php_error_docref(NULL, E_WARNING, "Pading string cannot be empty");
    return;
}
```

#### 2.3 填充方式

填充方式有三种，使用左侧、右侧填充时，仅仅会将填充字符串按照规则填充到对应位置。如果使用双侧填充，而需要填充的长度(`$pad_length - strlen($input)`)是奇数时，左边会比右边少一个填充字符。对应如下的 PHP 源码：

```c
// num_pad_chars = pad_length - ZSTR_LEN(input);
switch (pad_type_val) {
    case STR_PAD_RIGHT:
        left_pad = 0;
        right_pad = num_pad_chars;
        break;
    
    case STR_PAD_LEFT:
        left_pad = num_pad_chars;
        right_pad = 0;
        break;
        
    case STR_PAD_BOTH:
        left_pad = num_pad_chars / 2;
        right_pad = num_pad_chars - left_pad;
        break;
}
```

### 3. 参考

* [str_pad - PHP Manual](http://www.php.net/manual/zh/function.str-pad.php)
* [PHP 字符串填充 str_pad 函数有什么文档上没写需要注意的呢](https://mp.weixin.qq.com/s/QSmiTiQ39zY9flmm709MdQ)

