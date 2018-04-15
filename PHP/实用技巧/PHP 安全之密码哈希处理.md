### 导读

对于密码安全，开发者需要做到：

1. 绝对不能知道用户的密码，我们必须将用户的密码进行加密处理，不能将用户的原始密码直接保存在数据库。

2. 不要限制用户密码格式，如果规定格式的话，很可能被攻击者利用并破解，当然我们需要限制密码的最小长度即可，建议最少 8 位，越长越好。

3. 不能通过邮箱发送用户密码，我们在开发邮件找回密码的应用时，并不是将用户密码通过邮件告知给用户，而是将重置密码的链接以邮件的形式发给用户，让用户自己去重新设置密码。

对于其中的第一项，就需要我们在保存用户注册时的密码时进行一定的加密混淆处理。常见的就是对密码进行 Hash 处理。

### Hash 与加盐

PHP 中有很多的哈希函数，`md5()`就是最常用的一个了。很多开发者都使用`md5('abc123')`对用户密码进行加密处理，这样做没有错，但是安全性还是很低的，因为很多网站的用户数据都是用`md5`进行加密处理的，所以就发生了撞库事件，最典型的就是前些年 12306 火车票网站上的用户信息泄露事件。很多人在多个网站使用相同的密码，黑客将 A 站的用户密码和 B 站的密码对比发现是相同的，于是 A 站的密码就登录上了 B 站。另外一个，`md5`算法很早就能破解了，所以单单`md5`处理是不安全的。

那么我们可以使用`md5`加`salt`来增强加密后的密码安全性，`salt`即盐值，这个值要随机生成，可在用户注册的时候和密码一起生成并保存到数据库中，用户登录验证的时候再把密码和盐值一起组合验证。

下面就是一个简单的示例：

```php
$password = '1dhsh#sdLs';
$salt = randStr();
$md5pass = md5('hello'.$password.$salt);

function randStr($length = 8){ 
    $randpwd = ''; 
    for ($i = 0; $i < $length; $i++){ 
        $randpwd .= chr(mt_rand(33, 126)); 
    } 
    return $randpwd; 
}
```

上面的代码将密码和盐值以及常量混合，再`md5`的到一个复杂的加密后的字符串，然后将加密后的字符串`$md5pass`和盐值`$salt`保存到数据表中，验证的时候再拿出来，按照同样的方式组合，如果组合加密后的字符串与数据表中的`$md5pass`值一样，那么就验证成功了。总的来说设置相对复杂的密码加盐处理后破解难度还是很大的。

> 当然，我们也可以将`md5`换成其他的 Hash 函数。

### password_hash/password_verify

PHP 版本 >= 5.5 时，可以使用`password_hash()`和`password_verify()`来对用户的密码进行加密和验证。

对于密码加盐处理，`password_hash`函数现在使用的是目前 PHP 所支持的最强大的加密算法 BCrypt。 当然，此函数未来会支持更多的加密算法。

`password_hash()`已经帮我们处理好了加盐。加进去的随机子串通过加密算法自动保存着，成为哈希的一部分。`password_verify()`会把随机子串从中提取，所以你不必使用另一个数据库来记录这些随机子串，大大简化了计算密码哈希值和验证密码的操作。

下面是一个简单的示例：

首先是注册页面：

```php
<?php
try {
    // 验证email
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('非法的Email');
    }

    // 验证密码
    $password = filter_input(INPUT_POST, 'password');
    if (!$password || mb_strlen($password) < 8) {
        throw new Exception('密码长度必须大于8位');
    }

    // 检测用户名是否已存在
    $sql = "SELECT username FROM user WHERE username=:username";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(
        ':username' => $email
    ));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        exit('用户Email已存在');
    }

    // 创建密码哈希值
    $passwordHash = password_hash(
       $password,
       PASSWORD_DEFAULT,
       ['cost' => 12]  // 工作因子，默认是 10，可以根据服务器硬件能力适当提升
    );
    if ($passwordHash === false) {
        throw new Exception('Password hash failed');
    }
    
    $sql_insert = "INSERT INTO `user` (username,password) VALUES (:username,:password)";
    $stmt = $db->prepare($sql_insert);
    $stmt->execute(array(
        ':username' => $email,
        ':password' => $passwordHash,
    ));
    
    $insert_id = $db->lastinsertid();
    if ($insert_id) {
        // 重定向到登录页面
        header('HTTP/1.1 302 Redirect');
        header('Location: login.html');
    }
} catch (Exception $e) {
    // 报告错误
    header('HTTP/1.1 400 Bad request');
    echo $e->getMessage();
}
```

