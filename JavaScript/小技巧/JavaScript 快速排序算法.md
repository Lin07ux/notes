### 方法一：尽可能不用 js 数组方法

```javascript
function quickSort(arr){
    qSort(arr,0,arr.length - 1);
}
function qSort(arr,low,high){
    if(low < high){
        var partKey = partition(arr,low,high);
        qSort(arr,low, partKey - 1);
        qSort(arr,partKey + 1,high);
    }
}
function partition(arr,low,high){
    var key = arr[low];  // 使用第一个元素作为分类依据
    while(low < high){
        while(low < high && arr[high] >= arr[key])
            high--;
        arr[low] = arr[high];
        while(low < high && arr[low] <= arr[key])
            low++;
        arr[high] = arr[low];
    }
    arr[low] = key;
    return low;
}
```

### 方法二：使用 js 数组方法

```javascript
function quickSort(arr){
   if(arr.length <= 1) return arr;
   var index = Math.floor(arr.length/2);
   var key = arr.splice(index,1)[0];
   var left = [],right = [];
   arr.forEach(function(v){
       v <= key ? left.push(v) : right.push(v);
   });
   return quickSort(left).concat([key],quickSort(right));
}
```

