### 简介

匿名函数（Anonymous functions），也叫闭包函数（closures），就是这里要将的闭包。闭包是 PHP 5.3 新增加的语法，允许临时创建一个没有指定名称的函数。最经常用作回调函数参数的值，或者函数的返回值。当然，也有其它应用的情况。

下面是一个简单的实例：

```php
function callback (\Closure $callback) {
    $callback();
}

callback(function () {
    echo "This is a anonymous function.";
});
// 输出：This is a anonymous function.
```

上面示例中，在调用`callback()`函数的时候，传入的就是一个闭包函数。

### 传入参数给闭包

闭包函数最终一般也是要被作为函数执行，那么在执行的时候，我们也可以给其传递参数。

```php
function callback (\Closure $callback) {
    $callback('Lin07ux');
}

callback(function ($name) {
    echo 'Hello, ' . $name;
});
// 输出：Hello, Lin07ux
```

### 引入父作用域变量

闭包函数可以从父作用域中继承变量。任何此类变量都应该用`use`语言结构传递进去。

需要注意的是：**一般的变量引入，在闭包中操作的是变量值的副本，而不是传入的变量自身。如果强制通过引用传递`&`则是操作变量自身**。

如下，我们定义了一个新的函数，

```php
function callback (\Closure $callback) {
    $callback();
}

$msg = "Hello, everyone";

$cb = function () use ($msg) {
    print "This is a closure use string value, msg is: $msg.";  
};

$msg = "Hello, World!";
callback($cb);
// 输出：This is a closure use string value, msg is: Hello, everyone.
```

可以看出，通过使用`use`语言结构，我们能把当前作用域中的变量的值传入到闭包函数中使用，突破了 PHP 中的变量作用域的限制。

如果在`use`语言结构中通过引用方式传入变量，那么这个变量会和当前作用域中的变量的值保持同步：

```php
function callback (\Closure $callback) {
    $callback();
}

$msg = "Hello, everyone";

$cb = function () use (&$msg) {
    print "This is a closure use string value, msg is: $msg.";  
};

$msg = "Hello, World!";
callback($cb);
// 输出：This is a closure use string value, msg is: Hello, World!
```

这两种不同的引入变量的方式对于对象变量来说，也是一样的。在通过`use`引入变量的时候，其实是创建了该变量对应的值的一个副本，在闭包中实际操作的是副本，而不是原先的值，而且每次闭包被执行的时候，都是一个新的副本，多次执行之间并没有关联。

看如下的示例：

```php
function callback (\Closure $callback) {
    $callback();
}

$arr = [1, 2, 3, 4];

$cb = function () use ($arr) {
    echo 'Array: [' . implode(', ', $arr) . ']';
};

callback($cb);  // Array: [1, 2, 3, 4]

array_push($arr, 5);
callback($cb);  // Array: [1, 2, 3, 4]

$arr = [1, 2];
callback($cb);  // Array: [1, 2, 3, 4]

$cb = function () use ($arr) {
    array_push($arr, 5);
    echo 'Array: [' . implode(', ', $arr) . ']';
};
callback($cb);  // Array: [1, 2, 5]
callback($cb);  // Array: [1, 2, 5]

$arr[1] = 3;
callback($cb);
```

### 作为返回值

闭包函数还可以作为返回值被返回，这样就可以通过一个函数来生成一个定制的闭包函数：

```php
// 一个利用闭包的计数器产生器
function counter() {
    $counter = 1;
    return function() use(&$counter) { return $counter++; };
}

$counter1 = counter();

echo "counter: " . $counter1();  // counter: 1
echo "counter: " . $counter1();  // counter: 2
```

### 自动绑定 $this

在对象的方法中，闭包函数作为返回值时，会自动绑定其作用域中的`$this`。

```php
class Test
{
    public function testing()
    {
        return function() {
            var_dump($this);
        };
    }
}

$object = new Test;
$function = $object->testing();
$function();

// 输出类似如下
// object(Test)#1 (0) {
// }
```

> 在 PHP 5.3 中并没有该效果，上述程序返回的是 NULL，并给出一个提示：`Notice: Undefined variable: this in script.php on line 8`。

### 参考

[匿名函数](http://php.net/manual/zh/functions.anonymous.php)