上面的代码首先将用户的密码使用`password_hash()`函数进行加密，然后存入到数据库中。

然后是登录验证：

```php
<?php
session_start();
try {
    // 获取post的email
    $email = filter_input(INPUT_POST, 'email');
    // 获取登录密码
    $password = filter_input(INPUT_POST, 'password');

    // 查找用户
    $sql = "SELECT id,password FROM user WHERE username=:username";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(
        ':username' => $email
    ));
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        exit('用户不存在');
    }

    // 验证密码
    if (password_verify($password, $row['password']) === false) {
        exit('密码错误！');
    }

    // 保存 session 会话
    $_SESSION['user_logged_in'] = 'yes';
    $_SESSION['user_email'] = $email;

    // 重定向跳转
    header('HTTP/1.1 302 Redirect');
    header('Location: user.php');
} catch (Exception $e) {
    header('HTTP/1.1 401 Unauthorized');
    echo $e->getMessage();
}
```

上面的代码首先根据用户登录账户名从数据库中获取用户信息，然后使用`password_verify()`函数进行密码验证，验证通过则说明登录成功。


### 补充

#### 1. password_hash()

该函数使用足够强度的单向散列算法创建密码的哈希。[官方文档](http://php.net/manual/zh/function.password-hash.php)

语法：

```php
string password_hash( string $password, integer $algo [, array $options ] )
```

参数：

* `$password` 需要被加密的密码字符串。

* `$algo` 一个用来在散列密码时指示算法的[密码算法常量](http://php.net/manual/zh/password.constants.php)。当前支持的算法有：
    - `PASSWORD_DEFAULT` 使用 bcrypt 算法 (PHP 5.5.0 默认)，注意，该常量会随着 PHP 加入更新更高强度的算法而改变。 所以，使用此常量生成结果的长度将在未来有变化。 因此，数据库里储存结果的列可超过60个字符（最好是255个字符）。
    - `PASSWORD_BCRYPT`。使用 CRYPT_BLOWFISH 算法，这会产生兼容使用`$2y$`的`crypt()`。 结果将会是 60 个字符的字符串，或者在失败时返回 FALSE。

* `$options` 一个包含有选项的关联数组。支持的选项：
    - `cost` 用来指明算法递归的层数。省略时，默认值是 10，这是个不错的底线，但也可以根据自己硬件的情况，加大这个值。
    - `salt` 手动提供哈希密码的盐值（salt）。这将避免自动生成盐值（salt）。省略此值后，`password_hash()`会为每个密码哈希自动生成随机的盐值。

    > 盐值（salt）选项从 PHP 7.0.0 开始被废弃（deprecated）了。 现在最好选择简单的使用默认产生的盐值。


返回值：

* 返回哈希后的密码，或者在失败时返回 FALSE。

> 使用的算法、cost 和盐值作为哈希的一部分返回。验证哈希值的所有信息都已经包含在内，在使用`password_verify()`函数验证的时候，不需要额外储存盐值或者算法的信息。


#### 2. password_verify()

验证密码是否和哈希匹配。时序攻击（timing attacks）对此函数不起作用。[官方文档](http://php.net/manual/zh/function.password-verify.php)

语法：

```
boolean password_verify ( string $password , string $hash )
```

参数：

* `$password` 用户的密码。
* `$hash` 一个由`password_hash()`创建的散列值。

返回值：

* 如果密码和哈希匹配则返回 TRUE，否则返回 FALSE 。

