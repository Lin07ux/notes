加密通常分为对称加密和非对称加密。对称加密中的对称，指的是加密和解密使用的是同一个密钥，非对称加密则表示加密和解密的密钥不同。对称加密相对于非对称加密而言，优点是速度快，缺点是安全性相对低一点，不过只要能保证密钥不泄露，其安全性还是有保证的，所以在实际项目中，对称加密的使用非常广泛。

对称加密不同于摘要算法：摘要算法是不可逆的，主要作用是对信息一致性和完整性的校验；而对称加密的主要作用是保证私密信息不被泄露。

目前最流行的对称加密标准是 AES，全称是 Advanced Encryption Standard，是 DES 算法的替代者。需要说明的是：**AES 是一个标准，而不是一个算法**，实际上背后的算法是 Rijndael，二者很容易混淆，比如很多人会搞不清楚 AES256 和 Rijndael256 有什么不同，甚至会认为是一个东西。其实 AES256 中的 256 指的是密钥的长度是 256 位，而 Rijndael256 中的 256 指的是分组大小是 256 位。更进一步说明的话，因为 AES 的分组大小是固定的 128 位，所以我们可以认为 AES256 等同于密钥长度是 256 位的 Rijndael128。

## 一、基本概念

AES 算法主要包括三个部分：**密钥**、**填充**、**模式**。

### 1.1 密钥

密钥是 AES 算法实现加密和解密的根本。AES 支持三种长度的密钥：128 位、192 位、256 位，分别对应的是 AES128、AES192 和 AES256。

从安全性上来看，AES256 的安全性最高，从性能上来看，AES128 性能最高，而造成这个现象的本质原因是天猫的加密处理轮数不同。

### 1.2 填充

在了解填充前，需要先了解 AES 的**分组加密**特性：在对明文加密的时候，AES 标准并不是把整个明文一股脑加密成一整段密文，而是把明文拆分成一个个等长的明文块，这些明文块经过加密器的复杂处理，生成一个个独立的密文块，再把这些密文块拼接在一起，就是最终的加密结果。类似于如下的概念图：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557546452942.png" />

在对明文进行分组的时候，就会涉及到一个问题：假如一段明文长度是 192bit，如果按每 128bit 一个明文块来拆分的话，第二个明文块只有 64bit，不足 128bit。这时候怎么办呢？就需要对明文块进行**填充**（Padding）。

填充方式有如下几种典型的方式，每种方式都规定了特定的填充内容：

* `NoPadding` 不做任何填充，但是要求明文必须是 16 字节(128bit)的整数倍。 
* `PKCS#5` 如果明文块少于 8 字节(64bit)，在明文块末尾补足相应数量的字符，且每个字节的值等于缺少的字符数。比如明文`{1,2,3,a,b,c}`缺少 2 个字节，则补全为`{1,2,3,a,b,c,2,2}`。
* `PKCS#7` 和`PKCS#5`类似，主要区别在于块大小的定义上，`PKCS#7`中的块没有特指某个长度，一般是 128 位（也就是 16 字节），可以说`PKCS#5`是`PKCS#7`的一个特例。
* `ISO10126Padding` 如果明文块少于 16 个字节(128bit)，在明文块末尾补足相应数量的字节，最后一个字符值等于缺少的字符数，其他字符填充随机数。比如明文`{1,2,3,4,5,a,b,c,d,e}`缺少 6 个字节，则可能补全为`{1,2,3,4,5,a,b,c,d,e,5,c,3,G,$,6}`。

> 需要注意：如果在 AES 加密时使用了某一种填充方式，解密时也必须使用相同的填充方式。

如果明文长度是 128 位，按每 128 位一个明文块来拆分的话，恰好是一个完整的块，此时依旧需要填充一个完整的长度的块。因为加密前要填充，解密后要去掉填充，如果没有填充，解密后最后一个字节恰好是`0x01`，那么不方便判断这个`0x01`是实际的数据还是之前填充的数据。

### 1.3 模式

AES 的工作模式，体现在把明文块加密成密文块的处理过程中。AES 加密算法提供了五种不同的工作模式：

* ECB 模式（默认）：电码本模式 Electronic Codebook Book
* CBC 模式：密码分组链接模式 Cipher Block Chaining
* CTR 模式：计算器模式 Counter
* CFB 模式：密码反馈模式 Cipher FeedBack
* OFB 模式：输出反馈模式 Output FeedBack

> 如果 AES 加密的时候使用了某一种工作模式，那么解密的时候也必须使用相同的工作模式。

模式之间的主体思想是近似的，所有工作模式的差别提现在宏观上，即明文块之间的关联不同，而 AES 加密器的内部处理流程都是相同的。

