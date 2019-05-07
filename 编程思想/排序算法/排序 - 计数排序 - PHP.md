### 1. 步骤

计数排序的思想是：将元素按照值增加位置处的计数，然后再将非 0 计数对应的值依次展开，便得到了有序序列。*计数排序要求输入的数据必须是有确定范围的整数。*

具体步骤如下：

1. 花`O(n)`的时间扫描一下整个序列 A，获取最小值`min`和最大值`max`；
2. 开辟一块新的空间创建新的数组 B，长度为`max - min + 1`；
3. 数组 B 中 index 的元素记录的值是 A 中某元素出现的次数；
4. 最后输出目标整数序列，具体的逻辑是遍历数组 B，输出相应元素以及对应的个数。

> 对于 PHP 中，需要使用定长数组，这样键会自动排序。如果用普通的数组，键不会自动排序，不存在的键也不会自动填充 null。

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/PNsq8d.gif)

### 3. 代码实现

```php
function countingSort($arr) {
    $len = count($arr);
    
    if ($len <= 1) {
        return $arr;
    }
    
    $max = $arr[0];
    for ($i = 1; $i < $len; $i++) {
        if ($arr[$i] > $max) {
            $max = $arr[$i];
        }
    }
   
    // 定长数组键会自动排序，PHP 数组是 hash 表的实现
    // 如果用普通的数组键不会自动排序，不存在的键也不会自动填充 null
    $frequency = new SplFixedArray($max + 1);
    
    // 统计 arr 中, 值出现的频次
    for ($i = 0; $i < $len; $i++) {
        if (empty($frequency[$arr[$i]])) {
            $frequency[$arr[$i]] = 0;
        }
    
        $frequency[$arr[$i]] += 1;
    }
    
    $arr = [];
    $len = count($frequency);
    
    for ($i = 0; $i < $len; $i++) {
        if (! empty($frequency[$i])) {
            for ($j = 0; $j < $frequency[$i]; $j++) {
                $arr[] = $i;
            }
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
$sortArr = countingSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

计数排序是一个稳定的排序算法。当输入的元素是 n 个 0 到 k 之间的整数时，**时间复杂度是`O(n+k)`，空间复杂度也是`O(n+k)`**，其排序速度快于任何比较排序算法。

当 k 不是很大并且序列比较集中时，计数排序是一个很有效的排序算法。






