弱类型的语言对变量的数据类型没有限制，你可以在任何地时候将变量赋值给任意的其他类型的变量，同时变量也可以转换成任意地其他类型的数据。

在 PHP 中的变量都是弱类型的，内置函数对于传入参数也是松散处理。这就会引起一些意想不到的问题。

## 类型转换问题
类型转换是无法避免的问题。例如需要将 GET 或者是 POST 的参数转换为 int 类型，或者是两个变量不匹配的时候，PHP 会自动地进行变量转换。但是 PHP 是一个弱类型的语言，导致在进行类型转换的时候会存在很多意想不到的问题。

### 相等比较
一般的相等比较(==)时，如果两边的变量的类型不一致，PHP 会进行隐式的类型转换后再进行比较。所以，会有下面的一些比较结果：

```php
null == false;   //true
'' == null;      //true
0 == '0'	;        //true
0 == 'abcdefg'	;  //true
1 == '1abcdef'	;  //true
```

但是全等比较(===)则不会发生类型转换：

```php
0 === 'abcdefg';	//false
```

所以，建议尽可能的使用全等比较。

### Hash 比较
除了以上的这种方式之外在进行 hash 比较的时候也会存在问题。如下：

```php
"0e132456789" == "0e7124511451155"; //true
"0e123456abc" == "0e1dddada";       //false
"0e1abc" == "0";     //true
```

这是因为：在进行比较运算时，如果遇到了`/^0e\d+$/`这种字符串，就会将这种字符串解析为科学计数法。所以上面例子中 2 个数的值都是 0 因而就相等了。如果不满足`/^0e\d+$/`这种模式就不会相等。

### 十六进制转换
还存在一种十六进制余字符串进行比较运算时的问题。例子如下：

```php
"0x1e240" == "123456";		//true
"0x1e240" == 123456;		//true
"0x1e240" == "1e240";		//false
```

当其中的一个字符串是 0x 开头的时候，PHP 会将此字符串解析成为十进制然后再进行比较，0x1240 解析成为十进制就是 123456，所以与 int 类型和 string 类型的 123456 比较都是相等。


## 内置函数的参数的松散性
内置函数的松散性说的是，调用函数时给函数传递函数无法接受的参数类型。

### md5()
PHP 手册中的`md5()`函数的描述是`string md5 ( string $str [, bool $raw_output = false ] )`，`md5()`中的需要是一个 string 类型的参数。但是当你传递一个 array 时，`md5()`不会报错，知识会无法正确地求出 array 的 md5 值，这样就会导致任意 2 个 array 的 md5 值都会相等。

> 在命令行工具下会出现警告信息，而不会给出结果。
> 
```php
$array1[] = array(
    "foo" => "bar",
    "bar" => "foo",
);
$array2 = array("foo", "bar", "hello", "world");
var_dump(md5($array1) == md5($array2)); //true
```

### strcmp()
`strcmp()`函数在PHP官方手册中的描述是`int strcmp ( string $str1 , string $str2 )`，需要给`strcmp()`传递 2 个 string 类型的参数。如果 str1 小于 str2，返回 -1，相等返回 0，否则返回 1。strcmp 函数比较字符串的本质是将两个变量转换为 ascii，然后进行减法运算，然后根据运算结果来决定返回值。

如果传入给`strcmp()`的参数是数字呢？

```php
$array=[1,2,3];
var_dump(strcmp($array,'123')); //null,在某种意义上null也就是相当于false。
```

### switch()
如果 switch 是数字类型的 case 的判断时，switch 会将其中的参数转换为 int 类型。如下：

```php
$i ="2abc";
switch ($i) {
case 0:
case 1:
case 2:
    echo "i is less than 3 but not negative";
    break;
case 3:
    echo "i is 3";
}
```

这个时候程序输出的是`i is less than 3 but not negative`，是由于`switch`结构将 $i进行了类型转换，转换结果为 2。

> JavaScript 中，switch 语句则是使用全等比较。

### in_array()
在 PHP 手册中，`in_array()`函数的解释是`bool in_array ( mixed $needle , array $haystack [, bool $strict = FALSE ] )`，如果 strict 参数没有提供，那么 in_array 就会使用松散比较来判断`$needle`是否在`$haystack`中。当`strict`的值为 true 时，`in_array()`会比较`$needls`的类型和`$haystack`中的类型是否相同。

```php
$array=[0,1,2,'3'];
var_dump(in_array('abc', $array));  //true
var_dump(in_array('1bc', $array));  //true
```

上面的情况返回的都是 true，因为 'abc' 会转换为 0，'1bc' 转换为 1。`array_search()`与`in_array()`也是一样的问题。

## 参考
[PHP 弱类型安全问题总结](http://blog.spoock.com/2016/06/25/weakly-typed-security/)




