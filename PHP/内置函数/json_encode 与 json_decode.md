## 一、json_encode

> [json_encode - PHP Manual](https://www.php.net/manual/zh/function.json-encode.php)

### 1.1 语法

```
json_encode ( mixed $value [, int $options = 0 [, int $depth = 512 ]] ) : string
```

对变量进行 JSON 编码，对变量进行 JSON 编码。编码受传入的`options`参数影响，此外浮点值的编码依赖于 [serialize_precision](https://www.php.net/manual/zh/ini.core.php#ini.serialize-precision)。

成功则返回 JSON 编码的 string，在失败时返回 FALSE 。

### 1.2 value 参数

参数`value`可以是除了 resource 类型之外的任何数据类型。但是其所有字符串数据的编码必须是 UTF-8。

### 1.3 options 参数

参数`options`可以影响 JSON 序列化的结果。可以为以下常量组成的二进制掩码：

* `JSON_HEX_QUOT` 将所有的`"`转换成`\u0022`。自 PHP 5.3.0 起生效。
* `JSON_HEX_TAG`
* `JSON_HEX_AMP`
* `JSON_HEX_APOS`
* `JSON_NUMERIC_CHECK`
* `JSON_PRETTY_PRINT`
* `JSON_UNESCAPED_SLASHES`
* `JSON_FORCE_OBJECT`
* `JSON_PRESERVE_ZERO_FRACTION`
* `JSON_UNESCAPED_UNICODE`
* `JSON_PARTIAL_OUTPUT_ON_ERROR`

具体可以参考 [JSON 常量](https://www.php.net/manual/zh/json.constants.php)。

比如，要在输出结果中保持中文编码，则需要使用`JSON_UNESCAPED_UNICODE`参数：

```php
json_encode("试试", JSON_UNESCAPED_UNICODE);
```

### 1.4 depth 参数

设置进行序列化遍历的最大深度，必须大于0。默认为 512。

该参数用于避免在序列化时无穷尽的进行遍历查询，特别是在有循环引用的情况下。

## 二、json_decode

`json_decode`用于对 JSON 格式的字符串进行解码，得到对象或数组。

> [json_decode - PHP Manual](https://www.php.net/manual/zh/function.json-decode.php)

### 2.1 语法

```
json_decode ( string $json [, bool $assoc = false [, int $depth = 512 [, int $options = 0 ]]] ) : mixed
```

接受一个 JSON 编码的字符串并且把它转换为 PHP 变量。

### 2.2 json 参数

`json`参数待为解码的 json string 格式的字符串，需要为 UTF-8 编码的数据。

### 2.3 assoc 参数

当`assoc`参数为`true`时，该方法返回的结果是一个数组(`array`)而不是一个对象(`object`)。

默认情况下该参数为 false，表示返回对象。

### 2.4 depth 参数

表示解析的结果的层数，避免过度解析。必须大于 0，默认为 512。

### 2.4 options 参数

该参数为 JSON 解码的掩码选项。现在有两个支持的选项：

* `JSON_BIGINT_AS_STRING` 用于将大整数转为字符串而非默认的 float 类型(科学计数法)
* `JSON_OBJECT_AS_ARRAY` 与将 assoc 设置为 TRUE 有相同的效果

比如，将一个大整数进行解码时：

```php
// 下面会得到一个科学计数法表示的浮点数：1.0E+25
json_decode("9999999999999999999999999";

// 下面会得到一个字符串："9999999999999999999999999"
json_decode("9999999999999999999999999", true, 512, JSON_BIGINT_AS_STRING);
```

## 三、使用实例

### 3.1 与 JavaScript 语言互通

当需要将一个 PHP 数组通过 json 序列化后的字符串形式传递给 JavaScript，然后后者通过`JSON.parse()`方法进行解析的时候，有可能会遇到原 PHP 数组中的内容包含引号，从而导致 JavaScript 中的解析出错。

此时可以考虑使用 PHP 中的`addslashes()`方法来将序列化之后的字符串进行处理，以便将其中的引号(单引号、双引号)、反斜线(`\`)、null 进行特殊的转义处理，这样就可以避免出现问题了。

```php
addslashes(json_encode($array, JSON_UNESCAPED_UNICODE));
```


