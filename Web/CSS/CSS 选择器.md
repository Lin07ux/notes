参考：[W3school](http://www.w3school.com.cn/cssref/css_selectors.asp)

## 一、基本选择器
1.	`*`  通用元素选择器，匹配所有元素
2.	`E`  标签选择器，匹配所有使用 E 标签的元素
3.	`.info`	  class 选择器，匹配所有 class 属性中包含 info 的元素
4.	`#footer`  id 选择器，匹配所有 id 属性等于 footer 的元素


## 二、组合选择器
5.	`E,F`		多元素选择器，匹配所有 E 元素或 F 元素，E 和 F 之间用逗号分隔
6.	`E F`		后代元素选择器，匹配所有属于 E 元素内部的 F 元素，E 和 F 之间用空格分隔
7.	`E > F`	子元素选择器，匹配所有 E 元素的子元素 F (不是所有的后代元素，*仅子元素*)
8.	`E + F`	毗邻元素选择器，匹配所有*紧随* E 元素之后的第一个同级元素 F (E 和 F 要前后紧靠相邻)


## 三、CSS 2.1 属性选择器
9.	`[attr]`		  匹配所有具有 attr 属性的 E 元素，不考虑它的值。
10. `[attr=val]`	  匹配所有 attr 属性等于"val"的元素。这里元素的 attr 属性的值必须和 val 相等。
11. `[attr~=val]` 匹配所有 attr 属性具有值为"val"的**单词**的元素。注意这里的 val 需要是一个独立的单词，而不能是单词中的一部分。由于“单词”只对 ASCII 字符有效，所有对于中文是没有效果的，即便在每个中文字词之间加上空格。
12. `[attr|=val]` 匹配所有 attr 属性值的开头是`val`的单词，或者开头是`val-`的元素。同样，这里也是“单词”，而且也不支持中文。该选择器主要用于 lang 属性，比如"en"、"en-us"、"en-gb"等


## 四、CSS 2.1 中的伪类
13. `E:first-child`	匹配作为某元素的第一个子元素的 E 元素(即匹配的是父元素中的第一个 E 子元素)
14. `E:link	`		匹配所有未被点击的链接，只能用于超链接
15. `E:visited	`	匹配所有已被点击的链接，只能用于超链接
16. `E:active`		匹配鼠标已经其上按下、还没有释放的 E 元素
17. `E:hover`		匹配鼠标悬停其上的 E 元素
18. `E:focus`		匹配获得当前焦点的 E 元素
19. `E:lang(c)`	匹配 lang 属性以 c 开头的 E 元素


## 五、 CSS 2.1 中的伪元素
20. `E:first-line	`    匹配 E 元素的第一行
21. `E:first-letter`  匹配 E 元素的第一个字母
22. `E:before`	  在 E 元素之前插入生成的内容
23. `E:after`   在 E 元素之后插入生成的内容


## 六、CSS 3 的同级元素通用选择器
24. `E ~ F`	  匹配任何在 E 元素之后的同级 F 元素(和`E + F`的区别是，这个是 E 元素后面的所有同级 F 元素，而非紧靠着 E 元素的 同级 F 元素)


## 七、CSS 3 属性选择器
25. `[attr^="val"]`  属性 attr 的值以"val"字符开头的元素
26. `[attr$="val"]`  属性 attr 的值以"val"字符结尾的元素
27. `[attr*="val"]`  属性 attr 的值包含"val"字符的元素
	
> 注：`E[att!="val"]`这种方式可以在 jQuery 作为选择器使用，表示属性 att 的值不是 val 的元素。

> 上面这三个选择器虽然是 CSS3 中的，但是 IE7 浏览器也支持。


## 八、CSS 3 中与用户界面有关的伪类
28. `E:enabled	`    匹配表单中激活的元素
29. `E:disabled`   匹配表单中禁用的元素
30. `E:checked	`    匹配表单中被选中的 radio（单选框）或 checkbox（复选框）元素
31. `E::selection` 匹配用户当前选中的元素(其实是伪元素)


## 九、CSS 3 中的结构性伪类
32. `E:root`	匹配文档的根元素，对于 HTML 文档，就是 HTML 元素
33. `E:empty`	匹配一个不包含任何子元素的元素，注意，文本节点也被看作子元素
34. `E:nth-child(n)`  匹配作为父元素的第 n 个子元素的 E 元素，第一个编号为 1。如：`p:nth-child(2)`匹配的是属于其父元素的第二个子元素的每个 p 元素
35. `E:nth-last-child(n)`  匹配作为父元素的倒数第 n 个子元素的E元素，倒数第一个编号为 1
36. `E:nth-of-type(n)`  匹配父元素中所有 E 元素中的第 n 个 E 元素
37. `E:nth-last-of-type(n)`  匹配父元素中所有 E 元素中的倒数第 n 个 E 元素
38. `E:last-child	`     匹配作为父元素的最后一个子元素的 E 元素
39. `E:first-of-type`  匹配父元素下第一个 E 子元素，即便 E 元素不是其父元素的第一个子元素。如果父元素中有多个 E 子元素，也只选择其中的第一个 E 子元素
40. `E:last-of-type`   匹配父元素下最后一个 E 子元素，即便这个 E 元素后面还有其他类型的子元素。
41. `E:only-child	`     匹配 E 元素是其父元素下仅有的一个子元素，如果父元素中还有其他元素，那么该 E 元素就不会被选中。
42. `E:only-of-type`	匹配父元素下使用同种标签的唯一一个子元素，等同于`:first-of-type:last-of-type`或`:nth-of-type(1):nth-last-of-type(1)`


## 十、CSS 3 的反选伪类
43. `:not(s)`  匹配不符合当前选择器 s 的任何元素。这里的 s 表示其他的任何一种选择器。


## 十一、CSS 3 中的 :target 伪类
44. `E:target`  匹配文档中特定"id"(锚点)点击后的效果。参考：[Suckerfish :target](http://htmldog.com/articles/suckerfish/target/)。


如：一个 a 元素的 href 属性为"#test"，而且页面中有一个 h2 元素的 id 为"test"，则点击 a 元素之后，就会跳转到 h2 元素，同时会使 h2 元素的`:target`伪类(即`h2:target`)生效。可以查看这个[示例](http://htmldog.com/articles/suckerfish/target/example/)

如果这个锚点指向的是一片区域(如一个 div)，那么这一片区域(div)将会触发这个伪类。IE 可能不支持这个伪类。


## 十二、CSS4 属性选择器
45. `[attr="val" i]` 忽略大小写匹配
46. `[attr="val" g]` 字符串全局匹配


## 其他事项
### a 伪类
`:link`、`:visited`、`:hover`、`:active`。

* `:active`一般不必写
* 一定要注意顺序：LVHA
* `a:link`可以简写为 a，但是前者只针对所有写了`href`属性的超链接（不包括锚点），后者则针对所有的超链接，包括锚点。



