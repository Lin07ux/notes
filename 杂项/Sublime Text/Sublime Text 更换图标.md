Sublime Text 可以使用自己的图标来替换默认的 icon。不过，如果只是简单的直接替换源程序文件中的`sublime text.icns`文件，并不能更换其快捷方式上的图标。可以使用下面的方式来替换。

参考：[Changing sublime text 3 icon in dock on Yosemite](http://apple.stackexchange.com/questions/153176/changing-sublime-text-3-icon-in-dock-on-yosemite)

1. 先删除 Dock 上的 Sublime text 的图标。
2. 打开两个 Finder 窗口：一个是 Application 窗口，找到 Sublime Text 程序；一个是你的新 icon 图片文件所在的文件夹窗口。
3. 选中 Sublime Text，然后按`Cmd + i`打开 App 信息简介窗口。
4. 拖放新的图标到 Sublime Text 的 App 信息简介窗口中，左上角的那个图标上(最顶部，App 名称前面的那个图标)。然后你就会看到信息简介窗口上的两个图标都被更换了。
5. 此时重新打开 Sublime Text 并固定快捷方式在 Dock 中，Dock 中显示的就是新的图标了。



