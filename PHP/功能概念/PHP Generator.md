PHP 5.5 中增加了对生成器(generator)的支持。生成器提供了一种更容易的方法来实现简单的对象迭代，相比较定义类实现 Iterator 接口的方式，性能开销和复杂性大大降低。

## 一、基础

生成器主要包含两块内容：生成器函数和生成器对象。其中，生成器函数是用于得到生成器对象的。

### 1.1 生成器函数

生成器函数也是一个函数，但是与一般的函数定义和执行方式有所不同：

* 一般的方法在被调用的时候，会执行完定义的逻辑才会返回数据，并将控制权交还给调用者，之后再次执行该方法则又是重新开始。
* **生成器函数在被调用的时候，并不会立即执行定义的逻辑，而是返回一个生成器对象**，并通过调用该生成器对象中的方法来执行定义的逻辑，并且生成器对象可以返回一系列的数据，这些数据是逐个返回的。并且，生成器函数中的逻辑并不是一次性执行完的，而是可以暂停中断，并在未来某个时候从中断处继续运行。

生成器函数的定义方式和一般函数差不多，只是在函数体中使用了`yield`关键词。比如，下面就是一个生成器函数：

```php
function xrange($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}
```

例子中的`xrange()`函数与内置函数`range()`函数的功能相同，唯一的区别在于`range()`会返回一个包含了一百万个数字的数组，而`xrange()`则返回一个可以吐出这些数字的迭代器，不会去老实地计算出一个包含所有数字的数组。


### 1.2 生成器对象

生成器对象是通过生成器函数执行得到的，无法通过`new`方式进行实例化，和一般的类不同。

生成器对象最大的特点是实现了 Iterator 接口，也就是其是**可迭代**的，所以它具有如下几个方法：

* `Generator::current` 返回当前产生的值
* 	`Generator::key` 返回当前产生的键
* `Generator::next` 生成器继续执行
* `Generator::rewind` 重置迭代器，如果迭代已经开始了，则会抛出一个异常。
* `Generator::send` 向生成器中传入一个值
* `Generator::throw` 向生成器中抛入一个异常
* `Generator::valid` 检查迭代器是否被关闭
* `Generator::getReturn` 获取生成器通过`return`返回的值
* `Generator::__wakeup` 序列化回调

其中，`current`、`next`、`key`、`rewind`和`valid`五个方法均是用于实现 Iterator 迭代器接口的，而`send`、`throw`方法则是生成器自身特有的，用于扩展功能。

> `__wakeup`方法用于阻止生成器被反序列化。

如下，就是对上面的`xrange()`生成器函数的使用：

```php
$range = xrange(1, 1000000);
var_dump($range); // object(Generator)#1
$range instanceof Iterator; // true
$range->valid(); // true
$range->current(); // 1
$range->next();
$range->current(); // 2
$range->key(); // 1

// 迭代使用
foreach (xrange(1, 10) as $num) {
    echo $num . "\n"; // 展示 10 个数值
}
```

### 1.3 yield

`yield`关键词是生成器函数的核心，可以如`return`一样返回数据，但是`yield`具有更多的效果：

* `yield`构成了生成器函数的中断，但并不会结束整个生成器函数的逻辑。
* `yield`后面的表达式的值会作为生成器对象的一个迭代值。如果其后面没有值，那么就会返回`null`。
* `yield`本身也是一个表达式，只是其比较特殊，它的值就是通过`send()`方法传入进来的参数，这使`yield`语句也可以被赋值给其他变量或参与其他运算。

由此可以看出，`yield`关键词即是语句(可以向外部返回值)，又是表达式(可以从外部接收值)，

### 1.4 优点

使用生成器的优点如下：

1. 方便快捷：使用生成器函数能够方便的得到实现了迭代器接口的对象，避免为了生成一个迭代器而不得不去实现该接口的五个不同方法。
2. 性能开销小：使得程序可以处理超大规模的数据，而无需一次性将它们载入内存，甚至可以处理无穷无尽的数据流。比如前面示例中的`xrang()`就比系统自带的`range()`节省内存。
3. 可中断、可交互：生成器函数的代码执行是可以设置中断点的，而且在中断后还可以传递值到生成器中，实现数据的输入输出。

其作用主要体现在三个方面：

1.	数据生成（生产者）：通过`yield`返回数据；
2.	数据消费（消费者）：消费`send`传来的数据；
3.	实现协程。

## 二、特点

生成器除了能方便创建迭代器，还具有很多其他的特点。

### 2.1 惰性执行

这里说的惰性有两层意思：

1. 生成器函数的逻辑执行是惰性的，也就是说，调用一个生成器函数时，并不会立即执行生成器函数的代码，而是仅仅返回一个生成器对象；
2. 生成器对象只有在调用`current`或`next`方法的时候，才会执行具体的代码，否则就一直暂停着。

对于第一层意思，可以看如下示例：

```php
function foo() {
    exit('exit script when generator runs.');
    yield;
}
 
$gen = foo();
var_dump($gen); // object(Generator)#1 (0)
$gen->current(); // exit script when generator runs.
 
echo 'unreachable code!';
```

这段代码，在执行`$gen = foo()`时虽然调用了`foo()`生成器函数，但是代码并没有结束，而是在执行`$gen->current()`的时候结束掉。这说明，生成器函数被调用时，并没有执行其内部代码，而是在调用的时候才执行。

### 2.2 单向迭代

