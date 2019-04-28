### 1. 步骤

快速排序的主要步骤如下：

* 从数组中选个基准值；
* 将数组中大于基准值的放同一边、小于基准值的放另一边，基准值位于中间位置；
* 递归的对分列两边的数组再依次进行快速排序。

### 2. 动态图

动态过程类似如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/jyM7jaF.gif)


### 3. 代码实现

```php
function quickSort($arr)
{
    $len = count($arr);
    
    if ($len <= 1) {
        return $arr;
    }

    $v = $arr[0];
    $low = $up = [];
    
    for ($i = 1; $i < $len; ++$i) {
        if ($arr[$i] > $v) {
            $up[] = $arr[$i];
        } else {
            $low[] = $arr[$i];
        }
    }
    
    $low = quickSort($low);
    $up = quickSort($up);

    return array_merge($low, array($v), $up);
}
```

### 4. 测试示例

```php
$startTime = microtime(1);

$arr = range(1, 10);
shuffle($arr);

echo "before sort: ", implode(', ', $arr), "\n";
$sortArr = quickSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

测试结果类似如下：

```
before sort: 1, 7, 10, 9, 6, 3, 2, 5, 4, 8
after sort: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10
use time: 0.0009009838104248s
```

### 5. 时间复杂度

**快速排序的时间复杂度在最坏情况下是`O(N^2)`，平均的时间复杂度是`O(N*lgN)`**。

假设被排序的数列中有 N 个数。遍历一次的时间复杂度是`O(N)`，需要遍历多少次呢？至少`lg(N+1)`次，最多`N`次：

* 1) 为什么最少是`lg(N+1)`次？快速排序是采用的分治法进行遍历的，将它看作一棵二叉树，它需要遍历的次数就是二叉树的深度，而根据完全二叉树的定义，它的深度至少是`lg(N+1)`。因此，快速排序的遍历次数最少是`lg(N+1)`次。
* 为什么最多是`N`次？这个应该非常简单，还是将快速排序看作一棵二叉树，它的深度最大是`N`。因此，快读排序的遍历次数最多是`N`次。



