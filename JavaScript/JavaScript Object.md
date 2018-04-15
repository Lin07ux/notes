JavaScript 中，一切皆对象。作为最基础的 Object，在新版的 ES5、ES6 中也逐渐增加了更多的方法。

## 方法

### Object.assign
该方法可以深拷贝一个对象。属于 ES6 规范。格式为：

```javascript
Object.assign(target, source);
```

这个方法会将所有可枚举的自由属性从`source`复制到`target`。并且它返回(修改后的)`target`。关于这个函数最终签名至今还在争论，最终还有可能支持多个来源(被复制的对象)。即便是使用简单的签名(signature)，也可以处理多个来源，使用`Array.prototype.reduce`：

```javascript
[source1, source2 source3].reduce(Object.assign, target);
```

在 JavaScript 中，对象是通过引用的方式调用的，所以如果将一个值为对象的变量赋值给另一个变量，那么这两个变量就会指向同一个对象，修改任一变量，另一个变量也会受到影响。如果需要两不相干，就需要用到这个方法来进行深拷贝。

```javascript
var a = { x: 0, y: 1, z: 2 };
var b = Object.assign({}, a);

b.x = 3;
console.log(a.x); // 0
```

上面的例子可以看到，b 和 a 已经引用的不是同一个对象了。

实现同样功能的，在 jQuery 中可以使用`$.extend(target, source...)`。

对应的 polyfill 如下：

```JavaScript
if (typeof Object.assign != 'function') {     Object.assign = function(target, varArgs) {         'use strict';
        
        // TypeError if undefined or null
                 if (target == null) {             throw new TypeError('Cannot convert undefined or null to object');         }          var to = Object(target);         var length = arguments.length;          for (var index = 1; index < length; index++) {             var nextSource = arguments[index]; 
            // Skip over if undefined or null
             if (nextSource != null) {                 for (var nextKey in nextSource) {                     // Avoid bugs when hasOwnProperty is shadowed                     
                    if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {                         to[nextKey] = nextSource[nextKey];                     }                 }             }         }          return to;     }; }
```


## 对象继承
js 创建之初，正值 java 大行其道，面向对象编程春风正盛，js 借鉴了 java 的对象机制，但仅是看起来像，也就是 js 的构造函数，如下：

```JavaScript
function People(age) {
	this.age = age;
	this.getAge = function () { return this.age; };
}

var p1 = new People(20);  // People 的实例 1
var p2 = new People(40);  // People 的实例 2
```

上面的代码很像 java 了，通过`new constructor()`的方式，可以创建多个实例。

但上面代码问题是`getAge`方法会在每个 People 的实例中存在，如果实例多的话，会浪费很多空间，js 采用了牺牲时间，获取空间的方法，js 引入了原型理念，将方法放入原型中：

```JavaScript
function People(age) {
	this.age = age
}

People.prototype.getAge = function () { return this.age; };
```

但是在 JavaScript 中实现继承就比较麻烦了。

### 场景

我们假设我们有一个父构造函数 People 和子构造函数 Student。People 有一个属性 age 和一个方法 getAge，Student 有一个属性 num 和 getNum。

```JavaScript
function People (age) {
	this.age = age;
}
People.prototype.getAge = function () { return this.age; };

function Student (num) {
	this.num = num;
}
Student.prototype.getNum = function () { return this.num; };
```

要实现 Student 继承 People，在 js 里可要费一番力气了。

### 默认模式

我们可以利用 js 的原型机制，将子构造函数的原型属性设置为父构造函数的实例，这是 js 中比较常用的方式：

```JavaScript
function Student (num) {
	this.num = num;
}

Student.prototype = new People();

Student.prototype.getNum = function () {return this.num;};

var stu1 = new Student('123');
```

这样做其实基本实现了我们的需求，但如果深入思考上面的方式，其实有几个缺点：

1.	子类无法继承父类的实例属性；
2.	会将父类的实例属性，扩展到子类的原型上；
3.	修改了子类的原型属性，会导致在 stu1 上获取`constructor`属性为 People，而不是 Student。

### 借用构造函数

先来看看如何解决第一个问题，我们可以巧用 js 的 call 方法。

```JavaScript
function Student(age, num) {
	People.call(this, age);
	this.num = num;
}
```

我们在子构造函数内部，借用父构造函数，这样就巧妙地在子类中继承了父类的实例化属性。这其实类似 java 的 super 关键字。

### 共享原型

再来看看如何解决第二个问题。解决这个问题，其实我们可以将子构造函数的原型更改为父构造函数的原型，而不是父构造函数的实例。

```JavaScript
Student.prototype = People.prototype;
```

这样就不会将父构造函数的实例属性扩展到子构造函数的原型上了。但这样做会导致另一个问题，就是无法再在 Student 的原型上扩展方法了，因为会扩展同时会扩展到 People 的原型上。

### 临时构造函数

为了解决上面引发的问题，和第三个问题。我们可以在子构造函数和父构造函数之间，加一层临时构造函数。

```JavaScript
function F() {
}

F.prototype = People.prototype;

Student.prototype = new F();
```

这样就可以 Student 的原型上扩展子构造函数的方法，同时不影响父构造函数的原形了。在修复一下`constructor`属性就 ok 啦。

```JavaScript
Student.prorotype.constructor = Student;
```

### 圣杯

我们将上面的几种方法综合起来，代码看起来就像下面这样子：

```JavaScript
//继承函数
function inherit(C, P) {
	var F = function (){};
	F.prototype = P.prototype;
	C.prototype = new F(); // 临时构造函数

	C.prototype.constructor = C; // 修复 constructor
	C.superclass = P; // 存储超类
}

function People(age) {
	this.age = age;
}
People.prototype.getAge = function (){ return this.age; };

function Student(age, num) {
	Student.superclass.call(this, age);
	this.num = num;
}

inherit(Student, People); // 继承父构造函数
Student.prototype.getNum = function () { return this.num; };
```

### 转摘
[JavaScript对象继承一瞥](http://yanhaijing.com/javascript/2014/11/09/object-inherit-of-js/)



