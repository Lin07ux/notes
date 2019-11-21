> 转摘：[String - 30 seconds of php](https://php.30secondsofcode.org/tag/string)

### 1. countVowels

计算字符串中元音字母的个数。

元音字母有：`a`、`e`、`i`、`o`、`u`。

```php
function countVowels (string $string)
{
    preg_match_all('/[aeiou]/i', $string, $matches);
    
    return count($matches);
}
```

例如：

```php
countVowels('sampleInput'); // 4
```

### 2. endsWith

检查字符串的尾部是否是由子字符串组成。

```php
function endsWith (string $haystack, string $needle)
{
    return strpos($haystack, $needle) === (strlen($haystack) - strlen($needle));
}
```

### 3. startsWith

检查字符串的首部是否是由子字符串组成。

```php
function startsWith (string $haystack, string $needle)
{
    return strpos($haystack, $needle) === 0;
}
```

### 4. isAnagram

比较两个字符串是否互为同字母异序词(anagram)。

> 使用`count_chars()`方法的模式 1 来获取两个字符串的字母构成和各个字母出现的次数，并比较这个结果是否完全相同。

```php
function isAnagram (string $string1, string $string2)
{
    return count_chars($string1, 1) === count_chars($string2, 1);
}
```

例如：

```php
isAnagram('cat', 'act');  // true
isAnagram('cat', 'acta'); // false
```

### 5. firstStringBetween

获取字符串在指定的起始和结束字符之间的第一个子字符。

使用`strstr()`函数可以搜索字符串在另一字符串中是否存在，如果是，返回该字符串及剩余部分(其第三个参数如果是`true`那么将会返回该字符串及其之前的部分)。

实现中先使用`strstr()`获取起始字符在原字符串中第一次出现之后的部分(包含起始字符)，然后再用`strstr()`获取结束字符在剩余子字符串中第一次出现之前的部分(包含结束字符)，最后再将起始和结束字符去除，即可得到起始和结束字符包裹的第一个子字符串。

```php
function firstStringBetween (string $haystack, string $start, string $end)
{
    return trim(strstr(strstr($haystack, $start), $end, true), $start.$end);
}
```

例如：

```php
firstStringBetween('This is a [custom] string', '[', ']'); // custom
firstStringBetween('This is a <[custom]> string', '<[', ']>'); // custom
```


