> 转摘：[Function - 30 seconds of php](https://php.30secondsofcode.org/tag/function)

### 1. compose

将多个函数串成一个函数，然后调用的时候将依次执行，并将上一个函数执行的结果作为给下一个函数的参数。

> `array_reduce()`方法的回调函数可以接收两个参数，分别表示上次迭代的返回值和当前迭代的值。

```php
function compose (...$functions)
{
    return array_reduce(
        $functions,
        function ($carry, $function) {
            return function ($x) use ($carry, $function) {
                return $function($carry($x));
            };
        },
        function ($x) {
            return $x;
        }
    );
}
```

例如：

```php
$compose = compose(
    // add 2
    function ($x) {
        return $x + 2;
    },
    // multiply 4
    function ($x) {
        return $x * 4;
    }
);

$compose(3); // 20
```

### 2. curry

将一个函数柯里化，由多参数调用变成多次单参数调用的方式。当传入的参数数量足够的时候，调用原始函数并返回其结果，否则继续返回柯里化后的函数。

```php
function curry ($function)
{
    $accumulator = function ($arguments) use ($function, &$accumulator) {
        return function (...$args) use ($function, $arguments, $accumulator) {
            $arguments = array_merge($arguments, $args);
            $reflection = new ReflectionFunction($function);
            $totalArguments = $reflection->getNumberOfRequiredParameters();
            
            if ($totalArguments <= count($arguments)) {
                return $function(...$arguments);
            }
            
            return $accumulator($arguments);
        };
    };
    
    return $accumulator([]);
}
```

例如：

```php
$curriedAdd = curry(function ($a, $b) {
    return $a + $b;
});

$add10 = $curriedAdd(10);
$add10(15); // 25

$curriedAdd(10, 15); // 25
```


