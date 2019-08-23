PHP 中`serialize`函数用于将对象格式化为字符串，而`unserialize`函数则是压缩格式化的字符串还原对象。

序列化是为了将对象或者变量永久存储的一种方案，但是在反序列化的时候，需要注意一些特殊的情况，以免出现注入漏洞。

### 1. 序列化

`serialize`对一个对象进行序列化的时候，会将对象的属性(包括 public、protected、private 类别的属性)都系列化到字符串中，只是其名称会有一些变化。

另外，对于非对象数据(如数字、字符串、布尔值等)也可以用`serialize`进行序列化。序列化不同类型的格式如下：

 类型     | 结果
---------|------------
 Integer | `i:value;`
 String  | `s:size:value;`
 Boolean | `b:value;`(false 时 value 为 0，true 时 value 为 1)
 Null    | `N;`
 Array   | `a:size:{key definition;value definition;(repeated per element)}`
 Object  | `O:strlen(object name):object name:object size:{s:strlen(property name):property name:property definition;(repeated per property)}`

示例如下：

```php
class User {
    public $name;
    private $sex;
    protected $money = 1000;

    public function __construct($data, $sex) {
        $this->data = $data;
        $this->sex = $sex;
    }
}
$number = 66;
$str = 'Y4er';
$bool = true;
$null = NULL;
$arr = array('a' => 1, 'b' => 2);
$user = new User('jack', 'male');

var_dump(serialize($number)); // string(5) "i:66;"
echo '<hr>';
var_dump(serialize($str));    // string(11) "s:4:"Y4er";"
echo '<hr>';
var_dump(serialize($bool));   // string(4) "b:1;"
echo '<hr>';
var_dump(serialize($null));   // string(2) "N;"
echo '<hr>';
var_dump(serialize($arr));    // string(30) "a:2:{s:1:"a";i:1;s:1:"b";i:2;}"
echo '<hr>';
var_dump(serialize($user));   // string(99) "O:4:"User":4:{s:4:"name";N;s:9:"Usersex";s:4:"male";s:8:"*money";i:1000;s:4:"data";s:4:"jack";}"
```

在这里需要注意一点就是对象的的`private`和`protected`属性序列化后的名称和长度问题：

* `public`属性序列化后，名称与原本的属性名相同
* `private`属性序列化后，名称变成`"\0类名\0属性名"`，比如上面的`sex`属性序列化后就成了`"\0User\0sex"`，长度为 9
* `protected`属性序列化后，名称变成`"\0*\0属性名"`，比如上面的`money`属性序列化后就成了`"\0*\0money"`，长度为 8

这里的`\0`是一个转义字符，是一个空字节，如果用 url 编码，则是`%00`；如果是 ASCII 编码，则是`\00`。

### 2. 反序列化

反序列化就是根据序列化的规则，将字符串转换为原来的变量或对象。如果是反序列化一个对象字符串，那么会触发该类中的`__wakeup()`方法(如果有的话)。

比如：

```php
class User
{
    public $name = 'Y4er';

    function __wakeup()
    {
        echo $this->name;
    }
}

$string = 'O:4:"User":1:{s:4:"name";s:4:"Y4er";}'
unserialize($string);
// Y4er
```

这里在反序列化字符串`"O:4:"User":1:{s:4:"name";s:4:"Y4er";}"`的时候，会新建一个`User`对象，并触发其`__wakeup()`魔术方法，从而输出了对象的`$name`属性值`Y4er`。

### 3. 序列化漏洞：跳过 __wakeup

**当反序列化时，如果字符串中表示对象属性个数的值大于实际属性个数，就会因属性检查失败跳过`__wakeup()`方法的执行**。虽然没有执行`__wakeup()`方法，但是由于对象已经创建，而且属性值已经设置，所以紧接着会销毁对象，这就会执行对象的`__destruct()`方法。

这是 PHP 反序列化的一个漏洞，利用这个漏洞可以构造序列化字符串，使其在被反序列化的时候，跳过对象的`__wakeup()`方法的执行，但会执行对象的`__destruct()`。

> 该漏洞影响版本：PHP5 < 5.6.25，PHP7 < 7.0.10

比如，对于如下的一个类：

```php
class SoFun
{
    protected $file = 'index.php';

    function __destruct()
    {
        if (!empty($this->file)) {
            echo($this->file);
        }
    }

    function __wakeup()
    {
        $this->file = 'index.php';
    }

    public function __toString()
    {
        return '';
    }
}
```

实例化该类，然后进行序列化，可以得到类似如下的结果：

```
O:5:"SoFun":1:{s:7:"*file";s:9:"index.php";}
```

对于`protected`属性，补充完整属性名，结果如下：

```
O:5:"SoFun":1:{s:7:"\0*\0file";s:9:"index.php";}
```

然后修改这个字符串中表示对象属性个数的值为 2，并修改 file 属性的值为`flag.php`：

```
O:5:"SoFun":2:{s:7:"*file";s:9:"falg.php";}
```

然后反序列化修改过的字符串，由于字符串中设置的属性个数超过实际属性个数，将不会执行`SoFun`方法的

### 4. 转摘

[PHP反序列化学习](https://y4er.com/post/unserialize/)

