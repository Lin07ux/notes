在 Chrome 中手动添加非官方扩展时，会出现重启浏览器以后无法启用扩展的情况。如图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1486534798807.png)

最近群友提供了一个方法，特此弄一个中文教程；

> 原文地址：[https://hencolle.com/2016/10/16/baidu_exporter/](https://hencolle.com/2016/10/16/baidu_exporter/)

**注意：该方法只适用于 通过 crx 文件安装的扩展（将crx文件拖入chrome扩展程序界面）**

1. 首先下载一个描述文件：
    [Github 地址](https://gist.github.com/Explorare/be3dd598289252698cd37bca04abd0fe#file-com-google-chrome-mobileconfig)
    
    [百度网盘](https://pan.baidu.com/s/1qYERRac)  密码：`mt25`

2. 打开 chrome 的扩展程序界面，复制扩展的 ID。
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1486534971110.png)

3.	用文本编辑器打开下载好的`com.google.Chrome.mobileconfig`找到如图所示位置：
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1486535009246.png)

    这里的三个`<string></string>`中分别对应单个扩展的 ID，如果只需要一个的话，可以吧多余的两个删掉，将其中的一个标签中的值替换为第二步复制过来的 ID。编辑完后保存。如图：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1486535052632.png)

4.	双击打开第三步编辑好的描述文件，按照弹出的提示点击`继续` - `安装`（输入电脑密码），安装结束后重启浏览器，就可以勾选这个扩展了，并且以后重启浏览器也不会有停用的提示了！

> 转摘：[将非官方扩展程序加入 Chrome 的白名单](http://xclient.info/a/1ddd2a3a-d34b-b568-c0d0-c31a95f0b309.html)

