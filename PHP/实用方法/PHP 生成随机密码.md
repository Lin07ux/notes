
## 思路分析
生成随机密码，简单来说就是在允许的字符集合中无规律的返回指定长度的字符串。主要是三点：指定字符集合、指定长度、无规律(随机)。

**指定字符集合**
由于密码一般是让用来在验证权限的时候输入给系统的，所以一般要求密码的字符是可输入的。另外，还会根据情况需要去除一些特定的字符等。一般来说，字符集就是26个英文字符的大小写、十个数字、一些特殊符号等。只要我们根据

**指定长度**
为了安全，我们也经常会要求密码与一定的长度，比如最低6位长度等。我们可以使用随机数生成函数(如`mt_rand()`)来生成一个长度，也可以指定固定一个长度。

**无规律**
有了字符集、长度，就可以从字符集中选择出来指定长度的个数的字符，而作为密码来说，关键是需要选择出来的字符是不固定的，而且选择出来的顺序也是随机的。给字符集中的字符指定次序，然后可以考虑在取字符的时候，用生成的随机数来定位要取出来的字符，从而就可以生成基本上是无规律的字符串了。

## 实现
**方法1**

1. 在 33 – 126 中生成一个随机整数，如 35，
2. 将 35 转换成对应的ASCII码字符，如 35 对应 #
3. 重复以上 1、2 步骤 n 次，连接成 n 位的密码

```php
function create_password($length = 8)
{
  $pwd = '';
  for ($i = 0; $i < $length; $i++) 
  {
    $pwd .= chr(mt_rand(33, 126));
  }
  return $pwd;
}
// 调用该函数，传递长度参数$pw_length = 6
echo create_password(6);
```

**方法2**

1. 预置一个的字符串 $chars ，包括 a – z，A – Z，0 – 9，以及一些特殊字符
2. 在 $chars 字符串中随机取一个字符
3. 重复第二步 n 次，可得长度为 n 的密码

```php
unction generate_password( $length = 8 ) {
  // 密码字符集，可任意添加你需要的字符
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
  $password = '';
  for ( $i = 0; $i < $length; $i++ ) 
  {
    // 这里提供两种字符获取方式
    // 第一种是使用 substr 截取$chars中的任意一位字符；
    // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
   
    // 第二种是取字符数组 $chars 的任意元素 
    $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
  }
  return $password;
}
```

**方法3**

1. 预置一个的字符数组 $chars ，包括 a – z，A – Z，0 – 9，以及一些特殊字符
2. 通过 array_rand() 从数组 $chars 中随机选出 n 个元素
3. 根据已获取的键名数组，从数组 $chars 取出字符拼接字符串。该方法的缺点是相同的字符不会重复取。

```php
function make_password( $length = 8 )
{
  // 密码字符集，可任意添加你需要的字符
  $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 
  'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's', 
  't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D', 
  'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 
  'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', 
  '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', 
  '@','#', '$', '%', '^', '&', '*', '(', ')', '-', '_', 
  '[', ']', '{', '}', '<', '>', '~', '`', '+', '=', ',', 
  '.', ';', ':', '/', '?', '|');
  // 在 $chars 中随机取 $length 个数组元素键名
  $keys = array_rand($chars, $length); 
  $password = '';
  for($i = 0; $i < $length; $i++)
  {
    // 将 $length 个数组元素连接成字符串
    $password .= $chars[$keys[$i]];
  }
  return $password;
}
```

**方法4**
这个方法和前面的三个方法有点不太一样，是通过获取一个 hash 值中的子串来作为生成的密码。比较简单，但是生成的结果都是英文字母和数字。

1. time() 获取当前的 Unix 时间戳
2. 将第一步获取的时间戳进行 md5() 加密
3. 将第二步加密的结果，截取 n 位即得想要的密码

```php
function get_password( $length = 8 ) 
{
    $str = substr(md5(time()), 0, $length);
    return $str;
}
```

## 时间效率
我们使用以下PHP代码，计算上面的 4 个随机密码生成函数生成 6 位密码的运行时间，进而对他们的时间效率进行一个简单的对比。

```php
<?php
function getmicrotime()
{
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}
 
// 记录开始时间
$time_start = getmicrotime();
    
// 这里放要执行的PHP代码，如:
// echo create_password(6);
 
// 记录结束时间
$time_end = getmicrotime();
$time = $time_end - $time_start;

 // 输出运行总时间 
echo "执行时间 $time seconds";
?>
```

最终得出的结果是：

* 方法一：9.8943710327148E-5 秒
* 方法二：9.6797943115234E-5 秒
* 方法三：0.00017499923706055 秒
* 方法四：3.4093856811523E-5 秒

可以看出方法一和方法二的执行时间都差不多，方法四运行时间最短，而方法三的运行时间稍微长点。

转摘：[PHP生成随机密码的几种方法](http://www.techug.com/how-to-create-a-password-generator-using-php)


