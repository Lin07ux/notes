> 转摘：[安利！这是我见过最好的NumPy图解教程](https://mp.weixin.qq.com/s/y0Em7LAIg6ZcL3oyTVwZ8g)

NumPy 是 Python 中用于数据分析、机器学习、科学计算的重要软件包。它极大地简化了向量和矩阵的操作及处理。Python 的不少数据处理软件包依赖于 NumPy 作为其基础架构的核心部分(例如 scikit-learn、SciPy、pandas 和 tensorflow)。除了数据切片和数据切块的功能之外，掌握 NumPy 也使得开发者在使用各数据处理库调试和处理复杂用例时更具优势。

在本文中，将介绍 NumPy 的主要用法，以及它如何呈现不同类型的数据（表格，图像，文本等），这些经 NumPy 处理后的数据将成为机器学习模型的输入。

以下默认通过如下方式导入 NumPy：

```Python
import numpy as np
```

## 一、数组操作

### 1.1 创建数组

可以通过将 Python 列表传入`np.array()`来创建一个 NumPy 数组(也就是强大的`ndarray`)。

通常情况下，希望 NumPy 为我们初始化数组的值，为此 NumPy 提供了诸如`ones()`，`zeros()`和`random.random()`之类的方法，只需传入元素个数即可。

在下面的例子里，创建出的数组如右边所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198098169.png)

### 1.2 数组的算术运算

创建两个 NumPy 数组，分别称作`data`和`ones`，然后将其进行相加计算：

```Python
data = np.array([1, 2])
ones = np.ones(2)
data + ones
```

这样就可以实现对应位置上的数据相加的操作(即每行数据进行相加)，这种操作比循环读取数组的方法代码实现更加简洁。效果图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198240769.png)

当然，在此基础上举一反三，也可以实现减法、乘法和除法等操作：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198274106.png)

也可以对数组和单个数值进行算术操作(也称作向量和标量之间的操作)。比如，如果数组表示的是以英里为单位的距离，希望将其转换为公里数，可以简单的写作`data * 1.6`：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198340217.png)

NumPy 是通过数组广播(broadcasting)知道这种操作需要和数组的每个元素相乘。

### 1.3 数组的切片操作

可以像 Python 列表操作那样对 NumPy 数组进行索引和切片，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198402864.png)

### 1.4 聚合函数

NumPy 中还有聚合函数，可以将数据进行压缩，统计数组中的一些特征值：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198461974.png)

上面的所有例子都在一个维度上处理向量。除此之外，NumPy 之美的一个关键之处是它能够将之前所看到的所有函数应用到任意维度上。

除了`min`、`max`和`sum`等函数，还有`mean`(均值)、`prod`(数据乘法)计算所有元素的乘积、`std`(标准差)，等等。

## 二、矩阵操作

矩阵也可以理解为多维数组。常见的如二维数据、三维数组，其内部中心数据结构称为`ndarray`(N 维数组)。

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200738122.png)

很多时候，增加维度只需在 NumPy 函数的参数中添加一个参数，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200768078.png)

### 2.1 创建矩阵

可以通过将二维列表传给 Numpy 来创建矩阵：

```Python
np.array([[1, 2], [3, 4]])
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198584480.png)

除此外，也可以使用上文提到的`ones()`、`zeros()`和`random.random()`来创建矩阵，只需传入一个元组来描述矩阵的维度：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198612157.png)

### 2.2 矩阵的算术运算

对于大小相同的两个矩阵，可以使用算术运算符(`+-*/`)将其相加或者相乘。NumPy 对这类运算采用对应位置(position-wise)操作处理：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198681517.png)

对于不同大小的矩阵，只有其中一个矩阵的维度同为 1 时(例如矩阵只有一列或一行)，才能进行这些算术运算。在这种情况下，NumPy 使用广播规则(broadcast)进行操作处理：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198756271.png)

与算术运算有很大区别是使用点积的矩阵乘法。NumPy 提供了`dot()`方法，可用于矩阵之间进行点积运算：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198782481.png)

上图的底部添加了矩阵尺寸，以强调运算的两个矩阵在列和行必须相等。可以将此操作图解为如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563198850905.png)

### 2.3 矩阵的切片

索引和切片功能在操作矩阵时变得更加有用，可以在不同维度上使用索引操作来对数据进行切片。

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200468814.png)

### 2.4 矩阵的聚合

可以像聚合向量一样聚合矩阵：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200488948.png)

还可以使用`axis`参数指定行和列的聚合：`axis = 0`表示按列聚合，`axix = 1`表示按行聚合。

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200545405.png)

### 2.5 矩阵的转置

处理矩阵时经常需要对矩阵进行转置操作，常见的情况如计算两个矩阵的点积。NumPy 数组的属性`T`可用于获取矩阵的转置。

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200614853.png)

### 2.6 矩阵的重构

在较为复杂的用例中，可能会发现需要改变某个矩阵的维度。这在机器学习应用中很常见，例如模型的输入矩阵形状与数据集不同，可以使用 NumPy 的`reshape()`方法，只需将矩阵所需的新维度传入即可。也可以传入`-1`，NumPy 可以根据你的矩阵推断出正确的维度：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200656681.png)

### 三、公式应用

### 3.1 均方误差

在 NumPy 中可以很容易地实现均方误差：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200824322.png)

这样做的好处是，NumPy 无需考虑`predictions`与`labels`具体包含的值。

下面将通过一个示例来逐步执行上面代码行中的四个操作：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200860085.png)

预测(predictions)和标签(labels)向量都包含三个值。这意味着`n`的值为 3。在执行减法后最终得到如下值：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200897624.png)

然后可以计算向量中各值的平方：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200946316.png)

再对这些值求和，即可得到该预测的误差值和模型质量分数：

![](http://cnd.qiniu.lin07ux.cn/markdown/1563200965908.png)

