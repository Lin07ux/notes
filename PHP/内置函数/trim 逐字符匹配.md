### 1. 处理原理

`trim()`方法可以去除字符串两端的指定字符，类似的方法还有`ltrim()`去除字符串左侧的指定字符、`rtrim()`去除字符串右侧的指定字符。他们的实现原理基本一样，都是**逐字符匹配**。

**逐字符匹配**的意思就是说，从字符串的某一侧的第一个的字符开始，依次查找该字符是否在指定的要去除的字符列表中，如果存在则去除，直到找到第一个不在指定的要去除的字符列表中为止。而`trim()`则需要分别从左侧和右侧进行上述操作。下面用实例来进行说明：

```php
$str = 'abc66646abc666';
echo trim($str, 'abc666');
```

输出结果是`4`而不是`46`，下面进行分析：

1. 从左往右开始匹配取出第一个字符`a`，字符`a`在需要去除的'abc666'里面，所以`a`被去除；
2. 然后取出第二个字符`b`，字符`b`也在需要去除的'abc666'里面，所以`b`也被去除了；
3. 重复进行匹配和去除操作，直到字符`4`，字符`4`不在需要去除的'abc666'里面，所以从左往右匹配停止，这个时候剩下'46abc666'；
4. 然后从右往左匹配，匹配过程同从左往右，最后一直匹配到`4`，所以最后结果为`4`。

实际上，将需要去除的字符列表`abc666`改成`abc6`也可以得到一样的结果。

### 2. .. 符号

去除列表还可以使用`..`符号，表示两个字符之间的全部字符都需要去除。

比如`trim('abcdefg','a..f')`，在执行的时候会被当做`trim('abcdefg','abcdef')`来执行，所以结果就是`g`。

当然，如果要去除`a`、`.`、`f`三个字符，就不能使用上述的方法，而是使用一个`.`符号，如`trim('abcdefg','a.f')`。

### 3. 多字节

由于`trim()`方法的底层是通过十六进制数据进行匹配和去除的，在多字节处理的时候就会出现问题了，这也就是为什么`trim()`处理中文时会产生乱码。

比如，对于`trim('品、' , '、')`，`品`的十六进制表示为`e5 93 81`，字符`、`的十六进制表示`e3 80 81`。在`trim()`中，按字节计算，utf8 中文编码 3 个字节表示一个汉字。因此相当于`trim()`去掉内容是三个字符。这三个字符的十六进制表示为`e3 80 81`。所以最终返回字符串的十六进制表示为`e5 93`，因为`81`已经被去除了。

而`trim('的、', '、')`就能返回正确结果，因为`的`的十六进制表示`e7 9a 84`，与`、`的十六进制没有相同的部分。


