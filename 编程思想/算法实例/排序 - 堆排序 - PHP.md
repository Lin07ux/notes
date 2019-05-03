### 1. 步骤

堆是具有以下性质的完全二叉树：

* 每个结点的值都大于或等于其左右孩子结点的值(`$arr[i] >= $arr[2i+1] && $arr[i] >= $arr[2i+2]`)，称为大顶堆；
* 每个结点的值都小于或等于其左右孩子结点的值(`$arr[i] <= $arr[2i+1] && $arr[i] <= $arr[2i+2]`)，称为小顶堆。

堆排序是指使用类似上面的近似完全二叉树进行排序。具体步骤如下：

1. 将无需序列构建成一个大顶堆(升序)或小顶堆(降序)；
2. 将堆顶元素与末尾元素交换，将最大元素"沉"到数组末端；
3. 把堆的尺寸缩小 1 并重新调整结构，使其满足堆定义；
4. 然后重复执行第 2、3 步操作，直到堆的尺寸为 1。

### 2. 动态图

![](http://cnd.qiniu.lin07ux.cn/markdown/dSG1l.gif)

### 3. 代码实现

```php
function heapSort($arr) {
    $len = count($arr);
    
    // 建堆(下标小于或等于 floor($len / 2) - 1 的节点都是要调整的节点)
    for ($i = floor($len / 2) - 1; $i >= 0; $i--) {
        heapify($arr, $i, $len);
    }
    
    // 调整堆(将堆顶元素与最后一个元素交换，然后重新调整堆)
    for ($i = $len - 1; $i >= 0; $i--) {
        $temp = $arr[0];
        $arr[0] = $arr[$i];
        $arr[$i] = $temp;
        
        heapify($arr, 0, $i - 1);
    }
    
    return $arr;
}

function heapify(&$arr, $start, $len) {
    $left = 2 * $start + 1;
    $right = 2 * $start + 2;
    $largest = $start;
    
    // 对比父节点和左右子节点，找到三者中的最大值
    if ($left < $len && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }
    
    if ($right < $len && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }
    
    // 如果最大值不是当前父节点，则交换父节点和最大的子节点，并沿较大的子节点继续向下筛选
    if ($largest !== $start) {
        $temp = $arr[$largest];
        $arr[$largest] = $arr[$start];
        $arr[$start] = $temp;
        
        heapify($arr, $largest, $len);
    }
}
```

### 4. 测试示例

```php
$startTime = microtime(1);

$arr = range(1, 10);
shuffle($arr);

echo "before sort: ", implode(', ', $arr), "\n";
$sortArr = heapSort($arr);
echo "after sort: ", implode(', ', $sortArr), "\n";

echo "use time: ", microtime(1) - $startTime, "s\n";
```

### 5. 复杂度

**堆排序的时间复杂度是`O(n log n)`，空间复杂度是`O(1)`。**

堆排序中，首先是需要建队，然后是调整队。

**建堆**：建堆是不断调整堆的过程，从 len/2 处开始调整，一直到第一个节点，此处 len 是堆中元素的个数。建堆的过程是线性的过程，从 len/2 到 0 处一直调用调整堆的过程，相当于`o(h1) + o(h2)...+ o(hlen/2)`其中 h 表示节点的深度，len/2 表示节点的个数，这是一个求和的过程，结果是线性的`O(n)`。

**调整堆**：调整堆在构建堆的过程中会用到，而且在堆排序过程中也会用到。利用的思想是比较节点 i 和它的孩子节点`left(i)`、`right(i)`，选出三者最大(或者最小)者，如果最大（小）值不是节点 i 而是它的一个孩子节点，那边交互节点 i 和该节点，然后再调用调整堆过程，这是一个递归的过程。调整堆的过程时间复杂度与堆的深度有关系，是`log(n)`的操作，因为是沿着深度方向进行调整的。

**堆排序**：堆排序是利用上面的两个过程来进行的。首先是根据元素构建堆。然后将堆的根节点取出(一般是与最后一个节点进行交换)，将前面 len-1 个节点继续进行堆调整的过程，然后再将根节点取出，这样一直到所有节点都取出。堆排序过程的时间复杂度是 `O(nlogn)`。因为建堆的时间复杂度是`O(n)`（调用一次）；调整堆的时间复杂度是`log(n)`，调用了 n-1 次，所以堆排序的时间复杂度是`O(nlog(n))`。





