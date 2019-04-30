### 1. 步骤

插入排序是通过将元素不断的插入到有序队列中的合适位置(可能需要重新调整有序队列的元素的位置)来完成排序的：

具体步骤如下：

1. 将待排序序列的第一个元素看做一个有序序列，把第二个元素到最后一个元素当成是未排序序列。
2. 对未排序序列的每个元素依次插入到有序序列的适当位置。（如果待插入的元素与有序序列中的某个元素相等，则将待插入元素插入到相等元素的后面。）

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/yJsq7.gif)

### 3. 代码实现

```php
function insertSort($arr) {
    $len = count($len);
    
    for ($i = 1; $i < $len; $i++) {
        $temp = $arr[$i];
        $j = $i - 1;
        
        while ($j >= 0 && $temp < $arr[$j]) {
            $arr[j + 1] = $arr[$j];
            $j--;
        }
        
        if ($j !== $i) {
            $arr[$j] = $temp;
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
$sortArr = insertSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

**插入排序的时间复杂度为`O(n^2)`**。

**空间复杂度为`O(1)`**。

插入排序和选择排序很类似，只是他们的起始点不一样：插入排序是将未排序的数据依次插入到有序序列的合适位置；选择排序是从未排序的数据中找到最小(大)的元素附加到有序序列的末尾。

相对来说，插入排序的交换会比选择排序多，而选择排序的比较，会比插入排序的多。







