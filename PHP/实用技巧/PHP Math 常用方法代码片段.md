> 转摘：[Math - 30 seconds of php](https://php.30secondsofcode.org/tag/math)

### 1. approximatelyEqual

计算两个数值在一定误差范围内是约等的。

```php
function approximatelyEqual ($number1, $number2, $epsilon = 0.001)
{
    return abs($number1 - $number2) < $epsilon;
}
```

### 2. average

计算两个或更多数值的平均值。

```php
function average (...$numbers)
{
    $count = count($numbers);
    
    return $count > 0 ? (array_sum($numbers) / $count) : 0;
}
```

### 3. fibonacci

计算 fibonacci 序列。

```php
function fabonacci (int $length)
{
    $sequence = [0, 1];
    
    for ($i = 2; $i < $n; $i++) {
        $sequence[$i] = $sequence[$i - 1] + $sequence[$i - 2];
    }
    
    return $sequence;
}
```

### 4. gcd

计算多个数值的最大公约数(Greatest Common Divisor)。

```php
function gcd (...$numbers)
{
    if (count($numbers) > 2) {
        return array_reduce($numbers, 'gcd');
    }
    
    $r = $numbers[0] % $numbers[1];
    
    return $r === 0 ? abs($numbers[1]) : gcd($numbers[1], $r);
}
```

例如：

```php
gcd(8, 36);     // 4
gcd(2, 4, 3);   // 1
gcd(12, 8, 32); // 4
```

### 5. lcm

求两个或者多个数值的最小公倍数(Least Common Multiple)。

计算过程利用了如下的公式：`lcm(x, y) = x * y / gcd(x, y)`，也就是说，两个数的最小公倍数等于他们的乘积处于其最大公约数。

```php
function lcm (...$numbers)
{
    $ans = $numbers[0];
    
    for ($i = 1, $max = count($numbers); $i < $max; $i++) {
        $ans = ($numbers[$i] * $ans) / gcd($numbers[$i], $ans);
    }
    
    return $ans;
}
```

例如：

```php
lcm(12, 7);      // 84
lcm(1, 3, 4, 5); // 60
```

### 6. isPrime

验证一个数值是否是质数。只需要用小于这个数的平方根的值依次除该数值，如果余数为 0 就不是质数。同时还要验证该数不小于 2。

```php
function isPrime (int $number)
{
    $boundary = floor(sqrt($number));
    
    for ($i = 2; $i < $boundary; $i++) {
        if ($number % $i === 0) {
            return false;
        }
    }
    
    return $number >= 2;
}
```

### 7. maxN

获取数组中最大的数值的个数。

利用`max()`函数获取最大的数值，然后使用`array_filter()`方法过滤出最大的数值元素，最后返回过滤出的结果的个数。

```php
function maxN (array $numbers)
{
    $maxValue = max($numbers);
    
    $maxValueArray = array_filter($numbers, function ($value) use ($maxValue) {
        return $maxValue === $value;
    });
    
    return count($maxValueArray);
}
```

例如：

```php
maxN([1, 2, 3, 3, 4, 5, 5]); // 2
maxN([1, 2, 3, 3, 4, 5]);    // 1
```

### 8. minN

获取数组中最小的数值的个数。

和`maxN()`函数类似，利用`min()`获得数组中最小的值，然后使用`array_filter()`方法过滤最小的值，最后返回过滤的结果的数量。

```php
function minN (array $numbers)
{
    $minValue = min($numbers);
    
    $minValueArray = array_filter($numbers, function ($value) use ($minValue) {
        return $minValue === $value;
    });
    
    return count($minValueArray);
}
```

例如：

```php
minN([1, 1, 2, 3, 3, 4, 5]); // 2
minN([1, 2, 3, 3, 4, 5]);    // 1
```

### 9. median

求一组数值的中间值。

先用`sort()`方法对数组排序，然后获取中间的元素。如果数组有偶数个元素，那么就返回中间两个元素的平均值。

```php
function median (...$numbers)
{
    sort($numbers);
    
    $totalNumbers = count($numbers);
    $mid = floor($totalNumbers / 2);
    
    return ($totalNumbers % 2) === 0 ? ($numbers[$mid - 1] + $numbers[$mid]) / 2 : $numbers[$mid];
}
```

例如：

```php
median([1, 3, 3, 6, 7, 9, 8]); // 6
median([1, 2, 3, 6, 7, 9]);    // 4.5
```

