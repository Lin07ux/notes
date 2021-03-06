> 参考：[知乎 - 刘浩博](https://www.zhihu.com/question/24415787/answer/57187211)

对于下面的代码，输出是`NO`：

```javascript
var a = 0.1,
    b = 0.2;
    
if (a + b === 0.3) {
    console.log('OK');
} else {
    console.log('NO');
}
```

在 JavaScript 中`0.1 + 0.2 ≠ 0.3`。这是由于 IEEE 754 标准的浮点数精度限制：JavaScript 中，数字都是用浮点数表示的，并规定使用 IEEE 754 标准的双精度浮点数表示。

IEEE 754 规定了两种基本浮点格式：单精度和双精度。

* IEEE 单精度格式具有 24 位有效数字精度(包含符号号)，并总共占用 32 位。
* IEEE 双精度格式具有 53 位有效数字精度(包含符号号)，并总共占用 64 位。

- 十进制 0.1

    * => 二进制 0.00011001100110011…(循环 0011 ) 
    * => 尾数为 1.1001100110011001100…1100（共 52 位，除了小数点左边的 1），指数为 -4（二进制移码为 00000000010 ）,符号位为 0
    * => 计算机存储为：0 00000000100 10011001100110011…11001
    * => 因为尾数最多52位，所以实际存储的值为 0.00011001100110011001100110011001100110011001100110011001

- 十进制 0.2

    * => 二进制 0.0011001100110011…(循环 0011)
    * => 尾数为 1.1001100110011001100…1100（共 52 位，除了小数点左边的 1），指数为 -3（二进制移码为 00000000011）,符号位为 0
    * => 存储为：0 00000000011 10011001100110011…11001
    * => 因为尾数最多 52 位，所以实际存储的值为0.00110011001100110011001100110011001100110011001100110011

- 那么两者相加得： 

    ```
    0.00011001100110011001100110011001100110011001100110011001 + 0.00110011001100110011001100110011001100110011001100110011
    = 0.01001100110011001100110011001100110011001100110011001100
    ```
    
    转换成 10 进制之后得到：0.30000000000000004。


