`JSON.stringify()`用于将一个变量序列化成字符串，常用于保存对象或深度拷贝对象。

## 一、基础

### 1.1 语法

`JSON.stringify()`的语法如下：

```
JSON.stringify(value[, replacer [, space]])
```

其中各个参数的说明如下：

* `value` 将要序列化成 一个 JSON 字符串的值。
* `replacer` 可选。如果该参数是一个函数，则在序列化过程中，被序列化的值的每个属性都会经过该函数的转换和处理；如果该参数是一个数组，则只有包含在这个数组中的属性名才会被序列化到最终的 JSON 字符串中；如果该参数为 null 或者未提供，则对象所有的属性都会被序列化。
* `space` 可选指定缩进用的空白字符串，用于美化输出；如果参数是个数字，它代表有多少的空格；上限为10。该值若小于1，则意味着没有空格；如果该参数为字符串（当字符串长度超过10个字母，取其前10个字母），该字符串将被作为空格；如果该参数没有提供（或者为 null），将没有空格。

### 1.2 转换规则

1. 转换值如果有`toJSON()`方法，则使用该方法定义什么值将被序列化。
2. 非数组对象的属性不能保证以特定的顺序出现在序列化后的字符串中。
3. 布尔值、数字、字符串的包装对象在序列化过程中会自动转换成对应的原始值。
4. `undefined`、函数、Symbol 值，出现在非数组对象的属性值中时在序列化过程中都会被忽略，出现在数组中时会被转换成`null`。`undefined`、函数被单独转换时会返回`undefined`。
5. `NaN`、`Infinity`、`null`都会被当做`null`处理。
6. 其他类型的对象，包括 Map/Set/WeakMap/WeakSet，仅会序列化可枚举的属性。
7. 对包含循环引用的对象执行此方法会抛出错误。
8. 所有以 Symbol 为属性键的属性都会被忽略掉，即便使用`replacer`参数指定了要包含它们。

### 1.3 示例

一般用法如下：

```JavaScript
const user = { 
    "name": "Prateek Singh",
    "age": 26
};

console.log(JSON.stringify(user)); // "{ "name" : "Prateek Singh", "age" :26 }" 
```

## 二、提升

### 2.1 指定序列化字段

`JSON.stringify()`方法的第二个参数可以用来指定参与序列化结果的字段，未指定的字段就不会出现在结果中。

### 2.1.1 使用数组指定

当使用一个数组作为`JSON.stringify()`方法的第二个参数时，仅在数组中的字段会在序列化结果中出现。

例如：

```JavaScript
const user = { 
    "name": "Prateek Singh",
    "age": 26
};

console.log(JSON.stringify(user, ['name', 'weight')); // "{ "name" : "Prateek Singh" }" 
```

这里，指定了可以出现的字段为`'name'`和`'weight'`，那么首先`user.age`将不会出现在结果中。而由于`user.weight`不存，所以结果中也没有该字段。

### 2.1.2 使用函数指定

如果第二个参数指定的是一个函数，那么该函数会接收到两个参数：键(`key`)和值(`value`)。

开始时，`replacer`函数会被传入一个空字符串作为 key 值，代表着要被序列化的这个对象。随后每个对象或数组上的属性会被依次传入。 

replacer 函数应当返回 JSON 字符串中的 value。需要注意的是：

1. 如果 replacer 函数在被首次调用(接收到空字符串和需要序列化的对象)时，返回了`null`，那么最终结果就是字符串`'null'`。
2. 如果 replacer 函数在被首次调用(接收到空字符串和需要序列化的对象)时，返回了`undefined`或者函数，那么最终结果就是`undefined`。
3. 如果 replacer 函数在后续的调用中，返回了`undefined`，当被序列化的不是数组时，将会忽略该键和值；如果被序列化的是数组时，当前的值会被当做`null`处理。

例如：

```JavaScript
JSON.stringify({ 1: 'a' }, function (key, value) {
    return key === '' ? null : (key == 1 ? undefined : value)
}); // 'null'

JSON.stringify({ 1: 'a' }, function (key, value) {
    return key === '' ? undefined : (key == 1 ? undefined : value)
}); // undefined

JSON.stringify({ 1: 'a', 2: 'b' }, function (key, value) {
    return key == 1 ? undefined : value
}); // '{"2":"b"}'

JSON.stringify(['a', 'b'], function (key, value) {
    return key == 1 ? undefined : value
}); // '["a",null]'
```

### 2.2 美化输出

`JSON.stringify()`方法还可以接受第三个参数，用来指定结果字符串之间的间距：

* 如果第三个参数是一个数字，则在字符串化时每一级别会比上一级别缩进多这个数字值的空格（最多10个空格）；
* 如果第三个参数是一个字符串，则每一级别会比上一级别多缩进该字符串（或该字符串的前10个字符）。

比如：

```JavaScript
JSON.stringify({ a: 2 }, null, 2);
/*
"{
  "a": 2
}"
*/

JSON.stringify({ a: 2 }, null, '\t');
/*
"{
    "a": 2
}"
*/
```

### 2.3 自定义序列化结果

由于一个对象有`toJSON()`方法时，序列化时就会调用对象的这个方法，并将其返回结果进行序列化。所以可以通过为对象增加自定义的`toJSON()`方法来实现对象的自定义序列化结果。

比如：

```JavaScript
let obj = {
  foo: 'foo',
  toJSON: function () {
    return 'bar';
  }
};
JSON.stringify(obj);      // '"bar"'
JSON.stringify({x: obj}); // '{"x":"bar"}'
```


