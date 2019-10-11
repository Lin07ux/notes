为了协助建立表格，HTML DOM 给`table`、`tbody`和`tr`等元素添加了一些特性和方法。 

给`table`元素添加了以下内容：

* `caption` 指向`caption`元素（如果存在）
* `tBodies` `tbody`元素的集合 
* `tFoot` 指向`tfoot`元素（如果存在）
* `tHead` 指向`thead`元素（如果存在） 
* `rows` 表格中所有行的集合
* `createTHead()` 创建`thead`元素并将其放入表格
* `createTFoot()` 创建`tfoot`元素并将其放入表格
* `createCaption()` 创建`caption`元素并将其放入表格
* `deleteTHead()` 删除`thead`元素
* `deleteTFood()` 删除`tfoot`元素
* `deleteCaption()` 删除`caption`元素
* `deleteRow(index)` 删除指定位置上的行
* `insertRow(index)` 在 rows 集合中的指定位置上插入一个新行

`tbody`元素添加了以下内容：

- `rows` `tbody`中所有行的集合
- `deleteRow(index)` 删除指定位置上的行
- `insertRow(index)` 在 rows 集合中的指定位置上插入一个新行

`tr`元素添加了以下内容：

- `cells` `tr`元素中所有的单元格的集合
- `deleteCell(index)` 删除给定位置上的单元格
- `insertCell(index)` 在 cells 集合的给点位置上插入一个新的单元格



