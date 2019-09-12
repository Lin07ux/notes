> 转摘：[Object.defineProperty详解](http://blog.poetries.top/2018/12/23/Object.defineProperty/)

`Object.defineProperty()`方法用于给对象定义一个属性，和直接在对象上使用字面量方式添加属性相比，这种定义属性的方式可以为属性定义相关特性。除了可以给新定义的属性设置特性，也可以给已有的属性设置特性。

另外，ECMAScript 5 还增加了`Object.defineProperties()`方法，用于一次性为多个属性定义特性。对应的，还有一个`Object.getOwnPropertyDescriptor()`方法用于获取属性的特性描述信息。

> 在 IE 8 下只能在 DOM 对象上使用，尝试在原生的对象使用`Object.defineProperty()`会报错。

## 一、Object.defineProperty

语法：

```JavaScript
Object.defineProperty(obj, prop, descriptor)
```

参数说明：

* `obj` 必需，目标对象
* `prop` 必需，需定义或修改的属性的名字
* `descriptor` 必需，目标属性所拥有的特性

调用之后，返回传入函数的对象，即第一个参数`obj`。

`descriptor`特性描述有两种类别：**数据描述**和**存取器描述**。

### 1.1 数据描述

数据描述中的属性都是可选的，用于控制该属性是否可重写、是否可枚举等特性，主要通过`configurable`、`enumerable`、`writable`、`value`四个选项来控制该属性的行为特性。

示例如下：

```JavaScript
var obj = {
    test:"hello"
}

// 对象已有的属性添加特性描述
Object.defineProperty(obj,"test",{
    configurable:true | false,
    enumerable:true | false,
    value:任意类型的值,
    writable:true | false
});

// 对象新添加的属性的特性描述
Object.defineProperty(obj,"newKey",{
    configurable:true | false,
    enumerable:true | false,
    value:任意类型的值,
    writable:true | false
});
```

#### 1.1.1 value

属性对应的值，可以使任意类型的值，默认为`undefined`。

```JavaScript
var obj = {}

// 第一种情况：不设置 value 属性
Object.defineProperty(obj, "key", {

});
console.log( obj.key );  // undefined

// 第二种情况：设置 value 属性
Object.defineProperty(obj, "newKey", {
    value:"hello"
});
console.log( obj.newKey );  // hello
```

#### 1.1.2 writable

属性的值是否可以被重写。设置为`true`可以被重写，设置为`false`，不能被重写。默认为`false`。

```JavaScript
var obj = {}

// 第一种情况：writable 设置为 false，不能重写
Object.defineProperty(obj, "key", {
    writable: false,
    value: "hello"
});

// 更改 key 的值
obj.newKey = "change value";
console.log( obj.newKey );  // hello

// 第二种情况：writable 设置为 true，可以重写
Object.defineProperty(obj, "newKey", {
    writable: true,
    value: "hello"
});

// 更改 newKey 的值
obj.newKey = "change value";
console.log( obj.newKey );  //change value
```

#### 1.1.3 enumerable

设置此属性是否可以被枚举（使用`for...in`或`Object.keys()`）。设置为`true`可以被枚举，设置为`false`不能被枚举。默认为`false`。

```JavaScript
var obj = {}

// 第一种情况：enumerable 设置为 false，不能被枚举
Object.defineProperty(obj, "key", {
    enumerable: false,
    value: "hello"
});

// 枚举对象的属性
for( var attr in obj ){
    console.log( attr ); // 没有输出
}

// 第二种情况：enumerable 设置为 true，可以被枚举
Object.defineProperty(obj, "newKey", {
    enumerable: true,
    value: "hello"
});

// 枚举对象的属性
for( var attr in obj ){
    console.log( attr );  // newKey
}
```

#### 1.1.4 configurable

这个配置起到两个作用：

* 目标属性是否可以使用`delete`删除
* 目标属性是否可以再次配置特性(`writable`、`configurable`、`enumerable`)

设置为 true 可以被删除或可以重新设置特性；设置为 false 不能被可以被删除或不可以重新设置特性。默认为 false。

在非严格模式下，删除`configurable`特性为 false 的属性，什么也不会发生，但在严格模式下将会导致错误。

**测试目标属性是否能被删除**

```JavaScript
var obj = {}
// 第一种情况：configurable 设置为 false，不能被删除
Object.defineProperty(obj, "key", {
    configurable: false,
    value: "hello"
});

// 删除属性
delete obj.newKey;
console.log( obj.newKey ); // hello

// 第二种情况：configurable 设置为 true，可以被删除
Object.defineProperty(obj,"newKey",{
    configurable: true,
    value:"hello"
});

// 删除属性
delete obj.newKey;
console.log( obj.newKey ); // undefined
```

**测试是否可以再次修改特性**

```JavaScript
var obj = {}

// 第一种情况：configurable 设置为 false，不能再次修改特性
Object.defineProperty(obj, "key", {
    configurable: false,
    value: "hello"
});

// 重新修改特性
Object.defineProperty(obj, "key", {
    configurable: true,
    value: "hello"
}); // 报错：Uncaught TypeError: Cannot redefine property: newKey

// 第二种情况：configurable 设置为 true，可以再次修改特性
Object.defineProperty(obj, "newKey", {
    configurable: true,
    value: "hello",
});

// 重新修改特性
Object.defineProperty(obj, "newKey", {
    configurable: true,
    value: "hello"
});
console.log( obj.newKey ); // hello
```

### 1.2 存取器描述

属性的值除了可以通过描述符中的`value`属性来设置，还可以通过添加获取器和设置器的方式控制属性的读写。

使用存取器定义的属性，不包含数据值，而是有一对`get`和`set`方法，通过这两个方法来对属性的值进行操作。在读取该属性的值的时候，会调用`get`方法；在更新该属性的值的时候，会调用`set`方法。

> 注意：当设置了读取器，就不允许使用`writable`和`value`这两个属性了。

语法如下：

```JavaScript
var obj = {};
Object.defineProperty(obj,"newKey",{
    configurable: true | false,
    enumerable: true | false,
    get: function () {} | undefined,
    set: function (value) {} | undefined
});
```

读取器和设置器并非必须要成对出现，如果不设置，则默认为 undefined：

* 不设置`get`方法则无法获取该属性的值，此时读取改属性，在非严格模式会返回 undefined，而在严格模式会抛出错误。但是这个属性依旧在对象中，可以通过`Object.keys()`或其他遍历方法得到。
* 不设置`set`方法则无法更新该属性的值，也就是让该属性变成只读的了。

#### 1.2.1 读取器

设置了读取器之后，访问对象的该属性时，就会自动调用读取器方法，并将读取器的返回值作为该属性的值。

```JavaScript
var obj = {};
var initValue = 'hello';

Object.defineProperty(obj, "newKey", {
    get: function () {    // 当获取值的时候触发的函数
        return initValue;    
    }
});

console.log( obj.newKey );  // hello

initValue = 'hello world';
console.log( obj.newKey );  // hello world
```

#### 1.2.2 设置器

如果只设置访问器，那么就没办法直接修改属性的值，可以通过添加设置器来解决。

设置器会在为对象的属性赋值时被调用执行。

```JavaScript
var obj = {};
var initValue = 'hello';
Object.defineProperty(obj, "newKey", {
    get: function () {      // 当获取值的时候触发的函数
        return initValue;    
    },
    set: function (value) { // 当设置值的时候触发的函数，设置的新值通过参数 value 拿到
        initValue = value;
    }
});

// 获取值
console.log( obj.newKey );  // hello

// 设置值
obj.newKey = 'change value';
console.log( obj.newKey ); // change value
```

## 二、相关方法

### 2.1 Object.defineProperties()

该方法可以一次性为对象定义多个属性。

语法：`Object.defineProperties(obj, descriptors)`

参数：

* `obj` 要定义属性的对象
* `descriptors` 属性描述符，是一个对象，对象的键是要定义到 obj 的属性名，对象的值是一个和`Object.defineProperty()`方法的第三个参数一样的属性描述对象，可以包含`configurable`、`enumerable`、`writable`、`value`、`get`、`set`属性。

比如：

```JavaScript
var book = {};

Object.defineProperties(book, {
    _year: {
        writable: true,
        value: 2004
    },
    edition: {
        writable: true,
        value: 1
    },
    year: {
        get: function () { return this._year },
        set: function (newValue) {
            if (newValue > 2004) {
                this._year = newValue;
                this.edition += newValue - 2004;
            }
        }
    }
})
```

这段代码在`book`对象上创建了`_year`和`edition`两个数据属性和一个存取器属性`year`。

### 2.2 Object.getOwnPropertyDescriptor()

该方法用于取得对象的给定属性的描述符。

语法：`Object.getOwnPropertyDescriptor(obj, property)`

参数：

* `obj` 属性所在的对象
* `property` 属性名称

返回值：

* 如果该属性是访问器属性，则返回一个包含有`configurable`、`enumerable`、`get`、`set`属性的对象。
* 如果该属性是数据属性，则返回一个包含有`configurable`、`enumerable`、`writable`、`value`属性的对象。
* 如果该属性不存在与该对象上，或者不是直接定义在该属性上的(是从原型链上继承的)，那么返回 undefined。

> 注意：该方法只能用于对象实例本身的属性，如果要获取对象的原型属性的描述符，则需要对对象的原型对象执行该方法。

比如，对于上面`Object.defineProperties()`示例中的`book`对象：

```JavaScript
console.log(Object.getOwnPropertyDescriptor(book, '_year'));
// { value: 2004, writable: true, enumerable: false, configurable: false }

console.log(Object.getOwnPropertyDescriptor(book, 'year'));
// {get: ƒ, set: ƒ, enumerable: false, configurable: false}

console.log(Object.getOwnPropertyDescriptor(book, 'edition'));
// {value: 1, writable: true, enumerable: false, configurable: false}

console.log(Object.getOwnPropertyDescriptor(book, 'none'));
// undefined

console.log(Object.getOwnPropertyDescriptor(book, 'toString'));
// undefined
```

