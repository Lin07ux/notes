## 基础

### 使用

PHPUnit 命令行测试执行器可通过`phpunit`命令调用。对于每个测试的运行，PHPUnit 命令行工具输出一个字符来指示进展：

- `.` 当测试成功时输出；
- `F` 当测试方法运行过程中一个断言失败时输出；
- `E` 当测试方法运行过程中产生一个错误时输出；
- `R` 当测试被标记为有风险时输出；
- `S` 当测试被跳过时输出；
- `I` 当测试被标记为不完整或未实现时输出。

### 测试编写规则

用 PHPUnit 编写测试的基本惯例与步骤如下：

1. 针对`Class`的测试写在类`ClassTest`中。
2. `ClassTest`通常需要继承自`PHPUnit\Framework\TestCase`。
3. 测试方法都是命名为`test*`的公用方法。也可以在文档的注释块(docblock)中使用`@test`将其标注为测试方法。
4. 在测试方法内，使用类似于`assertEquals()`这样的断言方法来对实际值和预期值的匹配做判断。

## 注释块标记

### 依赖 @depends

PHPUnit 支持对测试方法之间的显式依赖关系进行声明。这种依赖关系并不是定义在测试方法的执行顺序中，而是允许生产者(producer)返回一个测试基境(fixture)的实例，并将此实例传递给依赖于它的消费者(consumer)们。

* 生产者(producer)，是能生成被测单元并将其作为返回值的测试方法。
* 消费者(consumer)，是依赖于一个或多个生产者及其返回值的测试方法。 
在注释块中，使用`@depends testFunction`来声明当前的方法依赖于`testFunction()`方法，再执行当前测试方法前，会先执行`testFunction()`方法，并将其返回值作为参数传递到当前方法中。

**注意**：

1. 默认情况下，生产者所产生的返回值将“原样”传递给相应的消费者。这意味着，如果生产者返回的是一个对象，那么传递给消费者的将是一个指向此对象的引用。如果需要传递对象的副本而非引用，则应当用`@depends clone`替代`@depends`。

2. 当所依赖的测试方法失败时，会跳过当前的测试。

3. 测试可以使用多个`@depends`标注。PHPUnit 不会更改测试的运行顺序，因此你需要自行保证某个测试所依赖的所有测试均出现于这个测试之前。

4. 拥有多个`@depends`标注的测试，其第一个参数是第一个生产者提供的基境，第二个参数是第二个生产者提供的基境，以此类推。

### 数据供给器 @dataProvider

可以给测试方法提供一系列的数据进行测试，这些参数可以由数据供给器方法提供，用`@dataProvider`标注来指定使用哪个数据供给器方法。

**注意**：

1. 数据供给器方法必须声明为`public`，其返回值要么是一个数组，其每个元素也是数组；要么是一个实现了`Iterator`接口的对象，在对它进行迭代时每步产生一个数组。每个子元素数组都是测试数据集的一部分，将以它的内容作为测试方法的参数来调用测试方法。

2. 当使用到大量数据集时，最好逐个用字符串键名对其命名，避免用默认的数字键名。这样输出信息会更加详细些，其中将包含打断测试的数据集所对应的名称。

3. 如果测试同时从`@dataProvider`方法和一个或多个`@depends`方法接收数据，那么来自于数据供给器的参数将先于来自所依赖的测试的。而来自于所依赖的测试方法的参数对于每个数据集都是一样的。

4. 如果一个测试 A 依赖于另一个使用了数据供给器的测试 B，那么，仅当 B 至少在一组数据上成功时，A 才会运行。使用了数据供给器的测试，其运行结果是无法注入到依赖于此册数的其他测试中的。

> 所有的数据供给器方法的执行都是在对`setUpBeforeClass`静态方法的调用和第一次对`setUp`方法的调用之前完成的。因此，无法在数据供给器中使用创建于这两个方法内的变量。这是必须的，这样 PHPUnit 才能计算测试的总数量。

### 异常 @expectedException

当需要测试异常时，可以在注释块中使用`@expectedException`、`@expectedExceptionCode`、`@expectedExceptionMessage`、`@expectedExceptionMessageRegExp`标注来为被测代码所抛出的异常建立预期。

或者，也可以在被测方法中，使用`expectException()`、`expectExceptionCode()`、`expectExceptionMessage()`、`expectExceptionMessageRegExp()`方法为代码所抛出的异常建立预期。

默认情况下，PHPUnit 将测试在执行中触发的 PHP 错误、警告、通知都转换为异常。利用这些异常，就可以预期测试将触发 PHP 错误。

> PHP 的`error_reporting`运行时配置会对 PHPUnit 将哪些错误转换为异常有所限制。如果在这个特性上碰到问题，请确认 PHP 的配置中没有抑制想要测试的错误类型。

PHPUnit 提供了如下几种常用的异常：

* `PHPUnit\Framework\Error\Error`   错误
* `PHPUnit\Framework\Error\Notice`  通知
* `PHPUnit\Framework\Error\Warning` 警告

> 对异常进行测试是越明确越好的。对太笼统的类进行测试有可能导致不良副作用。因此，不再允许用`@expectedException`或`setExpectedException()`对`Exception`类进行测试。

如果测试依靠会触发错误的 PHP 函数，例如`fopen`，在测试中通过抑制住错误通知，就能对返回值进行检查，否则错误通知将会导致抛出`PHPUnit\Framework\Error\Notice`。

## 测试判断

### 测试输出

有时候，想要断言某方法的运行过程中生成了预期的输出（例如，通过`echo`或`print`）。`PHPUnit\Framework\TestCase`类使用 PHP 的**输出缓冲**特性来为此提供必要的功能支持。

通过如下的几个方法设定预期输出，如果没有产生预期的输出，测试将计为失败：

- `void expectOutputRegex(string $regularExpression)` 设置输出预期为输出应当匹配正则表达式`$regularExpression`。
- `void expectOutputString(string $expectedString)` 设置输出预期为输出应当与`$expectedString`字符串相等。
- `bool setOutputCallback(callable $callback)` 设置回调函数，用来做诸如将实际输出规范化之类的动作。
- `string getActualOutput()` 获取实际输出。



