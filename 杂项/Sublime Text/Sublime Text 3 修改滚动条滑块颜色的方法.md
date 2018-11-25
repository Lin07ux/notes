转摘：[修改Sublime Text编辑器滚动条滑块颜色的方法](http://www.vaikan.com/change-sublime-text-scrollbar-color/)

Sublime Text 的滑块颜色并不是只通过修改颜色就可以解决的，它的滑块实际上是用图片表现的，也就是说，你不仅要修改颜色，还要换图片。

所以，解决方法就是创建两个新的滚动条滑块图片(一个横向的，一个竖向的)，图片颜色要明亮些，然后，按照下面的步骤一步步设置。

1. 找到你的 Sublime Text 的“User”目录(`Packages/User`)。这个“Packages”目录你可以通过 Sublime Text 上的菜单“Preferences>Browse Packages”打开，就可以看到“User”目录。（在 Linux 系统里，这个目录通常位于`/home/username/.config/sublime-text-3/Packages/User`目录。）

2. 在“User”目录下，创建一个叫`Theme – Default`的目录。我们将在这个目录里创建一个配置文件，来覆盖掉缺省主题皮肤的某些属性。

3. 在`Theme – Default`目录下创建一个叫做`Default.sublime-theme`的文件，在文件里添加如下内容：

```
[
    // More visible scrollbar
    {
        "class": "puck_control",
        "layer0.texture": "User/Theme - Default/vertical_white_scrollbar.png",
        // Adjust RGB color. Optional: comment the following line (or set 255,255,255) to not modify image color
        "layer0.tint": [200, 170, 250]
    },
    {
        "class": "puck_control",
        "attributes": ["horizontal"],
        "layer0.texture": "User/Theme - Default/horizontal_white_scrollbar.png"
    }
]
```

4. 在`Theme – Default`目录里，放入我们创建的两个新的、颜色更明亮的滑块图片，并命名如下：`vertical_white_scrollbar.png`和`horizontal_white_scrollbar.png`。

5. 重启 Sublime Text。


> 你还可以修改配置里的 RGB 颜色值来获得不同的润色，这种修改不需要重启 Sublime Text。


