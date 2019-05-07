> 转摘：[PHP 实现归并排序算法](https://shockerli.net/post/merge-sort-implement-by-php/)

### 1. 步骤

归并排序是利用递归，先拆分数据，然后后合并并排序：

* 均分数列为两个子数列；
* 对得到的子数组递归重复上一步骤，直到子数列只有一个元素；
* 父数列合并两个子数列并排序，递归返回数列。

### 2. 动态图

该算法的动态实现过程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/B3Un6vY.gif)

### 3.代码实现

```php
// 归并排序主程序
function mergeSort($arr) {
    $len = count($arr);
    
    // 递归结束条件：到达这步的时候数组就只剩下一个元素了，也就是分离了数组
    if ($len <= 1) {
        return $arr;
    }

    // 从数组中间分隔数组
    $mid = intval($len / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);
    
    // 拆分完后开始递归合并往上走
    $left = mergeSort($left);
    $right = mergeSort($right);
    
     // 合并已排序后的两个数组
    $arr = merge($left, $right);

    return $arr;
}

// 将指定的两个有序数组(arrA, arr)合并并且排序
function merge($arrA, $arrB) {
    $arrC = [];
    
    // 这里不断的判断哪个值小，并将小的值给到 arrC, 但是到最后肯定要剩下几个值,
    // 不是剩下 arrA 里面的就是剩下 arrB 里面的值，而且这几个有序的值
    // 而且肯定比 arrC 里面所有的值都大，所以可以直接合并
    while (count($arrA) && count($arrB)) {
        $arrC[] = $arrA[0] < $arrB[0] ? array_shift($arrA) : array_shift($arrB);
    }

    return array_merge($arrC, $arrA, $arrB);
}
```

### 4. 示例

```php
$startTime = microtime(1);

$arr = range(1, 10);
shuffle($arr);

echo "before sort: ", implode(', ', $arr), "\n";
$sortArr = mergeSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

**归并排序的时间复杂度是`O(N*lgN)`**。

**空间复杂度为`O(N)`**。

归并排序的形式就是一棵二叉树，它需要遍历的次数就是二叉树的深度，而根据完全二叉树的可以得出，被排序的数列中有 N 个数时，它的时间复杂度是`O(N*lgN)`。