* ECB 模式是最简单的工作模式，在该模式下，每一个明文块的加密都是完全独立，互不干涉的。

    - 优点：实现比较简单，而且方便进行并行计算；
    - 缺点：相同的明文块经过加密会变成相同的密文块，因此安全性较差。

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1659432236-a7efa05466c0.jpg)

* CBC 模式的原理是加密算法的输入是当前的明文分组和前一密文分组的异或，第一个明文分组和一个初始向量进行异或，这样同一个明文分组重复出现时会产生不同的密文分组。

    CBC 引入的 初始化向量 iv，可以将其理解为使用 md5 时的 salt。通过不同的 iv 可以防止同样的明文块始终加密成同样的密文块，能够避免被 AES 加密后的数据被通过彩虹表之类的暴力破解方式解密。
    
    - 优点：同一个明文分组重复出现时产生不同的密文分组，难以破解，安全性更高；
    - 缺点：因为每一个分组的加密都依赖前一个分组的密文，所以无法并行计算，性能上不如 ECB，而且增加了复杂度。

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1659432916-8e4369a79fdf.jpg)

## 二、AES 加密器

前面 AES 工作流程图中，最关键的部分在于 AES 加密器。下面是 AES 加密器的一个示意图：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557584488408.png"/>

### 2.1 加密轮次

前面已经介绍过，AES 加密不是一次把明文变成密文，而是先后经过很多轮分别进行加密。具体分成如下三种类别的轮次：

* 初始轮（Initial Round） 1次
* 普通轮（Rounds） N 次
* 最终轮（Final Round） 1次

而决定加密轮数的关键在于 AES 加密的 key 的长度：

* AES128：11轮
* AES192：13轮
* AES256：15轮

不同阶段的 Round 有不同的处理步骤。

* 初始轮只有一个步骤：加轮密钥（AddRoundKey）
* 普通轮有四个步骤：
    1. 字节代替（SubBytes）
    2. 行移位（ShiftRows）
    3. 列混淆（MixColumns）
    4. 加轮密钥（AddRoundKey）
* 最终轮有三个步骤：
    1. 字节代替（SubBytes）
    2. 行移位（ShiftRows）
    3. 加轮密钥（AddRoundKey）

### 2.2 字节替代（SubBytes）

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557584945498.png"/>

首先需要说明的是，16 字节的明文块在每一个处理步骤中都被排列成 4X4 的二维数组。

所谓字节替代，就是把明文块的每一个字节都替代成另外一个字节。替代的依据是什么呢？依据一个被称为 S 盒（Subtitution Box）的 16X16 大小的二维常量数组。

假设明文块当中`a[2,2] = 5B`（一个字节是两位 16 进制），那么输出值`b[2,2] = S[5][11]`。

### 2.3 行移位（ShiftRows）

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557585038081.png"/>

这一步很简单，就像图中所描述的：

* 第一行不变
* 第二行循环左移 1 个字节
* 第三行循环左移 2 个字节
* 第四行循环左移 3 个字节

### 2.4 列混淆（MixColumns） 
<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557585072484.png"/>

这一步，输入数组的每一列要和一个名为修补矩阵（fixed matrix）的二维常量数组做矩阵相乘，得到对应的输出列。

### 2.5 加轮密钥（AddRoundKey）

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557585106613.png"/>

这一步是唯一利用到密钥的一步，128bit 的密钥也同样被排列成 4X4 的矩阵。让输入数组的每一个字节`a[i,j]`与密钥对应位置的字节`k[i,j]`异或一次，就生成了输出值`b[i,j]`。

需要补充一点，加密的每一轮所用到的密钥并不是相同的。这里涉及到一个概念：**扩展密钥**（KeyExpansions）。

### 2.6 扩展密钥（KeyExpansions）

AES 源代码中用长度`4 * 4 *（10+1）`字节的数组 W 来存储所有轮的密钥。`W{0-15}`的值等同于原始密钥的值，用于为初始轮做处理。后续每一个元素`W[i]`都是由`W[i-4]`和`W[i-1]`计算而来，直到数组 W 的所有元素都赋值完成。 
W 数组当中，`W{0-15}`用于初始轮的处理，`W{16-31}`用于第 1 轮的处理，`W{32-47}`用于第 2 轮的处理 ......一直到`W{160-175}`用于最终轮（第 10 轮）的处理。

## 三、转摘

1. [聊聊 AES](https://blog.huoding.com/2019/05/06/739)
2. [漫画解读：什么是 AES 算法](https://zhuanlan.zhihu.com/p/45155135)
3. [AES 简介](https://github.com/matt-wu/AES)

