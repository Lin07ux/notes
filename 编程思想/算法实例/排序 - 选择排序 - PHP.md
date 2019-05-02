### 1. 步骤

选择排序和冒泡排序类似，也是通过两两比较来实现，但是选择排序在每次循环中只是通过比较来找到最小(大)的元素，并不立即交换，而是在循环最后进行交换，比冒泡排序减少了很多的交换操作。

具体步骤如下：

1. 从数组中找到最小(大)的元素，交换到起始位置；
2. 从未排序的元素中继续最小(大)的元素，交换到未排序数据中的首位；
3. 重复执行第 2 步，直到最后一个元素。

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/markdown/M9aL1.gif)

### 3. 代码实现

```php
function selectionSort($arr) {
    $len = count($arr);
    
    for ($i = 0; $i < $len; $i++) {
        $min = $i;
        
        for ($j = $i + 1; $j < $len; $j++) {
            if ($arr[$j] < $arr[$min]) {
                // 记录找到的最小值的索引
                $min = $j;
            }
        }
        
        // 如果最小值的位置与当前假设的位置 $i 不同则互换值
        if ($i !== $min) {
            $temp = $arr[$i];
            $arr[$i] = $arr[$min];
            $arr[$min] = $temp;
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
$sortArr = selectionSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

**选择排序的时间复杂度为`O(n^2)`**。

**空间复杂度为`O(1)`**。

选择排序的主要优点与数据移动有关。如果某个元素位于正确的最终位置上，则它不会被移动。选择排序每次交换一对元素，它们当中至少有一个将被移到其最终位置上，因此对 n 个元素进行排序总共进行至多 n-1 次交换。在所有的完全依靠交换去移动元素的排序方法中，选择排序属于非常好的一种。

交换次数比冒泡排序少多了，但是比较的次数也多了很多。由于交换所需 CPU 时间比比较所需的 CPU 时间多，所以 n 值较小时，选择排序比冒泡排序快。

