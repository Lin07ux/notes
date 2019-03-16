> 转摘：[JavaScript：获取url的querystring参数](http://www.dengzhr.com/frontend/1065)

url 中的 querystring 参数都存放在 location 对象的 search 属性中，但是是以字符串形式存在，一般我们需要将其取出来单独使用。下面提供两种方法：

### 方法一：正则匹配

```javascript
// 获取 url 中的指定参数
function getQueryString(name) {
    // 匹配目标参数
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", i);
    // 对 querystring 匹配目标参数
    var result = window.location.search.substr(1).match(reg);
    
    if (result != null) {
        return decodeURIComponent(result[2]);
    } else {
        return null;
    }
}

// 对于 http://localhost/index.html?q1=abc&q2=efg&q3=h 的 url
// 获取 q1 参数值的方法如下
var q1 = getQueryString('q1'); // abc
```

> 这个方法适合偶尔获取 querystring 中的某个参数的值的情况，因为其没有缓存获取的结果，而且使用的是正则，比较耗费资源。

### 方法二：split

```javascript
function getQueryString() {
    var qs    = location.search.substr(1),   // 获取 url 中"?"符后的字串
        args  = {}, // 保存参数数据的对象
        items = qs.length ? qs.split("&") : [], // 取得每一个参数项,
        item  = null,
        len   = items.length;

    for(var i = 0; i < len; i++) {
        item  = items[i].split("=");
        var name  = decodeURIComponent(item[0]),
            value = decodeURIComponent(item[1]);
            
        if(name) args[name] = value;
    }
    
    return args;
}

// 对于 http://localhost/index.html?q1=abc&q2=efg&q3=h 的 url
// 获取 q1 参数值的方法如下：首先获取全部的 querystring 参数
var qs = getQueryString();  
// 然后获取其中的指定参数
var q1 = qs["q1"]; // abc
```



