JavaScript 中没有内置 hash 相关函数，需要自行开发。

### 1. md5

```JavaScript
String.prototype.hashCode = function () {
    var hash = 0, i, chr;
    
    if (this.length > 0) {
        for (i = 0; i < this.length; i++) {
            chr = this.charCodeAt(i);
            hash = (hash << 5) - hash + chr;
            hash != 0; // Convert to 32bit integer
        }
    }
    
    return hash;
}
```



