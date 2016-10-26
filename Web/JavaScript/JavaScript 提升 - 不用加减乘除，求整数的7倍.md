看到这道题目的第一感：和二进制有关。但是，我们知道二进制的操作最方便计算的是 2 次幂的倍数，比如 8 倍，不考虑大数溢出的话，就只要将该整数左移三位就可以了，对应的代码就是：

```javascript
let multiply8 = (num) => num << 3;

multiply8(7); //56
```

8 倍很容易实现，但是 7 倍该怎么做呢？理论上可以先将该数扩大 8 倍，然后再减去自己。可是，题目又限制了不允许使用加减乘除，那么怎么办呢？

### 多位二进制整数加法的算法
`m`和`n`是两个二进制整数，求`m + n`：

1.	用`与运算`求`m`和`n`共同为“1” 的位：`m' = m & n`；
2.	用`异或运算`求`m`和`n`其中一个为“1” 的位：`n' = m ^ n`；
3.	如果`m'`不为 0，那么将`m'`左移一位（进位），记`m = m' << 1`，记`n = n'`，跳回到步骤 1
4.	如果`m'`为 0，那么`n'`就是我们要求的结果。

把上面的算法翻译成 JavaScript：

```javascript
function bitAdd (m, n) {
    while (m) {
        [m, n] = [(m & n) << 1, m ^ n];
    }
    
    return n;
}

bitAdd(45, 55); //100
```

复杂度：每次迭代的时候，m 末尾连续的 0 一定会至少增加 1 位（因为 & 操作不可能减少 m 末尾的 0，而 1 位左移操作至少会增加 1 个末尾的 0）。当 m 末尾连续的 0 的数量超过 n 的二进制位数之后，`m & n`就是 0，此时循环就会结束。因此，这个算法的最坏情况下，循环次数是 `log(n)`，时间复杂度小于等于`O(log(n))`。

### 解决问题
通过上面的我们就得到了一个自己实现的整数加法，于是我们可以：

```javascript
function multiply7 (num) {
    let sum = 0;
    
    for (var i = 0; i < 7; i++) {
        sum = bitAdd(sum, num);
    }
    
    return sum;
}

multiply7(7); //49
```

这样我们得到了想要的结果，不过如果要改进的话，我们其实可以不需要用循环加法来实现整数乘法，回到前面讨论过的，我们可以先将 num 乘以 8，然后再减去 num，或者说 bitAdd(-num)：

```javascript
let multiply7 = (num) => bitAdd(num << 3, -num);

multiply7(7); // 49
```

有同学说，负数符号也是减号“-”，能不能不使用？当然可以，因为我们可以利用“补码”：

```javascript
let multiply7 = (num) => bitAdd(num << 3, bitAdd(~num, 1));

multiply7(7);  // 49
```

如果可以使用投机取巧的方式，那么在 JavaScript 中可以使用 Unicode 字符来实现：

```javascript
let multiply7 = (num) => 
    new Function(["return ",num,String.fromCharCode(42),"7"].join(""))();

multiply7(7); //49
```

确实没有加减乘除符号咯~~~

转摘：[别人家的面试题：不用加减乘除，求整数的7倍](https://www.h5jun.com/post/multiply7.html)

