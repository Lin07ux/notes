### 1. 步骤

基数排序是按照低位先排序，然后收集；再按照高位排序，然后再收集；依次类推，直到最高位。有时候有些属性是有优先级顺序的，先按低优先级排序，再按高优先级排序。最后的次序就是高优先级高的在前，高优先级相同的低优先级高的在前。

具体步骤如下：

1. 将所有待比较数值（正整数）统一为同样的数位长度，数位较短的数前面补零。
2. 从最低位开始，依次进行一次排序；
3. 从最低位排序一直到最高位排序完成以后, 数列就变成一个有序序列。

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/markdown/Iq1LBdaer.gif)

### 3. 代码实现

```php
function radixSort($arr) {
    $len = count($arr);
    
    if ($len <= 1) {
        return $arr;
    }
    
    $maxDigit = getMaxDigit($arr);
    $mod = 10;
    $dev = 1;

    for ($i = 0; $i < $maxDigit; $i++) {
        // 考虑负数的情况，这里扩展一倍队列数，其中 [0-9] 对应负数，[10-19] 对应正数 (bucket + 10)
        $counter = [];
        
        for ($j = 0; $j < $len; $j++) {
            $bucket = floor(($arr[$j] % $mod) / $dev) + $mod;
            
            if (empty($counter[$bucket])) {
                $counter[$bucket] = [];
            }
            
            $counter[$bucket] = [];
        }
        
        $pos = 0;
        foreach ($counter as $bucket) {
            foreach ($bucket as $val) {
                $arr[$pos++] = $val;
            }
        }

        $dev *= 10; $mod *= 10;
    }
    
    return $arr;
}

function getMaxDigit($arr) {
    $max = $arr[0];
    
    foreach ($arr as $val) {
        if ($max < $val) {
            $max = $val;
        }
    }
    
    if ($max == 0) {
        return 1;
    }
    
    for ($len = 0; $max >= 1; $max /= 10) {
        $len++;
    }
    
    return $len;
}
```

### 4. 测试示例

```php
$startTime = microtime(1);

$arr = range(1, 10);
shuffle($arr);

echo "before sort: ", implode(', ', $arr), "\n";
$sortArr = radixSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

基数排序的时间复杂度为`O(n*k)`空间复杂度为`O(n+k)`，其中 k 为桶的数量。一般来说`n>>k`，因此额外空间需要大概 n 个左右。

基数排序基于分别排序、分别收集，所以是稳定的。但基数排序的性能比桶排序要略差，每一次关键字的桶分配都需要`O(n)`的时间复杂度，而且分配之后得到新的关键字序列又需要`O(n)`的时间复杂度。假如待排数据可以分为 k 个关键字，则基数排序的时间复杂度将是`O(d*2n)`，当然 k 要远远小于 n，因此基本上还是线性级别的。






