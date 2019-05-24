## 原型文档
原型文档是一种流行于很多语言里的引用方式。它不必使用多个输出语句和引号就能引用大块文本，整个文本就好像被双赢好包围一样被处理。

如果 PHP 包含的 HTML 块里有很多变量、引号、转义序列，使用这种方式很方便。

### 使用规则
1. 使用自定义分隔符作为原型文档的开始与结束标记。文本插入到这两个标记之间。分隔符和变量的命名规则一致。习惯上，分隔符都使用大写字母，从而便于区分脚本里的其他内容。分隔符前面以三个 "<" 字符开始。

2. 分隔符周围不能有任何空格、注释、其他文本。最后一个分隔符可以使用分号结束，也可以不用，但必须位于单独一行。

3. 变量和转义序列都会在原型文档里得到解释。

```php
<?php
print <<<HERE_DOC_DELIMITER
	text here
	...
	more text
	...
HERE_DOC_DELIMITER
?>
```

## 转义序列
转义序列由一个反斜线和一个字符组成。
为了在浏览器里显示转义序列，可以使用 HTML 的`<pre></pre>`标签，否则在 PHP 脚本里的转义序列不会被解释。

|  符号  |     意义       |
| ------ | --------------|
|  \'    | 单引号标记      |
|  \"	    | 双引号         |
|  \t    | 制表符          |
|  \n	    | 换行符         |
|  \r	    | 回车/换行       |
|  \$	    | 美元符号        |
|  \\	    | 反斜线         |
|  \70	 | 代表八进制数据   |
|  \x05	 | 代表十六进制字符 |


## 注释
PHP 支持三种注释方式：

- C++ 单行注释(`//`)
- C 多行注释(`/**/`)
- Perl 单行注释(`#`)

> 在单行注释的内容中，不要出现`?>`标志，因为解释器会认为 PHP 脚本到此结束，而去直接显示该标志后面的代码。


## 打印和显示
PHP 中，有很多的方法可以用来打印变量，显示语句。每个方法均有所不同。

### echo 和 print
这两者是可以互换的，他们都不需要用括号包围参数(传递给 echo 的参数是绝对不能包围在小括号中的)。

他们唯一的区别在于：**echo 允许显示多行由逗号分隔的参数，而 print 不允许**。

```php
<?php
	$name = "Tom";
	$state = "New York";
	$salary = 8000;

	echo $name, $state, $salary;	// 直接用逗号分隔，
	echo $name . $state . $salary;	// 使用点连接
	echo "$name, $state, $salary";	// 双引号包裹
	echo '$name, $state, $salary';	// 单引号包裹，此时变量不会被用其值替代

	## print $name, $state, $salary;	// 不能这样用逗号分隔
	print $name . $state . $salary;
	print "$name, $state, $salary";
	print '$name, $state, $salary';
?>
```

### printf() 和 sprintf()
字符串格式化函数。这个函数能够为字符串设置一定的格式。

他们主要的区别在于如何处理输出结果：**前者将结果显示(打印)出来；后者将结果返回，可以保存到一个变量中**。

格式为：

`int printf (设置了格式的字符串 [, 混合参数 [, 混合参数 ...]])`
`string sprintf (设置了格式的字符串 [, 混合参数 [, 混合参数 ...]])`


```php
<?php
	printf("The number is %.2f\n", 152);    // The number is 152.00
	$output = sprintf("Product %s will cost \$%6.2f tax.", "PurpleDress", 199.95);
	// $output = Product PurpleDress will cost $199.95 tax
?>
```

### print_r()
语法：`bool print_r ( mixed variable [, bool return] )`

这个函数将变量的详细信息，比如类型、长度和内容，输出到屏幕上。它可以用来显示字符串、整数或浮点数的值，也可以显示数组和对象。

如果设置了第二个参数为 true，那么这个函数会将输出的内容返回给一个变量，而不是发送到标准输出。

显示数组时，会显示数组的全部元素，也就是“关键字-值”对，并且所有结果都显示在一行内。
使用该函数会把内部数组指针移动到数组的末尾，可以使用`reset()`函数把指针移回到数组开头。

> PHP 5 之后，使用这个函数后，会自动充值数组指针。

如果想把`print_r()`函数的输出保存到变量，就把它的第二个参数设置为布尔值 true。

### var_dump()
这个函数显示数组或对象的元素数量、每个字符串的长度。还以缩进方式显示数组或对象的结构。

### fprintf()
printf() 函数把结果写入到标准流（浏览器），而 fprintf() 函数可以把结果发送到任何指定的输出流（通常是文件）。

语法：`int fprintf (资源句柄, 格式化的字符串 [, 混合格式 [, 混合格式 ...]])`

```php
<?php 
	fprintf($filehandle, "%04d-%02d-%02d", $year, $month, $day)
?>
```

### number_format() 和 money_format()
如果要在数值之中添加逗号或空格，或是显示货币数额，就会导致数值转化为字符串，从而可以使用`printf()`函数进行处理。而 PHP 还提供了两个特殊函数来专门处理数值和货币，分别是`number_format()`和`money_format()`。

语法：`string number_format (浮点数 [, int decimal [, string dec\_point, string thousands\_sep]])`

作用：可以在数值中插入千位分隔符。

使用：有三种调用方式：一参数、双参数、四参数。（注意，没有三参数的。）
    
* 只有一个参数时，返回值是一个整数，其中每三位有一个逗号，而小数部分被四舍五入之后，连同小数点一起被截掉。
* 如果指定了两个参数，那么第二个数值将指定小数部分的位数，整数部分仍然使用逗号作为千位分隔符。
* 如果指定了四个参数，那么四个参数分别表示：格式化的数值，小数位数，小数点符号，千位分隔符

```php
<?php 
	$number = 123456.5456;
	$new_string = number_format($number);    // 123,457
	$new_string = number_format($number, 2);    // 123,456.55
	$new_string = number_format($number, 2, ',', ' ');    // 123 456,55
?>
```

`money_format()`函数是用来格式化代表货币的数字。由于这个函数基于名为`strfmon()`的 C 库函数，它不能用于 Windows 操作系统之上。这个函数能够设置很多国家和地区的货币格式，并且具有很多格式说明符。可以处理负数、左右精度、填充等，类似于`printf()`函数。

语法：`string money_format(格式化字符串, 浮点数)`

```php
<?php
	setlocale(LC_MONETARY, 'en_US');
	$number = 1234.5567;
	echo money_format('%i', $number) . "\n";    // USD 1,234.56
?>
```

### 格式说明符
**格式转化说明符**由一个百分号开始，后面跟一个**格式说明符**组成。下面的表格中，列出了相关的格式说明符。

|  说明符   |   格式                 |
| -------- | ----------------------|
|    b     | 以二进制显示整数         |
|    c     | 整数值对应的 ASCII 字符  |
|    d     | 有符号整数              |
|    e     | 科学计数法              |
|    f     | 浮点数                 |
|    o     | 以八进制表示整数         |
|    s     | 字符串                 |
|    u     | 无符号整数              |
|    x     | 以小写十六进制表示整数    |
|    X     | 以大写十六进制表示整数    |

### 修饰符
格式说明符还可以进一步进行修饰，从而指定显示精度、左对齐/右对齐、填充字符等。下面列出一些修饰符。

|   修饰符   |   范例    |   格式                                          |
| --------- | -------- | ------------------------------------------------|
|  .        | %.2f     | 指定浮点数里小数的位数                              |
|  整数值    | %8d      | 指定这个参数显示的字符数(数值位数，或字符串长度)        |
|  -        | %8.2f <br> %-30s| 实现左对齐。范例是让宽度为 8 位的浮点数左对齐。<br>或是让 30 字符长的字符串左对齐 |
|  0        | %08d     | 用 0 填充数字的空白位置                          |


## 变量
PHP 中，变量类型有 8 种：

- Boolean
- float
- integer
- string
- array
- null
- object
- resoure

> float 浮点数值只是一个近似值，所以要尽量避免浮点型数值之间比较大小，因为其结果往往是不准确的。

integer 整型数能够使用多种方式表示，如八进制、十六进制、二进制、十进制等。如果给定的数值超出了 int 型所能表示的最大范围，将会被当做 float 型处理，这种情况称为整数溢出。同样，如果表达式的最后运算结果超出了 int 型的范围，也会返回 float 型。

> 注意：如果在八进制中出现了非法数字（如 8 、9），则后面的数字会被忽略掉。

### 串接变量
为了把变量和字符串串接在同一行里，可以使用句点(`.`)。如果两侧的表达式分别是数值和字符串，PHP 仍会把数字转化成字符串。

> 句点是一个操作符，作用于其两侧的表达式(都被称为操作数)。

```php
<?php
	$n = 5 . " cats";
	$years = 9;
	echo "He owns ", $years . $n, ".";	// He owns 95 cats.
?>
```

### 变量引用
引用就是一个变量是另一个变量的别名，或者说另一个变量的指针。也就是说，这两个变量指向相同的底层数据。修改一个变量就会自动改变另一个变量的值。

为了以引用方式进行赋值需要在`旧变量`的前面添加一个`&`。比如，`$ref = & $old;`。

当不再需要引用时，可以取消引用。取消引用使用 unset() 函数，它只是断开了变量名和变量内容之间的绑定，而不是销毁变量内容。如果 unset() 操作的是原变量，那么引用这个变量内容的变量还是能正常使用的。

> 需要说明的是：只能是有名称的变量才能进行引用赋值。

```php
<?php
	$age = 26;
	$old = & $age;	// 引用
	echo $old . "<br>";   // 26
	# $old = &(26+7);	  // 非法操作

	unset($old);
	echo $old . "<br>";   // $old 已经不存在了
	echo $age;            // 26

	# unset($age);
	# echo $old . "<br>";  // 仍旧是 26
	# echo $age;           // $age 已经不存在了
?>
```

### 可变变量
可变变量也被称为动态变量，这种变量的名称被保存在另一个变量里。

通过使用两个美元符号，可变变量就可以防伪原始变量的值。一般需要使用大括号来包裹可变变量，这样能够确保 PHP 解析成功能够正确理解美元符号的含义。

在处理像表单变量这种具有类似名称的多个变量时，使用动态变量是很方便的。

```php
<?php
	$pet = "Bozo";
	$clown = "pet";
	echo $clown;		// 显示 "pet"
	echo ${$clown};	// 显示 "Bozo"
?>
```

### 变量的作用域
可变变量只存在于函数内部；全局变量可以在脚本之中**函数之外**的任意位置使用。

#### 局部变量
函数内部创建的变量在脚本的其他部分是不可用的，他们只属于函数这个局部范围，函数在退出时他们就会消失。

如果在程序主体和函数内部具有名称相同的变量，那么在修改函数内部的变量时**不会影响**函数之外的同名变量。

类似的，函数也不能访问在函数外部创建的函数。但是可以通过`use`关键字引入外部的变量。

#### 静态变量
静态变量能够在函数调用结束后，仍保留变量值；当再次返回到其作用域时，可以继续使用上次保留的变量值。

而一般变量在函数调用结束之后，其存储的数据值也将被清除，所占内存空间被释放。

使用静态变量时，先要用关键字`static`来声明变量，把关键字`static`放在要定义的变量之前。

#### 全局变量
全局变量是在脚本中（非函数中）定义的变量，可以在脚本之中**函数之外**的任意位置使用。

在函数内部不能直接访问在函数外部声明的变量，除非变量被当做变元传递进去。或者，在函数中，使用`global`保留字，放在变量名之前即可。再或者，可以使用`$GLOBALS[]`数组，这个数组包含脚本里所有的全局变量（也就是函数外声明的全局变量）。

```php
<?php
	$name = "Linux";
	function getName() {
		global $name;
		echo "$name";	// 显示 "Linux"
	}
?>
```

#### 超全局和环境变量
超全局变量能够从脚本和函数内部的任意位置进行访问。他们是 PHP 提供的特殊变量，有助于处理 HTML 表单、cookie、会话和文件，并且能够获取环境和服务器的有关信息。

|     名称     |    含义                                 |
| ------------ | -------------------------------------- |
|  $GLOBALS    | 一个数组，包含全部全局变量                 |
|  $_SERVER   | 包含服务器变量（比如 REMOTE_ADDR)         |
|  $_GET      | 包含由 GET 方法发送的表单变量               |
|  $_POST     | 包含由 POST 方法发送的表单变量              |
|  $_COOKIE   | 包含 HTPP cookie 变量                    |
|  $_FILES    | 包含通过 HTTP 提交文件上载向脚本提供的变量    |
|  $_ENV      | 包含环境变量                              |
|  $_REQUEST  | GET 变量、POST 变量和 COOKIE 变量的合并     |
|  $_SESSION  | 包含由会话模板注册的 HTTP 变量              |

PHP 验证：

`$_SERVER['PHP_AUTH_USER']`和`$_SERVER['PHP_AUTH_PW']`这两个预定义的变量中，保存着通过 HTTP 验证方式登录的用户的用户名和密码。可以直接将这两个变量和相关的用户名和密码对比，就可以验证是否能登陆。

使用这两个预定义变量时，建议：

- 这两个变量都必须在每个受限页面的开始处验证。
- $_SERVER 数组中的变量在 CGI 版本的 PHP 中不能正常工作，在 IIS 上也不起作用。

### 预定义变量
PHP 提供了一些预定义变量，这些变量描述了环境、服务器、浏览器、版本号和配置文件等。但是这些实际使用情况取决于服务器类型、配置等因素。有些是在 php.ini 文件中定义的。

|       变量        |    功能                                              |
| ----------------- | -------------------------------------------------- |
|  AUTH_TYPE        | 如果以模块方式运行于 Apache 服务器，就被设置为身份验证类型  |
|  DOCUMENT_ROOT    | Web 文档根目录的完整路径。在服务器的配置文件里定义         |
|  HTTP_USER_AGENT  | 当浏览器向服务发送请求时，这个变量标明了浏览器的类型        |
|  HTTP_REFFERER    | 引用链接，即从哪个页面跳转到当前页面上的。是一个完整的 URL。 |
|  REMOTE_ADDRESS   | 请求这个页面的客户端的远程 IP 地址                      |

### 管理变量
PHP 提供了一些函数来对变量做判断。

|     函数名     |      说明                                              |
| -------------- |------------------------------------------------------ |
|  isset()       | 可以同时设置多个变量，如果所有变量都不是 null，则返回 true    |
|  empty()       | 如果变量是 null、空字符串、0、没有值或不存在，则返回 true     |
|  is_bool()     | 如果变量是布尔型，则返回 true                             |
|  is_callable() | 如果变量被赋值为一个函数或一个对象，则返回 true              |
|  is_double()   | 如果变量是一个浮点数(5.67或4.5)，则返回 true               |
|  is_float()    | 同上                                                   |
|  is_real()     | 同上                                                   |
|  is_int()      | 如果变量是一个整数，则返回 true                            |
|  is_integer()  | 同上                                                   |
|  is_long()     | 同上                                                   |
|  is_null()     | 如果变量被赋予 NULL，则返回 true                          |
|  is_numeric()  | 如果变量被赋予一个数值字符串或一个数字，则 true              |
|  is_object()   | 如果变量是一个对象，则返回 true                           |
|  is_resource() | 如果变量是一个资源，则返回 true                           |
|  is_scalar()   | 如果变量是一个被赋予耽搁值(一个数值或布尔值)，但不是数组或对象，则返回 true |
|  is_string()   | 如果变量是一个文本字符串，则返回 true                      |
|  unset()       | 撤销或清除变量，使得变量不再存在，能接受多个参数             |
|  settype()     | 改变变量为另一种类型：bool settype(mixed var, string type) |

> `settype()`第二个参数可选类型有：boolean, float, integer, array, null, object 和 string。

### 常数
与变量不同的是，常数一旦设置之后，在程序运行期间不能改变或清除(`unset`)。

常数在整个程序中都是可见的（作用域是全局）。一般可以把服务器的文档根目录、站点名称、圆周率等定义成常数。

定义：常数使用`define()`函数来定义。用`defined()`来判断是否设置了相应的常数。用`constant()`返回常数的值。

语法：`bool define(name, value [, bool case_insensitive])`

说明：第一个参数表示常数的名称；第二个参数是常数的值；第三个参数表示是否忽略大小写(为 true 时忽略)。

按照惯例，PHP 常数一般都是用大写英文单词进行定义。命名规则和变量一样。而且和变量一样，区分大小写。

当常数名被保存在变量里，或是由函数返回时，我们不能确定常数名，这时`constant()`函数就可以发挥作用了。

```php
<?php
	define('ISBN', "0-13-140162-9");
	define('TITLE', "Modern PHP");
	if (defined('ISBN') and defined('TITLE')) {
		print ISBN . "<br>";
		print TITLE . "<br>";

		$value  = constant(ISBN);  // 0-13-140162-9
	}

	define('TITLE', "PHP by Example");	// Can't change TITLE, and can't redefine it.

	print TITLE . "<br>";
?>
```

#### 预定义常数
|    名称     |    描述                        |
| ----------- | ----------------------------- |
| PHP_VERSON  | 当前运行的 PHP 解析程序的版本。   |
| PHP_OS      | PHP 所在的服务器的操作系统。      |
| TRUE        | 逻辑真                         |
| FALSE       | 逻辑假                         |
| NULL        | 一个 null 值                   |
| E_ERROR     | 指向最近的错误处                 |
| E_WARNING   | 指向最近的警告处                 |
| E_PARSE     | 指向解析语法有潜在的问题处         |
| E_NOTICE    | 为发生不寻常处的提示但是一定的错误处 |

#### 神奇常数
有5个预定义常数被称为**神奇常数**，他们会根据被使用的方式发生改变。他们不能被包围在引号里，也不区分大小写。这些常数的名称两端都有两个下划线。

|     名称         |     描述                                               |
| --------------- | ------------------------------------------------------ |
|  `__LINE__`     | 指定当前语句在文件中的行号。值从1开始计算，注释也被算进去。      |
|  `__FILE__`     | 文件的完整路径与文件名。如果在一个包含文件里使用，就会返回被包含文件的名称。 |
|  `__FUNCTION__` | 函数名。在 PHP5 里这个常数返回函数被声明的名称，且区分大小写。  |
|  `__CLASS__`    | 类名。在 PHPH5 里这个常数返回类被声明的名称，且区分大小写。     |
|  `__METHOD__`   | 类的方法名。返回类的方法被声明的名称。                        |

```php
<?php
	// Using PHP built-in constants
	echo "PHP version = " . PHP_VERSION . "<br>";
	echo "Server operating system = " . PHP_OS . "<br>";
	echo "Current file name = " . __FILE__ . "<br>";
	echo "Current line number = " . __LINE__ . "<br>";

	/**
	 * 输出如下：
	 * PHP version = 5.6.11
	 * Server operating system = WINNT
	 * Current file name = D:\Web\xampp\htdocs\test.php
	 * Current line number = 6
	 */
?>
```

-------------------------------------------------------------------------------

## 操作符
下面列出 PHP 操作符的优先与结合规则：
	这些行的优先级是从高到低；
	位于同一行的操作符具有相同优先级。

