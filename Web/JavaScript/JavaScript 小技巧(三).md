### 向下取整

```JavaScript
var a = ~~3.14;   // 3
var b = 3.14 >> 0;   // 3
var c = 3.14 | 0;  // 3
```

### 字符串转换为数值并取整

```JavaScript 
var a = '3.14' | 0;  // 3
var b = '3.14' ^ 0;  // 3
```

### 变量值交换

```JavaScript
var a = 1, b =2;

a = [b, b = a][0];
```

### 截断数组

```JavaScript
var arr = [1, 2, 3, 4, 5, 6];

arr.length = 3;
console.log(arr);  // [1, 2, 3]
```

