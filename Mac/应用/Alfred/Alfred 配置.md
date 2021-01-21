> 转摘：[从零开始学习 Alfred：基础功能及设置](https://sspai.com/post/32979)

## 一、General

* `Startup` 勾选后，系统启动时会自动启动 Alfred
* `Alfred Hotkey` 设置 Alfred 窗口唤起的快捷键
* `Where are you` 设置所在的区域，设置之后 Alfred 在使用网站搜索功能时会打开搜索网站对应的国家的网站版本。

## 二、Features

这里是免费版的重点，Alfred 里所有的搜索功能都在这里设置。

### 2.1 Default Results

这里可以设置 Alfred 可以搜索什么结果，以及在哪里进行搜索。

* `Applications` 搜索 Apps 的匹配条件；
* `Essentials` 可以设置搜索「应用程序」、「联系人」、「设置」、「Safari 书签」；
* `Extras` 查询「文件夹」、「文本文件」、「压缩文件」、「图片」、「AppleScript」等其它文件。
* `Search all file types` 搜索所有文件类型。如果全部选中的话不但影响查询速度，还容易混淆查询结果。所以 Alfred 建议通过`Open + 关键字`或者`Space(空格键)`来查询文件或者文件夹；
* `Search Scope` 设置 Alfred 查询时会搜索的文件夹，在这里可以添加和删除文件夹；
* `Fallbacks` 设置如果没有查到结果时，使用什么搜索引擎来查询。

> 	**排除 Library 文件夹**：为了保证搜索结果的准确性和相关性，建议排除应用程序文件存放位置`~Library`。

### 2.2 File Search

#### 2.2.1 Search

这里用来设置文件搜索相关的基本操作：

* `Quick Search` 快速搜索，勾选该选项后，可以使用`'`(单引号)或者`Space`(空格键)快速启用打开文件或者文件夹，功能类似于使用`Open + 关键字`；
* `Opening Files` 定义打开打开文件、文件夹的关键词；
* `Revealing Files` 定义查找文件、文件夹的关键词；
* `Inside Files` 定义查找文件内容含有指定文字的关键词；
* `File Tags` 查询含有指定标签的文件、文件夹；
* `Dont't Show` 选择查询结果中不出现的内容；
* `Result Limit` 自定义显示结果个数。

![](http://cnd.qiniu.lin07ux.cn/uPic/tTfUVj-20210120180232.jpg)

#### 2.2.2 Navigation

在这里可以设置 Alfred 中搜索文件、文件夹时的导航操作。

Alfred 中默认具有如下的行为：

* 使用`/`直接定位到根目录；
* 使用`~`直接定位到当前用户的 Home 目录；
* 使用`Command + ↓`前往下一层文件夹；
* 使用`Command + ↑`前往上一层文件夹；
* 使用`Enter`回车键在 Alfred 中打开所选的下一级文件夹。

使用 Navigation 中的配置，可以修改这些行为：

* `Filtering` 设置是否开启模糊匹配。使用模糊匹配可以搜索到更多的结果。
* `Shortcuts` 可以将`←`和`→`定位为前往上一层和下一层文件夹的快捷键，将`Enter`回车键定义为在 Finder 中打开所选中的文件夹的快捷键。
* `Previous Path` 设置快速导航到之前在 Alfred 中访问过的路径的快捷键和关键词。

#### 2.2.3 Buffer

通过 Buffer，可以将查找到的文件、文件夹加入到缓存列表中，然后再对选择的结果进行批量处理了。

Buffer 选项卡中的设置很简单，主要就是是否启用缓存功能和缓存功能的几个快捷键，并且可以设置使用完后是否清空缓存等。

`Enable temporary file buffer`这个选项用来决定是否启用文件缓存。启用之后，就可以使用如下几个快捷键来操作文件、文件夹的缓存了：

* `Option + ↑`将选中的文件/文件夹加入到缓存列表，或者将已加入缓存列表的文件/文件夹从缓存列表中移除。加入缓存之后，就会在 Alfred 的搜索界面上出现选中文件的小图标了。
* `Option + ↓`和`Option + ↑`类似，也可以将选中的文件加入到缓存列表中，但是它不可以将已加入缓存列表的文件/文件夹移除。而且，使用这个快捷键将文件/文件夹加入缓存列表之后，会自动选中下一个文件/文件夹。
    ![](http://cnd.qiniu.lin07ux.cn/uPic/tzR7d4-20210120200128.jpg)
    
* `Option + →`调出控制面板，来对缓存列表中的文件/文件夹进行批量处理，比如：打开、发邮件、拷贝、移动、删除等。
    ![](http://cnd.qiniu.lin07ux.cn/uPic/LWYIWw-20210120200700.jpg)

* `Option + ←`可以移除缓存列表中的最后一项（也就是最近添加的一项）。
* `Option + Delete`可以将缓存列表清空。
* `Option + Click`在按住`Option`键时，使用鼠标点击特定的缓存项可以将其从缓存列表中移除。

启用文件缓存之后，还有三个选项可以用来对其进行增强或微调：

* `Buffer cleaning`
    - `Clear after actioning items in the buffer`对缓存中的文件/文件夹处理之后即清除缓存。
    - `Clear if buffer isn't used for 5 minutes`选中之后 5 分钟内为使用过则清除缓存。
* `Compatibility`
    - `Use ⇧⌥ as modifier key`将原本的快捷键修饰符从`Option`改为`Shift + Option`。

#### 2.2.4 Advanced

这里是一些高级的设置，但只需要保持默认的即可。

* `Copy Path`复制路径，选中该选项后，如果使用了将目录拷贝至粘贴板的功能后会在目录前后加上单引号。
* `AppleScript`设置当选中文件是 AppleScript 脚本的时候是否是直接执行。启用之后，可以使用`Command + O`来打开 AppleScript 文件。
* `Performance`对存在于扩展存储驱动中的文件使用文件 icon，这样可以避免对扩展存储器文件的误操作。
* `Sorting`使用打开文件/文件夹的时间戳排序。
* `Home Folder`设置用户目录的快捷符号（默认为`~`）。

### 2.3 Actions

这个配置的主要功能是在查询到文件夹或者文件后选择快捷键来显示操作界面。可以当做是对前面的 File Search 功能的增强。

`General`中有如下的几个选项：

* `Show Actions`可以设置调出动作的快捷键，默认设置为`fn`和`Control`。
* `Selection Hotkey`可以设置快捷键来调出 Alfred 的动作列表。设置快捷键之后，还可以在 Finder 中选中文件夹或者文件后使用快捷键来快速打开相同的操作界面。
* `Action Ordering`默认情况下，Alfred 会根据对动作的使用频次排列动作列表，可以改成按照最近使用的动作进行排序。

`File Actions`选项中则可以设置文件能够执行的动作。 

### 2.4 Web Search

这里是网页搜索的一些设置，可以使用 Alfred 已经添加的搜索功能，也可以自定义一些搜索。

在设置自定义查询界面中，主要设置有：

* `Search URL` 网站查询的 URL，每个网站的查询 URL 可以先通过网站查询功能，然后查看浏览器的地址栏就能知道了。当然查询内容使用`{query}`变量来代替。
* `Title` 标题，这个是设置在查询时 Alfred 查询主界面显示的提示文字。
* `Keyword` 查询关键字，尽量使用简短容易辨识的文字。
* `Validation` 有效性，这个是用来测试设置是否有效的。

另外每个查询设置都能设置相应网站的图标，只要将网站图标拖到设置的位置即可。在新版本的 Alfred 中，还增加了`Use HTTPS for default searches if possible`选项，以强化安全性。

![](http://cnd.qiniu.lin07ux.cn/uPic/Wg94PX-20210120205717.jpg)

### 2.5 Calculator

计算器主要有两个功能：一个就是直接输入简单的加减运算，一个就是输入`=`来输入复杂的计算，支持许多高级的数学函数。

![](http://cnd.qiniu.lin07ux.cn/uPic/Vjxuzt-20210120205828.jpg)

![](http://cnd.qiniu.lin07ux.cn/uPic/Kyl8vF-20210120205845.jpg)

### 2.6 Dictionary

字典功能其实使用的是 Mac 系统自带的字典，可以设置使用的字典和查询关键字，输入`di + 关键字`来查询中英字典：

![](http://cnd.qiniu.lin07ux.cn/uPic/SD4yVi-20210120205928.jpg)

### 2.7 Clipboard

#### 2.7.1 History

基于隐私的考虑，Alfred 是默认关闭「剪切板历史」功能的。对于普通用户来说，Alfred 的剪贴板功能已经完全够用了，无需重复购买 Paste 等剪贴板管理工具。

* `Clipboard History`这里可以设置 Alfred 是否记录文本、图片、文件列表的拷贝记录，以及记录多久。
* `Viewer Hotkey` 可以设置查看「剪切板历史」的热键（如双击 Control），方便调出。
* `Snippets` 设置粘贴片段的展示位置。
* `Universal` 设置是否忽略其他的苹果设备共享的拷贝数据。

#### 2.7.2 Merging

这是一个神奇的功能：当复制了一段文本后，再选中另外一段文本后，通过使用`Command ＋ C + C`(按住 Command 之后双击 C 键)，可以将当前选中的文本追加到第一次复制的文本后面，并且可以设置是使用空格、回车来分割不同的片段。

#### 2.7.3 Advanced

这里主要设置自动粘贴当前选中的记录和设置复制文本内容的最大字节。

### 2.5 Terminal

Alfred 中可以直接输入 Shell 命令，默认情况下，Alfred 会启动系统的 Terminal 终端来执行命令，但是也可以修改启动的终端。

比如，下面的配置会将输入命令的关键字修改为`;`，并将终端修改为 iTerm2：

* `Prefix`修改为`;`
* `Application`修改为`Custom`，然后将输入框中内容改成如下代码：

    ```shell
    on alfred_script(q)
        tell application ":Applications:iTerm.app"
            activate
            try  
                select first window
            on error
                create window with default profile
                select first window
            end try
            tell current session of the first window
                write text q
            end tell
        end tell
    end alfred_script
    ```
    
接下来就可以使用如下的方式自动打开 iTerm2 执行命令了：

![](http://cnd.qiniu.lin07ux.cn/markdown/alfred-terminal.png)


> 转摘：[怎么让Alfred支持Iterm2？](https://www.zhihu.com/question/36763287)


