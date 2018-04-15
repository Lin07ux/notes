## 横竖屏判断
### window.orientation
移动端的浏览器一般都支持`window.orientation`这个参数，通过这个参数可以判断出手机是处在横屏还是竖屏状态。从而根据实际需求而执行相应的程序。这个参数的值为只能为 0、90、-90、180 中的一个，但在不同设备上，每个值对应的状态可能不同。

屏幕方向对应的`window.orientation`值：

- ipad： 90 或 -90 横屏
- ipad： 0 或180 竖屏
- Andriod：0 或180 横屏
- Andriod： 90 或 -90 竖屏

### screen.orientation.angle
由于`window.orientation`在部分浏览器上并不被支持，所以还可以使用`screen.orientation.angle`的值来进行判断。


## 横竖屏切换事件
一般可以通过`orientationchange`来监听横竖屏状态的切换事件。不过，这个事件也有一定的兼容性问题，可以通过监听`resize`事件来补充。因为在移动端，一般横竖屏切换的时候，会自动触发 window 对象的 resize 事件。

```javascript
// 判断手机横竖屏状态：
function orientation () {
    if (window.orientation == 180 || window.orientation == 0){
        alert("竖屏状态！")
    }
    else if (window.orientation == 90 || window.orientation == -90) {
        alert("横屏状态！")
    }
}
window.addEventListener("onorientationchange" in window ? "orientationchange" : "resize", orientation, false);
```


## 转摘
[更靠谱的横竖屏检测方法](http://www.cnblogs.com/zhansingsong/p/5866692.html)

