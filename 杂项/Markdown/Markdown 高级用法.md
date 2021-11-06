### 1. 字符

  字符          |   描述   | 实体名称 | 实体编号
:--------------|:-------|:---------|:--------
<code> </code> | 空格    | `&nbsp;` | `&#160;`
 `<`           | 小于号  | `&lt;`   | `&#60;`
 `>`           | 大于号  | `&gt;`   | `&#62;`
 `&`           | 与号    | `&amp;`  | `&#38;`
 `"`           | 双引号  | `&quot;` | `&#34;`
 `'`           | 单引号  | `&apos;` | `&#39`;
 `|`           | 竖线    |          | `&#124;`

由于表格是用`|`符号来分隔的，所以如果在单元格中需要输入这个符号，就要用实体符号`&#124;`替代。

> 更多实体字符可以参考：[HTML特殊字符编码对照表](https://www.jb51.net/onlineread/htmlchar.htm)

### 2. 标记

Markdown 文档也支持 HTML 标记，常用的标记有：

* `<kdb>` 键盘符号，如：<kbd>Ctrl + Alt + Del</kbd>
* `<sup>` 上标，展示的更靠上且更小，如：x<sup>y</sup>
* `<sub>` 下标，展示的更靠下且更小，如：a<sub>1<sub>

### 3. 图示

不同的平台对 Markdown 的图示支持不尽相同，主要有以下几种图示语法：

* mermaid：[mermaid 语法](https://cloud.tencent.com/developer/article/1334691)、[Markdown 高级技巧](https://www.runoob.com/markdown/md-advance.html)
* flow
* sequence

