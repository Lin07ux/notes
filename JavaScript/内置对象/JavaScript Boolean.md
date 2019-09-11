Boolean 是与布尔值对应的引用类型。

### 1. 基本

创建 Boolean 对象可以使用 new 关键词，并传入 true 或 false 值。

需要注意的是，Boolean 对象也是一个对象，所以转换成基本类型的布尔值时，总是 true，这和创建 Boolean 对象时传入的是 true 还是 false 无关。

比如：

```JavaScript
var falseBoolean = new Boolean(false);
if (falseBoolean) {
    console.log('falseBoolean 转换成布尔值是 true')
} else {
    console.log('falseBoolean 转换成布尔值是 false')
}
// falseBoolean 转换成布尔值是 true
```

而且，基本类型的布尔值不是 Boolean 的实例，而且它们的`typeof`结果也不同：

```JavaScript
var falseObject = new Boolean(false);
var falseValue = false;

console.log(typeof falseObject); // "object"
console.log(typeof falseValue);  // "boolean"
console.log(falseObject instanceof Boolean); // true
console.log(falseValue instanceof Boolean);  // false
```

### 2. 方法

* `valueOf()` Boolean 类型的实例重写了该方法，返回基本类型值 true 或 false。
* `toString()` 返回字符串`"true"`或`"false"`。




