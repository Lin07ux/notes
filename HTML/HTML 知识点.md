# FORM
* 用户单击提交按钮或图像按钮时，就会提交表单，使用 input 或者 button 都可以提交表单，只需将 type 设置为 submit 或者 image 即可。

* 同样，将 input 或者 button 的 type 设置为 reset 就能重置表单。
* 访问表单字段：使用dom节点来访问；通过表单元素的 elements 属性。
> 每个表单都有 elements 属性，该属性是表单中所有表单元素集合；这个 elements 是个有序列表；包含着所有字段，比如有input, textarea, button, fieldset 等。可以通过索引次序或者表单元素的 name 属性值来访问相应的元素，比如：`formId.elements[0]`可以获取表单中的第一个表单元素；`formId.elements['select1']`可以获取到表单中名称为 select1 的表单元素。如果一个表单中，有多个name相同的属性，那么取得数据是一个集合

