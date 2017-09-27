### 一、快速切换文件

和 Sublime Text 中的文件跳转一样，当 DevTools 被打开的时候，按`Ctrl + P`（在 Mac 是`Cmd + P`），就能快速搜寻和打开你项目的文件。

![](http://7xkt52.com1.z0.glb.clouddn.com/221212kh1ry6vnkgsid2r2.gif)

### 二、在源代码中搜索

在页面已经加载的文件中搜寻一个特定的字符串，快捷键是`Ctrl + Shift + F` (`Cmd + Opt + F`)，这种搜寻方式还支持正则表达式。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221214kcfezcpe4eyjfdpe.gif)

### 三、快速跳转到指定行

在 Sources 标签中打开一个文件之后，按`Ctrl + G`(`Cmd + L`)，然后输入行号，DevTools 就会跳转到文件中的任意一行。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221216f9mficdiipoxaoz7.gif)

另外一种方式是按`Ctrl + O`，输入`:`和行数，而不用去寻找一个文件。 

### 四、在控制台选择元素

DevTools 控制台支持一些变量和函数来选择 DOM 元素：

* `$()` - `document.querySelector()`的简写，返回第一个和 css 选择器匹配的元素。例如`$('div')`返回这个页面中第一个 div 元素；

* `$$()` – `document.querySelectorAll()`的简写，返回一个和 css 选择器匹配的元素数组；

* `$0-$4` – 依次返回五个最近你在元素面板选择过的DOM元素的历史记录，`$0`是最新的记录，以此类推。

![](http://7xkt52.com1.z0.glb.clouddn.com/221217n2n7b1zlbuorb2j5.gif)

### 五、使用多个插入符进行选择

当编辑一个文件的时候，可以按住`Ctrl`（`Cmd`），在要编辑的地方点击鼠标，可以设置多个插入符，这样可以一次在多个地方编辑。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221224cbgerrr8famg8mzx.gif)

### 六、保存记录

勾选在 Console 标签下的保存记录选项，你可以使 DevTools 的 console 继续保存记录而不会在每个页面加载之后清除记录。当你想要研究在页面还没加载完之前出现的 bug 时，这会是一个很方便的方法。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221226ivgzchc1nvca2kzd.gif)

### 七、代码格式化(美化)

Chrome’s Developer Tools 有内建的美化代码，可以返回一段最小化且格式易读的代码。Pretty Print 的按钮在 Sources 标签的左下角。

![](http://7xkt52.com1.z0.glb.clouddn.com/221237ua5ppvvny6onqyq8.gif)

### 八、设备传感仿真

设备模式的另一个很酷的功能是模拟移动设备的传感器，例如触摸屏幕和加速计，甚至可以设置地理位置。这个功能位于元素标签的底部，点击`show drawer`按钮，就可看见`Emulation`标签，选择其中的`Sensors`。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221239tm6fffomh8cz6nuh.gif)

### 九、颜色选择器

当在样式编辑中选择了一个颜色属性时，可以点击颜色预览，就会弹出一个颜色选择器。当选择器开启时，如果停留在页面，鼠标指针会变成一个放大镜，可以选择像素精度的颜色。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221255oa4zsaeeno3tddsa.gif)

### 十、强制改变元素状态

DevTools 有一个可以模拟 CSS 状态的功能，例如元素的`hover`和`focus`，可以很容易的改变元素样式。在 CSS 编辑器中可以利用这个功能。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221300u9d6fr92fizz5gg9.gif)

### 十一、可视化的 DOM 阴影

Web 浏览器在构建如文本框、按钮和输入框一类元素时，其它基本元素的视图是隐藏的。不过可以在`Settings -> General`中切换成`Show user agent shadow DOM`，这样就会在元素标签页中显示被隐藏的代码。甚至还能单独设计他们的样式。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221313c5o6pp5plob3e6jo.gif)

### 十二、选择下一个匹配项

当在`Sources`标签下编辑文件时，按下`Ctrl + D` (`Cmd + D`) ，当前选中的单词的下一个匹配也会被选中，可以同时对它们进行编辑。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221316y37tzzkkxzrad4rk.gif)

### 十三、改变颜色格式

在颜色预览功能使用快捷键S`hift + 点击`，可以在 rgba、hsl 和 hexadecimal 来回切换颜色的格式。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/221319gxhaq7h6ux7eh01b.gif)

### 十四、通过 workspaces 来编辑本地文件

Workspaces 是Chrome DevTools 的一个强大功能，这使 DevTools 变成了一个真正的 IDE。

Workspaces 会将 Sources 选项卡中的文件和本地项目中的文件进行匹配，所以你可以直接编辑和保存，而不必复制/粘贴外部改变的文件到编辑器。

为了配置 Workspaces，只需打开 Sources 选项，然后右击左边面板的任何一个地方，选择`Add Folder To Worskpace`，或者只是把你的整个工程文件夹拖放入 Developer Tool。现在，无论在哪一个文件夹，被选中的文件夹，包括其子目录和所有文件都可以被编辑。为了让 Workspaces 更高效，你可以将页面中用到的文件映射到相应的文件夹，允许在线编辑和简单的保存。

了解更多关于 Workspaces 的使用，戳这里：[Workspaces](https://developer.chrome.com/devtools/docs/workspaces)。

### 转摘

原文地址：[15 个必须知道的 chrome 开发工具技巧](https://linux.cn/article-6343-1.html)


