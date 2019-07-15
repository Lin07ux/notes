> 转摘：[HTML audio基础API完全使用指南](https://www.zhangxinxu.com/wordpress/2019/07/html-audio-api-guide/)

目前 Audio 的兼容性已经比较好了，不需要在分别写多种类别的`source`标签了：

```html
<!-- 兼容写法 -->
<audio controls>
  <source src="audiofile.mp3" type="audio/mpeg">
  <source src="audiofile.ogg" type="audio/ogg">
  <!-- 如果浏览器不支持，则会呈现下面内容 -->
  <p>你的浏览器不支持HTML5音频，你可以<a href="audiofile.mp3">下载</a>这个音频文件。</p>
</audio>

<!-- 简洁写法 -->
<audio src="audiofile.mp3" controls></audio>
```

音频文件常见 3 种格式：`.ogg`、`.wav`和`.mp3`，其中，`.ogg` Safari浏览器不支持（目前版本 13），IE 到 Edge16 都不支持；`.wav`则是 IE-IE11 不支持；但是`.mp3` IE9+都支持。因此，如果不想麻烦，直接一个 MP3 格式就好了，由于就一种文件格式，因此`type`属性也可以不用设置。

## 一、HTML 属性

Audio 元素的 HTML 中，可以设置多个属性，对其行为进行控制。大部分属性都是布尔属性，但`src`和`type`是字符串属性。

### 1.1 autoplay

`autoplay`是个布尔属性值，表示声音是否自动播放，默认不自动播放。

```html
<audio src="audiofile.mp3" autoplay></audio>
```

随着浏览器的发展，这个属性变得限制越来越多。首先在移动端，`autoplay`自动播放已经被禁止了，PC 端也已经禁止。原因是网页在没有警告的情况下自发地发出声音，可能会让用户不愉快，体验不太好。因此，浏览器通常只允许在特定情况下成功地进行自动播放。

### 1.2 loop

`loop`是个布尔属性值，表示声音是否循环播放，默认不循环播放。

```html
<audio src="audiofile.mp3" loop></audio>
```

也可以使用 JavaScript 控制循环播放：

```JavaScript
document.querySelector('audio').loop = true;
```

`loop`属性适合用在可以不断循环的背景音乐上。`loop`属性在各个平台，各个浏览器下的表现良好，可以放心使用。

### 1.3 muted

`muted`也是个布尔属性值，表示音频是否静音，默认不静音播放。

```html
<audio src="audiofile.mp3" muted></audio>
```

同样，也可以使用 JavaScript 控制静音：

```JavaScript
document.querySelector('audio').muted = true;
```

`muted`属性在各个平台，各个浏览器下的表现良好，可以放心使用。

### 1.4 preload

`preload`可以指定音频的预加载策略，也就是在播放之前需要提前加载好音频的哪些资源。支持下面 3 个属性值：

1.	`none` 表示在点击播放按钮之前不加载任何信息。
2.	`metadata` 下载音频的meta信息，就是视频长度，类型，还有作者（如果有）等信息。
3.	`auto` 会尝试下载整个音频，通常浏览器自己也会优化加载策略，不会所有音频文件都加载下来，只是会加载一部分，保证点击播放按钮的时候，可以立即播放。

```html
<audio src="audiofile.mp3" preload="auto"></audio>
```

`preload`属性在 iOS Safari 浏览器下是被禁止的（桌面端无此问题），对于一些对音频播放时间实际要求比较高的场合，会给开发带来困难。通常解决方法是，第一次触摸的时候，音频静音，同时触发音频`play()`然后很快再`pause()`，此时，可以有类似`preload`的预加载行为。

### 1.5 controls

`controls`是个布尔属性值，表示声音是否显示音频播放暂停等控制器，默认是不显示的。

```html
<audio src="audiofile.mp3" controls></audio>
```

如果没有设置`controls`属性，整个音频文件播放面板都是完全隐藏的；如果有设置，则各个浏览器的 UI 可能各不相同，需要自行调整。

### 1.6 src

`src`属性表示音频的文件地址。

```html
<audio src="audiofile.mp3"></audio>
```

可以用在`<audio>`元素上，也可以用在`<source>`元素上。`<audio>`元素上只能一个音频地址，使用`<source>`可以并列多个不同格式的音频文件。

### 1.7 type

`type`属性用来指定音频文件的 MIME type 类型。

```html
<audio src="audiofile.mp3" type="audio/mpeg"></audio>
```

虽然不加`type`浏览器也能正确播放音频文件，但通常建议加上`type`属性。当然，如果`src`音频格式不固定，则`type`属性反而推荐不加，错误的`type`不如没有`type`。

## 二、DOM 属性

除了能够在 HTML 中为 Audio 添加属性，还可以通过 JavaScript 在 DOM 中对其进行属性调整。

下面的介绍都是假设有如下的 HTML：

```html
<audio id="myAudio" src="audiofile.mp3"></audio>
```

### 2.1 currentTime

`currentTime`是一个可读兼可写的属性，用来设置或获取当前已经播放的时长，单位是秒。

```JavaScript
// 获取音频已经播放时长
var playedTime = myAudio.currentTime;
```

如果音频尚未开始播放，则该值为 0.

也可以通过设置`currentTime`属性值，让音频定位到对应的时间点进行播放，例如，从 5 秒那里开始播放，则：

```JavaScript
// 跳到 5 秒那里
myAudio.currentTime = 5;
```

### 2.2 volume

`volume`也是一个可读兼可写的属性，用来设置或获取音频的音量大小，范围是`0-1`。例如，设置音量 50%，则：

```JavaScript
// 设置音量 50%
myAudio.volume = 0.5;
```

如果音频文件设置了静音`muted = true`，则该属性的返回值是 0。

### 2.3 playbackRate

`playbackRate`是一个可读兼可写的属性，用来设置或获取当前媒体文件的播放速率，值为数值，例如：

```JavaScript
// 获取音频播放速率
var audioSpeed = audio.playbackRate;
// 设置音频设置播放速率为正常速度的 1.5 倍
audio.playbackRate = 1.5;
```

这个播放速率，Gecko 内核浏览器速率范围是 0.25 到 5.0，超出这个范围就静音。而 Chrome 浏览器目前可以支持到 16。

此属性兼容性非常好，IE9+ 都支持。

### 2.4 paused

`paused`是一个只读属性，表示当前音频是否处于暂停状态。

```JavaScript
// true 或 false
console.log(myAudio.paused);
```

未播放或者播放暂停都会返回`true`。

## 三、方法

除了以上这些控制属性，Audio DOM 还支持更多的控制方法，可以控制其是否播放、暂停等。

### 3.1 play()

播放音频：

```JavaScript
myAudio.play();
```

需要注意的是，目前在现代浏览器下，无论是桌面端还是移动端，执行`myAudio.play()`不总是有效果的。

目前策略是，网页需要至少又一次可信任的用户行为(点击、触摸)后，才能`myAudio.play()`播放才可以执行，否则会报错。

### 3.2 pause()

暂停播放：

```JavaScript
myAudio.pause();
```

音频元素是没有`stop()`方法的，如果想要实现音频的`stop()`效果，可以先设置`currentTime`属性值为 0，然后再执行`pause()`方法。

### 3.3 canPlayType()

`canPlayType()`可以用来检测浏览器是否支持某种类型的音频文件，支持一个 MIME type 值作为参数。使用示意：

```JavaScript
if (myAudio.canPlayType('audio/mpeg')) {
  // 如果支持 mp3
}
```

该方法可以返回下面三个值中的某一个：

1.	`probably`
2.	`maybe`
3.	`""`（空字符串）

实际开发的时候，只要不是空字符串，都可以认为是支持的，因此，直接使用`if`弱匹配返回值即可。

### 3.4 load()

触发音频文件的加载。如果浏览器不支持`preload`属性，则此方法也不会有效果。

```JavaScript
myAudio.load();
```

## 四、加载事件

音频元素在加载的时候会触发一些相关事件，可以利用这些事件进行相关操作。但是需要注意的是，因为`preload`以及`autoplay`等属性的限制，这类加载事件在移动端，尤其 iOS Safari 并不总能触发。

音频事件触发的顺序如下：

`loadstart` → `durationchange` → `loadedmetadata` → `loadeddata` → `progress` → `canplay` → `canplaythrough`。

### 4.1 loadstart

`loadstart`事件表示加载过程已经开始，浏览器正在连接到媒体。

```JavaScript
myAudio.addEventListener("loadstart", function() {
  // 抓取文件
});
```

### 4.2 durationchange

如果想尽快知道音频文件的播放时长，则`durationchange`事件非常管用，因为音频文件默认`duration`初始值是`NaN`，当准确时长返回时候，会触发`durationchange`，此时就可以快速显示音频播放时间了。

通常实际开发时会使用`00:00`占位，`durationchange`事件触发后在替换为准确的总播放时间：

```JavaScript
myAudio.addEventListener("durationchange", function() {
  // 可以显示播放时长了哟
});
```

### 4.3 loadedmetadata

当第一个音频文件字节数据到达时，会触发`loadeddata`事件。虽然播放头已经就位，但还没有准备好播放。

```JavaScript
myAudio.addEventListener("loadeddata", function() {
  // 可以显示播放头
});
```

### 4.4 progress

`progress`事件在媒体文件仍然在下载中的时候触发，通常各种`loading`效果的显示就是在这个事件中。

```JavaScript
myAudio.addEventListener("progress", function() {
  // 可以让用户知道媒体文件正在下载
});
```

### 4.5 canplay

当媒体文件可以播放的时候会触发`canplay`事件。

在自定义音频播放器的时候，可以默认把一些按钮`disabled`禁用，等可以播放的时候再恢复为`enabled`，此时就可以使用`canplay`事件。

```JavaScript
myAudio.addEventListener("canplay", function() {
  // 音频可以播放了
});
```

### 4.6 canplaythrough

`canplaythrough`事件在音频文件可以从头播放到尾时候触发。这种情况包括音频文件已经从头到尾加载完毕了，或者浏览器认为一定可以按时下载，不会发生缓冲停止。

```JavaScript
myAudio.addEventListener("canplaythrough", function() {
  // 音频可以不发生缓冲从头播放到结束
});
```

### 4.7 加载中断事件

* `suspend` 即使文件尚未完全下载，也不再拉取媒体数据。
* `abort` 不是因为出错而导致的媒体数据下载中止。
* `error` 媒体下载过程中错误。例如突然无网络了，或者文件地址不对。
* `emptied` 媒体缓冲区已被清空，可能是由于错误或调用了`load()`方法重新加载。
* `stalled` 媒体数据意外地不再可用。

### 五、播放事件

Audio 元素在播放中也会触发一些相关事件。

### 5.1 timeupdate

每次`currentTime`属性值发生变化的时候会触发`timeupdate`事件。实际开发的时候，这个事件每 250 毫秒出发一次。这个事件可用来实时显示播放进度。

```JavaScript
myAudio.addEventListener("timeupdate", function() {
  // 更新与播放进度相关的内容
});
```

### 5.2 playing

音频文件在缺少媒体信息（如时长等）的时候，播放会被迫停止，如果之后在启动播放，会触发`playing`事件。

### 5.3 waiting

音频文件因为缺少媒体信息（如时长等）导致播放停止时会触发`waiting`事件。

### 5.4 play

`play`事件在`play()`方法生效，或者`autoplay`导致播放开始时候触发，此事件触发的播放状态一定是一个从暂停到播放。

### 5.5 pause

`pause`事件在`pause()`方法执行并生效后触发，此事件触发需要一个从播放到暂停的状态变化。

### 5.6 ended

当整个音频文件播放完毕的时候触发`ended`事件。

```JavaScript
myAudio.addEventListener("ended", function() {
  // 当音轨播放完毕时候做你想做的事情
});
```

### 5.7 volumechange

音量发生变化的时候会触发`volumechange`事件，包括静音行为。

### 5.8 ratechange

播放速率发生变化的时候会触发`ratechange`事件。

## 六、缓冲

媒体文件的播放进度可以使用`currentTime`和`duration`属性获取，但是有时候希望知道缓冲加载的进度，此时可以使用下面几个和缓冲相关属性和方法。

### 6.1 buffered 属性

此属性表示音频的哪些部分已被缓冲（提前下载），是一个称为 TimeRanges 的对象。

```JavaScript
myBufferedTimeRanges = myAudio.buffered;
```

### 6.2 seekable 属性

`seekable`属性表示是否可以直接跳到媒体的该部分，而不需要进一步缓冲。

```JavaScript
mySeekableTimeRanges = myAudio.seekable;
```

### 6.3 seeking 事件

当媒体资源正在请求时会触发`seeking`事件。

### 6.4 seeked 事件

当`seeking`属性变成`false`时候会触发`seeked`事件。

## 七、总结

本文展示的这些`<audio>`音频元素相关的属性和方法以及各种回调事件，对于`<video>`视频元素同样受用，基本上都是一模一样的，很多自动播放以及媒体自动加载策略也是一致的。

本文展示的这些 API 并不是全部。


