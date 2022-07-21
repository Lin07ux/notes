### 1. SECG

ECDSA 是一种椭圆曲线签名算法，其基础就是一个选定的椭圆曲线方程，通过这个方程即可生成公钥、签名和验签。

由于椭圆曲线有非常多种，每种椭圆曲线在实现 ECDSA 算法的时候，安全性、效率方面都不相同，有一些特定的椭圆曲线能在安全性和性能上保持较好的融洽，在实现 ECDSA 的时候就能选用这些特定的椭圆曲线方程。

SECG，即 Stands for Efficient Cryptography Group，是一个成立于 1998 年的行业联盟，旨在开发商业标准，以促进在广泛的计算平台上采用高效密码学和互操作性。

### 2. SECP

SECP 即 Standards for Efficient Cryptography specifications，也就是 SECG 的主要输出规范。

目前已生成的规范有：

* [SEC 1: Elliptic Curve Cryptography, Version 2.0](https://www.secg.org/sec1-v2.pdf)
* [SEC 2: Recommended Elliptic Curve Domain Parameteres, Version 2.0](https://www.secg.org/sec2-v2.pdf)
* [SEC 4: Elliptic Curve Qu-Vanstone Implicit Certificates](https://www.secg.org/sec4-1.0.pdf)

每个规范中定义了一些椭圆曲线的参数，通过这些参数即可唯一确定一个椭圆曲线。可以简单理解为，每个 SECP 规范就是定义了一些椭圆曲线方程参数。

### 3. secp256k1

`secp256k1`是 SEC 2 标准中定义的一套椭圆曲线参数，类似的还有`secp256r1`、`secp192k1`等。

secp256k1 为基于 Fp 有限域上的一个椭圆曲线，由于其特殊的构造，优化后的实现比其他曲线性能上有 30% 的提高，有以下两个明显的优点：

* 密钥的长度很短：占用很少的带宽和存储资源；
* 统一性：让所有用户都可以使用同样的操作完成域运算。

> 有限域的定义如下：
> 
> 有限域是指有限数的集合和`+`(addition)和`*`(multiplication)两个运算，并且这两个运算满足以下条件：
> 
> 1. 如果 a、b 属于集合 S，并且`a + b`和`a * b`属于集合 S，则称之为闭合性；
> 2. 0 属于集合 S，并且`a + 0 = a`，那么 0 为加法单位元(additive identity)；
> 3. 1 属于集合 S，并且`a * 1 = a`，那么 1 为乘法单位元(multiplicative identity)；
> 4. 如果 a 属于集合 S，-a 也属于集合 S，并且`a + (-a) = 0`，这个称之为加法可逆(additive inverse)；
> 5. 如果 a 属于集合 S，并且不为 0，`a^-1`也属于集合 S，并且`a * a^-1 = 1`，这个称之为乘法可逆(multiplicative inverse)。
> 
> Fp 有限域是指模底为 p 的整数域。

### 4. 以太坊账户

要创建以太坊账户，只需要一个非对称加密密钥对——由不同的算法(如 RSA、ECC 等)生成。

以太坊使用椭圆曲线加密算法(ECC)，这个算法有多个参数来调节算法的运算速度和安全性，而且以太坊选用的参数即为`secp256k1`参数。

每个账户用一个地址表示，有了密钥对之后就可以导出地址。以太坊钱包地址是从公钥中导出的：

1. 生成公钥的 Keccak-256 哈希，得到一个 256 比特的数字；
2. 丢弃前面的 96 比特（即 12 字节），得到一个 160 比特（即 20 字节）的数字；
3. 把数字编译成十六进制，即可得到一个有 40 个字符的字符串，这就是以太坊账户地址。