一般的迭代器在迭代后可以重置后从头开始迭代，但是生成器对象则不行：**生成器对象的`rewind()`方法只能在迭代开始前被调用**，否则会抛出异常。

这里所说的“迭代开始前”指的生成器对象的`next()`方法没有被调用，而`current()`方法被调用后是可以重置的。

另外，生成器的重置只是将迭代重置到第一个`yield`处，下次执行时，返回第一个迭代的值，而不会从生成器函数中的起始代码执行。

示例如下：

```php
function bar() {
    echo "begin ";
    yield 1;
    yield 2;
}

$bar1 = bar();
$bar1->current(); // begin 1
$bar1->rewind();
$bar1->current(); // 1

$bar2 = bar();
$bar2->rewind(); // begin
$bar2->current(); // 1
$bar2->rewind();
$bar2->current(); // 1
$bar2->next();
$bar2->current(); // 2
$bar2->rewind(); // Exception with message 'Cannot rewind a generator that was already run'
```

### 2.3 可中断

一般的函数被调用时都是运行完成之后才会将控制权归还到调用者，而生成器函数则是可中断的，`yield`语句则构成了这些中断点。

生成器函数中的逻辑在执行的时候，遇到`yield`语句就会返回一个值给调用者，并暂停执行。当调用者继续调用生成器对象中的迭代方法时，会继续执行生成器函数中的逻辑，直到遇到下一个`yield`语句或`return`语句。

使用这个特性，可以创建 PHP 协程编程，也即是由用户控制的、非抢占式程序调度。

### 2.4 可交互

生成器对象中的`send()`方法和`yield`能够让生成器对象和外部有双向数据通信的能力：`yield`返回数据，`send()`提供继续运行的数据。

在调用生成器对象的`send()`方法时，会让生成器对象继续执行，并且可以传入一个参数，该参数会作为当前中断处的值提供给生成器对象中的代码。

由于`send()`会让生成器继续执行，这个行为与迭代器的`next()`接口类似，`next()`相当于`send(null)`。

示例如下：

```php
function logger(string $filename) {
  $fd = fopen($filename, 'w+');
  
  while($msg = yield) {
    fwrite($fd, date('Y-m-d H:i:s') . ':' . $msg . PHP_EOL);
  }
  
  fclose($fd);
  echo "Closed!";
}
 
$logger = logger('log.txt');
$logger->send('program starts!');

$value = rand();
$logger->send($value);

$logger->send('program ends!');
$logger->next();
```

`logger()`方法创建了一个简单的日志记录器，并使用生成器函数的方式使其可以保持住，并在需要使用的时候直接传入要记录数据即可，而不使用的时候，直接发送空内容可以关闭了。在每次调用`send()`时，传入的数据会替换`logger()`方法中的中断处`yield`，也就是会被赋值给`$msg`变量。

当然，这样并不完善，但足以可以看到`send()`方法的使用方式了。

## 三、其他

### 3.1 迭代的键

迭代器在被使用的时候，只会返回相应的值，而没有对应的键。生成器对象则通过`key()`方法为每次迭代提供了一个键。同时，`yield`语句也可以定义相应的键。

生成器对象的迭代键会根据`yield`语句的返回值情况自动处理得到：

1. `yield $key => $value` 迭代的键为`$key`，值为`$value`；
2. `yield $value` 迭代的键为从 0 开始单调递增的正整数值，值为`$value`；
3. `yield` 迭代的键为从 0 开始单调递增的正整数值，值为`null`。

而对于执行到了`return`语句时，生成器对象的迭代键值均为`null`。

示例如下：

```php
function bar () {
    yield 'status' => 'OK';
    yield 'name' => '成功';
    yield true;
    yield;
    return 0;
}

$b = bar();

$b->key(); // "status"
$b->current(); // "OK"
$b->next();

$b->key(); // "name"
$b->current(); // "成功"
$b->next();

$b->key(); // 0
$b->current(); // true
$b->next();

$b->key(); // 1
$b->current(); // null
$b->next();

$b->valid(); // false
$b->key(); // null
$b->current(); // null

$b->getReturn(); // 0
```

### 3.2 语法改进

* PHP5 生成器函数不能使用`return`返回值，PHP7 后则可以，并通过生成器对象的`getReturn()`方法获取返回的值。详情参考 [返回值的 RFC](https://wiki.php.net/rfc/generator-return-expressions)。
* 在 PHP7 前，表达式`$string = yield $data;`是不合法的，需要加括号使用：`$string = (yield $data)`。
* PHP7 新增了`yield from`语法，实现了生成器委托，可以使用另一个迭代器进行迭代。详情请参考[生成器委托 RFC](https://wiki.php.net/rfc/generator-delegation)。

对于生成器委托，示例如下：

```php
function foo() {
    yield from [1, 2, 3];
}

foreach (foo() as $value) {
    echo $value, "\n";
}
```

由于数组也实现了 Iterator 接口，也是迭代器，所以上述代码会依次输出 1、2、3。

### 3.3 抛出异常

生成器对象的`throw()`方法接受一个异常实例，并在协程的当前中断点处抛出。这样就可以在生成器内部进行异常的处理了。

```php
function gen() {
  echo "Foo\n";
  try {
    yield;
  } catch (Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
  }
  echo "Bar\n";
}

$gen = gen();
$gen->rewind(); // 输出 "Foo"
$gen->throw(new Exception('Test')); // 输出 "Exception: Test" 和 "Bar"
```

