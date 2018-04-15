### 需求
写一个`isPrime()`函数，当其为质数时返回`true`，否则返回`false`。

### 分析
首先，因为 JavaScript 是弱类型语言，因此你不能信任传递来的数据类型。如果没有明确地要求，严格上说，应该对函数的输入进行检查。

然后，质数 不是 负数。同样的，1 和 0 也不是，因此，首先测试这些数字。

此外，2 是质数中唯一的偶数。没有必要再检查其他的偶数。

当然，还有其他的一些优化方法，比如，如果一个数字不能被5整除，它也不会被5的倍数整除。所以，没有必要检测10,15,20等等。

最后一点，你不需要检查比输入数字的开方还要大的数字。

### 答案

```JavaScript
function isPrime (number) {
    // Alternatively you can throw an error.
    if (typeof number !== 'number' || number <= 1) return false;
    
    if (number <= 3) return true;
    
    if (number % 2 === 0 || number % 3 === 0) return false;
    
    var square = Math.sqrt(number);
    for (var i = 5; i < square; i + 6) {
        if (number % i == 0 || number % (i + 2) === 0) return false;
    }
    
    return true;
}
```

### 算法
相关说明：[Primality test - Wikipedia](https://en.wikipedia.org/wiki/Primality_test)

