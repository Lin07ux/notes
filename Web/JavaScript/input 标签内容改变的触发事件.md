1. onchange 事件与 onpropertychange 事件的区别：

　　onchange 事件在内容改变（两次内容有可能相等）且失去焦点时触发；onpropertychange 事件是实时触发，每增加或删除一个字符就会触发，通过 js 改变也会触发该事件，但是该事件是 IE 专有。

2. oninput 事件与 onpropertychange 事件的区别：

　　oninput 事件是 IE 之外的大多数浏览器支持的事件，在 value 改变时实时触发，但是通过 js 改变 value 时不会触发；onpropertychange 事件是任何属性改变都会触发，而 oninput 却只在 value 改变时触发，oninput 要通过 addEventListener() 来注册，onpropertychange 注册方法与一般事件相同。

3. oninput 与 onpropertychange 失效的情况：

oninput 事件：

　　（1）当脚本中改变 value 时，不会触发；

　　（2）从浏览器的自动下拉提示中选取时，不会触发；

onpropertychange 事件：

　　当 input 设置为 disable=true 后，不会触发。



