### 特定字符特定字体
有时候可能我们需要将某个或某些特定的字符使用不同的字体来显示，而其他的字符则正常显示。比如下图所示的，将`&`符号使用不同的字体来显示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476189873995.png)

我们可以通过修改 HTML 结构将特定的字体进行单独的处理，添加不同的 CSS 样式从而实现需求。但是更多情况下，我们可能并不能修改 HTML 结构(比如 CMS 系统中)。这就需要用到下面的方法了。

我们通常会在`font-family`声明中指定多种字体（字体栈），这样当我们的首要选择不可用的时候，浏览器可以降级使用其它字体，也符合我们的设计。起始这种作用是**基于每个字符的**。**如果字体是可用的，但只包含几个字符，该字体将会应用于它有的这些字符；然后对于其它字符，浏览器将会降级使用其他字体**。这适用于本地字体和通过 @font-face 规则嵌入的字体。

利用这种特性，就有了一个简单的方法来给特定符号添加字体样式了：创建一个只包含我们想要的那个字符的 web 字体，通过`@font-face`调用它，然后把它放在你字体栈中的第一个位置：

```css
@font-face {
    font-family: Ampersand;
    src: url("fonts/ampersand.woff");
}
h1 {
    font-family: Ampersand, Helvetica, sans-serif;
}
```

这种方法还有改进的空间。创建一个字体文件不仅非常麻烦，还增加了额外的 HTTP 请求，更别提潜在的法律问题，如果字体要禁用子集呢？如果我们是想用系统内置字体中的一种给特定符号添加样式呢？

首先，对于使用本地字体，可以通过`@font-face`规则中的`src`描述符调用`local()`函数来实现。`local()`函数接受一个字体名称，来调用本地的字体。

```css
@font-face {
    font-family: Ampersand;
    src: local('Baskerville'),
         local('Goudy Old Style'),
         local('Garamond'),
         local('Palatino');
}
```

不过，如果仅仅这样操作，会使其他字符也被应用了这些字体：因为这些字体包含非常多的字符。我们需要一个标识来限定使用这些字体中的某个或某些特定字符，这个描述符是存在的，它的名字是`unicode-range`。

`unicode-range`描述符只在`@font-face`规则中可用（也被称为术语描述符，它不是 CSS 属性），限制子集中对字符的使用。它在本地和远程字体中都是有效的。有一些浏览器甚至智能到，只要这些符号在页面中没有使用到，它就不会下载远程字体！**它使用的是 unicode 代码点，不是转义字符**。因此，在使用它之前，你需要找到你想要的字符对应的十六进制编码值。

拿到对应的十六进制编码值，可以在它们前面加上`U+`，这样就已经指定了一个字符了！下面是`&`用例的声明：

```css
unicode-range: U+26;
```

如果你想要指定一个范围的字符，你还是只需要一个`U+`即可，像：`U+400-4FF`。事实上，对于这种范围，你可以使用通配符把它指定为`U+4??`。多个字符或多个范围也是可以的，用逗号分隔，如`U+26, U+4, U+2665-2670`。在这里，我们只需要一个字符就 OK。所以我们的代码如下：

```css
@font-face {
    font-family: Ampersand;
    src: local('Baskerville'),
         local('Goudy Old Style'),
         local('Palatino'),
         local('Book Antiqua');
         unicode-range: U+26;
}
h1 {
    font-family: Ampersand, Helvetica, sans-serif;
}
```

效果类似如下：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476191000485.png)

可以看到，`&`符号应用了一个不同的字体！不过，我们也仅能给其设置不同的字体，而不能修改其他的样式。

如果我们需要使用字体的不能效果，就需要使用我们想要的那个字体的`style/weight`的`PostScript`名称。比如，下面是使用斜体样式的字体：

```css
@font-face {
    font-family: Ampersand;
    src: local('Baskerville-Italic'),
         local('GoudyOldStyleT-Italic'),
         local('Palatino-Italic'),
         local('BookAntiqua-Italic');
         unicode-range: U+26;
}
h1 {
    font-family: Ampersand, Helvetica, sans-serif;
}
```

其效果就是一开始那个图片上的样式了。


### 连体字符
和人一样，不是所有的字形放在一起的时候都可以显得很自然。例如，对于大多数衬线字体的 f 和 i 而言。字母 i 上边的小点经常和 f 中的横线重叠，使得它们的组合看起来非常笨拙。

为了解决这个问题，类型设计师经常会在他们的字体中添加额外的字符，称为连字符。这些都是单独设计的字形的二联体或三联体，用于排版方案中当对应的字符彼此相邻时。例如，下图中的一些常见的连体字母，它们看起来比之前对应字符单独放在一起的时候好了很多。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476191257134.png)

还有一些所谓的自由连体字“discretionary ligatures”：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476191340028.png)

但是，浏览器从来不会默认使用自由连体字（尽管这是正确的），并往往不会使用常见的连字体（这是一个 bug）。事实上，直到最近，显式使用任何连写字的唯一方法就是使用它的等效 Unicode 字符——如，`&#xfb01;`表示 fi 连写。这种方法比解决问题带来了更大的麻烦：

