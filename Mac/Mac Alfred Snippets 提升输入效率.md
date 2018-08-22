Alfred 3.0 中增加的文字快速拓展功能，可以帮我们把一些日常经常使用的信息，例如个人邮箱、地址和联系方式等保存成 Snippets，之后每次只需要打几个简单的字符就能快速输入完整内容。

> Snippets 为 Alfred 的付费功能，需购买 Powerpack 后才能使用。

> 转摘：[真正提升你的输入效率，从用好 Alfred 的这个功能开始：Alfred Snippets](https://sspai.com/post/46034)

### 1、添加 Snippets

打开 Alfred 的设置菜单，找到 Features 里面的 Snippets，你可以看到下图这个设置面板：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1534906519747.png)

想要创建 Snippets，首先要建立一个 Collection（集合）。点击左侧 Collection 底部的 「+」号，输入集合的名字。

在设置中，你可以选择是否为这个 Collection 设置一个前缀或者后缀，这个功能的主要目的是为了方便区分，当你在使用时，通过输入前缀或者后缀可以快速显示某一个集合内的所有 Snippets。我们在这里给这个名为「Personal」的 Collection 添加一个「!」作为前缀。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1534906647235.png)

添加 Collection 后，就可以来创建你的第一个 Snippets 了。点击右侧底部的「+」，输入 Snippets 的名字和关键词，然后在下方输入你希望拓展的内容。在这里以添加个人邮箱为例，在上面的 Keyword 里填入「GM」作为关键词，然后下方输入 myemail@gmail.com，点击 Save 来保存，这样我们就创建了一条 Snippets。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1534906686579.png)


### 2、设置

回到 Snippets 设置菜单，在右上角你可以找到 Automatically expand snippets by keyword，打开这个选项后你才能在 macOS 中直接输入关键词来进行拓展（第一次打开时需要在「系统设置 - 隐私 - 辅助功能」中开启服务），否则就需要每次手动进行粘贴。

如果你希望在一些应用中关闭 Snippets 拓展，可以选择 Auto Expansion Options，打开 Finder 里的应用程序，将希望关闭 Snippets 的应用拖到列表里即可。

### 3、使用

设置完成后，你就可以在 macOS 里输入关键词「!GM」使用刚才创建 Snippets 来快速输入邮箱地址了。值得一提的是，Alfred 的快速拓展功能支持中文输入法下使用，这样你就不用来回切输入法了。

### 4、高级

Snippets 不仅仅可以输入固定的内容，还能通过一些特殊的占位符来得到动态的输出。比如可以得到相关的日期、时间、某个时间之后的日期时间，还能插入粘贴板内容。

使用这些高级功能时，需要在创建 Snippets 时，点击创建面板左下角的`{}`，然后选择相关的选项，这样就会在内容区域插入相关的占位符了。

还可以使用多个剪贴板内容，以便我们多次粘贴，然后一次性输出格式化的数据。使用这个功能前，需要先在 Alfred -- Feature -- Clipboard 中打开 Clipboard History 中的 Keep Plain Text。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1534907251517.png)



