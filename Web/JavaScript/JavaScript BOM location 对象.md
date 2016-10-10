在浏览器中，宿主提供了一个 location 对象，以便 JavaScript 获取和操作当前页面的链接地址。

### location 对象的属性
> 以`http://localhost:80/dir/index.html?q1=abc&q2=efg&q3=h#anchor`为例。

|   属性    |   定义          |   示例                    |
|----------|--------------------------------------|-----------------------|
| hash     | URL 中的 hash，没有则为空，包含 #        | #anchor               |
| host     | 服务器地址，即：主机名（域名）＋ 端口号    | localhost:80          |
| hostname | 服务器域名                             | localhost             |
| href     | 当前页面的完整 url                      | http://localhost:80/dir/index.html?q1=abc&q2=efg&q3=h#anchor |
| origin   | 服务器域，包含协议、域名和端口             | http://localhost:80   |
| pathname | URL 中的目录和文件名                    | /dir/index.html       |
| port     | 端口号（如果没有指定则为空字符串）          | 80                   |
| protocol | 协议                                   | http                 |
| search   | 查询字符串。这个字符以问号开头，没有则为空   | ?q1=abc&q2=efg&q3=h   |

location 的这 9 个属性都是可读写的。其中，改变`location.href`会跳转到新的 URL 页面，而修改`location.hash`会跳到当前页面中锚点位置。每次修改`window.location`的属性（hash 除外），页面都会以新的 URL 重新加载，并在浏览器的历史纪录中生成一条新纪录。

### location 对象的方法
* `assign(url)` 打开新的 URL，并在浏览器的历史纪录中生成一条记录。
* `replace(url)` 打开新的 URL，但是不会在浏览器的历史纪录中生成新纪录。
* `reload(force)` 刷新当前页面。force 为 true 时从服务器端重新加载；为 false 时从浏览器缓存中重新加载。默认值 false。

其中，`location.assign(url)`的效果跟下列两行代码的效果完全一样：

```javascript
window.location = url;
location.href = url;
```

位于`location.reload()`调用之后的代码可能会也可能不会执行，这取决于网络延迟或系统资源等因素。因此，最好将`location.reload()`放在代码的最后一行。

### 获取 url 的 querystring 参数
url 中的 querystring 参数都存放在 location 对象的 search 属性中，但是是以字符串形式存在，一般我们需要将其取出来单独使用。下面提供两种方法：

**方法一：正则匹配**

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

这个方法适合偶尔获取 querystring 中的某个参数的值的情况，因为其没有缓存获取的结果，而且使用的是正则，比较耗费资源。

**方法二：split**

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
            
        if(name) {
            args[name] = value;
        }
    }
    
    return args;
}

// 对于 http://localhost/index.html?q1=abc&q2=efg&q3=h 的 url
// 获取 q1 参数值的方法如下：首先获取全部的 querystring 参数
var qs = getQueryString();  
// 然后获取其中的指定参数
var q1 = qs["q1"]; // abc
```

### 转摘
[JavaScript：获取url的querystring参数](http://www.dengzhr.com/frontend/1065)
或：
[JavaScript：获取url的querystring参数](http://www.tuicool.com/articles/jmi2YrN)


