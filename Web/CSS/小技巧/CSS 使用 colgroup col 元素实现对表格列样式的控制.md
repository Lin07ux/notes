> 转摘：[快速了解CSS新出的列选择符双管道（||）](https://www.zhangxinxu.com/wordpress/2019/02/css-column-combinator/)

在表格中，如果需要控制整列的样式，可以使用兼容性不怎么好的`:nth-col()`或`:nth-last-col()`伪类，也可以使用原生 Table 布局中的`<colgroup>`和`<col>`元素实现，这个兼容性非常好。

### 1. 示例

如下表格代码：

```html
<table border="1" width="600">
    <colgroup>
        <col>
        <col span="2" class="ancestor">
        <col span="2" class="brother">
    </colgroup>
    <tr>
        <td> </td>
        <th scope="col">后代选择符</th>
        <th scope="col">子选择符</th>
        <th scope="col">相邻兄弟选择符</th>
        <th scope="col">随后兄弟选择符</th>
    </tr>
    <tr>
        <th scope="row">示例</th>
        <td>.foo .bar {}</td>
        <td>.foo > .bar {}</td>
        <td>.foo + .bar {}</td>
        <td>.foo ~ .bar {}</td>
    </tr>
</table>
```

表格共有 5 列。其中`<colgroup>`元素中有 3 个`<col>`元素，从`span`属性值可以看出，这 3 个`<col>`元素分别占据 1 列、2 列和 2 列。此时给后面 2 个`<col>`元素设置背景色，就可以看到背景色作用在整列上了，CSS 如下：

```css
.ancestor {
    background-color: dodgerblue;
}
.brother {
    background-color: skyblue;
}
```

效果如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1553586644233.png"/>

### 2. 缺点

这种方式有个缺点，如果单元格并不是正好属于某一列，而是跨列，此时，`<col>`元素是会忽略这些跨列元素的。举个例子：

```html
<table border="1" width="200">
    <colgroup>
        <col span="2">
        <col class="selected">
    </colgroup>
    <tbody>
        <tr>
            <td>A</td>
            <td>B</td>
            <td>C</td>
        </tr>
        <tr>
            <td colspan="2">D</td>
            <td>E</td>
        </tr>
        <tr>
            <td>F</td>
            <td colspan="2">G</td>
        </tr>
    </tbody>
</table>
```

CSS 如下：

```css
col.selected {
    background-color: skyblue;
}
```

此时仅 C 和 E 两个单元格有天蓝色的背景色，G 单元格虽然也覆盖了第 3 列，由于同时也属于第 2 列，于是被无视了，效果如下图：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1553586738103.png"/>

> 这个问题可以使用新的`||`选择符来解决，但是兼容性很差：
> 
> ```css
> col.selected || td {
>    background-color: skyblue;
> }
> ```
> 
> `col.selected || td`意思就是选择所有属于`col.selected`的`<td>`元素，哪怕这个元素横跨多列。


