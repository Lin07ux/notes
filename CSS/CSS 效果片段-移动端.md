### 移动端标签点击后变暗

在移动端使用 a,button,input,optgroup,select,textarea 标签的时候，点击后会出现一个"暗色的"背景，这时候我们需要在css加入如下代码即可禁用这个效果：

```css
a,button,input,optgroup,select,textarea{
    -webkit-tap-highlight-color: rgba(0,0,0,0);
}
```

### webkit 表单输入框 placeholder 的颜色值改变：

如果想要默认的颜色显示红色，代码如下：

`input::-webkit-input-placeholder { color: red; }`

如果想要用户点击变为蓝色，代码如下：

`input:focus::-webkit-input-placeholder { color: blue; }`

### 移动端 iOS 手机下清除输入框内阴影

`input, textarea { -webkit-appearance: none; }`

### 在 iOS 中 禁止长按链接与图片弹出菜单

`a, img { -webkit-touch-callout: none; }`



