
### 1. 步骤

冒泡排序就是通过两两比较，将交小(大)的元素通过依次交换，放到顶部。具体步骤如下：

1. 比较相邻的元素。如果第一个比第二个大，就交换他们两个。
2. 对每一对相邻元素作同样的工作，从开始第一对到结尾的最后一对。这步做完后，最后的元素会是最大的数。
3. 针对所有的元素重复以上的步骤，除了最后一个。
4. 持续每次对越来越少的元素重复上面的步骤，直到没有任何一对数字需要比较。

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/markdown/NQ0d1.gif)

### 3. 代码实现

```php
function bubbleSort($arr) {
    $len = count($arr);
    
    for ($i = 0; $i < $len; $i++) {
        // 通过设置是否发生交换的标识来加快结束，当没有发生过交换时，
        // 说明当前数据已经是有序的了，不需要再继续比较了
        $flag = false;
        
        for ($j = $i + 1; $j < $len; $j++) {
            if ($arr[$i] > $arr[$j]) {
                $temp = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $temp;
                
                $flag = true;
            }
        }
        
        if (! $flag) {
            break;
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
$sortArr = bubbleSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

**冒泡排序最差复杂度为`O(n^2)`，最好为`O(n)`**。

**空间复杂度为`O(1)`**。

由于冒泡排序是通过两两比较并交换进行排序的，所以最差的情况下，也就是完全逆序时，需要进行`n^2`次排序；而即便是已经排序好的情况下，也要进行一次完整的遍历。


