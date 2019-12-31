这两个设备的事件均需要放在`window`对象上。

## 问题

### 1. iOS 中无法触发事件

iOS 需要`https`页面才能触发这两个事件。不论是在 Safari 中还是微信中，都需要使用 https 服务才可触发。

### 2. iOS 13 中无法触发

> 转摘：[How to requestPermission for devicemotion and deviceorientation events in iOS 13+](https://dev.to/li/how-to-requestpermission-for-devicemotion-and-deviceorientation-events-in-ios-13-46g2)

从 iOS 12.2 开始，Apple 默认关闭了 Safari 浏览器中的 Orientation 和 Motion 设备的访问权限。而在 iOS 13 开始，Safari 中的 JavaScript 提供了获取这两个设备事件的访问权限的 API。

和获取相机和 GPS 事件的方式类似，获取 Orientation 和 Motion 权限的请求需要由用户的操作(如`click`)触发，然后用户才可以看到请求授权的提示。如果用户不授权，那么就无法获得对应的权限。

获取 DeviceMotionEvent 授权的代码类似如下：

```JavaScript
if (typeof DeviceMotionEvent.requestPermission === 'function') {
    document.addEventListener('click', requestMotionRequestPermission, false);
} else {
    window.addEventListener('devicemotion', () => {});
}

function requestMotionRequestPermission () {
    DeviceOrientationEvent.requestPermission().then(permissionState => {
        if (permissionState === 'granted') {
            window.addEventListener('deviceorientation', () => {});
        } else {
            // Unauthorized
        }
    }).catch(console.error);
    
    // Remove Ux handler
    document.removeEventListener('click', requestMotionRequestPermission, false);
}
```

获取 DeviceOrientationEvent 授权的方式类似。

