margin 的计算方式：

* 普通元素的百分比 margin 都是相对于容器的宽度；
* 绝对定位元素的百分比 margin 是相对于第一个定位祖先元素的宽度。

重叠条件：

* 必须是 block 元素；
* 	不考虑 writing-mode 的话，只发生在垂直方向

margin 重叠的情形：

* 相邻兄弟元素
* 父级和第一个 / 最后一个子元素
* 空的 block 元素

父子 margin 重叠的其他条件：

* 父元素不是 BFC
* 父元素没有 border-top
* 父元素没有 padding-top
* 父元素和第一个子元素之间没有 inline 元素分割
* 父元素没有设置 height min-height max-height

margin 重叠的计算规则：

* 正正取最大
* 正负取相加
* 负负取最小


