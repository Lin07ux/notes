搜索引擎都遵守互联网 robots 协议，可通过网站根目录下面`robots.txt`来搜索引擎的抓取进行限制。

### 示例

下面介绍一下 robots 的基本写法。

* 例1. 禁止所有搜索引擎访问网站的任何部分
    ```
    User-agent: *
    Disallow: /
    ```
　　
* 例2. 允许所有的 robot 访问 (或者也可以建一个空文件 “/robots.txt”)
    ```
    User-agent: *
    Allow:
    ```

* 例3. 禁止某个搜索引擎的访问(禁止 BaiDuSpider)
    ```
    User-agent: BaiDuSpider
    Disallow: /
    ```

* 例4. 允许某个搜索引擎的访问
    ```
    User-agent: Baiduspider
    allow: /
    ```

* 例5.禁止二个目录搜索引擎访问
    ```
    User-agent: *
    Disallow: /admin/
    Disallow: /install/
    ```

* 例6. 仅允许 Baiduspider 以及 Googlebot 访问
    ```
    User-agent: Baiduspider
    Allow: /
    User-agent: Googlebot
    Allow: /
    User-agent: *
    Disallow: /
    ```

* 例7. 禁止百度搜索引擎抓取你网站上的所有图片
    ```
    User-agent: Baiduspider
    Disallow: /*.jpg$
    Disallow: /*.jpeg$
    Disallow: /*.gif$
    Disallow: /*.png$
    Disallow: /*.bmp$
    ```

因为搜索引擎索引数据库的更新需要时间。所以，虽然蜘蛛已经停止访问您网站上的网页，但搜索引擎数据库中已经建立的网页索引信息，可能需要数月时间才会清除。

也就是说设置限制之后日志还会看见蜘蛛爬行，逐渐会降低抓取直到完全生效，这种问题会持续一段时间。如果您需要尽快屏蔽，访问以下帮助中心进行投诉，搜索引擎就会较快处理。

> 注：BaiDuSpider 等于百度蜘蛛、Googlebot 等于 google 蜘蛛、Sogou Spider 等于搜狗蜘蛛。

### 常见问题

*1、robots.txt 文件放在哪里?*

robots.txt 文件应该放置在网站根目录下。

举例来说，当 spider 访问一个网站（比如`http://www.abc.com`）时，首先会检查该网站中是否存在`http://www.abc.com/robots.txt`这个文件，如果 Spider 找到这个文件，它就会根据这个文件的内容，来确定它访问权限的范围。
	
*2、我在 robots.txt 中设置了禁止百度收录我网站的内容，为何还出现在百度搜索结果中？*

如果其他网站链接了您`robots.txt`文件中设置的禁止收录的网页，那么这些网页仍可能会出现在百度的搜索结果中，但您的网页上的内容不会被抓取、建入索引和显示，百度搜索结果中展示的仅是其他网站对您相关网页的描述。
	
*3、禁止搜索引擎跟踪网页的链接，而只对网页建索引*

如果您不想搜索引擎追踪此网页上的链接，且不传递链接的权重，请将此元标记置入网页的`<HEAD>`部分：`<meta name="robots" content="nofollow">`。

如果您不想百度追踪某一条特定链接，百度还支持更精确的控制，请将此标记直接写在某条链接上：`<a href="signin.php" rel="nofollow">sign in</a>`。

要允许其他搜索引擎跟踪，但仅防止百度跟踪您网页的链接，请将此元标记置入网页的`<HEAD>`部分：`<meta name="Baiduspider" content="nofollow">`。

*4、禁止搜索引擎在搜索结果中显示网页快照，而只对网页建索引*

要防止所有搜索引擎显示您网站的快照，请将此元标记置入网页的`<HEAD>`部分：`<meta name="robots" content="noarchive">`。

要允许其他搜索引擎显示快照，但仅防止百度显示，请使用以下标记：`<meta name="Baiduspider" content="noarchive">`。

> 注：此标记只是禁止百度显示该网页的快照，百度会继续为网页建索引，并在搜索结果中显示网页摘要。

*5、robots.txt文件的格式*

此文件包含一条或更多的记录，这些记录通过空行分开(以`CR,CR/NL`或`NL`作为结束符)，每条记录的格式如下：`"<field>:<optional space><value><optional space>"`。

在该文件中可以使用`#进行`注解，具体使用方法和 UNIX 中的惯例一样。

该文件中的记录通常以一行或多行`User-agent`开始，后面加上若干`Disallow`和`Allow`行，详细情况如下：

* `User-agent` 该项的值用于描述搜索引擎 robot 的名字。如果有多条`User-agent`记录说明有多个 robot 会受到限制。
		对该文件来说，至少要有一条`User-agent`记录。如果该项的值设为`*`，则对任何 robot 均有效。但在 robots.txt 文件中，`User-agent:*`这样的记录只能有一条。
		如果在该文件中，加入`User-agent: SomeBot`和若干`Disallow`、`Allow`行，那么名为"SomeBot"只受到`User-agent:SomeBot`后面的`Disallow`和`Allow`行的限制。

* `Disallow` 该项的值用于描述不希望被访问的一组 URL，这个值可以是一条完整的路径，也可以是路径的非空前缀，以`Disallow`项的值开头的 URL 不会被 robot 访问。
		例如`Disallow: /help`禁止 robot 访问`/help.html`、`/helpabc.html`、`/help/index.html`，而`Disallow: /help/`则允许 robot 访问`/help.html`、`/helpabc.html`，不能访问`/help/index.html`等。
		`Disallow: /`说明不允许 robot 访问该网站的所有 url，在该文件中，至少要有一条 Disallow 记录。
		如果 robots.txt 不存在或者为空文件，则对于所有的搜索引擎 robot，该网站都是开放的。

* `Allow` 该项的值用于描述希望被访问的一组 URL，与`Disallow`项相似。
		以`Allow`项的值开头的 URL 是允许 robot 访问的。
		例如`Allow: /hibaidu`允许 robot 访问`/hibaidu.htm`、`/hibaiducom.html`、`/hibaidu/com.html`。
		一个网站的所有 URL 默认是 Allow 的，所以 Allow 通常与 Disallow 搭配使用，实现允许访问一部分网页同时禁止访问其它所有 URL 的功能。

* `*`和`$`
    Baiduspider 支持使用通配符`*`和`$`来模糊匹配 url。其中，`$`匹配行结束符。`*`匹配0或多个任意字符。

> 注：注意区分不想被抓取或收录的目录的大小写，会对 robots 中所写的文件和不想被抓取和收录的目录做精确匹配。否则 robots 协议无法生效。
		

