### 基本数组去重

```javascript
Array.prototype.unique = function () {
    var result = [];
    
    this.forEach(function (v) {
        if (result.indexOf(v) < 0) {
            result.push(v);
        }
    });
    
    return result;
}
```

### 利用 hash 表去重，这是一种空间换时间的方法

```javascript
Array.prototype.unique = function () {
    var result = [], hash = {};
    
    this.forEach(function (v) {
        if (!hash[v]) {
            hash[v] = true;
            result.push(v);
        }
    });
    
    return result;
}
```

上面的方法存在一个 bug，对于数组`[1, 2, '1', '2', 3]`，去重结果为`[1,2,3]`，原因在于对象对属性索引时会进行强制类型转换，`arr[‘1’]`和`arr[1]`得到的都是`arr[1]`的值，因此需做一些改变：

```javascript
Array.prototype.unique = function () {
    var result = [], hash = {};
    
    this.forEach(function (v) {
        var vType = typeof(v);
        
        hash[v] || hash[v] = [];
        
        if (hash[v].indexOf(vType) < 0) {
            hash[v].push(vType);
            result.push(v);
        }
    });
    
    return result;
}
```

### 先排序后去重

```javascript
Array.prototype.unique = function () {
    var result = [];
    this.sort();
    this.forEach(function (v) {
        // 仅与 result 最后一个元素比较
        v != result[result.length - 1] && result.push(v);
    });
}
```

