移动端的浏览器一般都支持`window.orientation`这个参数，通过这个参数可以判断出手机是处在横屏还是竖屏状态。从而根据实际需求而执行相应的程序。这个参数的值为只能为 0、90、-90、180 中的一个。

屏幕方向对应的`window.orientation`值：

- ipad： 90 或 -90 横屏
- ipad： 0 或180 竖屏
- Andriod：0 或180 横屏
- Andriod： 90 或 -90 竖屏

这个参数改变时，对应的事件是`onorientationchange`。

```javascript
// 判断手机横竖屏状态：
function orientation () {
    if(window.orientation == 180 || window.orientation == 0){
        alert("竖屏状态！")
    }
    if(window.orientation == 90 || window.orientation == -90){
        alert("横屏状态！")
    }
}
window.addEventListener("onorientationchange" in window ? "orientationchange" : "resize", orientation, false);
```


