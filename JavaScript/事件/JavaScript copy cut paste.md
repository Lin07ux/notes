> 转摘：[Javascript中的复制粘贴功能](http://blog.poetries.top/2018/12/23/js-copy/)

### 1. 事件

JavaScript 中的赋值粘贴涉及到的事件如下：

* `copy` 复制时触发事件
* `cut` 剪切时触发事件
* `paste` 粘贴时触发

> 这三个事件都有一个`before`事件对应：`beforecopy`、`beforecut`、`beforepaste`。但这几个 before 事件一般不怎么用。

使用鼠标和快捷键都可以触发这三个事件：

* 鼠标右键菜单的复制、粘贴、剪切
* 使用了相应的键盘组合键，比如：`Command + C`、`Command + V`

### 2. 使用

复制粘贴事件除了可以使用在`document`上，还可以使用在具体的 DOM 中。如下：

```JavaScript
document.addEventListener('copy', e => {
  // 监听全局复制事件
});
document.body.oncut = e => {
  // 监听全局剪切
};
document.querySelector('.content').addEventListener('paste', e => {
  // 监听 .content 元素的粘贴事件
});
```

### 3. 事件对象

这些事件被触发时，会生成一个 ClipboardEvent 事件对象，该对象中有两个特殊属性：

* `clipboardData` 一个 DataTransfer 对象，包含具体的复制、剪切、粘贴的内容/文件。
* `type` 表示是什么类别的事件，一般是`copy`、`cut`、`paste`。

> 不同浏览器，`clipboardData`所属的对象不同：在 IE 中这个对象是 Window 对象的属性，在 Chrome、Safari 和 Firefox 中，这个对象是相应的 event 对象的属性。所以在使用的时候需要做一下兼容。

其中，`clipboardData`是需要主要关注和操作的，其包含有事件涉及的内容或者文件，一般会用 DataTransfer 对象中如下的三个方法来操作剪贴事件的数据：

* `getData(type)` 接受一个参数，设置要取得的数据的格式
* `setData(type, data)` 设置剪贴板的数据，第一个表示数据格式，第二个表示数据内容
* `clearData()` 清除剪贴板内容

实际测试中，`getData()`方法在 Chrome 中只能获取到`paste`事件涉及到的内容，而`copy`和`cut`中则需要使用另外的方式来获取，类似如下：

```JavaScript
// 粘贴事件
document.body.onpaste = e => {
  let clipboardData = e.clipboardData || window.clipboardData;
  
  console.log('要粘贴的数据', clipboardData.getData('text'));
};

// 拷贝事件
document.body.oncopy = e => {
  console.log('被复制的数据:', window.getSelection(0).toString());
};
```

### 3. 应用

#### 3.1 复制时增加版权信息

在复制大段文字的时候，可以为其加上版权信息内容。

实现方式相对简单：在监听到拷贝/剪切事件的时候，获取剪贴板中的内容，然后在其后面增加版权文字信息，再设置剪贴板内容即可。可以全局监听，也可以监听内容元素。

```JavaScript
document.addEventListener('copy', event => {
  event.preventDefault(); // 取消默认的复制事件
  
  let text = window.getSelection(0).toString(); // 被复制的文字
  
  // 复制内容较少则不添加版权信息，超过一定长度则添加版权信息
  if (text.length > 10) {
    text +=
      '\n作者：OBKoro1\n' +
      '链接：' + window.location.href +
      '\n来源：掘金\n' +
      '著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。';
  }
  
  if (event.clipboardData) {
    return event.clipboardData.setData('text', text);  // 将信息写入粘贴板
  } else {
    return window.clipboardData.setData('text', text); // 兼容IE
  }
}
```

#### 3.2 防复制功能

禁止复制时，可以设置复制、剪切事件的监听器，取消默认效果，或者使用 CSS 来达到禁止复制剪切。

JavaScript 代码如下，虽然也可以使用`e.preventDefault()`来阻止默认事件，但直接返回`false`更简单一些：

```JavaScript
// 禁止右键菜单
document.body.oncontextmenu = () => false;

// 禁止文字选择。
document.body.onselectstart = () => false;

// 禁止复制
document.body.oncopy = () => false;

// 禁止剪切
document.body.oncut = () => false;

// 禁止粘贴
document.body.onpaste = () => false;
```

> 也可以对特定的 DOM 设置相应的事件处理器。

CSS 代码如下：

```CSS
/** css 禁止文本选择 这样不会触发 js **/
body {
    user-select: none;
    -moz-user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
}
```

#### 3.3 点击复制

当要实现点击一个按钮就能将相关内容放在剪贴板中时，就不能使用 clipboardData 对象了：clipboardData 只能在相应事件的 event 中获取，这是为了防止对剪贴板的未经授权的访问而做的限制。

> 前面提到，在 IE 中 clipboardData 是 Window 的属性，所以在 IE 中，可以使用这样的方式来实现：`window.clipboardData.setData('text','内容')`。

可以通过如下的方式来解决：

* 创建一个隐藏的 input 框，最好设置为只读`readonly`，避免被编辑
* 点击的时候，将要复制的内容放进 input 框中
* 选择文本内容`input.select()`（这里只能用 input 或者 textarea 才能选择文本）
* 使用`document.execCommand("copy")`执行浏览器的复制命令

```JavaScript
function copyText() {
    var text = document.getElementById('text').innerText; // 获取要复制的内容
    var input = document.getElementById('input'); // 获取隐藏 input 的 dom
  
    input.value = text; // 修改文本框的内容
    input.select(); // 选中文本
  
    document.execCommand('copy'); // 执行浏览器复制命令
    alert('复制成功');
}
```

> 也可以使用第三方库 [clipboard](https://github.com/zenorocha/clipboard.js)

#### 3.4 点击复制增强

上面一种点击复制方式在 iOS 上是无效的，为了兼容，可以使用如下的方式：

* 创建一个隐藏的文本元素，如`p`、`span`等
* 点击的时候将该元素的内容设置为要复制的内容
* 使用系统的`selection`功能选择文本元素
* 执行复制命令

```JavaScript
function selectionCopyText() {
    var element = document.getElementById('element'); // 获取隐藏的文本元素
    element.innerText = document.getElementById('text').innerText; // 修改文本框的内容
  
    var selection = window.getSelection();
    var range = document.createRange();
   
    selection.removeAllRanges(); // 清空选择
    range.selectNode(element); // 选中文本元素
    selection.addRange(range); // 将选中的元素加入到选择中
    
    document.execCommand('Copy'); // 执行浏览器复制命令
    alert('复制成功');
}
```


