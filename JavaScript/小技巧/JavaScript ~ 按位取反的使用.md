在计算机中，数据都是二进制编码的，对于正数就直接存储对应的二进制编码原码，对于负数则是使用其绝对值的二进制的补码存储的。

> 求负整数的补码，将其原码除符号位外的所有位取反（0 变 1，1 变 0，符号位为 1 不变）后加 1。

例如：

```JavaScript
// 9
00000000000000000000000000001001
         
// -9
11111111111111111111111111110111
```

那么，**使用`～`对任一数值`x`进行按位非操作的结果为`-(x + 1)`**。

比如：

```JavaScript
～9    // -10
～-9  // 8
```



