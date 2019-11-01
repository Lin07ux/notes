mycrypt 作为 PHP 5.x 版本的对称加密方式，从 PHP 7.0 开始被废弃，所以需要将代码从 mycrypt 更新到 openssl 系列的函数。在进行代码更新时，需要先了解 mycrypt 和 openssl 系列函数的区别和联系。

## 一、基础知识

### 1.1 加密模式

模式是用来将数据分组串起来从而使得任意数据都能加密的算法。模式适用于任何分组的加密算法，包括 AES。

常用的加密模式主要有：CBC(密码分组链模式)、ECB(电子密码本)、CFB(密文反馈)、OFB(输出反馈)、CTR(计数器模式)、PCBC(填充密码块链接)。

这些模式除了 ECB 由于没有使用 IV 而不太安全，其他模式差别并没有太明显，大部分的区别在 IV 和 KEY 来计算密文的方法略有区别。一般推荐使用 CBC 模式。

### 1.2 填充

AES 是分组加密，也就是说它是对固定大小的分组数据进行处理。AES 每次处理 128 位（16 字节）的输入。不过，很多时候要加密的数据都不只是 16 字节长。为了解决这个问题，就需要对明文进行填充处理，将普通文本的长度扩展到需要的长度。

在加密时进行填充，相应的，在解密的是就需要去除填充，关键点就在于填充的数据能够在解密后正确的移除。

填充算法的实现方式有多种，常用的一般有两种填充方式：`PKCS#7`和`PKCS#5`。

 > `PKCS#5`其实是`PKCS#7`的一个特例：PKCS5 的块大小固定为 8，而 PKCS7 的块大小可以根据使用的加密算法改变，如果块大小设为 8，就是 PKCS5，因此 PKCS7 更通用一些。
> 
> 块的大小是加密位数决定的，8 个二进制位一个字节，128 位算法的块大小就是`128 / 8 = 16`，256 位算法的块大小就是`256 / 8 = 32`。

### 1.3 初始化向量

在链模式(如 CBC)中，每个分组都会影响到下一个分组的加密。这就是为了保证两个相同的普通文本分组不会生成相同的密文分组。

第一个分组是个特列，因为它前面再没有其他的分组了。链模式允许定义一个额外的称为初始化向量(Initialization Vector, IV)的分组来开始这个链，这个通常会被标成可选的。默认的它会用一个全是 0 的分组，那样会容易受到特定的攻击的侵害。

IV 的长度为 16 字节，超过或者不足，可能实现的库都会进行补齐或截断。但是由于块的长度是 16 字节，所以一般可以认为需要的IV 是 16 字节。

### 1.4 密钥

AES 使用固定长度的密钥，而不是变长密码。必须将密码转成密钥才能在 AES 中使用它们。AES 接受三种长度的密钥：128 位(16 字节)、192 位(24 字节)、256 位(32 字节)。

在内部实现上，AES 只是提供一个接收固定长度密钥和 16 字节大小的分组，然后生成另外一个不同的 16 字节大小的分组的数学函数。

> 一些语言的库会进行自动截取，让人以为任何长度的秘钥都可以。而这其实是有区别的。

## 二、mcrypt 加解密

mcrypt 加解密时，如果不主动设置填充，那么 PHP 会使用`\x00`来进行填充。所以解密时则要移除多余的`\x00`。当然也可以懒一点，不移除`\x00`，因为在 PHP 中字符串`"string\x00"`与字符串`"string"`除了长度不一样外，其他表现均一致。

