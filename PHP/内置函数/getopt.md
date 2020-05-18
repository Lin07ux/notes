`getopt()`方法用来从命令行参数列表中获取选项，在编写 PHP 命令行脚本的时候非常有用。比如，执行如下命令的时候，可以在脚本中通过`getopt()`获取传入的`h`、`port`参数：

```php
php server.php -h 127.0.0.1 --port 8080
```

[getopt - PHP Manual](https://www.php.net/manual/zh/function.getopt.php)

### 1. 方法签名

该方法的签名如下：

```php
getopt ( string $options [, array $longopts [, int &$optind ]] ) : array|bool false
```

其中：

* `$options` 指定缩写的参数集合组成的字符串。该字符串中的每个字符会被当做选项字符，匹配传入脚本的选项以单个连字符(`-`)开头。 比如，一个选项字符串`"x"`识别了一个选项`-x`。缩写的参数只允许`a-z`、`A-Z`和`0-9`。
* `$longopts` 指定非缩写的参数集合的数组。此数组中的每个元素会被作为选项字符串，匹配了以两个连字符(`--`)传入到脚本的选项。例如，长选项元素`["opt"]`识别了一个选项`--opt`。
* `$optind` 停止参数解析的索引。如果提供了这个参数，则该索引会被写入到这个变量中。

需要注意的是，`$options`和`$longopts`均可以包含可选项、必选项、开关项(不接受值的选项)：

* 开关项：不包含冒号的单独的字符串（不接受值）
* 必选项：后面跟随一个冒号`:`的字符（此选项需要值）
* 可选项：后面跟随两个冒号`::`的字符（此选项的值可选）

> 选项的值不接受空格（" "）作为分隔符。

> 选项的解析会终止于找到的第一个非选项，之后的任何东西都会被丢弃。

### 2. 示例

**短选项**

对于如下脚本`1.php`：

```php
<?php
$options = getopt("h:p:d");
var_export($options);
```

在命令行中执行如下命令时：

```shell
php 1.php -h 127.0.0.1 -p 8000 -d
# 选项和值中间也可以不要空格
php 1.php -h127.0.0.1 -p8000 -d
```

会有如下输出：

```php
array (
  'h' => '127.0.0.1'
  'p' => '8080',
  'd' => false,
)
```

**长选项**

对于如下的脚本`2.php`：

```php
<?php
$shortopts  = "";       // 短选项 用字母字符串
$shortopts = "h:";     // 必选选项 字母后面一个冒号
$shortopts .= "p::";    // 可选选项 字母后面两个冒号
$shortopts .= "vd";     // 无需值的选项 字母后面没有冒号

$longopts  = array(     // 长选项 用单词的数组
    "host:",            // 必选选项 单词后面一个冒号
    "port::",           // 可选选项 单词后面两个冒号
    "version",          // 无需值的选项 单词后面没有冒号
    "debug",            // 无需值的选项 单词后面没有冒号
);

$options = getopt($shortopts, $longopts);
var_export($options);
```

运行测试：

```shell
php 2.php -h127.0.0.1 -p8000 -d --host=127.0.0.1 --port=8000 --version --debug
```

输出：

```php
array (
  'h' => '127.0.0.1',
  'p' => '8000',
  'd' => false,
  'host' => '127.0.0.1',
  'port' => '8000',
  'version' => false,
  'debug' => false,
)
```

**同一选项传递多次**

对于上面的`2.php`脚本，传递两次`-d`选项：

```shell
php 2.php -h127.0.0.1 -p8000 -d -d --host=127.0.0.1 --port=8000 --version --debug
```

可以得到如下的结果：

```php
array (
  'h' => '127.0.0.1',
  'p' => '8000',
  'd' =>
  array (
    0 => false,
    1 => false,
  ),
  'host' => '127.0.0.1',
  'port' => '8000',
  'version' => false,
  'debug' => false,
)
```

可以看到，一个选项传递多次时，会用数组方式包含每次输入的值。

**使用 optind**

对于如下的脚本`3.php`：

```php
<?php
// Script 3.php
$opts = getopt('a:b:', [], $optind);
var_export($argv);
echo "\n";

var_export($opts);
echo "\n";

var_export($optind);
echo "\n";

$pos_args = array_slice($argv, $optind);  // 从数组中取出一段
var_export($pos_args);
```

运行测试：

```
php 3.php -a 1 -b 2 -- test
```

输出如下：

```php
array (
  0 => '1.php',
  1 => '-a',
  2 => '1',
  3 => '-b',
  4 => '2',
  5 => '--',
  6 => 'test',
)
array (
  'a' => '1',
  'b' => '2',
)
6
array (
  0 => 'test',
)
```

可以看到，遇到`--`时就停止了解析，后面的就不再归于选项了，所以得到的选项只有`a`和`b`两个。