|  操作符                  |     描述                 |   结合   |
| ----------------------- | ------------------------ | -------- |
|  ( )                    | 小括号                    |  左到右  |
|  new                    | 创建对象                  |  不结合  |
|  [                      | 数组下标                  |  右到左  |
|  ++ --                  | 自增、自减                |  不结合  |
|  ! ~ -                  | 逻辑非、比特操作非、取反     |  不结合  |
|  (int) (float) (string)<br>(array) (object) | 强制指派数据类型  |  ——    |
|  @                      | 抑制错误                  |  ——      |
|  * / %                  | 乘法、除法、取模           |  左到右  |
|  + - .                  | 加法、减法、字符串串连      |  左到右  |
|  << >>                  | 向左移位、向右移位         |  左到右  |
|  < <=                   | 小于、小于等于             |  左到右  |
|  > >=                   | 大于、大于等于             |  ——      |
|  == !=                  | 等于、不等于              |  不集合  |
|  === !==                | 等同、不等同              |  ——      |
|  &                      | 比特操作与                |  左到右  |
|  ^                      | 比特操作异或              |  ——      |
|  &&                     | 逻辑与                   |  左到右  |
|  &#124;&#124;           | 逻辑或                   |  左到右  |
|  ? :                    | 三元操作符                |  左到右  |
|  = += -= *= /= <br> %= <<= >>= | 赋值              | 右 到左  |
|  and                    | 逻辑与                   |  左到右  |
|  xor                    | 逻辑异或                 |  左到右  |
|  or                     | 逻辑或                   |  左到右  |
|  ,(逗号)                | 列表分隔符等              |  左到右  |

> 通过`(int)`、`(float)`、`(bool)`、`(array)`、`(object)`可以强制把变量转换成相应的类型，转换中可能会有精度的损失。
> 将一个数据类型强制转换为数组中的一个成员后，所转换的值将成为数组中的第一个成员，索引是 0。
> 任何数据类型转换成对象后，该变量变成了对象的一个属性，该属性的名称是`scalar`。

```php
<?php
	$score = 112;
	$scoreboard = (array) $score;
	echo $scoreboard[0];      // Returns 112

	$model = "Toyota";
	$obj = (object) $model;
	echo $obj->scalar;       //  Returns "Toyota"
?>
```

### 逻辑操作符
从这个表格中，可以看到，`and`、`or`操作符比`&&`、`||`操作符的优先级低，甚至比赋值符号(如`=`号)的优先级都低。

### 比较操作符
- 比较的时候，有可能会出现隐形的类型转变；
- 两个数字比较，是按值进行比较；
- 对于字符串的比较，是按照 ASCII 码来比较；
- 对于一个是数字，一个是字符串的，将会把字符串转换成数字，然后进行比较；
- 对于均只包含数字的两个字符串，将会被转化成数字，然后进行值比较。

### 比特移动操作符
- 左移操作符(<<)把第一个操作数向左移动指定的比特位数。向左移出的比特会被抛弃，由右侧移进 0。
- 右移操作符(>>)把第一个操作数向右移动指定数量的比特位数。向右移出的比特会被抛弃，左侧空出的位置用最高位的比特填充。
- 添零右移(>>>)操作符把第一个操作数向右移动指定数量的比特位数。向右移出的比特会被抛弃，左侧移进 0。

### 执行操作符
执行操作符：反引号(`)。

PHP 会把反引号里的内容看做一个操作系统命令来执行，执行结果会返回并可以赋值给一个变量。
内置的`shell_exec()`函数具有相同的功能。

> 需要注意的是，反引号里的具体内容的执行，取决于实际使用的操作系统。

当安全模式被启动，或是`shell_exec()`函数被关闭时，执行操作符就无效了。

```php
<?php
	# 下面是 UNIX 或 Linux 的列出文件或目录的命令
	$output = `ls -al`;
	echo "<pre> $output</pre>";
?>
```

### 错误控制操作符
错误控制操作符(@)添加在表达式的前面，就可以禁止 PHP 可能产生的错误消息。
这个操作符禁止的是脚本执行时产生的错误，而不是程序第一次解析时产生的错误（如语法错误等）。

`@`操作符只能用于代表数值的表达式，比如变量、函数和`include()`调用、常数等，但**不能是语言结构**，比如`if`、`switch`、`foreach`或函数定义。

### 类型操作符
PHP 5 有一个类型操作符：instanceof，用于判断某个对象是否属于指定的类。

### 常用数学函数
|    函数        |    含义              |    范例                           |
| -------------- | ------------------- | -------------------------------- |
|  abs()         | 绝对值               | abs(-5) == 5   abs(5.3) == 5     |
|  base_convert  | 在任意基数直接转换数值  | base\_convert("ff",16,10) == 255 |
|  bindec()      | 二进制转化为十进制      | bindec('1010') == 10             |
|  ceil()        | 向上取整              | ceil(6.2) ==7    ceil(6.8) == 7   |
|  decbin()      | 十进制转化为二进制     | decbin(5)==101  decbin(20)==10100 |
|  dechex()      | 十进制转化为十六进制    | dechex(15) == f                  |
|  decoct()      | 十进制转成八进制       | decoct(8) ==10   decoct(20) == 24 |
|  floor()       | 向下取整              | floor(6.2) == 6   floor(6.8) ==6  |
|  getrandmax()  | 最大的随机数           | getrandmax() == 32767            |
|  hexdec()      | 十六进制转化为十进制    | hexdec('ff') == 255               |
|  is_finite()   | 判断一个值是否为有限大小 | is_finite(pi()) == 1(true)       |
|  is_infinite() | 判断一个值是否是无穷 | is_infinite(pow(10,100000))==1(true) |
|  is_numeric()  | 判断值是不是数字       | is_numeric(5.2) = 1(true)         |
|  max()         | 找到最大值            | max(1, 2, 4, 17, 10) == 17        |
|  min()         | 找到最小值            | min(1, 2, 4, 17, 10) == 1         |
|  octdec()      | 八进制转化成十进制      | octdec(10) == 8                  |
|  pi()          | 圆周率π值             | pi() == 3.1415926535898           |
|  pow()         | 幂                   | pow(3,2) == 9   pow(10,3) == 1000 |
|  rand(s, e)    | 生成从 s 到 e 的随机数  | rand(1,10) == 5  rand(1,10) == 2 |
|  round()       | 四舍五入取整           | rand(6.4) == 6  rand(6.5) == 7   |
|  sqrt()        | 平方根                | sqrt(81) == 9                    |
|  srand()       | 为随机数生成器设置种子   | ——                               |


## 字符串函数
在处理大量字符时，正则表达式会使速度大幅减慢。应该只在需要使用正则表达式解析比较复杂的字符串时才使用正则表达式的一些函数。如果要解析简单的表达式，应该采用下面介绍的很多可以显著加快处理过程的预定义函数。

### 字符串长度
**strlen()** 获取字符串的长度，也就是字符串李包含多少个字符。
语法：int strlen(string variable)
> 注意：一个中文占两个字符位置。

**str_word\_count()** 返回字符串里的单词数量。“单词是因地而异的包含字母的一串字符，能够包含单引号(')和连字符(-)，但是不能以它们开始。
语法：mixed str_word\_count(string string [, int format [, string charlist]])
默认情况下，这个函数统计字符串李的单词数量，也可以设置第二个可选参数和第三个可选参数。
	- 第二个可选参数可以取值 0 (返回单词数量)，1 (返回一个数组，包含字符串里找到的全部单词)，2 (返回一个关联数组，其中关键字是单词在字符串里的位置序号，值是单词本身)。
	- 第三个可选参数用于添加可以作为单词组成部分的字符，比如重音符号、省略号、破折号或连字符。
> 注意：中文字符好像没有办法进行统计。

**count_chars()** 提供了关于字符串中字符数的信息。
语法：mixed count_chars ( string str [, mode] )
这个函数的行为依赖于如何定义可选参数 mode：
    - 0   返回一个数组，由找到的每个字节值作为键，相应的频率(即使频率为 0)作为值。这个是默认值。
    - 1   与 0 相同，但只返回频率大于 0 的字节/值。
    - 2   与 0 相同，但只返回频率为 0 的字节/值。
    - 3   返回一个字符串，其中包含找到的所有字节/值。
    - 4   返回一个字符串，其中包含所有未使用的所有字节/值。

```php
<?php 
	$sentence = "The rain in Spain falls mainly on the plain";

	$chart = count_chars($sentence, 1);
	foreach ($chart as $letter=>$frequency) {
		echo "Character ".chr($letter)." appears $frequency times<br>";
	}
	/* 输出
	Character appears 8 times
	Character S appears 1 times
	Character T appears 1 times
	Character a appears 5 times
	Character e appears 2 times
	Character f appears 1 times
	Character h appears 2 times
	Character i appears 5 times
	Character l appears 4 times
	Character m appears 1 times
	Character n appears 6 times
	Character o appears 1 times
	Character p appears 2 times
	Character r appears 1 times
	Character s appears 1 times
	Character t appears 1 times
	Character y appears 1 times
	*/
?>
```

### 改变字符串大小写
|    函数            |    功能                                    |
| ------------------ | ------------------------------------------ |
| strtoupper()       | 把字符串转成大写字符                       |
| strtolower()       | 把字符串转成小写字符                       |
| ucfirst()          | 把字符串里第一个字符转成大写               |
| ucwords()          | 把字符串里每个单词的第一个字母转成大写字符 |
| mb_convert\_case() | 根据 Unicode 字符属性转化字符串的大小写    |

语法：
	- string strtoupper(string str)
	- string strtolower(string str)
	- string ucfirst(string str)
	- string ucwords(string str)
	- string mb_convert\_case (string str, int mode [, string encoding])

mb_convert\_case() 函数类似于 strtolower() 和 strtoupper() 函数，但是不会受地域的影响。
它是局域 Unicode 字符而不是 ASCII 进行转化，这意味着德语变音、瑞典语音调或法语重音标记都包含在转化范围之内。
这个函数提供了 3 种方式来制定大小写：
	- MB_CASE\_UPPER
	- MB_CASE\_LOWER
	- MB_CASE\_TITLE
第三个参数是用来指定某个字符集来设置字符串的编码方式。
可指定的字符集如下：
	- ISO-8859-1
	- ISO-8859-15
	- UTF-8
	- cp 866

## 字符串比较
为了确保正确，对字符串进行比较的时候，不应该使用比较操作符，而是应该使用字符串比较函数。

所有的字符串比较函数都至少需要两个参数，并且根据比较结果返回整数值：
	- 0 			两个值相等
	- > 0(大于 0)  第一个值大于第二个值
	- < 0(小于 0)  第一个值小于第二个值

|    函数         |    说明                                          |
| --------------- | ------------------------------------------------ |
| strcmp()        | 比较两个字符串（区分大小写）                     |
| strcasecmp()    | 比较两个字符串（不区分大小写）                   |
| strnatcmp()     | 以 ASCII 字符顺序比较两个字符串，<br>但是数字是按照其值来进行比较 |
| strnatcasecmp() | 以 ASCII 字符顺序比较两个字符串（不区分大小写），<br>但是数字是按照其值来进行比较 |
| strncasecmp()   | 比较两个字符串（不区分大小写），<br>并且可以指定要进行比较的字符数量 |
| strspn()        | 比较字符串与表征码表示的字符，<br>返回字符串在出现特定字符之前的长度 |
| strcspn()       | 比较字符串与表征码表示的字符，<br>返回字符串中不匹配表征码的字符数量 |

语法
	- int strcmp(string str1, string str2)
	- int strcasecmp(string str1, string str2)
	- int strnatcmp(string str1, string str2)
	- int strnatcasecmp(string str1, string str2)
	- int strncasecmp(string str1, string str2, int length)
	- int strspn(string str1, string str2 [, int start [, int length]])
	- int strcspn(string str1, string str2 [, int start [, int length]])

strspn() 函数把两个字符串进行比较，第二个字符串指定了一个字符集，被称为`表征码`。
函数会返回第一个字符串里匹配表征码的第一个部分的字符数量。
这个函数还有两个可选参数，分别指定字符串里进行比较的其实位置和要进行比较的字符串长度。

strcspn() 函数与 strspn() 函数相似，只是其返回的是字符串不在表征码中的字符数量。

```php
<?php
	$mask = "0123456789";	// 表征码
	$zip = "95926";			// 字符串
	$count = strspn($zip, $mask);	// 返回 $zip 里与表征码匹配的字符数量，也就是 5。
									// 因为 $zip 里的 5 个字符都属于表征码李指定的字符集
	if ($count == strlen($zip)) {
		print "The zip code consists of $count numbers.<br>";
	}

	$name = "test03";	// 字符串
	$length = strcspn($name, $mask, 0, 5);  
	// 返回 $name 里，从 0 位开始的 5 个字符中，不在表征码中的字符的数量
	// $length = 4，因为前五个字符中，只有 'test' 这四个字符不在表征码中
?>
```

### 字符串相似
**语音相似**
soundex() 和 metaphone() 函数，以一个字符串作为参数，返回一个关键字(简短的数字和字母组合)，代表单词的英语发音。
之后，就可以用这个关键字用于比较字符串的发音，也就是说，如果关键字是相同的，相应的单词在英语里的发音就相同。

这两个函数的唯一区别在于，metaphone() 对于单词发音是否相同的判断更加精确。

语法：
	- string soundex(string str)
	- string metaphone(string str [, int phones])

> 这两个函数是基于美式英语发音的，而不是英式英语。这会有些微的差别。

```php
<?php
	$key1 = soundex("bored");
	$key2 = soundex("board");
	if ($key1 == $key2) {
		echo "The strings sound alike<br>";
	}
?>
```

**文字相似**
similar_text() 和 levenshtein() 函数用于测试两个字符在文字上的相似性。
前者计算两个字符串的相似性，返回相同字符的数量。它还有第三个可选参数，其中包含的值代表了字符串相似的百分比；
后者计算两个字符串(长度不超过255个字符)的 LevenShtein 距离(编辑距离)。其额外的三个可选参数用于定义插入、替换、删除操作的权重，从而对得到加权处理的结果，否则每种操作的权重就是一样的。权重代表着优先采取何种操作来修改字符串，也就是说，在转化字符串时是应该使用插入操作，还是删除操作。

语法：
	- int similar_text ( string first, string second [, float percent])
	- int levenshtein ( string str1, string str2 [, int cost_ins [, int cost\_rep, int cost\_del]])

```php
<?php
	$str1 = "Once upon a time, there ware three little pigs...";
	$str2 = "Once upon a time, there were three bears...";
	$number = similar_text($str1, $st2, $percent);
	print "There are $number of the same characters in the two strings.<br>";
	echo "The strings are similar by " . number_format($percent, 0) . "%.<br>";
	# 输出：
	# There are 40 of the same charactors in the two trings. 
	# The strings are similar by 87%.
?>
```

### 分解字符串

|    函数      |    功能                                             |
| ------------ | --------------------------------------------------- |
| split()      | 使用正则表达式把字符串分解为单词。                  |
| spliti()     | 类似 split() 函数，但是使用区分大小写的正则表达式。 |
| str_split()  | 把字符串转成数组，并且能够指定元素的大小。          |
| preg_split() | 使用 Perl 兼容的正则表达式分解字符串，<br>返回由子字符串组成的数组。 |
| explode()    | 用一个字符串(不是正则表达式)分期另一个字符串，<br>返回一个数组。 |
| implode()    | 把数组元素组合在一起，返回一个字符串。              |



strtok() 把字符串用传入的分隔符分解为较小的字符串。一般情况下，是使用空格分隔。
	语法：string strtok ( string str, string token)
	第一次调用这个函数的时候，使用两个参数：要被分解的字符串，和充当分隔符的字符。此时返回第一个被分解的语言符号。
	之后再调用这个函数的时候，就只需要传入分隔符即可，因为 strtok() 会记住它在字符串里的位置，逐次返回被分解的语言符号，直到字符串末尾。
	如果需要重新开始分解过程，就需要再次指定字符串及分隔符来启动这个过程。

```php
<?php
	$piece1 = strtok("/usr/local/bin", "/");    // Returns usr
	$piece2 = strtok("/");    					// Returns local
	$piece3 = strtok("/");    					// Returns bin
?>
```

### 重复字符串
str_repeat() 函数来把一个字符串重复指定次数。
	语法：string str_repeat( string input, int multiplier)
    第一个参数是要重复的字符串，
    第二个参数是要重复的次数，这个次数必须大于等于0。如果等于0，函数返回一个空字符串。

```php
<?php
	echo str_repeat("-", 30);   // prints 30 dashes
?>
```

### 字符串裁剪与填充
- trim() 清除字符串起始位置和末尾的空格（或其他字符）。第二个参数可选，称为`字符列表`，指定字符串的清除范围。
这个函数还可以裁剪数组值。

- ltrim() 清除字符串起始处的空格。

- rtrim() 清除字符串结束处的空格。
- chop()  清除字符串结束处的空格。

语法：
		string trim ( string str [, string charlist])
		string ltrim ( string str [, string charlist])
		string rtrim ( string str [, string charlist])
		string chop ( string str [, string charlist])

如果想删除其他字符，可以在第二个参数`charlist`里列出相应的字符，还可以利用符号`..`指定字符范围。

在没有设置第二个参数时，上述函数会清除的空白字符如下表所示：

|  空白字符  |  ASCII 值(十进制/十六进制)  |  含义             |
| ---------- | --------------------------- | ----------------- |
|   " "      |  32(0x20)                   |  普通空格         |
|   "\t"     |  9(0x09)                    |  制表符           |
|   "\n"     |  10(0x0A)                   |  新行（换行）     |
|   "\r"     |  13(0x0D)                   |  回车（回到行首） |
|   "\0"     |  0(0x00)                    |  NULL             |
|   "\x0B"   |  11(0x0B)                   |  垂直制表符       |

```php
<?php
	ltrim("\t\tHello\n");			// 删除字符串左侧的两个制表符
	trim("***Hello****", "*");		// 删除字符串里的所有 * 号
?>
```

- str_pad() 通过添加指定数量的字符来增加字符串的长度。默认操作是在字符串右侧填充空格。
语法：string str_pad (string input, int pad\_length [, string pad\_string [, int pad\_type]])
参数：
	- 第一个参数表示要填充的字符串
	- 第二个参数指定**填充后**字符串的长度
	- 第三个参数可选，指定进行填充的的字符
	- 第四个参数可选，指定填充的方式
		STR_PAD\_RIGHT    向右填充(默认值)
		STR_PAD\_LEFT     向左填充
		STR_PAD\_BOTH     两侧填充

### 查找和替换
- str_replace()  用指定字符串替换查找到的所有结果，然后返回替换之后的字符串(或数组)。
	如果指定了第三个参数，则第三个参数被赋值为搜索到的字符串数量。
- str_ireplace() 和上面的方法类似，只是不区分大小写。

语法：
	mixed str_replace (mixed search, mixed replace, mixed subject [, int &count])
	mixed str_ireplace (mixed search, mixed replace, mixed subject [, int &count])

```php
<?php
	$text = "Icecream is good for you. You should eat icecream daily.";
	$modified_text = str_replace("icecream", "broccoli", $text);
	// Icecream is good for you. You should eat broccoli daily.

	$imodified_text = str_ireplace("icecream", "broccoli", $text);
	// broccoli is good for you. You should eat broccoli daily.
?>
```

这两个函数不仅能够替换单个的字符串，还能同时替换多个字符串。

```php
<?php
	$text = "I love pizza and beer!";
	$search = array("love", "pizza", "beer");
	$replace = array("heta", "fruits", "vegetables");
	$modified_text = str_ireplace($search, $replace, $text);
	// I heta fruits and vegetables!
?>
```

### 字符定位
- strpos() 返回字符或子字符串在指定字符串里第一次出现的位置。这也可以被称为子串的索引位置(从 0 开始计数)。
	如果什么也找不到，函数就会返回布尔值 False。
	默认会从字符串的起始处开始查找；如果使用了第三个可选参数(一个数值)，函数就会从这个参数指定的位置处开始查找。
	不论从哪里开始查找，返回的位置计数都是从字符串起始处开始。
	语法：int strpos ( string, substring [, int offset])

- strrpos() 返回字符在指定字符串里最后一次出现的位置。(r 表示 rightmost)
	语法：int strrpos( string, character [, int offset])
- strripos() 和 strrpos() 类似，只是忽略大小写。
	语法：int strripos( string, character [, int offset])

### 字符串提取
子字符串（子串）就是一个字符串中的某部分。PHP 中有一组函数专门用于从字符串里提取子串。

|    函数          |    功能                                                            |
| ---------------- | ------------------------------------------------------------------ |
| strchr()         | 找到指定字符在字符串里第一次出现的位置，并且返回它及其后面全部字符 |
| strichr()        | 类似于 strchr()，只是不区分大小写                                  |
| strrchr()        | 找到指定字符在字符串里最后一次出现的位置，返回它及其后面的全部字符 |
| substr()         | 返回由指定位置开始、具有指定长度的子串                             |
| substr_replace() | 进行子串替换，返回替换后的整个字符串                               |
| substr_count()   | 统计子串在指定字符串里出现的次数                                   |

语法：
	- string strchr( string haystack, string needle)
	- string strichr( string haystack, string needle)
	- string strrchr( string haystack, string needle)
		这三个函数的第一个参数是原始字符串，第二个参数是子串的起始字符(单个字符？)，如果找不到这个字符，就返回 false。
	- string substr( string string, int start [, int length])
		第一个参数是原始字符串，第二个参数指定子串的起始位置，第三个参数可选，指定截取的子串的长度。
		如果第二个参数是整数，搜索操作会从字符串起点开始；如果是负数，则从字符串末尾开始。如果原始字符串长度小于或等于指定的起始位置数，函数会返回 false。
		如果没有指定第三个参数，子串会包含从搜索到的起始位置到字符串末尾的全部字符。如果是负数，则会从字符串末尾忽略相应数量的字符。
	- mixed substr_replace( mixed string, string replacement, int start [, int length])
		类似于 substr() 函数，但是返回的不是部分字符串，而是原始字符串被部分替换之后的结果。和 str_replace() 不同。
		子串被插入到字符串指定的位置，并且可以指定插入的长度。
		如果第一个参数是数组，那么返回的就是一个数组。
		第一个参数是原始字符串，第二个是替换字符串，第三个是替换字符串要插入的位置，第四个参数可选，可以是正数或负数。
		如果第三个参数为正，表示从字符串起始处开始计数；如果是负数，表示从字符串末尾开始计数。
		如果第四个参数为正，表示正在子串插入到原字符串时要覆盖多少个字符；如果是负数，表示插入新子串时要派出多少个字符。如果没有指定，则默认值就是原始字符串的长度。例如：如果插入位置为0，长度为4，表示原始字符串的前4个字符会被替换；如果长度为-2，表示不要替换字符串里的最后两个字符。
	- int substr_count( string, substring [, int offset [, int length]])
		返回字符串里特定子串的数量，区分大小写。
		第一个参数是搜索的字符串，第二个参数是要寻找的子串，第三个参数可选，用于指定开始查找的位置。第四个参数可选，表示从指定位置开始进行搜索的字符数。


```php
<?php
	$email = "joe@yahoo.com";
	$user_name = substr($email, 0, strpos($email, "@"));	// joe
	$domain = substr($email, strpos($email, "@") + 1);		// yahoo.com

	$str = "three jars of jam";
	echo substr_replace($str, "two", 0, 5);		// two jars of jam
	echo substr_replace($str, "jelly", -3);		// three jars of jelly
	echo substr_replace($str, "I made", 0, 0);	// I made three jars of jam
	echo substr_replace($str, " plum", -4, -4);	// three jars of plum jam
?>
```

### 特殊字符和字符串
**中括号与大括号语法**
在 PHP 5 之前，如果想从字符串里提取单个字符，需要把字符串当做数组来处理，并且使用中括号提取特定的字符。

使用中括号的问题在于，无法判断变量是代表一个数组还是一个字符串，在 PHP 5 里，利用中括号提取字符的语法被更换为使用大括号。

```php
<?php
	$car = "Honda";
	# < PHP 5
	echo $car[0];    // H
	echo $car[3];    // d
	# >= PHP 5
	echo $car{2};    // n
?>
```

**换行**
为了在浏览器里插入换行符，可以使用 HTML 的`<pre>`标记及`\n`转义序列，或是在 PHP 字符串里使用 HTML `<br>`标记。
还可以使用 PHP 内置的 nl2br() 和 wordwrap() 函数。
- nl2br() 在文本里每个新行前面插入一个`<br>`标记。利用这个函数可以迅速把一段纯文本转化为 HTML 代码。
	语法：string nl2br(string old_string)

- wordwrap() 使用字符串中断符(比如`<br>`或`\n`)让字符串在指定长度上自动换行。默认情况下，该函数会在第 75 列插入`\n`。
	语法：string wordwrap( string str [, int width [, string break [, bool cur]]])
	第一个参数是原始字符串，第二个参数是设置换行宽度，第三个参数是换行符号，第四个参数表示是否强制换行。
	如果指定了宽度，字符串就会在指定的列进行换行；如果单词太长了，该单词会被保留在换行符之前。
	默认换行符是`\n`，但是也可以指定其他的符号。如，在浏览器中查看的话，可以使用`<br>`。
	如果第四个参数是 true，那么字符串就会始终在指定的宽度上自动换行，也就是说如果单词长度超过行宽，它就会被强行拆分。

**ASCII 字符值**
- char() 函数以数字为参数，返回该值所代表的相应的 ASCII 字符。
	语法：string chr (int ascii)
- ord() 返回字符的 ASCII 值。
	语法：int ord (string char)

```php
<?php
	echo chr(66);	// B
	echo ord("B");  // 66
?>
```

**HTML 特殊字符处理**
HTML 里有一些具有特殊意义的字符，这些字符是以 HTML 实体表示的：以`&`符号开始，以分号结束。如果用户输入的文本包含这些特殊 HTML 字符，就可以使用函数 htmlspecialchars() 把大多数厂家的特殊字符转化为相应的 HTML 实体。如果需要转化全部的 HTML 字符实体， 就应该使用下一小结介绍的 htmlentities() 函数。
- htmlspecialchars()  把特殊字符转化为 HTML 实体。
	语法：string htmlspecialchars (string string [, int quote_style [, string charset]])
	第二个可选参数可以用来定义如何处理单引号或双引号，第三个可选参数可以定义字符集。
- htmlspecialchars_decode()  把特殊字符转化为字符。
- htmlentities() 把所有的可用的字符转化为相应的 HTML 实体。
	语法： string htmlentities( string string [, int quote_style [, string charset]])

会被 htmlspecialchars() 转化的特殊字符如下表：
|   字符   |   HTML 实体                        |
| -------- | ---------------------------------- |
|    &     |  &amp;                             |
|    "     |  &quot; 当 ENT_NOQUOTES 没有设置时 |
|    '     |  &#039; 仅当 ENT_QUOTES 被设置时   |
|    <     |  &lt;                              |
|    >     |  &gt;                              |

相应的引号常数如下：
|    常数名     |    含义                              |
| ------------- | ------------------------------------ |
|  ENT_COMPAT   | 转化双引号，保持单引号不变，默认模式 |
|  ENT_QUOTES   | 转化双引号和单引号                   |
|  ENT_NOQUOTES |双引号和单引号都不转化                |

**其他函数**
**strtr()** 这个函数根据一对一的对应关系，把一个字符串里的字符转换为其他字符，返回转换后的字符串(类似于 Unix 里的 tr 命令)。
	语法：string strtr( string str, string from, string to )
		  string strtr (string str, array replace_pairs )
	第一个参数是待处理字符串，第二个参数表示要被替换的字符串，第三个字符是用来替换的字符串。
	如果参数 from 和 to 指定的字符串长度不等，较长的字符串里的多余字符会被忽略。

```php
<?php
	echo strtr("aaacaa", "a", "b");    // bbbcbb
	echo strtr("aaacaa", "a", "bt");   // 依旧是 bbbcbb
?>
```

**addslashes() 和 addcslashes()** 这两个函数用来将字符串里的一些特殊字符利用反斜线实现转义。
	需要转义的字符有：单引号，双引号，反斜线，NULL。
	两者的区别在于，前者是直接用反斜线转义所有需要转义的字符，而后者可以指定需要转义的字符。
	这个功能还可以通过在 php.ini 文件里把指令 *magic_quotes_gpc* 设置为 "On" 实现（这也是默认设置）。这个设置会在全部的 GET、POST 和 COOKIE 数据里应用 addslashes()。
	语法：string addslashes(string str);
		  string addcslashes(string str, string charlist)

> 对于已经被 *magic_quotes_gpc* 转义过的字符串，就不要再使用 addslashes() 函数了。否则就会形成双重转义。

**stripslashes()** 用于去除引号前面的反斜线。如果字符串里有双斜线，就会变成单斜线。
		如果需要直接显示表单数据，而不是发送到数据库，这个函数就可以用于去除不必要的斜线了。
	同样，还有 stripcslashes() 函数。
	语法：string stripslashes ( string str )
		  string stripcslashes ( string str, string charlist )

```php
<?php
	$string = "She said, \"Don\'t do that.\"";
	echo stripslashes($string);   // She said, "Don't do that."
?>
```

**escapeshellarg()** 用单引号界定给定的参数，并为输入的参数中的单引号加上前缀(转义)。
    语法：string escapeshellarg ( string arguments )
    处理的效果是：当把 arguments 用这个函数处理之后传递给 shell 命令时，会将这个当做单个参数。
    这样就减少了攻击者利用 shell 命令参数伪装额外命令的可能性。

**escapeshellcmd()** 通过对字符串中的 shell 元字符进行转义来清理可能危险的输入。
    语法：string escapeshellcmd ( string command )
    这个函数和上面的 escapeshellarg() 的工作前提相同，都是为了执行 shell 命令时增加安全性。
    会被转义的字符包括：`#  &  ;  ,  |  *  ?  ~  <  >  ^  (  )  [  ]  {  }  $  \\` 。

-------------------------------------------------------------------------------

## 循环控制
`break`和`continue`是循环控制语句，分别用于提前终止循环和提前返回到测试条件。

- break   退出 for、foreach、while、do...while 循环，转到循环语句块右大括号后面的语句。
	可以接收一个数值参数，用于指定要退出多少层嵌套的循环结构。
	break n;  默认情况下，只跳出一层循环（即当前循环）

- countinue   把循环控制直接转到循环顶部，重新测试条件表达式。如果结果为真，就再次进入循环语句块。
	可以接收一个数值参数，御用指定它要忽略多少层嵌套的循环结构
	continue n;  默认情况下，只忽略一层循环（即当前循环）

-------------------------------------------------------------------------------

## 数组
PHP 中，数组是一种有序映射，就是把值映射到关键字，简单说，数组就“关键字-值”对的集合。

关键字（被称为索引）可以是数字或字符串，或者是他们的组合，用于标识数组里相应的值。
- 如果关键字是数字，则代表值在数值里的位置（通常是从位置0开始，逐个加1），被称为数字数组。
- 如果关键字是字符串，或数字和字符串的混合，则字符串就和对应的值形成关联，被称为关联数组。

数组里的值被称为元素，可以定义为任意数据类型，甚至是混合型。

在访问数组里的元素时，PHP 是用了一个内部指针追踪当前元素的位置，并且提供了内置函数来移动数组指针。
- end() 函数可以移动到数组末尾
- reset() 回到数组开头

### 创建数组
|    函数         |    功能                                            |
| --------------- | -------------------------------------------------- |
| array()         | 创建一个数组                                       |
| array_combine() | 以一个数组的值为关键字，另一个数组的值为新数组的值 |
| array_fill()    | 用值填充一个数组                                   |
| array_flip()    | 将数组元素的值与索引互换                           |
| array_pad()     | 用一个值把数组填充到指定长度                       |
| compact()       | 创建包含变量及其值的数组                           |
| range()         | 用一定范围内的元素，按照特定步长创建一个数组       |

array() 这是一个 PHP 内置的构造器函数，其参数就是要构成数组元素的“关键字-值”对。
	语法：array array(value1, value2, value3 ...)
		  array array(key1=>value2, key2=>value2 ...)
	如果没有指定关键字，则 PHP 会自动为它分配一个数字（从0开始，依次加1）。
	如果指定了关键字，那么关键字和值直接都要用`=>`符号连接。

array_fill()  可以在声明数组的同时填充数组的值。
	语法：array array_fill (int start\_index, int num, mixed value);
	三个参数分别表示：起始索引，数组中的元素数量（必须大于0），每个元素的默认值。

```php
<?php
	// 创建一个数组，起始索引为 0， 长度为 5，每个元素的值都是 'to be defined'
	$names = array_fill(0, 5, "to be defined");

	// 创建一个数组，起始索引为 5，长度为 10，每个元素的值都是 0
	$nums = array_fill(5, 10, 0);
?>
```

array_flip() 将数组每个元素的值与其相应的索引互换位置，组成一个新数组返回。
    语法：array array_flip ( array input\_array )

```php
<?php 
	$state = array("Delaware", "Pennsylvnia", "New Jersey");

	$state = array_flip($state);
	print_r($state);
	// Array ( [Delaware] => 0, [Pennsylvnia] => 1, [New Jersey] => 2 )
?>
```

range() 这个函数从低到高创建一个包含连续整数或字符值的数组。
	语法：array range (mixed low, mixed high [, number step]);
	三个参数分别表示：起始值、结束值和增量。增量默认情况下是 1(或 -1)。

```php
<?php
	range(1, 10);          // array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
	range(10, 1);          // array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1)
	range(1, 10, 2);       // array(1, 3, 5, 7, 9)
	range(15, 0, -5);      // array(15, 10, 5, 0)
	range('a', 'c');       // array('a', 'b', 'c')
?>
```

### 数组标识符 []  
数组标识符也能用于创建一个数组，并赋值。
	语法：$array_name[] = value;
		  $array_name[key] = value;
	如果把一个值赋予数组，而没有在中括号中指定索引，PHP 会自动创建一个数字索引，索引值为上一个`非负数`数字索引值加 1，如果之前没有`非负数`数字索引，则索引值为 0。
	如果提供了一个起始或末尾索引值，PHP 会创建一个包含这个命名元素的数组，但不会填充数组中缺少的元素。比如，$name[4] = "Tommy" 会创建一个单元素数组，数组的大小是1，并且索引0、1、2、3不存在。
	可以为索引指定为一个负数。

```php
<?php
	$color[] = 'red';
	$color['b'] = "blue";
	$color[] = "green";
	print_r($color);       // Array ( [0] => red [b] => blue [1] => green ) 

	$name[10] = "Tommy";	// 一个单元素数组，索引从 10 开始。
	$name[] = 'Jack';		// 这个元素的索引是 11。
	print_r($name);         // Array ( [10] => Tommy [11] => red ) 

	$cars[] = "Ford";		// 索引为 0
	$cars[5] = 'Honda';     // 索引为 5
	$cars[] = "BMW";        // 索引为 6
	print_r($cars);			// Array ( [0] => Ford [5] => Honda [6] => BMW ) 

	$book[-2] = "PHP 自学";    // 索引为 -2
	$book[] = "Morden PHP";    // 索引为 0
	print_r($book);            // Array ( [-2] => PHP 自学 [0] => Morden PHP )
?>
```

### 循环显示数组
为了显示数组的元素，可以使用 for、while、foreach 三种循环。

使用 froeach 循环时，语法是：`foreach ( $array_name as $key=>$value )`。也可以不指定 key，只调用 value。

> 注意：foreach 循环并非操作数组本身，而是操作数组的一个备份。也就是说，在 foreach 循环中，对 value 的操作，并不会反映到原数组中。
为了操作数组本身，可以使用值引用，或者在循环里，直接对元素中心相应的索引对应的数据进行修改。

使用值引用方式修改元素值的语法为：`foreach ( $array_name as &$value )`。此时，对值的操作就会反映到原数组中。

### 检查数组是否存在
|    函数              |    功能                              |
| -------------------- | ------------------------------------ |
| array_key\_exists()  | 检查数组里是否存在指定的关键字或索引 |
| in_array()           | 检查数组里是否存在指定的值           |
| is_array()           | 检查变量是不是数组，返回 true、false |

语法：
	- bool is_array( array $array\_name )
	- bool in_array( mixed value, array $array\_name)
	- bool array_key\_exists( mixed key, array $array\_name)

### 数组和字符串
|    函数     |    功能                                        |
| ----------- | ---------------------------------------------- |
|  explode()  | 基于指定分隔符分解字符串，创建一个字符串数组   |
|  implode()  | 利用指定分隔符把数组值连接在一起创建一个字符串 |
|  join()     | 同 implode()                                   |
|  split()    | 根据正则表达式把字符串分解为数组               |

语法：
	- string implode( string glue, array elements )
	- array explode ( string separator, string string [, int limit])
	 	如果指定了第三个参数，那么新生成的数组最多只能有 limit 个元素，最后一个元素包含字符串剩余的所有字符。

### 数组大小
|    函数                |    功能                                                  |
| ---------------------- | -------------------------------------------------------- |
|  array_count\_values() | 返回一个数组，包含另一个数组中不同的值及每个值出现的次数 |
|  count()               | 返回数组中元素的数量，或是对象里属性的数量               |
|  sizeof()              | 与 count() 相同                                          |

count()
	语法：int count( array array_name [, COUNT\_RECURSIVE] )
	如果只有一个参数，则直接返回数组中的元素是数量（当做一维数组来处理）。
	如果传入的第二个参数是`COUNT_RECURSIVE`或是 1，则用递归的方式统计数组中的所有元素的数量。此时会将数组中的每个元素作为一个整体算作一个元素，而且会继续进入子元素的元素中去计算。

> sizeof() 是 count() 函数的别名。

```php
<?php 
	$gardn = array("cabbage", "peppers", 'carrots');
	echo count($gardn);        // 4

	$locations = array("Italy", "Amsterdam", array("Boston", "Des Moines"), "Miami");
	echo count($locations, 1); //6
?>
```

array_count\_values()
	语法：array array_count\_values( array array\_name )
	这个函数将数组里不同值作为索引，将该值出现的次数作为值，组成一个数组返回。

### 提取关键字和值
**array_keys()** 这个函数返回数组的全部关键字作为值组成的数组。
	语法：array array_keys ( array input [, mixed search\_value [, bool strict]])
	如果指定了第二个参数，则可以获取指定值的关键字。

**array_values()** 这个函数返回元素数组里的全部值组成的数组。
	语法：array array_values( array input )
	新得到的数组以数字作为索引。

**key()** 这个函数返回数组中当前指针所在位置的键。
    语法：mixed key ( array input_array )
    每次调用 key() 时，并不会移动数组指针。

**current()** 这个函数返回数组中当前指针所在位置的数组值。
    语法：mixed current ( array input_array )

**next()** 将数组指针移动到紧接近着放在当前数组指针的`下一个`位置的数组元素，并返回这个元素的值。
    语法：mixed next ( array input_array )

**prev()** 将数组指针移动到紧接近着放在当前数组指针的`前一个`位置的数组元素，并返回这个元素的值。
    语法：mixed prev ( array input_array )

**reset()** 将数组指针设置回数组的开始位置(第一个元素)，并返回第一个元素的值。
    语法：mixed reset ( array input_array )

**end()** 将数组指针设置回数组的结束位置(最后一个元素)，并返回最后一个元素的值。
    语法：mixed end ( array input_array )

```php
<?php 
	$fruits = array("apple", "orange", "banana");
	echo current($fruits);		// apple
	echo next($fruits);	        // orange
	echo next($fruits);	        // banana

	echo prev($fruits);         // orange

	echo reset($fruits);        // apple
	echo end($fruits);          // banana
?>
```

**each()** 这个函数返回数组里当前的“关键字-值”对，并且向后移动一个元素，让其变成当前值。
	语法：array each( array input_array )
	返回值是一个数组，其中的两个元素分别代表关键及其对应的值。这两个值可以用数字索引 0、1 分别访问，也可以用 key、value 索引访问。
	不断的使用这个函数访问一个数组，就能给达到数组的末尾。如果想再次访问数组，可以用 reset() 函数重置数组指针。
	如果执行这个函数时，数组已经到达末尾，则返回 false。

```php
<?php
	$colors = array('red', 'green', 'blue', 'yellow');
	while ($array = each( $colors )) {
		echo $array['key'] . " => " . $array['value'] . "<br>";
		# 也可以用如下方式访问
		// echo $array[0] . " => " . $array[1] . "<br>";
	}
	# 输出：
	// 0 => red
	// 1 => green
	// 2 => blue
	// 3 => yellow
?>
```

### 从数组元素创建变量
list() 这个函数从`数字数组`提取元素，把数组值赋予单个变量。
	语法：list( mixed varname1 , mixed varname2 ... ) = $array_name;
	list() 函数尾语赋值操作符的左侧，其参数是一组以逗号分隔的变量名，这些变量对应于赋值操作符右侧的数组元素。
	如果变量的数量少于数组元素，多余的数组元素会被忽略。
	如果变量的数量多于数组元素，则会报错。

> 在遍历关联数组时，list() 和 each() 函数能够很好的配合工作。

```php
<?php
	$colors = array('red', 'green', 'blue', 'yellow');
	list($a, $b) = $colors;    // 创造两个变量，$a 和 $b，值分别为 red 和 green

	$books = array('Title' = > 'War and Peace', 
					'Author' => 'Tolstoy', 
					'Publisher' => 'Oxford University Press'
				  );
	while (list($key, $val) = each($books)) {
		echo "$key => $val<br>";
	}
	# 输出
	// Title => War and Peace
	// Author => Tolstoy
	// Publisher => Oxford University Press
?>
```

extract() 这个函数从`关联数组`中提取变量，将关键字作为变量名，值作为变量值。
	语法：int extract ( array var_array [, extract\_type [, string prefix]] )
	如果局部变量与关联数组里的关键字有相同的名称，就会产生冲突。默认的行为是覆盖现有变量的值。
	如果关联数组里的关键字不是有效变量名，这个函数就不会将其生成变量导入。
	第二个参数影响这个函数如何产生变量；第三个参数可以为生成的变量添加前缀。

extract_type 参数有如下可选值：
|    提取类型             |    功能                                              |
| ----------------------- | ---------------------------------------------------- |
| EXTR_IF\_EXISTS         | 如果变量存在就覆盖它，否则就什么也不做               |
| EXTR_OVERWRITE          | 如果有冲突，就覆盖现有变量                           |
| EXTR_PREFIX\_ALL        | 为全部变量名称添加前缀                               |
| EXTR_PREFIX\_IF\_EXISTS | 只有在无前缀变量有现有变量重名时才创建有前缀的变量名 |
| EXTR_PREFIX\_INVALID    | 只对无效或数字变量名添加前缀                         |
| EXTR_PREFIX\_SAME       | 如果存在冲突，为变量名添加前缀                       |
| EXTR_REFS               | 以引用方式提取变量                                   |
| EXTR_SKIP               | 如果存在冲突，不要覆盖现有变量                       |

### 数组排序
|    函数    |    功能                                             |
| ---------- | --------------------------------------------------- |
| array_multisort() | 对多个数组或多维数组进行排序                 |
| arsort()          | 以逆序对数组排序，并且保持索引关联           |
| asort()           | 对数组排序，并且保持索引关联                 |
| krsort()          | 按照关键字以逆序对数组排序                   |
| ksort()           | 按照关键字对数组排序                         |
| natcasesort()     | 使用不区分大小写的自然顺序算法对数组排序     |
| natsort()         | 使用自然顺序算法对数组排序                   |
| rsort()           | 以逆序对数组排序                             |
| shuffle()         | 搅乱数组                                     |
| sort()            | 对数组排序                                   |
| uasort()          | 根据自定义比较函数对数组排序，并保持索引关联 |
| uksort()          | 用自定义比较函数根据关键字对数组排序         |
| usort()           | 用自定义比较函数根据元素值对数组排序         |

语法：bool sort_function ( array input [, int sort\_style] )

调整排序行为的参数（可选参数）：
|    标记        |     功能             |
| -------------- | --------------------- |
| SORT_LOCALE\_STRING | 基于当前地区把元素值当做字符串进行比较 |
| SORT_NUMERIC        | 把元素值当做数值进行比较               |
| SORT_REGULAR        | 按照普通方式比较元素值（不改变其类型） |
| SORT_STRING         | 把元素值当做字符串进行比较             |

sort() 函数对数组按字母顺序排序。在指定特殊参数时，也可以按数值排序。这个函数应该用于对数字数组进行排序。
	当数组被排序之后，所以会被重置。排序成功就返回 true，否则返回 false。
asort() 这个函数排序之后，索引会被保持，所以可以用于关联数组排序。

usort() 这个函数可以使用用户自定义的函数来进行排序。
    语法：void usort(array input_array, callback function )
    回调函数有两个参数，分别代表前一个数组元素值，和后一个数组原始值。如果回调函数返回`负整数`，则第一个元素排在第二个元素`后面`；如果返回`0`则表示两个元素相等，可以随意前后；如果返回`正整数`，则第一个元素排在第二个元素`前面`。

### 随机处理数组
array_rand() 从数组中选择一个或多个随机关键字或索引。
	语法：mixed array_rand ( array input [, int number] )
	如果指定了第二个可选参数，就可以指定从数组里挑选多少个随机关键字，从而返回一个包含指定数量的随机关键字组成的数组。

shuffle() 这个函数让`数字数组`变得随机化。也会随机化`关联数组`的元素值，但同时也会破坏关键字。
	语法：bool shuffle ( array input )

### 删除和添加数组及其元素
|    函数         |    功能                                  |
| --------------- | ---------------------------------------- |
| array_pop()     | 删除数组的最后一个元素，并返回该元素的值 |
| array_shift()   | 删除数组的第一个元素，并返回该元素的值   |
| array_unique()  | 删除数组里重复的元素                     |
| unset()         | 删除整个数组                             |
| array_push()    | 向数组末尾添加新元素（一个或多个）       |
| array_splice()  | 在数组任意位置删除和/或添加元素          |
| array_unshift() | 在数组起始位置添加新元素（一个或多个）   |

array_pop() 删除数组的最后一个元素，并返回该元素的值。同时让数组长度减一如果数组是空，则返回 NULL。这个函数执行之后，会重置数组指针。

array_shift() 和 array\_pop() 函数类似，只是其删除的是第一个元素。

array_unique() 函数从数组里删除重复的值，并且返回一个不包含重复值的数组。
	它首先把元素值当做字符串进行排序，然后记录每个相同值的第一个关键字，最后忽略重复值的其他关键字。
	只有两个元素是等同的，才会被当做相等的。

array_unshift() 和  array\_push() 返回被添加元素是数量。
	语法：int array_unshift (array &array, mixed var [, mixed var2 ... ] )
		  int array_push (array &array, mixed var [, mixed var2 ... ] )

array_splice() 这个函数可以从数组中的某个位置开始，删除数组中的一部分元素，还可以用新元素替换被删除的元素。
	语法：array arrary_splice( array $input, int offset [, int length [, array replacement]] )
	第一个参数是进行操作的数组；
	第二个参数是数组中开始删除元素的位置（从 0 开始计数）。为负数时，则从数组的末尾开始计算（末尾是 -1）。
	第三个参数表示要删除的数量，如果不指定，则会删除从指定位置开始到数组末尾的全部元素。
	第四个参数用于指定替换值。

### 拷贝数组的元素
array_slice()  从数组的指定位置处，提取指定数量的元素，用提取的元素组成一个新数组，并重置关键字/索引值，然后返回这个新数组。
	语法：array array_slice( array input, int offset [, int length [, bool preserve\_keys]] )
	第一个参数是原始数组；
	第二个参数是提起的起始位置。如果是负数，则从数组末尾开始计算。
	第三个参数指定提取的元素的个数。如果不指定，则提取从指定位置到数组末尾的所有元素。
	第四个参数如果为 true，则不会重置索引值。

### 结合与合并数组
array_combine() 返回一个由关键字与值组成的数组。新数组的关键字来源于第一个数组参数，与这些新关键字对应的值来自于第二个数组。
	语法；array array_combine ( array keys, array values )
	如果作为参数的两个数组的长度不同，则会返回 false。

array_merge() 把两个或多个数组合并在一起，返回单个数组。每个数组的元素都附加到前一个数组之后。
	语法：array array_merge ( array array1 [, array array2 ...] )
	如果数组元素具有相同的关键字，前一个数组的这个关键字对应的值会被后面的覆盖。
	然而如果数组包含数字索引，后面的值就不会覆盖前面的值，而是索引增大，附加在其后。
	如果只指定了一个数组，而且他是以数组为索引的，索引就会被重新排序。

array_merge\_recursive() 这个函数以递归的方式合并两个或多个数组，把一个或多个数组的元素合并在一起，让一个值附加到前一个值之后，最终返回得到的这个数组。
	语法：array array_merge\_recursive ( array array1 [, array arrray2 ... ] )
	如果在合并两个关联数组时，它们具有相同的关键字，但对应的值不同，这个函数会把两个值都合并到一个数组里，作为这个关键字的对应的值。
	如果数组具有相同的数字索引，后一个值不会覆盖前面的值，也不会合并到一个数组，而是索引会增加。

```php
<?php
	$cyclewear1 = array('item'=>'jersey', 'color'=>'blue', 'type'=>'hooded');
	$cyclewear1 = array('size'=>'large', 'color'=>'white', 'cost'=>'145');
	$merged = array_merge_recursive($cyclewear1, $cyclewear2);
	print_r($merged);
	# 返回值
	// Array ( [item] => jersey 
	//		   [color] => Array ( [0] => blue [1] => white ) 
	//		   [type] => hooded 
	//		   [size] => large 
	//		   [cost] => 145 ) 
?>
```

### 数组集合
**array_intersect() 数字数组的交集** 返回一个保留了键的数组，这个数组只由：在输入的每个数组中都出现的值组成。
    语法：array array_intersect ( array array1, array array2 ... )
    这个函数需要至少两个数组参数。
    其进行比较的是数组元素的值，而且元素的值是等同(===)时才会被认为是相同。

```php
<?php 
	$array1 = array("OH", "CA", "NY", "HI", "CT");
	$array2 = array("OH", "CA", "HI", "NY", "IA");
	$array3 = array("TX", "MD", "NE", "OH", "HI");
	$intersection = array_intersect($array1, $array2, $array3);
    print_r($intersection);
    // Array ( [0] => OH, [3] => Hi )
?>
```

**array_intersect\_assoc() 关联数组的交集** 和上一个函数基本相同，只是其进行比较的时候，需要整个元素的`键-值对`整体等同(===)才算相同。
    语法：array array_intersect\_assoc ( array array1, array array2 ...)
    返回的数组中，保留了整个键值对。

**array_diff() 数字数组差集** 返回一个数组，这个数组由：在第一个数组中出现，而没有在其他的数组中出现的值组成。与 array\_intersect() 函数相反。
    语法：array array_diff ( array array1, array array2 ... )
    至少需要两个数组参数。
    进行比较的是数组的元素的值，而且元素的值使用`!==`来比较。

**array_diff\_assoc() 关联数组的差集** 和上一个函数基本相同，只是比较的时候，需要整个元素的`键-值对`整体等同(===)才算相同，否则就算不同。
    语法：array array_diff\_assoc ( array array1, array array2 ... )
    返回的数组中，保留了整个键值对。

### 用函数处理数组值
**array_walk()** 将数组中的每个元素传递到用户自定义的函数中。（类似于 JavaScript 中的 map() 函数。）
    语法：bool array_walk ( array &array, callback function [, mixed userdata] )
    第一个参数是要操作的数组。
    第二个参数是要操作数组元素的函数，这个函数可以有两参数：第一个表示数组的当前元素值，第二个表示当前元素索引。如果调用 array_walk() 函数的时候，传递了 userdata，则其值会作为 function 函数的第三个参数。
    第三个参数是用户自定义的数据，会作为第三个参数传入回调函数中。

### 其他数组函数
**array_sum() 数组求和** 将数组的所有元素的值都按数学相加在一起，返回最终的和。
    语法：mixed array_sum (array input\_array )
    如果数组中包含其他类型(如字符串)的数据，这些值将会被转成数值，或忽略。

```php
<?php 
	$grades = array(42, "hello", 28);
	$total  = array_sum($grades);
	echo $total;    // 70
?>
```

**array_chunk 数组分解** 将数组分解称为一个多维的数组，该数组由多个包含指定个元素的数组所组成。
    语法：array array_chunk ( array input\_array, int size [, bool preserve\_keys] )
    将数组 input_array 的元素按照 size 个元素一组分解，并按顺序组成一个新的多维数组，然后返回这个数组。
    如果无法按照 size 均匀的划分(数组长度不能被 size 整除)，则数组的最后一个元素的数组中，包含的元素将少于 size。
    如果第三个参数 preserve_keys 参数是 true，则将会保持各个值所对应的键。如果忽略改参数，或为假，则会使每个数组的数值的索引从 0 开始。


### 数组操作符
|    操作符    |  功能  |    含义                                |
| ------------ | ------ | -------------------------------------- |
| $a + $b      | 联合   | 联合 $a 和 $b                          |
| $a == $b     | 相等   | 如果 $a 和 $b 具有相同的“关键字-值”对，<br>结果就是 true |
| $a === $b    | 等同   | 如果 $a 和 $b 具有相同的“关键字-值”对，<br>且有相同的次序和数据类型，结果就是 true |
| $a != $b     | 不相等 | $a 不等于 $b 时为 true                 |
| $a <> $b     | 不相等 | $a 不等于 $b 时为 true                 |
| $a !== $b    | 不等同 | $a 不等同于 $b 时为 true               |

+ 联合操作符会连接两个数组：操作符右侧的数组被连接到操作符左侧的数组。
	如果具有相同的关键字，左侧数组的关键字对应的值不会被覆盖。

```php
<?php
	$colors = array('R'=>'red', 'G'=>'green', 'B'=>'blue');
	$shades = array('G'=>'gray', 'BL'=>'black', 'W'=>'white');

	$combo = $colors + $shades;
	print_r($combo);
?>
```

-------------------------------------------------------------------------------

## 函数
自定义函数可以在文件的任意位置进行声明。他们被放置在标记`<?php  ?>`之间，并且只能被其定义时所在的脚本调用。
函数可以在其定义位置之前或之后调用，这是因为 PHP 在执行脚本里的语句之前，会在内部编译全部函数定义。

### 参数
函数在定义时，可以指定一些参数，在调用的时候，就需要传入这些参数，以便函数体进行操作。

在定义时指定的变量，被称为**参数**。
在调用函数时传入的变量，被称为**变元**。

变元可以是数字、字符串、引用、变量等。函数是以相应的参数的对应值来接受他们。
当函数退出时，这些变量就消失了。

PHP 并不检查发送到函数的变元个数来确保他们与函数定义中指定的参数相匹配。
比如，发送了三个变元，而函数定义中只有两个参数，那么第三个参数就好被忽略；如果发送了两个变元，而参数列表中有三个，就好产生一个警告，而参数保持没有设置的状态。

### 按值传递
按值传递变元的时候，PHP 会建立变量的拷贝。一般情况下，都是按值传递的。
此时，在函数内如果改变了变元的值，那么所改变的只是拷贝。当函数退出的时候，拷贝就会被清除。

### 按引用传递
按引用方式传递变量时（只有变量能够使用这种方式），函数内部对变量的改变会影响变量原始值。
在这种情况下，传递过去的不是复制值，而是对原始变量的引用。

在传递大型字符串、数组或对象时，与把全部值拷贝到参数列表相比，这种方式开销比较少。

### 默认参数
如果函数传入的变元少于定义时参数的数量，就会产生警告。
而我们可以在函数定义时，预先给参数设置一个值就可以避免这种情况。
这样，即便没有传入相应的变元，那么也会有一个默认值可以使用。

> 注意：
	- 没有默认值的参数必须位于赋予默认值的参数之前；
	- 只有像字符串或数字这样的基本值才能作为默认值，而不能使用变量；
	- 如果调用时，传入了相应的变元，默认参数的值就会被忽略。

### 变元数量
如果函数变元的数量不定，我们可以使用 PHP 的一些内置函数来确定实际传递了多少个变元。

|    函数               |    功能                        |
| --------------------- | ------------------------------ |
| func_num\_args()      | 返回传递给函数的变元个数       |
| func_get\_arg($index) | 返回指定索引位置上的变元       |
| func_get\_args()      | 返回一个数组，其中包含全部变元 |

### 动态函数调用
和动态变量（可变变量）一样，函数也能够动态的进行调用。
可以把函数名当做字符串赋予给一个变量，然后像使用函数名本身那样使用变量。

```php
<?php
	function fn_name () {
		echo "Something ... ";
	}
	$var_fn = "fn_name";
	$var_fn();		// 这样就能调用 fn_name() 函数了
?>
```

### 回调函数
函数能够把另一个函数（被称为“回调函数”）当做它的变元。此时，只需要将函数的名字当做`字符串`传进去就行了。

### 嵌套函数
在一个函数中，还能定义另外的函数。外层函数也被称为父元素，它必须先被调用，才能让嵌套函数称为可用的。

一旦外层函数（父函数）被执行了，嵌套函数就会被定义，然后就可以像其他函数一样在当前程序的任意位置被调用了。
如果父函数包含嵌套函数，那么父函数就`只能被执行一次`，否则就会产生一个严重错误。

### 要求与包含
可通过 include() 或 require() 把其他的文件包含到当前脚本，类似于把文件内容黏贴到执行这两个函数的位置。
在把函数放到外部文件里时，一定要确定把函数包围在 PHP 标记里，否则会被当做 HTML 文档进行处理。

为了找到要包含的文件，PHP 会搜索 php.ini 文件里定义的 include_path 路径，如果文件不在这个路径里，就会产生错误。

**区别**
这两个函数的区别有两点：
    首先在处理错误方面：
	    - 出现错误时，include() 会生成一个警告，并允许后面的脚本继续执行。
	    - 出现错误时，require() 会产生一个严重错误，阻止脚本的继续执行。
	其次，在处理文件包含方面：
	    - 无论 require() 的位置如何，指定文件都将被包含到出现了 require() 脚本文件中。比如，即使 require() 放在条件计算为假的 if 语句中，被调用的文件依然会被包含到这个脚本中。
	    - 而 include() 则不是，其会根据条件是否满足来加载文件。

include_once() 和 require\_once() 类似于 include 和 require()。

-------------------------------------------------------------------------------

## 表单
HTML 表单提交的方式主要有两种：get 和 post。当然，还有其他的方法，比如 update、delete、put 等。

PHP 可以通过超全局数组变量`$_SERVER['REQUEST_METHOD']`访问所使用的请求方法。

由于有效的 PHP 变量名称中不能以数字开头，不能含有空格，
所以当 HTML 表单中的字段名称中有空格或句点的时候，PHP 会用**下划线**代替他们，从而形成有效的变量名。

**出于安全考虑**，应该通过某个能够实现数据安全性检查的函数（如`htmlentities()`函数）对用户提供的数据进行过滤。
如：$email = htmlentities($_POST['email']);

### $_REQUEST 数组
在 PHP 的超全局变量中，`$_GET`数组和`$_POST`数组分别包含了通过 get 方式和 post 方式提交进来的数据。
而`$_REQUEST`数组则包含了`$_GET`数组、`$_POST`数组和`$_COOKIE`数组的所有信息。
但也正因为此，从`$_REQUEST`数组中获取数据并不一定安全，会有关键字重叠发生。

### 自处理 HTML 表单
把包含表单的 HTML 文档和处理表单的 PHP 脚本组合到一个脚本里，这就被称为自处理 HTML 表单。
具体做法是：把`<form>`标记的`action`属性的值设置为`$_SERVER['PHP_SELF']`。

### 处理具有多选项的表单
一般情况下，HTML 表单中的 text、textarea、radio 元素的值，都可以直接用其 name 从相应的 $_GET 或 $POST 数组中获得。

而对于 select(设置了 multiple 属性) 和 checkbox 元素来说，则需要在其名称后面加上`[]`来命名，
然后 PHP 会将其作为数字数组，可以通过遍历获取其值。

如果没有在其名称后面加上中括号，那么 PHP 不会将其作为数组处理，而是用最后一个值覆盖前面的值，类似于一个单值表单元素。

```html
<?php 
	if (isset($_POST['submit'])) {
		print_r($_POST);
		exit();
	}
?>

<form method="post" action="<?php $_SERVER['PHP_SELF'] ?>">
	<input type="checkbox" name="name[]" value="1" checked="checked">李雷
	<input type="checkbox" name="name[]" value="2">韩梅梅
	<input type="checkbox" name="name[]" value="3">lucy
	<input type="submit" value="submit" name="submit">
</form>

<!-- 选择了多个选项，比如全选，提交之后，将会输入如下内容 -->
<!-- Array ( [name] => Array ( [0] => 1 [1] => 2 ) [submit] => submit ) -->
<!-- 此时，即便只选择了一个选项，也是被当做数组处理 -->
<!-- Array ( [name] => Array ( [0] => 1 ) [submit] => submit ) -->
```

### 图像按钮
HTML 中还可以使用图像按钮替代提交按钮。此时点击这个图像按钮就和点击提交按钮的效果是一样的。

当用户点击图像按钮时，相应的表单内容就就会被发送到服务器，同时，用户在图像上点击的位置也会被发送。
也就是在 PHP 脚本里，可以用两个变量获取点击的像素坐标（相对图像按钮的左上角）：`[image_name]_x`和`[image_name]_y`。
> 这里的 [image_name] 就是图像按钮的 name 属性的值。在下面的示例代码中，就是`submit_x`和`submit_y`。
浏览器发送的实际的变量名其实是包含一个句点(`submit.x`)而不是下划线，但是 PHP 会自动处理成下划线。

```html
<input type="image" src="path/to/image.png" name="submit">
```

### 文件上传
通过表单上传文件的时候，需要：
    - 使用 file 类型的 input 控件
    - from 元素的 enctype 属性需要设置为`multi-part/form-data`（默认是`application/x-www-form-urlencoded`）
    - method 属性应该设置为`post`

> 可以在表单中设置一个`MAX_FILE_SIZE`隐藏字段，指定会被接收的最大字节数量。但是这个只是建议性质的，不能大于 php.ini 文件中定义的`upload_max_filesiz`的值（默认是 2MB）。还要注意，这个字段需要放在文件输入字段之前。

```html
<form action="PHPscript.php" method="post" enctype="multi-part/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000">
	<input type="file" name="uploadfile">
</form>
```

默认情况下，文件会被保存到服务器的默认临时目录中，除非 php.ini 文件里的`upload_tmp_dir`指定了其他的位置。
之后，可以使用内置的`move_uploaded_file()`函数把上传文件永久的保存到另外指定的位置中。

**php.ini 配置**
有一些配置质量可用于精细地调节 PHP 的文件上传功能。
这些指令用来确定是否启用 PHP 的文件上传、可允许的最大上传文件大小、可允许的最大脚本内存分配和其他各种重要的资源基准。

1. file_uploads=On/Off
    作用域：PHP_INI\_SYSTEM
    默认值：1
    这个指令确定服务器上的 PHP 脚本是否可以接受文件上传。

2. max_execution\_time=interger
    作用域：PHP_INI\_ALL
    默认值：30
    确定 PHP 脚本在注册一个致命错误之前可以执行的最长时间，`以秒为单位`。

3. memory_limit=interger M
    作用域：PHP_INI\_ALL
    默认值：8M
    设置脚本可以分批的最大内存量，`以 MB 为单位`。
    注意，设置中，整数值后必须跟一个 M 才能正常起作用。
    这个指令可以防止失控的脚本独占服务器内存，甚至造成服务器崩溃。

4. upload_max\_filesize=interger M
    作用域：PHP_INI\_SYSTEM
    默认值：2M
    确定上传文件的最大大小，`以 MB 为单位`。
    此指令必须小于 post_max\_size，因为它只应用于通过 file 输入类型传递的信息，而不应用于所有通过 POST 示例传递的信息。
    整数值后面需要有一个 M。

5. upload_tmp\_dir=string
    作用域：PHP_INI\_SYSTEM
    默认值：NULL
    上传文件在处理之前必须成功地传输到服务器中，所以必须指定一个位置，用于临时放置这些文件。
    默认情况下，上传文件放在服务器的默认临时目录中。

6. post_max\_size=string
    作用域：PHP_INI\_SYSTEM
    默认值：8M
    指定通过 POST 方法可以接受的信息的最大大小，`以 MB 为单位`。
    通常情况下，这个值应该比 upload_max\_filesize 大，因为除了上传的文件之外，还可能传递了其他的表单域。
    整数值后面需要有一个 M。

**$_FILES[]**
当文件被发送到服务器时，PHP 会把全部上传文件的信息都保存到超全局数组 $_FILES[] 中。
这是一个二维数组：第一维用于保存文件输入表单的 name 属性值；第二维用于保存这个文件的相应信息。

|     数组                        |    描述                                    |
| ------------------------------- | ------------------------------------------ |
| $_FILES['userfile']['name']     | 文件在客户计算机上的原始名称               |
| $_FILES['userfile']['type']     | 文件的 MIME 类型(如果浏览器提供了这个信息)<br>比如 "image/gif" |
| $_FILES['userfile']['size']     | 上传的文件的大小，以字节为单位             |
| $_FILES['userfile']['tmp_name'] | 服务器保存上传文件所用的临时文件名         |
| $_FILES['userfile']['error']    | 与上传文件相关的错误代码                   |

$_FILES['userfile']['error']  总共有 5 个不同的返回值，其中一个表示成功的结果，另外 4 个表示在尝试中出现的特定的错误。
  - UPLOAD_ERR\_OK         0  上传成功则返回 0
  - UPLOAD_ERR\_INI\_SIZE  1  如果试图上传的文件大小超出了 upload\_max\_filesize 指令设置的值，则返回 1
  - UPLOAD_ERR\_FORM\_SIZE 2  如果试图上传的文件大小超出了 max\_file\_size 指令设置的值(可能嵌入在 HTML 表单)，则返回2。
  - UPLOAD_ERR\_PARTIAL    3  如果文件没有完全上传，则返回 3。如果出现网络错误导致上传中断，就会发生这种情况。
  - UPLOAD_ERR\_NO\_FILE   4  如果用户没有指定上传的文件就提交了表单，则返回4.

> userfile 是在 HTML 表单中 file 元素的 name 属性设置的值。

**确定是否为上传文件**
is_uploaded\_file()  这个函数用来确定参数指定的文件是否是用 POST 方法上传的。
    语法：bool is_uploaded\_file ( string filename )
    在操作上传文件之前，先确认下该文件是否为上传到服务器的文件，可以防止潜在的攻击者对原本不能通过脚本交互的文件进行非法的管理。

```php
<?php
	# 上传的文件是可以立即通过公共网站进行浏览
	# 假如攻击者直接在表单的文件上传域中键入 /etc/passwd
	# 下面的代码就会将 /etc/passwd 文件复制到可以公共访问的目录中了
	copy($_FILES['classnotes']['tmp_name'], "/www/htdocs/classnotes/".basename($classnotes));

	#通过 is_uploaded_flie() 函数先进行过滤，即可避免这个问题
	if (is_uploaded_file( $_FILES['classnotes']['tmp_name'] )) {
		copy($_FILES['classnotes']['tmp_name'], "/www/htdocs/classnotes/".basename($classnotes));
	} else {
		echo "<p>Potential script abuse attempt detected.</p>";
	}
?>
```

**移动上传文件**
使用函数 move_uploaded\_file() 函数可以把上传到服务器的文件移动到新的位置中，以便永久保存。
	语法：bool move_uploaded\_file ( string file\_name, string destination )
	这个函数会先检查指定文件是否是有效的上传文件（意味着它是通过 POST 方法上传的）。
	  - 如果文件是有效的，就会被移动到参数 path 指定的目的路径中。
	  - 如果文件不是有效的上传文件，函数就什么也不做，直接返回一个 false 及一个警告。
	如果目标文件夹中存在同名的文件，则同名文件将会被覆盖。

> 上传到临时目录中，文件的名称会被更换成 PHP 自动生成的一个临时名称，需要使用`$_FILES['userfile']['tmp_name']`来调用这个文件。

> 上传到服务器临时目录中的文件，在对应的上传 PHP 脚本运行结束之后就会被删除了。
所以如果需要保存这个文件，需要调用这个函数将其重新保存到其他的目录中。

```html
<form action="upload.php" method="post" enctype="multi-part/form-data">
	<input type="file" name="picture">
	<br>
	<input type="submit" value="上传文件">
</form>

<?php 
	$filename = $_FILES['picture']['name'];
	$filesize = $_FILES['picture']['size'];
	// 重命名，pathinfo 获取文件的扩展名
	$rename   = date('Ymd',time()) . '/' . time() . '.' . pathinfo($filename, PATHINFO_EXTENSION);
	// 设置移动路径，并设置移动后的名称
	$dirctory = 'c:/wamp/www/pictures/' . $rename;
	if (move_uploaded_file($_FILES['picture']['tmp_name'], $dirctory)) {
		echo "The file is valid and was successfully uploaded.<br>";
		echo "The image file, $filename, is $filesize bytes.";
	}
?>
```

-------------------------------------------------------------------------------

## 文件和目录

### 权限
PHP 提供了三个函数用于更改文件/目录的相应权限。

**chown()** 这个函数改变文件的所有者，但是需要是该文件的所有者或者超级用户(root)来调用这个函数。
	语法：bool chown( filname, newuser );
	filename 是指要被修改的文件(可能需要指定相应的文件路径)。
	newuser 是指文件新所有者的用户名。

**chgrp()** 这个函数用于修改文件所属的组。执行这个函数需要：文件位于本地，用户是新组的成员。
	语法：bool chgrp( filname, newgroup )

**chmod()** 这个函数修改文件的权限，从而对不同的用户设置不同的读取、写入和执行权限。
	语法：bool chmod( filename, octal_mode )
	第一个参数执行要修改权限的文件，第二个参数用八进制数指定权限(以 0 为前导的数值)。
	执行这个函数，需要当前运行 PHP 脚本的进程的所有者必须也是文件的所有者，而且文件不能位于远程系统上。
	比如：`chmod("/dir/flie", 0755)` 或者 `chmod("/dir/flie", octdec(644))`

### 文件句柄
在 PHP 脚本里，我们不能通过文件名直接访问文件，而必须要通过文件句柄。
文件句柄也被称为文件指针，它把文件绑定到一个“流”。

### 打开文件
为了创建文件句柄，PHP 提供了 fopen() 函数。
这个函数会打开文件，并返回一个文件句柄，如果操作失败，就返回 false。
	语法：resoures fopen ( string filename, string mode [, 1 | true])
	第一个参数表示文件名，代表一个本地文件或者 URL。能够通过完整路径名、相对路径名或一个简单文件名而引用。
	第二个参数表示打开模式，具体的可选设置见下表。
	第三个参数可选，如果设置为 true，就是让 PHP 查看 include 路径中的这个文件。

> 如果在 Windows 系统中，想要用反斜线指定文件路径，需要对反斜线进行转义。

利用 fopen() 函数还可以打开 FTP 或 HTTP 文件。

> 如果打开 URL 的操作失败，请先检查 php.ini 文件里的 allow_url\_fopen 指令是否被禁止了。

|  模式  |  名称    |  描述                                           |
| ------ | -------- | ----------------------------------------------- |
|   r    | 读       | 打开文件只用于读，从文件起点开始。              |
|   r+   | 读写     | 打开文件进行读取和写入，从文件起点开始。        |
|   w    | 写       | 打开文件只用于写入，从文件起点开始。<br>如果文件存在就覆盖它；否则就创建它 |
|   w+   | 读写     | 打开文件进行过读取和写入，从文件起点开始。<br>如果文件存在就覆盖它；否则就创建它 |
|   a    | 附加     | 打开文件只用于写入，把内容附加到文件末尾。<br>如果文件不存在就创建它 |
|   a+   | 读/附加  | 打开文件进行读取和写入，把内容附加到文件末尾。<br>如果文件不存在就创建它 |
|   x    | 谨慎写入 | 创建和打开本地文件只用于写入，从文件起点开始。<br>如果文件以及存在返回假，且发送一个警告；<br>如果不存在就尝试创建它。|
|   x+   | 谨慎写入 | 创建和打开本地文件用于读取和写入，从文件起点开始。<br>如果文件以及存在返回假，且发送一个警告；<br>如果不存在就尝试创建它。|
|   b    |          | 默认模式。与其他模式配合使用。<br>用于对二进制文件和文本文件区别对待的文件系统。<br>对 Windows 是必需的，而 UNIX/Linux/Mac 则不是 |
|   t    |          | 与其他模式配合使用，代表 Windows 文本文件。<br>把行结束符`\n`换成`\r\n`。<br>与 b 模式配合使用以提高可移植性 |

### 常用文件操作函数
|    函数名            |    功能                      |    描述                        |
| -------------------- | ---------------------------- | ------------------------------ |
| fclose()             | 关闭文件或 URL               | fclose(fh)                     |
| feof()               | 测试文件指针是否到达文件末尾 | bool feof(fh)                  |
| fflush()             | 把输出转存到文件             | fflush(fh)                     |
| fgetc()              | 从文件获取一个字符           | string fgetc(fh)               |
| fgets()              | 从文件获取指定数量的内容     | string fgets(fh)               |
| fgetscsv()           | 从文件获取一行，并解析<br> CSV 字段(逗号分隔的值) | array fgetscsv(fh)             |
| fgetss()             | 从文件获取一行，并清除其中<br>的 HTML 和 PHP 标记 | string fgetss(fh)              |
| file()               | 把整个文件读取到数组         | array file(filename)           |
| file_exists()        | 检查文件（或目录）是否存在   | bool file\_exists(fileename)   |
| file_get\_contents() | 把整个文件读取到一个字符串   | string file\_get\_contents(fn) |
| fopen()              | 打开文件或 URL               | fh fopen(filename, mode)       |
| is_readable()        | 检查文件是否存在并可读取     | bool is\_readable(filename)    |
| fread()              | 从资源中读取指定长度字符     | string fread(fh, int length)   |
| fsacnf()             | 根据预定义的格式读取文件     | mixed fscanf(fh, format [, var]) |

当文件最初被打开进行读取时，内部文件指针位于文件起始位置。
当对文件执行了读取操作之后，程序会追踪它在文件中的位置，把文件指针移动到最后一个读取数据之后的下一个数据。

**fgets()** 从文件读取指定行数的内容。默认情况下，只读取文件中的一行。
	语法：string fgets( filehandler fd [, int length] )
	当没有设置第二个参数时，只读取文件中的一行字符(不超过 1024 个字节)；
	当设置了第二个参数时，表示最多读取`length-1 个字符`。

> 对于 PHP 4.3 来说，忽略第二个参数会让函数不断从文件流读取数据，直到遇到行尾标记。

> Windows 和 UNIX 上的行尾标记是不同的。

**fgetss()** 这个函数和 fgets() 函数类似，但在从文件读取内容时，会清除其中的 HTML 和 PHP 标记。
	语法：string fgetss ( resoures fh [, int length [, string allowable_tags]] )
	其中，第三个参数可选，用于指定不要清除的标记。

**fgetc()** 从一个打开的文件里，每次读取和返回一个字符，在到达文件末尾时，返回假。
	语法：string fgetc( resoures fh )

**file_get\_contents()** 这个函数把整个文件都读取到一个字符串中，而且`不需要使用文件句柄`。
	语法：string file_get\_contents ( string filename [, bool use\_include\_path [, 
									  resource context [, int offset [, int maxlen]]]] )

**file()** 在不适用文件句柄的情况下，还可以利用 file() 函数把整个文件读取到一个数组中。
	语法：array file ( string filename [, int use_include\_path [, resource context]] )
	这个函数和 file_get\_contents() 函数的唯一区别就是其把内容保存到数组中。
	文件名可以是完整路径、相对路径、或 URL。
	数组中每个元素的值对应于文件里的每一行，并且包含换行符。如果要去除末尾的换行符，可以使用 rtrim() 函数。
	操作失败会返回 false。

**readfile()** 这个函数可以读取文件内容，并`立即把它写入到输出缓存`中，然后返回读取的字节数量。
	语法：int readfile ( string filename [, bool use_include\_path [, resource context]] )
	如果操作过程发生了错误，函数就会返回 false 并且输出错误信息。（可以使用 @ 屏蔽错误信息）

**fread()** 这个函数从指定的资源中读取指定长度的字符并返回。和上一个函数有一定的相似性。
    语法：string fread ( resource handle, int length )
    这个函数会一直读取，直到读取了 length 个字符，或者到达资源末尾 EOF 时停止。
    与其他函数不同，此函数不会考虑换行符。
    因此，只要使用 filesize() 函数确定了应当读取的字符数，就能很方便的使用这个函数读取整个文件。

**fscanf()** 这个函数可以按照预定义的格式来解析资源。类似于 printf() 函数。
    语法：mixed fscanf ( resource handle, string format [, string var] )
    其中的 format 参数和 printf() 函数中的格式类似。

```php
<?php 
    # socsecurity.txt 文件中的内容如下：
    # 123-45-6789
    # 234-56-7989
    # 354-68-7514

	$fh = fopen("socsecurity.txt", 'r');
	while ($user = fscanf($fh, '%d-%d-%d')) {
		list($part1, $part2, $part3) = $user;
		printf("Part 1: %d; Part 2: %d; Part 3: %d;<br>", $part1, $part2, $part3);
	}
	fclose($fh);
	# 每次迭代时，变量 $part1, $part2, $part3 分别被赋值为 SSN 的三个部分
	# 并输出到浏览器
?>
```

### 文件写入
为了打开文件进行写入操作，可以使用 fopen() 函数返回一个指向文本文件或二进制文件的文件句柄。
> 虽然 UNIX 和 Mac 系统中不区分文本文件和二进制文件，但 Windows 对它们是区别对待的。
所以如果文件要在多个操作系统直接共享，更安全的方式是以二进制方式组合其他模式来打开。

**fwrite() 和 fputs()** 这两个函数完全相同，互为别名。
	语法：int fwrite ( resource fh, string str [, int length] )
	      int fputs ( resource fh, string str [, int length] )
	这两个函数，把字符串 str 写入到文件 fh 之中。成功时返回写入的字符数。失败时返回 false。
	第三个参数可选，用于指定向文件中写入最多多少个字符。如果设置了，则表示写入 length 个字符串之后就停止，否则就会一直写入，直到到达 str 结尾时才停止。

**file_put\_contents()** 这个函数也是向文件中写入一个字符串，返回写入的字符数。
	但是这个函数并不需要使用文件句柄，可以直接使用文件名。

### 锁定和解锁
**flock()** 可以使用这个函数锁定文件，让用户独占访问权，在完成操作之后，再调用解除锁定。
	语法：bool flock ( resource fh, int operation [, int &wouldblock] )
	第二个参数表示执行的操作，具体如下表所示。
	同时，fclose() 函数调用之后也会释放锁定，而且这个函数是在脚本结束时自动调用的。

|   操作   |  数值  |   执行的操作                 |
| -------- |:------:| ---------------------------- |
| LOCK_EX  | 2      | 获得独占锁定(写入者)         |
| LOCK_NB  | 4      | 在锁定被获取时不阻塞(不等待) |
| LOCK_SH  | 1      | 获取共享锁定(读取者)         |
| LOCK_UN  | 3      | 释放锁定(共享的或独享的)     |

### 文件指针定位
**fseek()** 这个函数能够设置指针在文件里的位置，其中第一个字节的位置是 0。
	语法：int fseek ( resoures fh, byteoffset, position)
	第二个参数表示相对文件位移的字节数。为正数表示向文件末尾移动；为负数表示向文件起点移动。
	第三个参数设置相对位移时的位置。可选值如下：
		SEEK_SET = 文件起点，默认位置。只能有正位移。
		SEEK_CUR = 文件指针的当前位置。可以有正位移或负位移。
		SEEK_END = 文件末尾。只能使用负位移。

**rewind()** 这个函数把指针移动打文件起点。相当于`fseek(fh, 0, SEEK_SET)`。
	语法：bool rewind ( resoures fh )

**ftell()** 这个行返回当前字节位置（从文件起点开始的字节数量），也就是下一次读操作开始的地方。
	语法：int ftell( resoures fh )
	和 fseek() 函数配合使用，就可以把指针移动到文件的正确位置。

> 如果使用文本模式，回车符和换行符会被统计为字节。

### 文件检查
在对文件或目录进行过操作之前，最好先确定文件/目录是否存在，是否可读、可写或可执行等。

|    函数           |    描述                           |
| ----------------- | --------------------------------- |
| file_exists()     | 检查文件或目录是否存在            |
| is_dir()          | 检查名称是否是个目录              | 
| is_file()         | 检查名称是否是个文件              | 
| is_link()         | 检查名称是否是个符号链接          | 
| is_readable()     | 检查文件是否可读                  | 
| is_upload\_file() | 检查文件是否是由 HTTP POST 上传的 | 
| is_writable()     | 检查文件是否可写                  | 
| is_writeable()    | is\_writable() 的别名             | 
| stat()            | 获得文件的信息                    | 

> 在这些函数中，使用相对路径比使用绝对路径的处理速度更快。

### 目录
在 PHP 中，目录和文件一样，可以打开、关闭、读取。也是一种资源。

|    函数     |    描述                                        |
| ----------- | ---------------------------------------------- |
| chdir()     | 改变当前的工作目录到新的目录中                 |
| chroot()    | 改变根目录                                     |
| closedir()  | 关闭由 opdir() 打开的目录句柄                  |
| getcwd()    | 获得当前工作目录                               |
| opendir()   | 打开一个目录，并返回目录句柄                   |
| readdir()   | 从目录句柄中读取当前文件的名称                 |
| rewinddir() | 把目录句柄指针移动到目录的起点                 |
| rmdir()     | 删除目录，这个目录必须是空的，并且具有写入权限 |
| scandir()   | 返回一个数组，包含指定路径里的文件和目录       |
| unlink()    | 删除目录里的一个文件                           |
| dirname()   | 返回路径名称里的目录名                         |
| basename()  | 返回不包含目录的文件名                         |
| pathinfo()  | 创建一个数组，包含路径中目录名、基本名和扩展名 |
| realpath()  | 确定绝对路径                                   |

> 目录中的句点表示当前工作目录，两个句点表示上一级目录。

**readdir()** 读取目录中，目录指针指向的当前文件的名称。
	语法：string readdir ( resource dh )
	每个文件的出现次序与操作系统在磁盘上存储它们的次序相同。
	在操作成功时，会将目录指针移动到目录里的下一个文件。

**scandir()** 返回一个由指定目录中的文件和目录的名称组成的数字数组。在发生错误时，返回 false。
    语法：array scanfir ( string directory [, int sorting_order [, resource content]] )
    返回的数组中，包含 . 和 .. 这两个文件夹。
    如果将可选参数 sorting_order 设置为 1，将以降序排列内容，而不是默认的升序排列。

**dirname() / basename()** 这两个函数，一个是返回一个完成路径中的路径部分(不含文件名)，一个是只返回文件名部分。
    语法：string dirname ( string path )
          string basename ( string path [, string suffix] )
    basename() 函数中，如果指定了 suffix 参数，当返回的文件名包含这个扩展名时将会忽略该后缀，也即是只返回文件的基本名称。

```php
<?php
	$path = "c:/wamp/www/exemples/first.php";
	echo dirname($path);	// c:/wamp/www/exemples
	echo basename($path);	// first.php
	echo basename($path, ".php");    // first
?>
```

**pathinfo()** 这个函数会返回一个数组，其中包括路径中的四个部分：目录名、文件名、扩展名、基本名。
    语法：array pathinfo( string path )
    返回的个部分的索引分别是：dirname、basename、extension、filename。

```php
<?php 
	$path = "c:/wamp/www/exemples/first.php";
	print_r(pathinfo($path));
	# 输出
	// Array ( [dirname] => c:/wamp/www/exemples [basename] => first.php [extension] => php [filename] => first )
?>
```

**realpath()** 将 path 中的所有符号链接和相对路径引用转换为相应的硬链接和绝对路径。
    语法：string realpath ( string path )

**chdir() / getcwd()** 当用 chdir() 改变了当前目录之后，再调用 getcwd() 将会显示到改变了之后的目录。

```php
<?php
	echo getcwd();     // 如："c:/wamp/www/exemples"
	chdir("..");       // 改变当前目录到上一级
	echo getcwd();     // 此时为："c:/wamp/www"
?>
```

**rmdir()** 这个函数是用来删除指定的目录的。成功时返回 true，否则返回 false。
    语法：int rmdir ( strig dirname )
    要想成功的删除目录，必须正确的设置权限，如果对目录没有写权限，将会导致失败。
    另外，也要求 dirname 目录必须为空。

```php
<?php 
    # 要删除一个非空的目录，可以使用一个能执行系统级命令的函数
    # 如 system() 或 exec()。
    # 也可以编写一个递归函数，在删除目录前，删除其中的所有的文件内容
    function delete_directory($dir) {
    	if ($dh = opendir($dir)) {
    		// Iterate through directory contents
    		while (($file = readir($dh)) != false) {
    			if (($file == ".") || ($file == ".."))
    				continue;

    			$tmp = $dir.'/'.$file;
    			if (is_dir($tmp))
    				delete_directory($tmp);
    			else
    				unlink($tmp);
    		}
    	}
    }
?>
```

### 执行系统指令
前面提到过，PHP 是能够和系统进行交互的，也能够直接执行系统指令。下面就是几个封装好的函数。

**exec()** 这个函数最适合执行在服务器后台连续执行的操作系统级应用程序。
    语法：string exec ( string command [, array output [, int return_var]] )
    默认情况下，这个函数会返回命令执行时输出的最后一行。
    如果需要得到所有的输出，可以设置可选参数 output。这个数组参数将会包含命令执行结束时的每一行输出。
    如果设置了 return_var 参数，将会得到执行命令的返回状态。

```php
<?php
	# 从 PHP 调用一个 Perl 脚本
	$output = exec("language.pl", $results);
	foreach ($results as $result) {
		echo "$result";
	}
?>
```

**system()** 这个函数能够执行指定指令，并输出执行命令的结果。
   语法：string system ( string command [, int return_var] )
   它是直接将输出返回给调用者，而不是像 exec() 那样需要传入参数才能得到返回结果。
   如果希望查看被调用程序的执行状态，就需要使用可选参数 return_var 指定一个变量。
   如，希望列出位于特定目录的所有文件，`$mymp3s = system("ls -1 /home/jason/mp3s");`。

**passthru()** 这个函数和 exec() 函数很相似，只不过其返回的是`二进制输出`。
    语法：void passthru ( striing command [, int return_var] )

```php
<?php
	# 如希望在浏览器显示 GIF 图片前
	# 先将 GIF 图片转换为 PNG 格式图片
	header("ContentType:image/png");
	passthru("giftopnm cover.gif | pnmto png > cover.png");
?>
```

**反引号**
使用反引号(backtick)界定字符串时，就是在告诉 PHP：该字符串应该作为 shell 命令来执行，并返回所有输出。

```php
<?php
	$result = `date`;
	printf("<p>The server timestamp is: %s</p>", $result);
	# 输出类似如下：
	# The server timestamp is: Sun Mar 3 15:32:14 EDT 2016
?>
```

**shell_exec()** 这个函数提供了与反引号相同的语法形式，会执行一个 shell  指令，返回指令输出。
    语法：string shell_exec ( string command )
    前面的那个例子，用这个函数执行，语句为：`$result = shell_exec("date");`。

### 其他文件操作
**拷贝**
copy() 函数可以生成文件的副本。在文件成功拷贝时，函数会返回 true，否则返回 false。
	语法：bool copy ( string source_file, string destination\_file )
	第一个参数表示源文件，第二个参数表示拷贝成的目标文件。
	在拷贝文件时，需要具有新文件所在目录的写入权限。

**重命名**
rename() 这个函数用于赋予文件或目录另一个名称。
	语法：bool rename ( string old_file, string new\_file )

> 如果目标文件位于另一个目录，那么实际上就是在移动文件。

**创建**
touch() 当操作的文件不存在的时候，就创建文件；当文件已经存在的时候，就更新其时标(访问和修改时间)。
	语法：bool touch ( string filename [, time [, atime]])
	第二个参数是指定时间，默认是系统当前时间。
	第三个参数是指定访问时间，默认是系统当前时间。

**删除**
unlink() 删除指定的文件
	语法：bool unlink ( string filename [, resource context] )

**文件最后修改时间**
fileatime() 函数返回文件的最后访问时间，采用 UNIX `时间戳`格式。有错误时返回 False。
    语法：int fileatime ( string filename )
    中间的 a 表示 access。

**文件最后改变时间**
filectime() 函数返回文件的最后改变时间，采用 UNIX `时间戳`格式。有错误时返回 False。
    语法：int filectime ( string filename )
    中间的 c 表示 change。

**文件最后修改时间**
filemtime() 函数返回文件的最后修改时间，采用 UNIX `时间戳`格式。有错误时返回 False。
    语法：int filemname ( string filename )
    中间的 m 表示 modify。
> 最后修改时间 和 最后改变时间 不是一个概念：
  - 最后修改时间 是指文件内容的改变，
  - 最后改变时间 是值对文件 inode 数据的任何改变，包括改变权限、所有者、组、或其他 inode 特定的信息。
  > inode 数据是 Unix/Linux 系统上管理文件的一种数据，不包含文件名。详见[理解 inode - 阮一峰](http://www.ruanyifeng.com/blog/2011/12/inode.html)

**资源末尾**
feof() 这个函数可以用来判断是否到达资源末尾。
    语法：int feof ( string resource )
    到达末尾，则返回 true，否则就返回 false。
    一般这个函数就用来作为循环读取资源内容时的条件语句。

### 文件、目录和磁盘大小
**1. 文件大小**
filesize() 函数返回指定文件的大小，以`字节为单位`。
    语法：int filesize ( string filename )

**2. 磁盘可用空间**
disk_free\_space() 返回指定的目录`所在磁盘分区`的可用空间，以`字节为单位`。
    语法：float disk_free\_space ( string directory )

> 注意，这个函数返回的是文件夹所在磁盘分区的可用空间，而不是指定文件夹的可用空间。文件夹没有可用空间一说。

```php
<?php 
	$path = "c:/Intel/";

	printf("Remaining GB on C: %.2f", round((disk_free_space($path) / 1048576 / 1024), 2));
	# 这里获取的是 C 盘分区的可用空间，而不是 C 盘中的文件夹 Intel 的可用空间：
	// Remaining GB on C: 59.83
?>
```

**3. 磁盘的总容量**
disk_total\_space() 返回指定的目录`所在磁盘分区`的总容量，以字节为单位。
    语法：float disk_total\_space ( string path )
    如果将此函数和 disk_free\_space() 一起使用，就能很容易的给出有用的空间分配统计结果：

**4. 获取目录大小**
PHP 目前不提供获取目录总大小的标准函数。
虽然可以使用 exec() 或 system() 做系统级调用命令`du`，但出于安全原因，这些函数通常是禁用的。
另一种解决方案是编写一个定制 PHP 函数来完成。（递归函数比较合适。）

```php
<?php 
    function directory_size($directory) {
    	$directorySize = 0;

    	// Open the directory and read its contents.
    	if ($dh = @opendir($directory)) {

    		// Iterate through each directory entry.
    		while ( ($filename = readdir($dh)) ) {

    			// Filter out some of the unwanted directory entries.
    			if ($filename != "." && $filename != "..") {
    				// File, so determine size and add to total.
    				if (is_file($directory."/".$filename)) {
    					$directorySize += filesize($directory."/".$filename);
    				} elseif (is_dir($directory."/".$filename)) {
    					$directorySize += directory_size($directory."/".$filename);
    				}
    			}
    		}

    		@closedir($dh);
    		return $directorySize;
    	}
    }

    $directory = "/usr/book/chapter10/";
    $totalSize = round( (directory_size($directory) / 1024 / 1024), 2);
    printf("Directory %s: %f MB", $directory, $totalSize);
    // Directory /usr/book/chapter10/: 2.12 MB
?>
```


-------------------------------------------------------------------------------

## 正则表达式
### 式样匹配函数
**Perl5 兼容函数**

|    函数                  |    功能                                            |
| ------------------------ | -------------------------------------------------- |
| preg_grep()              | 返回匹配的式样数组                                 |
| preg_match()             | 执行正则表达式式样匹配                             |
| preg_match\_all()        | 执行全局正则表达式                                 |
| preg_quote()             | 在字符串里找到的正则表达式字符串前面放置一个反斜线 |
| preg_replace()           | 搜索一个式样，用另一个式样替换它                   |
| preg_replace\_callback() | 类似于上面的函数，但它使用一个函数作为替换参数     |
| preg_split()             | 使用正则表达式作为分隔符分解字符串                 |


**POSIX 样式函数**

|    函数         |    功能                                          |
| --------------- | ------------------------------------------------ |
| ereg()          | 执行正则表达式式样匹配                           |
| eregi()         | 执行不区分大小写的正则表达式式样匹配             |
| ereg_replace()  | 搜索一个式样，用另一个式样替代它                 |
| eregi_replace() | 搜索一个式样(不区分大小写)，用另一个式样替代它   |
| split()         | 使用正则表达式作为分隔符分解字符串               |
| spliti()        | 使用正则表达式作为分隔符分解字符串，不区分大小写 |
| sql_regcase()   | 将字符串中的各个字符转换为一个包含两个字符并用<br>中括号括起来的表达式 |

sql_regcase() 将字符串中的各个字符转换为一个包含两个字符并用中括号括起来的表达式。
    如果字符是一个字母，中括号中将包含这个字母的大小写形式；否则，元字符将保持不变。
    语法：string sql_regcase ( string str )

```php
<?php 
	$version = "php 5.0";
	echo sql_regcase($version);   // [Pp] [Hh] [Pp] 5.0
?>
```

### 式样匹配修饰符(Perl 风格)
式样匹配修饰符可以控制式样的处理方式。

|   修饰符  |  功能                                                    |
|:---------:| -------------------------------------------------------- |
| A         | 即使嵌入了换行符并使用了修饰符 m，也只能匹配字符串的开头 |
| D         | 只在字符串末尾匹配。如果没有这个修饰符且设置了 m 修饰符，<br>美元符号就会被忽略(Perl 没有与之等效的修饰符) |
| e         | 在使用 preg_replace() 执行替换操作时，被替换部分将被当做<br>表达式进行求值。 |
| i         | 不考虑大小写                                             |
| g         | 查找所有的出现
| m         | 如果字符串内嵌了换行符，那么字符串里每个换行符都表示字符<br>串的结束。行首元字符和行尾元字符(^/$)<br>应用于每个嵌套的字符串而不是整个字符串。|
| S         | 研究经常被使用的式样，从而优化搜索时间                   |
| s         | 允许句点元字符匹配字符串里任何一个换行符。<br>通常情况下，句点并不匹配换行符。|
| X         | 如果式样里任意一个反斜线后面跟随的字母并不具有特殊含义，<br>就会产生错误。|
| x         | 忽略样式里的空白，除了使用反斜线进行转义或位于**中括号**里。<br>适合对正则表达式添加注释，提高可读性。|
| U         | 关闭量词的“控制域”。另外，使用 U 跟随一个问号可以<br>临时打开这种“控制域”。|

### 前测和后测
(?:x) 匹配 x，但记住这个匹配。这被称为非捕获性小括号

x(?=y) 只匹配后面跟着 y 的 x。如 /Jack(?=Sprat)/ 只匹配后面跟着 Spart 的 Jack，而 Spart 不会作为匹配结果的组成部分。

x(?!y) 只匹配后面没有跟着 y  的 x。如 /\d+(?!\.)/ 匹配后面没有小数点的一位或多位数字。

(?<=y)x 只匹配前面是 y 的 x。

(?<!y)x 只匹配前面不是 y 的 x。

### 记住或捕获
如果正则表达式被包围在小括号里，就创建了一个子式样。
这个子式样可以使用 \1、\2、\3 直到 \9， 或 $1、$2、$3 直到 $99 的方式引用。

### 正则表达式注释
可以为正则表达式添加注释，但是需要使用 x 修饰符。

```php
<?php
	# /^([A-Z][a-z]+)\s(\d{3})/
	$regex = 
	"/
	^        # At the beginning of the line
	(        # Start a new subpattern $1
	[A-Z]    # Find an uppercase letter
	[a-z]+   # Find one or more lowercase letter
	)        # Close first subpattern
	\s       # Find a whitespace character
	(        # Start another subpattern $2
	\d       # Match it three times
	{3}      # match it three times
	)        # Close the subpattern
    $        # End of line
	/x";
?>
```

### 函数说明

preg_match()  在搜索到第一个匹配的结果的时候就停止。所以其返回值要么是1要么是0。
	语法：int preg_match ( string pattern, string subject [, array matches [, int flag [, offset]]] )
	第一个参数是正则表达式，第二个参数是要被搜索的字符串。
	如果指定了第三个参数，那么会将匹配到的第一个结果保存在这个参数表示的数组的第一个元素(索引是 0)中。如果正则表达式中有被包围在小括号中的子样式，子样式会按照被发现的次序依次保存在数组中(索引从 1 开始)。
	第四个参数的值只能是`PREG_OFFSET_CAPTURE`，它让返回的数组也列出式样在字符串里被找到的位置。此时第三个参数将会变成二维数组，每个元素都存储了一个包含两个元素的数组，分别含有式样匹配到的元素，和匹配的位置。
	第五个参数指定搜索的起始位置的位移。默认情况下是从字符串起点开始搜索。

```php
<?php
	$string = "Looking for a fun and games.";
	$result = preg_match("/(fun) and (games)/", $string, $matches);
	if ( $result == 1) {
		print_r($matches);   // Array ( [0] => fun and games [1] => fun [2] => games )
	}

	$others = preg_match("/(fun) and (games)/", $string, $matches, PREG_OFFSET_CAPTURE);
	if ( $others == 1) {
		# 输出为：
		// Array ( [0] => Array ( [0] => fun and games [1] => 14 ) [1] => Array ( [0] => fun [1] => 14 ) [2] => Array ( [0] => games [1] => 22 ) )
		print_r($matches); 
	}
?>
```

preg_match\_all() 和 preg\_match() 函数类似，只是其返回所有的匹配到 pattern 的式样，而不是只有一个。
	语法：int preg_match\_all ( string pattern, string subject, array matches [, int flags] )
	所有的匹配到的式样会组成一个数组，存在第三个参数 matches 数组中的第一个元素(索引为 0) 中。matches 数组的其他元素存放子式样匹配到的内容。
	第四个参数如果设置了，也是只能取`PREG_OFFSET_CAPTURE`，列出每个式样的位置。

```php
<?php
	$string = "My Lovely glove are lost in the clover, Love.";
	$result = preg_match_all("/love/", $string, $matches);
	if ( $result > 0) {
		print_r($matches);   // Array ( [0] => Array ( [0] => love [1] => love ) )
	}

	$others = preg_match_all("/love/i", $string, $matches, PREG_OFFSET_CAPTURE);
	if ( $others > 0) {
		# 输出为：
		// Array ( [0] => Array ( [0] => Array ( [0] => Love [1] => 3 ) [1] => Array ( [0] => love [1] => 11 ) [2] => Array ( [0] => love [1] => 33 ) [3] => Array ( [0] => Love [1] => 40 ) ) )
		print_r($matches); 
	}
?>
```

preg_replace()  这个函数在**字符串**或**数组**里查找式样，并且用其他东西进行替换。
	语法：mixed preg_replace (  string pattern, mixed replacement, mixed subject [, int limit [, int &count]] )
	第一个参数是正则表达式，第二个参数是替换值，第三个参数是被查找的目标。
	第三个参数指定每个目标字符串里发生替换操作的次数限制。默认值是 -1(没有限制)。
	第四个参数将会被赋值为替换操作执行的次数。
	使用修饰符 e 可以让这个函数把替换值当做表达式来进行求值，比如 4+3 会变成 7.
	如果被查找的是个字符串，并且找到匹配的结果，就好返回得到的新字符串，否则就返回原始字符串；
	如果被查找的是个数组，查找和替换操作会应用于每个元素，并且返回一个数组。

```php
<?php
	$string = "I am feeling blue, blue, blue.";
	// I am feeling upbeat, upbeat, upbeat.
	echo preg_replace("/blue/", "upbeat", $string);
	// I am feeling upbeat, blue, blue.
	echo preg_replace("/blue/", "upbeat", $string, 1);

	// Peace and War
	echo preg_replace("/(Peace) and (War)/i", "$2 and $1", "Peace and War");

	// He gave me 42 dollars.
	echo preg_replace("/5/e", "6*7", "He gave me 5 dollars.");
?>
```

preg_replace\_callback()  这个函数能够使用传入的回调函数来处理字符替换。
    语法：mixed preg_replace\_callback ( mixed pattern, callback cb, mixed str [, int limit] )
    参数 pattern 确定要寻找的字符串。
    参数 cb 定义用于完成替换任务的函数名。
    参数 str 定义所要去搜索的字符串。
    可选参数 limit 指定要进行多少次匹配。如果不设置，或设置为 -1，则替换所有的匹配。

preg_split()  这个函数根据字符串里表示单词的分隔符（比如空格、逗号或这种字符串的组合）对字符串进行分解，返回由字符串组成的数组。
	语法：array preg_split ( string pattern, string subjcet [, int limit [, int flags]] )
	如果指定了第三个参数，就只返回指定数量的子串，其中最后一个子串包含剩余的所有字符。
	第四个参数的可选值如下：
		`PREG_SPLIT_DELIM_CAPTURE`  分隔符式样里被捕获的式样会保存和返回
		`PREG_SPLIT_NO_EMPTY`       只返回非空部分
		`PREG_SPLIT_OFFSET_CAPTURE` 对于每个找到的匹配式样，返回匹配结果在字符串里的位移。

> 如果使用单字符或剪子字符串作为分隔符，explode() 函数的处理速度会快一些。

preg_grep()  在数组而不是字符串里搜索匹配式样，返回由数组中匹配样式的所有元素组成的数组。
    语法：array preg_grep ( string pattern, array input [, int flags] )
    如果参数 flags 使用`PREG_GREP_INVERT`，还可以执行反转搜索，也即是让返回的数组包含字符串里不匹配指定式样的内容。

> 注意，输出数组对应于输入数组的索引顺序，而且会保持索引。

```php
<?php
	$array  = array("normal", "mama", "man", "plan");
	$first  = preg_grep("/ma/", $array);  // normal, mama, man
	$second = preg_grep("/ma/", $array, PREG_GREP_INVERT);  // plan

	$foods = array("pasta", "steak", "fish", "potatoes");
	$food  = preg_grep("/^p/", $foods);
	print_r($food);   // Array ( [0] => pasta [3] => potatoes )
?>
```

-------------------------------------------------------------------------------

## PHP 与 MySQL
### 连接数据库
resource mysql_connect ( [string server [, string username [, string password 
                         [, bool new\_link [, int client\_flags]]]]] )
第一个参数是 MySQL 服务程序所在的主机名，可以包含一个 IP 地址、端口号、本地套接字等。
第二个参数是用户名，默认值是服务进程所有者的名称。
第三个参数是密码。

如果使用相同的参数再次调用 mysql_connect() 函数，默认情况下并不会建立一个新连接，而是反悔已经被打开连接的标识符。
如果指定了第四个参数 new_link 才会新打开一个连接。

### 关闭数据库连接
bool mysql_close ( resource link\_identifier )
当 Web 页面或 PHP 脚本结束时，数据库会自动关闭，指向数据库的连接资源会被释放。
也可以使用 mysql_close 关闭一个数据库连接。

### 选择数据库
bool mysql_select\_db( string database\_name [, resource link\_identifier] )
在连接到数据库服务程序之后，下一步就是设置默认的数据库。
使用这个参数即可连接上指定的数据库。等效于命令行工具里的 use 命令。
第一个参数表示要连接的数据库的名称。
第二个参数是执行 mysql_connect() 函数所建立的 MySQL 连接。可以省略。

### 执行 SQL 语句
resource mysql_query ( string query [, resource link\_identifier] )
这个函数能够执行 SQL 语句，并返回执行的结果。

对于 SELECT、SHOW、DESCRIBE、EXPALIN 语句来说，这个函数在成功时返回查询的结果资源，失败时返回 False。

对于其他类型的 SQL 语句(INSERT、UPDATE、DELETE、DROP 等)，这个函数在成功时返回 true，在是不时返回 false。

> 为了对数据库执行查询，我们必须拥有足够的权限。

> 在进行 SQL 查询之前，所有字符串都必须加单引号，以避免可能的注入漏洞和 SQL 错误。

### 获取查询结果(SELECT)
使用 SELECT 语句通常会返回数据记录的集合（结果集）。对于结果集，我们可以使用一些函数来获取其中的详细记录。

**mysql_fetch\_row()** 
语法：array mysql_fetch\_row ( resource result )
这个函数用于从结果集中提取一条记录。并将内部指针移动到下一条记录，直到记录集的末尾。
返回的结果是个`数字数组`。

**mysql_fetch\_assoc()**
语法：array mysql_fetch\_assoc( resource result )
这个函数和上一个基本相同，只是其返回的是一个`关联数组`。
数组的索引就是记录集中的列名。

### 其他函数
|    函数                |    功能                                   |
| ---------------------- | ----------------------------------------- |
| mysql_connect()        | 连接到数据库服务程序                      |
| mysql_pconnect()       | 打开一个永久连接                          |
| mysql_select\_db()     | 选择默认数据库                            |
| mysql_change\_user()   | 改变登陆用户的标识                        |
| mysql_list\_dbs()      | 列出当前 MySQL 服务程序包含的数据库       |
| mysql_list\_tables()   | 列出数据库里的表                          |
| mysql_fetch\_assoc()   | 以关联数组形式返回结果集里的一条记录      |
| mysql_fetch\_row()     | 以数字数组形式返回结果集里的一条记录      |
| mysql_affected\_rows() | 返回受到操作影响的记录数量                |
| mysql_num\_rows()      | 返回结果集中的记录的数量                  |
| mysql_fetch\_object()  | 以对象形式返回结果集里的一条记录          |
| mysql_fetch\_field()   | 从结果集里获取字段信息，保存为一个对象    |
| mysql_field\_name()    | 获取结果集里指定字段的名称                |
| mysql_list\_fields()   | 把结果集里的指针设置到指定字段位移        |
| mysql_num\_fields()    | 获取结果集里的字段的数量                  |
| mysql_field\_seek()    | 把结果集的指针设置到指定字段位移          |
| mysql_field\_type()    | 获取结果集里指定字段的类型                |
| mysql_field\_len()     | 返回指定字段的长度                        |
| mysql_field\_table()   | 获取指定字段所在的表的名称                |
| mysql_tablename()      | 获取字段所在的表名                        |
| mysql_errno()          | 返回前一个 MySQL 操作的错误信息的编号     |
| mysql_error()          | 返回前一个 MySQL 操作的错误信息的文本内容 |

-------------------------------------------------------------------------------

## 对象
### 类函数
get_class() 返回对象的类名。如果 obj 不是对象，则返回 False。
    语法：string get_class ( object obj )

get_class\_methods() 返回一个数组，其中包含指定类中定义的所有方法名。
    语法：array get_class\_methods ( mixed class\_name )

get_class\_vars()  以关联数组形式返回指定类中的定义的所有字段名及其相应的值。
    语法：array get_class\_vars ( string class\_name )

get_declared\_classes()  以数组形式返回当前脚本里定义的所有类名。
    语法：array get_declared\_classes ( void )

get_object\_vars()  以关联数组形式返回对象的可用的已定义属性及其值。
    语法：array get_object\_vars ( object obj )

get_parent\_class()  返回类或对象的父类的名称。如果指定参数就是基类，则就返回该类的名称。
    语法：striing get_parent\_class ( mixed obj )

gettype()  返回变量的数据类型。如果是个对象，返回 object。
    语法：sting gettype ( mixed var )

instanceof  类型操作符，替代了 is_a()
    语法：obj instanceof classname

interface_exists() 如果接口已经被定义，就返回 true
    语法：bool interface_exists ( string interface\_name [, bool autoload] )

is_a()  如果对象属于这个类，或这个类是他的父类，返回 true
    语法：bool is_a ( object obj, string class\_name)

is_subclass\_of()  如果这个类是对象的子类，函数返回 true
    语法：bool is_subclass\_of ( mixed object, string class\_name )

method_exists()  在方法存在的时候，返回返回 true
    语法：bool method_exists ( object obj, string method\_name)

property_exists()  如果类里存在这个属性并且是可访问的，函数返回 true
    语法：bool property_exists ( mixed class, string property )

class_exists() 确定当前执行脚本上下文中如果存在指定的类名，则返回 True，否则返回 False。
    语法：bool class_exists ( string class\_name )

### 作用域
**公共作用域 public**
公共作用域是对象的属性和方法的默认作用域。
它表示在脚本里任何位置，类的实例都能够访问的类成员。

**私有作用域 private**
私有成员只能在定义他们的`类的内部`进行访问，不能从类外部或子类中直接访问。
在使用私有变量时，需要利用公共方法来操作数据。

**受保护作用域 protected**
受保护的成员只能在定义他们的`类的内部`或`子类的内部`进行访问，而不能从类外部直接访问。

**抽象方法abstract**
抽象方法很特殊，其只能在父类中声明，但具体的实现需要在子类中进行。
只有声明为 abstract 的类才能够声明抽象方法。
这种类一般用于定义个应用编程接口(API)，以后作为一个实现的模型存在。
继承于 abstract 类的子类，需要实现所有在 abstract 类中定义的方法。

```php
<?php 
	abstract class Employee {
		abstract function hire();
		abstract function fire();
		abstract function promote();
		abstract demote();
	}
?>
```

**最终类final**
标记为 final 的类，将不能被继承。
标记为 final 的方法，在子类里可以使用，但不能被子类覆盖重写。如果试图在子类中覆盖 final 方法，将会导致致命错误。

**静态作用域 static**
这种变量或方法不需要进行实例化就可以使用。而且其所做的改变会反映到所有的实例化对象中。
静态成员只能使用定义他的类的类名通过两个冒号进行访问。
如果需要在定义它的类中访问它，可以使用类名或特殊的类关键字`self`。

静态方法是以类作为作用域的函数。
静态方法不能访问这个类中的普通属性，因为那些属性属于一个对象，但可以访问静态属性。

> 因为在类中的方法中，无法使用`$this`关键字(这是只能在对象中使用的伪变量，指向当前变量)，但是可以使用`self`来表示当前的类，而`self`只能引用静态属性或静态方法，或者常量属性。

**常数const**
PHP 5 允许把常数封装在类里。他们不是对象，不能由 $this 引用。和静态属性类似。
引用时，需要使用类名或`self`再加上作用域解析符`::`及常数名。
在类外部引用，需要用类名和域解析符来引用常数名：`classname::constName`。

**类型提示**
类型提示就是在定义类的方法的时候，指定传入的参数的类型。这样可以确保传递给方法的对象确实是所期望的类实例。
例如，只有将类 Employ 的对象传递给 takeLunckbreak() 方法才有意义，则在定义该方法的时候，在其输入的参数 $employee 前面加上 Employee，强制实施此类型：`private function takeLunckbreak(Employee $employee)`。

> 类型提示只用于对象参数，而无法为整数、浮点数或字符串等类型的参数提供提示。


### 特殊方法
**构造函数**
__construct()  自定义构造函数。（在 PHP 4 中构造函数的名称与类名相同）
如果想定制对象的初始化过程，可以定义自己的构造函数。
当类的用户调用 new 时，PHP 会先查看是否有自定义的构造函数，如果有，就被自动调用。
这个函数会在 new 创建对象之后立即被调用。

还可以调用无关的类的构造函数，只需要在 __construct 前面加上类名即可：`classname::__construct()`。

**析构函数**
__destruct()  自定义析构函数。（PHP 4 中没有）
析构函数一般用于执行一些结束工作，如删除老文件、释放资源等，它在对象被释放之前被调用。
这个函数没有参数，也不能被直接调用。PHP 会在释放对象时隐含调用它。

**访问器方法**
__set() 写入器，用于给原本不存在的类变量赋值。
    这个函数必须要有两个参数。分别是对象的属性变量和要赋给它的值。成功执行则返回 True，否则返回 False。
    如果要赋值的变量不存在于类中，则会调用 __set() 方法来进行操作。
    如果要赋值的变量是`私有变量`或`受保护变量`，则会调用 __set() 方法，在这个函数中，能读取变量值，但是不能给其赋值，
    如果要赋值的变量存在且是公共变量，则不会调用这个函数，而是直接赋值。

__get() 读取器，用于获取类变量的值。
    这个参数只有一个参数。就是对象的属性名称。
    只有当要访问的变量不存在，或者是`私有变量`或`受保护变量`的时候，才会调用该方法，而且在这个函数中是可以访问`私有变量`和`受保护变量`的。
    如果要访问的变量不存在，最好要在 __get() 函数中做处理，否则会发生错误的。
    一般在 __get() 方法中，是需要一个 return 语句的，这样才能得到要访问的变量的值。

这两个方法每个在类里都只能定义一次。
当用户尝试访问对象属性时，无论是要赋值还是取值，这些特殊函数就会被自动调用。

```php
<?php
	class Employee {
		public $name;
		protected $secret = "Secret";

		function __set($propName, $propValue) {
			echo "Nonexistent variable: \$$propName";
		}

		function __get($propName) {
			echo "Get form __get() function.";
			return $this->$propName;
		}
	}

	$employee = new Employee();
	$employee->name = "Mario";
	$employee->title = "Executive Chef";
	# 给 name 赋值时，不会调用 __set()，不会有输出
	# 如果 name 是 private 或者 protected 的时候，
	# 输出：Nonexistent variable: $name
	# 给 title 赋值时，由于 title 不存在，则会调用 __set() 方法，
	# 输出：Nonexistent variable: $title

	$secret = $employee->secret;
	echo $secret;
	# secret 变量是受保护的变量，实例本是不能直接访问的
	# 但是由于设置了 __get() 方法，就会自动调用这个方法
	# 输出：Get form __get() function. Secret
?>
```

**自动加载对象**
如果一个脚本中需要使用多个类，那么一个个的加载这些类的文件将会很麻烦。
PHP 5 引入了一个自动加载对象的概念，能够很好的解决这个问题。

__autoload() 在当前执行脚本中定义了这个特殊的类之后，当引入了未在脚本中定义的类时，就会自动的调用这个函数。
所以我们可在这个方法中定义相应文件加载，就能自动引入所需要的类文件了。

> 这个方法不是在类中定义的，而是要在当前执行脚本中定义。

```php
<?php 
	function __autoload($class_name) {
		// 指定自动加载的文件的路径
		// 定义这个方法之后，就不需要再一个个的 require_once() 所需要的类文件了
		require_once("class/$class_name.class.php"); 
	}
?>
```

### 属性和方法
属性以在类声明体中定义，也可以在类外面动态的添加属性到对象上，但这并不是一个好的做法，建议不要使用。

方法则必须在类声明体中进行定义。
简单来说，方法是声明在类中的函数，所有也和函数定义类似，function 关键字在方法名前面，方法名之后的圆括号中是可选的参数列表，
如果没有提供关键字(如 public、protected、private 等)则默认为 public。方法体用大括号括起来：
	[keywords] function fn($argument ...) {
	   // To Do...
    }

在输出语句中(echo、print 等)：
	- 调用对象的属性时，可以直接放在双引号中，如`echo "author: $product->name";`，也可以将其放在大括号中，然后放在双引号中，如`echo "author: {$product->name}";`
	- 调用对象的方法时，如果在双引号中，则必须放在大括号中，否则会将方法名解析为对象的属性名，如`echo "author: $product->getName()";`会将方法 getName() 解析为属性名 getName 和一个圆括号 ()。正确的方法是：`echo "author: {$product->getName()}";`

### 继承
继承会使子类得到父类的方法和变量。
继承需要使用关键字`extends`。

在子类中，可以通过关键字`parent`引用到父类的一些方法和变量。
如，调用父类 Employee 的构造函数：`parent::Employee()` 或者 `parent::__construct()`。

如果企图从子类里设置或读取父类的私有属性，并不会看到一个错误消息，而是什么反应也没有，这个操作被忽略了。

如果在子类中定义了和父类中相同名称的方法或变量，就会发生覆盖。

**构造函数**
如果父类有构造函数，而子类没有构造函数，那么在子类实例化的时候，将会自动执行父类的构造函数。
而如果子类中也有构造函数，那么将只执行子类的构造函数，而不执行父类的构造函数，除非在子类构造函数中显式调用了父类的构造函数。

当使用`parent::__construct()`的时候，将会沿着类的继承链向上寻找，直到找到首先遇到的父类构造函数为止，然后就执行这个父类构造函数。

当然，我们也可以在子类的构造函数中，显式的用类名和域操作符来显式的调用其他类的构造函数。

### 对象克隆
PHP 5 中，将所有的对象都看做引用，而不是值。所有如果要创建对象的副本，就需要显式的使用`clone`命令。

通过在对象前面加上关键字`clone`来克隆对象。
语法`destinationObject = clone targetObject

克隆出来的对象和原对象有完全相同的变量和方法。但是之后对两者的操作将只影响其自身。

通过在对象的类定义中，定义一个 __clone() 方法，可以调整对象的克隆行为。
此方法会在类的对象被克隆的时候，自动执行。
在对象克隆的时候，除了将所有现有对象成员复制到目标对象之外，还会执行 __clone() 方法指定的操作。

```php
<?php 
	class Emplyoee {
		public $name = "OneEmplyoee";

		function __clone() {
			$this->name = "TwoEmplyoee";
		}
	}

	$one = new Employee();
	echo $one->name;       // OneEmplyoee

	$two = clone $one;
	echo $two->name;       // TwoEmplyoee
?>
```

### 接口
**接口只定义了类能做什么，而没有定义应该怎么做。**
接口(interface)定义了实现某种服务的一般规范，声明了所需要的`方法`和`常量`，但不指定如何实现。
之所以不给出实现的细节，是因为不同的实体可能需要使用不同的方式来实现公共的方法。
关键是要建立必须要实现的一组一般原则，只有满足了这些原则才能说实现了这个接口。

类需要使用`implements`关键字来声明其实现某个或某些接口。
通过 implements 关键字实现了接口后，就完成了一个契约：
    接口中的所有方法都必须实现，倘若实现类没有实现所有方法，则必须声明为抽象类，否则就将出现致命错误。

如果要同时实现多个接口，那么可以在每个接口之间使用逗号尽心分隔。

> 实现类能够继承自某个类，同时实现某个(些)接口。

```php
<?php 
    // 通常，在接口名前面加上字母 I 来进行标识，以便更容易确认
	interface IinterfaceName {
		const 1;
		const N;

		function methodName1 () ;
		function methodName2 () ;
	}

	interface Ideveloper {...}

	// 实现两个接口，每个接口之间使用逗号分隔
	class Class_name implements IinterfaceName, Ideveloper {
		function methodName1 () {
			// methodName1 () implementation
		}
		function methodName2 () {
			// methodName2 () implementation
		}
	}

	# 下面实现一个接口
	// 定义接口
	interface IPillage {
		function emptyBankAccount();
		function burnDocuments();
	}

	// 实现类：主管类
	class Executive extends Employee implements IPillage {
		private $totalStockOptions;

		function emptyBankAccount() {
			echo "Call CFO and ask to transfer funds to Swiss bank account.";
		}
		function burnDocuments() {
			echo "Torch the office suite.";
		}
	}

	// 实现类：普通员工
	class Assistant extends Employee implements IPillage {
		function takeMemo() {
			echo "Taking memo...";
		}
		function emptyBankAccount() {
			echo "Go on shopping spree width office credit card.";
		}
		function burnDocuments() {
			echo "Start small fire in the trash can.";
		}
	}
?>
```

### 抽象类
抽象类实质上就是无法实例化的类，只能作为由其他类继承的基类。抽象类将被可以实例化的类继承，后者称为具体类。
**抽象类可以完全实现，部分实现，或者根本未实现。**

如果试图实例化一个抽象类，将会得到一个错误信息：
    `Fatal error: Cannot instantiate abstract class Employee in ...`

> 抽象类还是接口？
- 如果要创建一个模型，这个模型将由一些紧密相关的对象采用，就可以使用抽象类；如果要创建将由一些不相干对象采用的功能，就使用接口。
- 如果必须从多个来源继承行为，就使用接口。PHP 类可以继承多个接口，但不能扩展多个抽象类。
- 如果知道所有类都会共享一个公共的行为实现，就使用抽象类，并在其中实现该行为；在接口中无法实现行为。

### 命名空间
命名空间(namespace)可根据上下文划分各种库和类，帮助我们更有效的管理代码库。

在 PHP 中，不能再同一个脚本中使用两个相同的类名，否则就会出现一个致命错误：
    `Fatel error: Cannot redeclaer class ...`

要使用命名空间，就需要再相应的类文件中，在最前面加入`namespace`开始的一个语句。
如在类文件开头加入`namespace Library;`语句，就会将这个文件的类放在`Library`空间中。

引用一个命名空间中的类的时候，就需要使用命名空间名称和域操作符后跟着类名来引用，从而可以避免类名冲突。

```php
<?php 
	# 文件 Library.inc.php 和 DataCleaner.inc.php 文件中都含有名为 Clean 的类
	# 在文件 Library.inc.php 开头加入这个语句：namespace Library;
	# 在文件 DataCleaner.inc.php 开头加入这个语句：namespace DataCleaner;
	# 之后就能如下方式引用各自的 Clean 类
	include "Library.inc.php";
	include "DataCleaner.inc.php";

	// 实例化 Library 中的 Clean 类
	$filter = new Library::Clean();

	// 实例化 DataCleaner 中的 Clean 类
	$profanity = new DataCleaner::Clean();

	# 之后就能对这两个类分别操作其方法和属性了
?>
```

### 不支持的高级 OOP 特性
**方法重载** PHP 不支持通过函数重载实现多态。
**操作符重载** 目前不支持根据所修改数据的类型为操作符赋予新的含义。
**多重继承** PHP 不支持多重继承，但是支持实现多个接口。


-------------------------------------------------------------------------------


## 日期和时间
### date()
date() 函数用于格式化时间和日期。
    语法：string date ( string format [, int timestamp] )
    第一个参数用来指定相应的格式，
    第二个参数是一个时间戳，默认等于 time() 函数的值。

日期和时间的格式选项
|  选项  |    描述                                                     |
|:------:| ----------------------------------------------------------- |
| a      | am 或 pm                                                    |
| A      | AM 或 PM                                                    |
| B      | 互联网时间                                                  |
| d      | 月里的日子，两位数字，包含前导 0                            |
| D      | 以文本表示的星期里的日子，三个字母(如星期一就是 Mon)        |
| F      | 以文本表示的月份(如十月 October)                            |
| g      | 小时，12小时制，没有前导 0( 1 ~ 12 )                        |
| G      | 小时，24小时制，没有前导 0( 0 ~ 23)                         |
| h      | 小时，12小时制，有前导 0( 01 ~ 12 )                         |
| H      | 小时，24小时制，有前导 0( 00 ~ 23)                          |
| i      | 具有前导 0 的分钟数( 00 ~ 59)                               |
| I      | 是否是夏时制，1 表示是，0 表示不是                          |
| j      | 月份里的日子，没有前导 0( 1 ~ 31)                           |
| l      | 以文本表示的星期里的日子(比如 Friday)                       |
| L      | 是否是闰年，1 表示是闰年，0 表示不是                        |
| m      | 具有前导 0 的月份( 01 ~ 12 )                                |
| M      | 以文本表示的月份，三个字母(比如七月是 Jul)                  |
| n      | 没有前导 0 的月份数( 1 ~ 12 )                               |
| r      | RFC 822 格式化的日期(比如：Thu, 21 Dec 2000 16:01:07 +0200) |
| s      | 具有前导 0 的秒数( 00 ~ 59 )                                |
| S      | 英语序数后缀，文本形式，两个字符(比如 th 和 nd)             |
| t      | 指定月份的总天数(也就是 28 ~ 31)                            |
| T      | 本机设置的时区，如 MDT                                      |
| U      | 从 1970 年 1 月 1 日 00:00:00GMT 以来的秒数                 |
| w      | 星期里的日子，以数字表示，也就是 0(星期日) ~ 6(星期六)      |
| Y      | 年份，四位数字                                              |
| y      | 年份，两位数字                                              |
| z      | 年里的日子( 0 ~ 365 )                                       |
| Z      | 以秒表示的时区偏差(-43200 ~ 43200)。<br>UTC 以西地区偏差总是负数，以东总是正数。| 

> 如果表示年份的数值是两位数，那么 0~69 之间的数值表示 2000 年到 2069 年；80~100 之间的数值表示 1980 年到 2000 年。

### 获取时间戳
**time()** 这个函数以秒为单位，返回当前自 UNIX 纪元以来的时间戳。
    语法：int time ( void )
    要得到更精确的时间可以使用以 microtime() 函数。
    把 time() 函数的输出加上或减去一定的秒数，就可以得到将来或从前的某个时间。

**mktime()** 返回指定时间的时间戳。
    语法：int mktime ([int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]])
	参数从右向左，可以按次序省略，被省略的参数会被设置为本地日期和时间中的相应的值。

**strtotime()** 这个函数能够从包含任何日期和时间式样的字符串里返回时间戳。
    语法：int strtotime ( string time [, int now] )
    在指定了第二个参数时，它返回指定时间的时标，否则就返回当前日期的时间的时标。

```php
<?php
	echo strtotime("last month");     		 // 上个月的时标
	echo strtotime("02 September 2006");     // 2006 年 2 月 2 日的时标
	echo strtotime("+2 days");               // 两天之后
	echo strtotime("+1 week");               // 一周后
	echo strtotime("+1 week 2 days 4 hours 2 seconds");     // 1周2天4小时2秒之后
	echo strtotime("next Thursday");         // 下个星期三
?>
```

### 获取日期和时间
**getdate()** 返回一个关联数组，其中包含本地的日期和时间。
    语法：array getdate ( [int timestamp] )
    如果没有指定参数，则表示获取当前的日期和时间。
    如果指定了时标参数，则表示获取时标表示的日期和时间。
    返回的数组里的值如下表：

|    关键字    |    值                               |
| ------------ | ----------------------------------- |
| seconds      | 秒数，0~59                          |
| minutes      | 分数，0~59                          |
| hours        | 小时，0~23                          |
| mday         | 月里的日子，1~31                    |
| wday         | 星期里的日子，0(星期日) ~ 6(星期六) |
| mon          | 月份，1~12                          |
| year         | 四位数年份                          |
| yday         | 年里的日子，0~365                   |
| month        | 以文本表示的月份，如 January        |
| 0            | 自 UNIX 纪元以来的秒数              |

**gettimeofday()** 这个函数返回与当前时间有关的元素所组成的一个关联数组。
    语法：mixed gettimeofday ( [bool return_float] )
    如果可选参数 return_float 设置为 true，则该函数将以浮点数值形式返回当前时间。
    返回的数组中，包含四个元素，其索引值如下：
      - dsttime   代表是否使用日光节约时间算法。有11个可能的值，其中 0 表示不使用日光节约算法。
      - minuteswest  格林尼治标准时间(GMT)西部的分钟数。
      - sec       自 UNIX 纪元后的秒数。
      - usec      整数秒值后面的微秒数。

### 检验日期
**checkdate()** 能够检查它收到的参数是否是个有效的日期值，有效则返回 true，否则返回 false。
    语法：bool checkdate ( int month, int day, int year )
    有效的日期值需要满足以下几个条件：
        - 月份必须在 1~12 之间；
        - 天数必须位于每个特定月份及闰年的允许范围之内
        - 年必须在 1~32767 之间。

### 本地化日期和时间
**setlocale()** 这个函数通过赋予新值来改变 PHP 的默认本地化环境。
    语法：string setlocale ( mixed category, string locale [, string locale ...] )
          string setlocale ( mixed category, array locale )
    参数 category 是为数据的某个分类制定本地化环境。一共有 6 个分类，见下面的表格。
    参数 locale 可以传入多个不同的字符串，或者用一个本地化环境值数组。这个特性用于弥补不同操作系统间本地化环境编码差异。
    正式的本地化字符串遵循如下结构：`language_COUNTRY.characterset`。
    如本地化字符串 zh_CN.gb18030 用于处理藏语、维吾尔语和黎语字符，而 zh\_CN.gb2312 用于简体中文。

|    分类     |    描述                        |
| ----------- | ------------------------------ |
| LC_ALL      | 为以下所有 5 类 设置本地化规则 |
| LC_COLLATE  | 字符串比较。                   |
| LC_CTYPE    | 字符分类和转换。如转换大小写   |
| LC_MONETARY | 货币形式。                     |
| LC_NUMERIC  | 数值形式。(数值的显示)         |
| LC_TIME     | 日期和时间形式。               |

```php
<?php
    # 根据意大利本地化环境格式化货币总和
    setlocale(LC_MONETARY, "it_IT");
    echo money_format("%i", 478.54);
    // EUR 478,54
?>
```

**strftime()** 这个函数根据 setlocale() 指定的本地化设置来格式化日期和时间。
    语法：string strftime ( string format [, int timestamp] )
    类似于 date() 函数，但是 format 参数具有不同的选项(见图片：strftime() 函数的参数.png)。

### 其他时间函数
**getlastmod()** 返回当前脚本的内容最后一次修改的时间戳。
    语法：int getlastmod( void )

### DateTime 类
PHP 5.1 中对日期和时间的支持有所改进，不仅增加了一个面向对象接口，还能够相应于各个时区管理日期和时间。

**构造函数**
使用 Date 特性之前，需要通过其构造函数实例化一个日期对象。
    语法：object DateTime ( [string $time [, DateTimeZone $timezone]] )
    可以在实例化时，设置日期，也可以示例化之后用其方法修改。如果没有设置参数，会将日期对象设置为当前日期。
    另外，time 参数可以是 strtotime() 函数支持的任何格式。
    可选参数 timezone 用来设置时区。默认情况下，PHP 会使用服务器指定的时间。

```php
<?php
	# 创建一个空的日期对象
    $date = new DateTime();

    # 创建一个日期为 2016-02-14 的日期对象
    $date = new DateTime("14 February 2016");

    # 创建一个日期为当天，时间为 14:36:00 的日期对象
    $date = new DateTime("14:36:00");
?>
```

**格式化方法**
format() 方法会将日期对象格式为指定的格式。
    语法：$date->format( string format )
    其参数和 date() 函数的参数相同。

**设置日期**
一旦实例化 DateTime 对象，可以使用 setDate() 方法来修改其日期。
    语法：bool setDate ( int year, int month, int day )
    如果设置成功则返回 True，否则就返回 False。
    这个方法会设置日期对象的年月日。

**设置时间**
实例化之后，还能用 setTime() 方法来修改日期对象的时间。
    语法：bool setTime ( int hour, int minute [, int second] )

**设置日期和时间**
可以使用 modify() 方法来修改 DateTime 对象的日期和时间。
    语法：bool modify ( string time )
    这个方法的参数和 strtotime() 的参数使用相同的格式。

```php
<?php
	$date = new DateTime("February 10, 2016 10:32");
	$date->modify("+7 hours");
	echo $date->format("Y-m-d h:i:s");
	// 2016-02-10 17:32:00
?>
```


-------------------------------------------------------------------------------


## 错误与异常处理
### 配置指令
**1. 设置错误敏感级别**
php.ini 文件中，error_reporting 指令用于确定报告的敏感级别。
共有 14 个不同的级别，这些级别的任何组合都是有效的。

完整的级别列表如下：(注意，每个级别都包含位于其下的所有级别)。
|    级别              |    描述                  |
| -------------------- | ------------------------ |
| E_ALL                | 所有错误和警告           |
| E_COMPILE\_ERROR     | 致命的编译时错误         |
| E_COMPILE\_WARNING   | 编译时警告               |
| E_CORE\_ERROR        | PHP 开始启动时的致命错误 |
| E_CORE\_WARNING      | PHP 开始启动时发生的警告 |
| E_ERROR              | 致命的运行时错误         |
| E_NOTICE             | 运行时注意消息           |
| E_PARSE              | 编译时解析错误           |
| E_RECOVERABLE\_ERROR | 可恢复错误               |
| E_STRICT             | PHP 版本可移植性建议     |
| E_USER\_ERROR        | 用户导致的错误           |
| E_USER\_NOTICE       | 用户导致的注意消息       |
| E_USER\_WARNING      | 用户导致的警告           |
| E_WARNING            | 运行时警告               |

> error_reportin 指令的值，使用 ~ 符号表示逻辑操作符 NOT，用 | 符号表示或，用 & 表示并且。

```php
<?php
	// 显示所有的错误。开发时一般选择这个
	error_reporting = E_ALL;

	// 只考虑致命的运行时错误、解析错误和核心错误
	error_reporting = E_ERROR | E_PARSE | E_CORE_ERROR;

	// 希望报告除用户导致的错误之外的所有错误
	error_reporting = E_ALL & ~(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE)
?>
```

**2. 在浏览器上显示错误**
启用 display_errors 时，将显示满足 error\_reporting 所定义规则的所有错误。

> 应当只在测试期间启用此指令，网站投入使用时要将其禁用。

**3. 显示启动错误**
启用 display_startup\_errors 指令会显示 PHP 引擎初始化时遇到的所有错误。

> 应当只在测试期间启用此指令，网站投入使用时要将其禁用。

**4. 记录错误**
应当使用启用 log_errors 指令，这样会将所有的错误信息都保存到日志文件中国。

这些日志语句记录的位置取决于 error_log 指令。

**5. 标识日志文件**
错误可以发送给系统 syslog，或者送往由管理员通过 error_log 指令指定的文件。

如果此指令设置为 syslog，在 Linux 上错误语句将送往 syslog，而在 Windows 上错误将发送到事件日志。

### 错误日志
除了一般的错误输出之外，PHP 还允许向系统 syslog 发送定制的消息。

**1. 初始化 PHP 的日志工具**
define_syslog\_variables() 函数初始化一些常量，这些常量是使用 openlog()、closelog() 和 syslog() 函数时所必需的。
语法：void define_syslog\_variables ( void )

> 使用下面的三个日志函数之前，需要先执行此函数。

**2. 打开日志链接**
openlog() 函数打开一个与所在平台上系统日志器的连接，通过指定几个将在日志上下文使用的参数，为向系统日志插入消息做好准备。
语法：int openlog ( string ident, int option, int facility )
参数：ident，增加到每一项开始处的消息标识符。通常这个值设置为程序名。如设置为 PHP 或 PHP5。
      option，确定生成消息时使用那些日志选项。具体见下表。如果需要多个选项，各个选项间要使用竖线分隔。
      facility，有助于确定记录消息日志的程序属于哪一类。包括 LOG_KERN、LOG\_USER、LOG\_MAIL、LOG\_DAEMON、LOG\_AUTH、LOG\_LPR 和 LOG\_LOCALN。

|     选项     |    描述                                              |
| ------------ | ---------------------------------------------------- |
| LOG_CONS     | 如果写入 syslog 时发生错误，则将输出发送到系统控制台 |
| LOG_NDELAY   | 立即打开与 syslog 的链接                             |
| LOG_ODELAY   | 不要打开连接，直到提交了第一条消息位置。这是默认值   |
| LOG_PERROR   | 要记录的消息要同时输出到 syslog 和标准错误           |
| LOG_PID      | 每个消息都带有进程 ID                                |

**3. 关闭日志连接**
closelog() 函数关闭由 openlog() 打开的连接。
语法：int closelog ( void )

**4. 向日志模板发送消息**
syslog() 函数负责向 syslog 发送一条定制消息。
语法：int syslog ( int pripority, string message )
第一个参数 pripority 指定 syslog 优先级，表示严重程度，可以是如下的值：
    LOG_EMERG  严重的系统问题，可能预示着崩溃。
    LOG_ALTER  必须解决的情况，可能危害系统完整性。
    LOG_CRIT   紧急错误，可能导致服务不可用，但不一定会使系统陷入危险。
    LOG_ERR    一般错误
    LOG_WARNING 一般警告
    LOG_NOTICE 正常，但值得注意的情况
    LOG_INFO   一般信息
    LOG_DEBUG  一般只与调试应用程序有关的信息
第二个参数 message 指定要记录的文本信息。如果希望记录由 PHP 引擎提供的错误信息，就可以在 message 中包含字符串`%m`。次字符串将被 PHP 引擎在运行时提供的错误消息字符串(strerror)所代替。

```php
<?php
	define_syslog_variables();
	openlog("CHP8", LOG_PID, LOG_USER);
	syslog(LOG_WARNING, 'Chapter 8 example waring.');
	closelog();

	# 这段代码将在 messages syslog 文件中生成类似下面的一条日志：
	// Dec 5 20:09:29 CHP8[30326]: Chapter 8 example warning.
?>
```
 
### 异常处理
PHP 提供了 C++ 语言的异常处理器语法：try ... catch。

可以创建多个处理器块 catch 来解决多个错误。为此，可以使用各个预定义处理器，或者扩展创造自己的定制处理器。

PHP 目前只提供了一个简单的处理器`exception`。不过，
如果如果扩展这个处理器，将来的版本很可能会增加其他的默认处理器。

如，有如下的伪代码：

```php
try {
	# perform som task
	if ($exp1) { # something goes wrong
		throw IOexception("Could not open file.");
	}
	elseif ($exp2) { # something else goes wrong
		throw Numberexception("Division by zero not allowed.");
	}
} catch(IOexception) {
	# process the IOexception
} catch(Numberexception) {
	# process the Numberexception
}
```

### 基本异常类
PHP 提供的基本异常类非常简单，它提供了一个不带参数的默认构造函数，一个带有两个可选参数的重载构造函数，还包括 6 个方法。

**默认构造函数**
默认的异常构造函数不带参数。可以如下方式调用：
    throw new Exception();

异常实例化之后，就可以使用后面介绍的 6 个方法。但是只有 4 个可以任意使用；另外两个只在使用重载构造函数实例化异常类时才能使用。

**重载构造函数**
重载构造函数可以有两个可选参数：
   第一个参数，message，表示一个对用户友好的解释。可以通过后面介绍的 getMessage() 方法传递给用户。
   第二个参数，error_code，用于保存错误标识符，可以映射到某个标识符-消息表。通常用于国际化和本地化。可以使用 getCode() 方法得到。
可以使用如下方式来调用：
    throw new Exception("Something bad just happended", 4);
    throw new Exception("Something bad just happended");
    throw new Exception("", 4);

当然，在异常被捕获之前，实际上对异常不会发生任何事情。

**方法**
getMessage()   返回传递给构造函数的消息
getCode()      返回传递给构造函数的错误代码
getLine()      返回抛出异常的行号
getFile()      返回抛出异常的文件名
getTrace()     返回一个数组，其中包括出现错误的上下文新：文件名、行号、函数名、函数参数。
getTraceAsString()  返回与 getTrace() 完全相同的信息，只是返回的信息是一个字符串。

> 虽然可以扩展异常基类，但不能覆盖任何一个方法，因为他们都声明为 final。

### 扩展异常类
我们也可以扩展基本异常类，创建自己的异常类。
扩展异常类和扩展其他类的方式相同。

创建自己的异常类之后，就可以根据情况抛出不同的异常，从而使用不同的异常处理器来处理。

```php
<?php
    # 下面这个示例，就是从相应的语言文本中，根据异常号，生成本地化的异常消息
	class My_Exception extends Exception {
		function __construct($language, $errorcode) {
			$this->language  = $language;
			$this->errorcode = $errorcode;
		}

		function getMessageMap() {
			$errors = file("errors/".$this->language.".txt");
			foreach ($errors as $error) {
				list($key, $value) = explode(",", $error, 2);
				$errorArray[$key]  = $value;
			}

			return $errorArray[$this->errorcode];
		}
	}

	try {
		throw new My_Exception("english", 4);
	}
	catch (My_Exception $e) {
		echo $e->getMessageMap();
	}
?>
```


-------------------------------------------------------------------------------

## 其他
### 用户重定向
通过位置头标可以把用户重定向到其他页面：
`header('Location: http://www.url.com/new_page.html')`

> 所有的头标信息必须位于任何 HTML 和文本之前发送给浏览器。