> 事实上，并非是以`\x00`进行填充，从 [源码](https://github.com/php/php-src/blob/php-7.0.30/ext/mcrypt/mcrypt.c) 中可以发现，首先申请了一个 16 位的空字符串，所以初始化时每位字节均为`\x00`，实际上可以说其中并没有填充，只是它本来就是`\x00`。

如果使用自主填充，则一般选择使用`PKCS#7`来填充，相应的，解密时也要按照`PKCS#7`方式去除填充。

### 默认填充

```php
$key = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'; 
$iv = 'aaaaaaaaaaaaaaaa';
$str = 'dataString';

// 加密
$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
mcrypt_generic_init($cipher, $key, $iv);
$encrypted = mcrypt_generic($cipher, $data);
mcrypt_generic_deinit($cipher);
mcrypt_module_close($cipher);

echo base64_encode($encrypted) . "\n";

// 解密
$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
mcrypt_generic_init($cipher, $key, $iv);
$decrypted = mdecrypt_generic($cipher, $cipherText256);
mcrypt_generic_deinit($cipher);
mcrypt_module_close($cipher);

echo trim($decrypted) . "\n";
```

### 自主填充

```php
$key = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'; 
$iv = 'aaaaaaaaaaaaaaaa';
$data = 'dataString';

$size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

// 加密
$pad = $size - (strlen($data) % $size);
$data = $data.str_repeat(chr($pad), $pad);
$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);

echo base64_encode($encrypted) . "\n";

// 解密
$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
$num = ord(substr($decrypted, -1));
$decrypted = $num === 62 ? $decrypted : substr($decrypted, 0, -$num);

echo $decrypted . "\n";
```

## openssl 加解密

使用 openssl 加解密的时候，如果`$options`参数不含有`OPENSSL_ZERO_PADDING`，那么默认就是使用`PKCS#7`方式进行填充的，和上面 mcrypt 的自主填充方式一样。

如果想自主填充，那么就需要先手动对明文进行填充，然后在解密的时候也需要手动去除填充字符。

### 默认填充

> 对应上面 mcrypt 的自主填充方式。

```php
$key = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'; 
$iv = 'aaaaaaaaaaaaaaaa';
$data = 'dataString';

// 加密
$encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

echo base64_encode($encrypted) . "\n";

// 解密
$decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

echo $decrypted . "\n";
```

### 自主填充

> 对应上面 mcrypt 的默认填充方式。

```php
$key = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'; 
$iv = 'aaaaaaaaaaaaaaaa';
$data = 'dataString';

$size = openssl_cipher_iv_length("AES-256-CBC");
$data = $data . str_repeat("\x00", $size - (strlen($data) % $size)); // 双引号可以解析 ASCII 码 \x00

$encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

echo base64_encode($encrypted) . "\n";

$decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

echo rtrim($decrypted) . "\n";
```

### options

在使用`openssl_encrypt()`和`openssl_decrypt()`方法时，可以对`$options`参数传入不同的值，而这个值是以下标记的按位或：`OPENSSL_RAW_DATA`、`OPENSSL_ZERO_PADDING`。这两个位有不同的作用：

1. `OPENSSL_ZERO_PADDING`

    这个位可以影响 openssl 作用域的上下文。如果`$options`不包含这个位，则会使用自动使用`PKCS#7`填充方式，如果包含这个位，则需要手动在加密前使用自己的填充，或者在解密后去除自己的填充。
    
    > So as we can see here, OPENSSL_ZERO_PADDING has a direct impact on the OpenSSL context.  EVP_CIPHER_CTX_set_padding() enables or disables padding (enabled by default).  So, OPENSSL_ZERO_PADDING disables padding for the context, which means that you will have to manually apply your own padding out to the block size.  Without using OPENSSL_ZERO_PADDING, you will automatically get PKCS#7 padding.

2. `OPENSSL_RAW_DATA`

    这个位不会影响 openssl 作用域的上下文，但是会影响执行后返回的结果。在加密时，如果不指定该位，则会返回 Base64 加密后的结果，否则会返回原始二进制结果。在解密时，如果不指定该位，则待解密的字符串会被作为 Base64 字符串进行处理，否会会被作为原始二进制字符串进行处理。
    
    > OPENSSL_RAW_DATA does not affect the OpenSSL context but has an impact on the format of the data returned to the caller.  When OPENSSL_RAW_DATA is specified, the returned data is returned as-is.  When it is not specified, Base64 encoded data is returned to the caller.
   
### 模式

在上面的示例中，mcrypt 中的`AES-128-CBC`算法，在 openssl 中需要替换成`AES-256-CBC`，这和所使用的加密密钥的长度有关：

* mcrypt 中`MCRYPT_RIJNDAEL_128`的 128 并不是指密钥的位数，可能是指的是加密之后出来的位数。
* openssl 中`AES-256-CBC`的 256 应该是跟秘钥的位数。

对于 mcrypt 来说，不论使用的是 16 字节、24 字节还是 32 字节的密钥，都使用`MCRYPT_RIJNDAEL_128`表示，在内部会自动进行判断；对 openssl 来说，则需要根据密钥位数使用对应的`AES-128-CBC`、`AES-191-ECB`、`AES-256-ECB`模式。
    
## 参考

1. [openssl_encrypt 文档](https://secure.php.net/manual/zh/function.openssl-encrypt.php#117208)
2. [mcrypt_encrypt 文档](https://secure.php.net/manual/zh/function.mcrypt-encrypt.php)
3. [PHP7.1中使用openssl替换mcrypt](https://www.tuicool.com/articles/vAf6B36)
4. [AES/CBC/PKCS5Padding的PHP实现](https://my.oschina.net/luoxiaojun1992/blog/883123)
5. [PHP aes加密 mcrypt转openssl问题](https://www.douban.com/note/628737175/)
6. [MCrypt rijndael-128 to OpenSSL aes-128-ecb conversion](https://stackoverflow.com/questions/45218465/mcrypt-rijndael-128-to-openssl-aes-128-ecb-conversion)

