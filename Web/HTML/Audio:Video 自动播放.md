Audio 元素可以设置`autoplay`属性来自动播放，但是并不是所有的浏览器都支持该属性，而且有些浏览器还会禁止自动播放，避免对用户的骚扰。

关于音乐自动播放的问题，现在可以分为三种：

1. 支持 audio 的`autoplay`，大部分安卓机自带浏览器和微信，大部分的 iOS 微信（无需特殊解决）；
2. 不支持 audio 的`autoplay`，部分的 iOS 微信（本文提供的解决方案）；
3. 不支持 audio 的`autoplay`，部分的安卓机子的自带浏览器（比如小米，开始模仿 Safari）和全部的 iOS Safari（这种只能做用户触屏时就触发播放了）。


如果确实需要自动播放，那么可以考虑在页面加载完成之后，通过一个事件的回调来播放音乐。如下所示：

```html
<audio id="Jaudio" class="media-audio" src="bg.mp3" preload loop="loop"></audio >
```

```js
function audioAutoPlay(id){
    var audio = document.getElementById(id),
        play = function() {
            audio.play();
            document.removeEventListener("touchstart", play, false);
        };
    
    audio.play();
    
    document.addEventListener("WeixinJSBridgeReady", play, false);
    document.addEventListener("touchstart", play, false);
}

audioAutoPlay('Jaudio');
```

> Video 元素也是类似处理，具体可以参考：[视频播放--踩坑小计](https://zhuanlan.zhihu.com/p/37793384)



