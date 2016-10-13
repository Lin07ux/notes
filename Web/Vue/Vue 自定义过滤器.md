### 截取字符串并补全省略号
根据给定长度，截取字符串的一部分，可以从字符串起始和结束处截取，并分别在截取得到的子串后面或前面添加省略号。如果字符串长度不足截取长度，则直接返回，不做处理。

```javascript Vue.filter('subtext', function (value, length) {     var arr = value.split('');      if (Math.abs(length) < arr.length) {         if (length >= 0) {             return arr.slice(0, length).join('') + '...';         } else {             return '...' + arr.slice(length).join('')         }     }      return value; });
```




