确定一个字符串是否在另一个字符串中，在 PHP 中有很多方法实现。`strpos`、`strstr`、`strpbrk`这几个函数都可以实现，但是他们之间也是有区别的。

> 转摘：[PHP strpos, strstr, strpbrk 这几个函数有什么区别](https://mp.weixin.qq.com/s/oqWbLQ5AUzJSpL-bmex9CA)

### 1 strstr

`strstr`返回 haystack 字符串从 needle 第一次出现的位置开始到 haystack 结尾的字符串。语法如下：

```
strstr ( string $haystack , mixed $needle [, bool $before_needle = FALSE ] ) : string
```

如果 needle 不是一个字符串，那么它将被转化为整型并且作为字符的序号来使用。

如果`$before_needle = true`，则返回第一次出现的位置前面的字符。如果字符不存在，则返回 false。

### 2 strpos

`strpos`查找子字符串首次出现的位置。语法如下

```
strpos ( string $haystack , mixed $needle [, int $offset = 0 ] ) : int
```

位置是从`0`开始计数的。如果提供了`$offset`参数，搜索会从字符串该字符数的起始位置开始统计。如果是负数，搜索会从字符串结尾指定字符数开始。

如果没找到 needle，将返回 FALSE。

`strpos`返回的是一个数，由于字符串返回的时候涉及到字符串复制的过程，因此会有速度和内存上的损耗。在性能上，`strpos`会比`strstr`好一点点。

### 3 strpbrk

`strpbrk`在字符串中查找一组字符的任何一个字符。返回一个以找到的字符开始的子字符串。如果没有找到，则返回 FALSE。语法如下：

```
strpbrk ( string $haystack , string $char_list ) : string
```

`strpbrk`直接使用两个循环，实现字符的查找。在性能上，应该是这三个函数垫底的了。

### 4 示例

以字符串`ABCGCAC`为例：

```php
$string = 'ABCGCAC';

strstr('ABCGCAC', 'CA');  // CAC
strpos('ABCGCAC', 'CA');  // 4
strpbrk('ABCGCAC', 'CA'); // ABCGCAC
```

