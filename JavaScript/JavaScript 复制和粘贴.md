## 一、命令

JavaScript 中可以通过 BOM 提供的`execCommand()`方法来执行复制和粘贴操作：

```JavaScript
// 选中内容
document.querySelector('#input').select();
// 复制到剪切版
document.execCommand('copy');
// 选中输入框
$("#bind-string")[0].select();
// 将复制的内容粘贴到指定位置，因有更大的安全风险所以限制比较多
document.execCommand('paste');
```

> 更多命令可以参考 [document .exec Command - MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/Document/execCommand)

## 二、事件

### 2.1 剪贴板事件

* `beforecopy`：在发生复制操作前触发；
* `copy`：在发生复制操作的时候触发；
* `beforecut`：在发生剪切操作前触发；
* `cut`：在发生剪切操作的时候触发；
* `beforepaste`：在发生粘贴操作前触发；
* `paste`：在发生粘贴操作的时候触发。

在实际的事件发生之前，通过`beforecopy`、`beforecut`和`beforepaste`事件，可以在向剪贴板发送数据，或者从剪贴板取得数据之前修改数据。

### 2.2 剪贴板数据

要访问剪贴板中的数据，可以通过`clipboardData`对象：

* 在 IE 中，clipboardData 对象是 window 对象的属性；
* 而在 Chrome、Safari 和 Firefox 4+ 中，clipboardData 对象是相应 event 对象的属性。

所以，在 Chrome、Safari 和 Firefox 4+ 中，只有在处理剪贴板事件期间，clipboardData 对象才有效，这是为了防止对剪贴板的未授权访问；在 IE 中，则可以随时访问 clipboardData 对象。为了确保跨浏览器兼容，最好只在发生剪贴板事件期间使用 clipboardData 对象。

另外，目前 clipboardDate 对象的兼容性并不是很好，能实际使用的地方有限。[can i use](http://caniuse.com/#search=Clipboard)

## 三、clipboardData 对象

### 3.1 方法

clipboardData 对象主要有如下几个方法；

* `getData()` 获取剪贴板数据
* `setData()` 设置剪贴板数据
* `clearData()` 清除剪贴板数据

#### 3.1.1 getData()

`getData()`方法用于从剪贴板中获取数据，它接收一个参数，即要取得的数据格式：

* 在 IE 中，有两种数据格式：`text`和`URL`。
* 在 Chrome、Safari 和 Firefox 4+ 中，这个参数是一种 MIME 类型。不过可以用`text`代表`text/plain`。

```javascript
// 获取剪贴板数据方法
function getClipboardText (event) {
    var clipboardData = event.clipboardData || window.clipboardData;
    return clipboardData.getData("text");
}
```

#### 3.1.2 setData()

`setData()`方法的第一个参数也是数据类型，第二个参数是要放在剪贴板中的数据。

对于第一个参数：

* IE 照样是支持`text`和`URL`。
* 在 Chrome、Safari 中，仍然支持 MIME 类型，不同的是`setData()`方法不能识别`text`类型。

在成功将数据放到剪贴板中后，该方法会返回 true，否则返回 false。

```javascript
// 设置剪贴板数据
function setClipboardText (event, value) {
    if (event.clipboardData) {
        return event.clipboardData.setData("text/plain", value);
    } else if (window.clipboardData) {
        return window.clipboardData.setData("text", value);
    }
}
```

### 3.2 属性

clipboardData 方法的主要属性如下：

|  属性          |  类型                |  说明                 |
|---------------|----------------------|----------------------|
| dropEffect    | String               | 默认是`none`          |
| effectAllowed | String               | 默认是`uninitialized` |
| Files         | FileList             | 粘贴操作为空 List      |
| items         | DataTransferItemList | 剪切板中的各项数据      |
| types         | Array                | 剪切板中的数据类型      |

#### 3.2.1 items 属性

items 是一个`DataTransferItemList`对象，类似数组，可遍历，里面都是`DataTransferItem`类型的数据。

`DataTransferItem`有两个属性：

* `kind` 一般为 string 或者 file。
* `type` 具体的数据类型，例如具体是哪种类型字符串或者哪种类型的文件，即 MIME-Type。

`DataTransferItem`有两个方法：

* `getAsFile()` 无参数，如果`kind`是`file`，可以用该方法获取到文件内容。
* `getAsString(callback)` 可以传入一个回调函数，如果`kind`是`string`，可以用该方法获取到字符串，获取到的字符串会作为回调函数的第一个参数传入。获取成功之后就会调用回调函数。

#### 3.2.2 types 属性

类是剪贴板中数据的类别，一般常见的值有`text/plain`(普通字符串)、`text/html`(带有样式的 html)、`Files`(文件)。

### 3.3 示例

将剪切板中的图片资源显示到页面上

```html
<input type="text" id="input">
```

```javascript
// 将图片资源显示到页面中
function showImage(imageData) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = new Image();
        img.src = e.target.result;
        document.body.appendChild(img);
    };
    // 读取图片文件
    reader.readAsDataURL(imageData);
}

document.querySelector("#input").addEventListener("paste", function(e) {
    var clipboardData = e.clipboardData,
        items,
        item,
        types;

    if (clipboardData) {
        if (!items = clipboardData.items) {
            return;
        }
        
        item = items[0];
        types = clipboardData.types || [];
        
        for(var i = 0; i < types.length; i++ ){
            if (types[i] === "Files") {
                item = items[i];
                break;
            }
        }
        
        // 判断是否为图片数据
        if( item && item.kind === 'file' && item.type.match(/^image\//i) ){
            var blob = item.getAsFile();
            showImage(blob);
        }
    }
})
```


### 转摘

[原生JavaScript实现复制/粘贴](http://www.dengzhr.com/js/1056)

