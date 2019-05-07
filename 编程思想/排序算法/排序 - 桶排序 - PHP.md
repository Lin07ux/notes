### 1. 步骤

桶排序是计数排序的升级版。它利用了函数的映射关系，高效与否的关键就在于这个映射函数的确定。

桶排序(Bucket sort)的工作的原理是：假设输入数据服从均匀分布，将数据分到有限数量的桶里，每个桶再分别排序（有可能再使用别的排序算法或是以递归方式继续使用桶排序进行排）。

具体步骤如下：

1. 设置一个定量的数组当作空桶，每个空桶存放相应区间的数值；
2. 遍历输入数据，并且把数据一个一个放到对应的桶里去；
3. 对每个不是空的桶进行排序；
4. 从不是空的桶里把排好序的数据拼接起来。 

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/markdown/Zmd2Ja.gif)

### 3. 代码实现

```php
function bucketSort($arr, $bucketSize = 5) {
    $len = count($arr);
    if ($len <= 1) {
        return $arr;
    }
    
    $max = $min = $arr[0];
    for ($i = 1; $i < $len; $i++) {
        if ($arr[$i] < $min) {
            $min = $arr[$i];
        } elseif ($arr[$i] > $max) {
            $max = $arr[$i];
        }
    }

    // 初始化桶
    for ($i = floor(($max - $min) / $bucketSize) + 1; $i > 0; $i--) {
        $buckets[$i] = [];
    }
    
    // 利用映射函数将数据分配到各个桶中
    for ($i = 0; $i < $len; $i++) {
        buckets[floor(($arr[i] - $min) / $bucketSize)][] = $arr[$i];
    }
    
    // 对每个桶中的元素进行插入排序，并整理数据到同一个数组中
    $arr = [];
    $len = count($buckets);
    for ($i = 0; $i < $len; $i++) {
        $bucketLen = count($buckets[$i]);
        
        if ($bucketLen < 1) {
            continue;
        }
        
        $buckets[$i] = $insertionSort($buckets[$i]);
        for ($j = 0; $j < $bucketLen; $j++) {
            $arr[] = $buckets[$i][$j];
        }
    }
    
    return $arr;
}
```

### 4. 测试示例

```php
$startTime = microtime(1);

$arr = range(1, 10);
shuffle($arr);

echo "before sort: ", implode(', ', $arr), "\n";
$sortArr = bucketSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

当输入的元素是 n 个 0 到 k 之间的整数时，桶排序时间复杂度是`O(n+k)`，空间复杂度也是`O(n+k)`

桶排序的时间复杂度，取决与对各个桶之间数据进行排序的时间复杂度，因为其它部分的时间复杂度都为`O(n)`。很显然，桶划分的越小，各个桶之间的数据越少，排序所用的时间也会越少。但相应的空间消耗就会增大。