* 很明显，它使得这些标签很难被阅读，也很难编写（能知道`de&#xfb01;ne`是什么意思纯属运气）。
* 如果当前字体不包含这个连写字符，结果将是乱七八糟的（如下图所示）。
* 不是每个连写字都有一个等同的、标准的 Unicode 字符。例如， ct 连写字不对应任何的 Unicode 字符，所有包括它的字体都需要把它放置在 Unicode PUA（私人使用区域）块中。
* 它会打破文本的可访问性，如复制/粘贴，搜索，和语音转换。很多应用都可以非常智能地处理这些，都不是所有都可以。它甚至可以打破一些浏览器的搜索。

解决方案是：使用 CSS3 中的`font-variant`相关熟悉。

在 CSS3 的中，`font-variant`可以被转换成`shorthand`，包含了很多新的`longhand`属性。其中之一是`font-variant-ligatures`，特别为连写的开启和关闭而设计的。要打开所有可能的连写字，你需要使用三个标识符：

```css
font-variant-ligatures: common-ligatures
                        discretionary-ligatures
                        historical-ligatures;
```

该属性是可继承的。你可能会发现自由连体字“discretionary ligatures”妨碍可读性，想要把它们关掉。在这种情况下，你可能希望只打开常用的连写字：

```css
font-variant-ligatures: common-ligatures;
```

你甚至可以明确地把其它两种连写字关闭：

```css
font-variant-ligatures: common-ligatures
                        no-discretionary-ligatures
                        no-historical-ligatures;
```

`font-variant-ligatures`还接受`none`值，就是完全把连写字关闭。不要使用`none`值，除非你知道自己设置的是什么。要把`font-variant-ligatures`重置为初始值，你需要使用`normal`，而不是`none`。


### 断字
在排版的时候，经常会用到文字两端对齐，一般都会用到`text-align: justify;`样式，但是这种样式会为了要对齐，去调整文本间距产生的“空白流”。这不仅让文本看起来不美观，对可读性也有影响：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476191833453.png)

在印刷中，对齐一直是和连字符走在一起的。因为连字符允许单词被分解成音节表示，需要用于调整的空白非常少，这样文本看起来也更自然。

直到最近，Web上出现了用连字符连接文字的方法，不过都是比问题本身更糟糕的解决方案。常用的方法包括在服务器端编写代码，JavaScript，在线生成器，或甚至我们自己花费很多耐心手动为文本添加连字符(`&shy;`)，这样浏览器才知道哪些单词需要断开。

在 CSS3 中，我们可以通过新属性`hyphens`来解决这个问题。它接受三个值：`none`，`manual`和`auto`。初始值是`manual`，以匹配现有的行为：我们可以用软连字符手动断字。很明显，`hyphens: none;`将禁用此行为。但是暂不谈前面两个，真正神奇的效果是通过这行非常简单的 CSS 代码来完成的：

```css
hyphens: auto;
```

这就是需要的所有东西了。你可以在下图中看到效果：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476191977682.png)

> 当然，为了让它能生效，你需要通过 HTML 的 lang 属性声明一种语言，虽然这是你在编写 HTML 时都必须声明的。

如果你想要对断字有更精细的控制（例如，简短的介绍文字），你可以使用一些软连接符（`&shy;`）来帮助浏览器搞定。连字符属性会优先显示它们，接着才查找还有什么需要断开的地方。

> TRIVIA自动换行的工作原理
> 像计算机科学中的很多东西，自动换行听起来非常简单而且直接，但是实际上并不是。要完成它有很多算法，但是最流行的是贪心算法和 knuth-Pass 算法。贪心算法是通过每次分析一行来实现的，用尽可能多的单词（或音节，如果使用断字的话）来填充，直到遇到第一个不适合单词/音节，它就会移动到下一行。
> knuth-Plass 算法，根据开发这个算法的工程师命名，要复杂得多。它的工作原理是，会将整个文本考虑在内，并产生更美观的效果，但是计算的过程也相当慢。
> 大多数桌面端的文本处理应用程序都使用 knuth-Plass 算法。但是浏览器目前是使用 Greedy，因为性能原因，所以它们的对齐结果看起来就不那么美观了。
> CSS 断字的降级是非常优雅的。如果`hyphens`属性不被支持的话，你得到的就是像下图那样的文本对齐效果。虽然阅读起来并不美观愉快，但是能看到文本也已经是非常棒的了。


### 转摘
1. [CSS秘密花园： 花式的&符号](http://www.w3cplus.com/css3/css-secrets/fancy-ampersands.html) 或 [CSS秘密花园： 花式的&符号](http://www.tuicool.com/articles/bQrQ3q)
2. [CSS秘密花园： 连体字母](http://www.w3cplus.com/css3/css-secrets/ligatures.html) 或 [CSS秘密花园： 连体字母](http://www.tuicool.com/articles/3Qreqie)
3. [CSS秘密花园： 断字](http://www.w3cplus.com/css3/css-secrets/hyphenation.html) 或 [CSS秘密花园： 断字](http://www.tuicool.com/articles/yemqQv)

