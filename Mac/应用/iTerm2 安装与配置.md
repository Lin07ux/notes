### 下载

从 [iTerm2 官网](http://www.iterm2.com/) 中下载 ITerm2，并移动到`Application`文件夹中即可。

### 配色方案

从 [iTerm Themes 官网](http://iterm2colorschemes.com/) 中查看喜欢的配色主题并从 [GitHub](https://github.com/mbadolato/iTerm2-Color-Schemes/tree/master/schemes) 仓库中下载对应的配色文件。

> 需要下载原始文件(raw)，否则会出现无法导入的情况。

下载成功之后，通过如下方式导入：

1. 通过`iTerm2->Preferences->Profiles->Color`步骤打开配色设置界面。
2. 通过右下角的`Color Presets->import`打开导入窗口，选择下载好的主题。
3. 重启 iTerm2 即可。

> 个人推荐使用的是`Solarized Dark Higher Contrast`。

### 字体

默认情况下，iTerm2 使用的字体可能显示效果并不好，可以考虑安装 Powerline 字体。

从[Powerline Fonts](https://github.com/powerline/fonts)仓库中下载项目之后，使用项目中的脚本安装即可：

```shell
# clone
git clone https://github.com/powerline/fonts.git --depth=1
# install
cd fonts
./install.sh
# clean-up a bit
cd ..
rm -rf fonts
```

然后通过`Preferences -> Profiles -> Text -> Change Font`来设置字体和大小。

> 个人使用的是`Courier 10 Pitch`字体，大小为 15pt。

### 窗口大小和透明度

默认情况下，打开 iTerm2 时，窗口比较小，可以设置打开时默认的窗口大小。通过`Preferences -> Profiles -> Window`打开窗口设置界面。

调整 Transparency 即可调整 iTerm2 窗口的透明度。

设置 Columns 和 Rows 的数值大小即可调整打开 iTerm2 时窗口的大小。

## 快捷键

### 光标按照单词快速移动设置

打开 iTerm2 的`settings -> Profiles -> Keys`，在这里重新定义`⌥←`和`⌥→`的快捷键即可。

设置`⌥←`的 Action 为“Send Escape Sequence”，然后输入“Esc+”的值为`b`：

![](http://cnd.qiniu.lin07ux.cn/20171108162921470.jpeg)

设置`⌥→`的 Action 为“Send Escape Sequence”，然后输入“Esc+”的值为`f`：

![](http://cnd.qiniu.lin07ux.cn/20171108163005385.png)


