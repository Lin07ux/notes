在 JavaScript 中，一切都是对象，而 JS 中对象的继承则是通过原型对象来实现的。通过原型链的传递，可以很方便的给每个对象都增加一些共用的属性和方法。下面就来具体的分析下 JavaScript 中的原型。

## 0x00 基本概念

在讲解`prototype`之前，我们需要先了解一些基本的概念。

### 1、什么是对象？

对象就是若干属性的集合。

### 2、什么是原型？

原型也是一个对象，其他对象可以通过它来实现继承。一旦原型对象被赋予属性和方法，那么由相应的构造函数创建的实例会继承`prototype`上的属性和方法。

> 任何一个对象都可以成为原型对象

```JavaScript
// Constructor : A
// Instance : a
function A(){};

var a = new A();

A.prototype.name = "xl";
A.prototype.sayName = function(){
    console.log(this.name);
}

// 由构造函数创建的实例会继承 prototype 上的属性和方法
console.log(a.name); // "xl"
a.sayName();         // "xl"
```

### 3、什么对象有原型？

所有的对象在默认情况下都有一个原型。因为原型是一个对象，所以每个原型自身又有一个原型。只有一种例外：Object的原型处于最顶端，为`null`。

> 对象的实例并没有原型对象，这个需要分清楚的。

### 4、什么是 constructor 属性？

每个对象实例都有`constructor`属性，而且这个属性始终指向创建这个对象实例的构造函数。

> 对象的实例有`constrctor`属性，这个和原型对象不相同。

### 5、什么是 \_\_proto\_\_ 属性？

每个由构造函数创建的实例都有一个`__proto__`属性，这个属性指构造函数的`prototype`，从而实现继承。

需要注意的是，实例的`__proto__`属性，是对构造函数的`prototype`属性的引用而非拷贝，所以，当修改了构造函数的`prototype`属性时，实例的相关属性也会同步变化。

但是如果在创建实例之后，替换构造函数的原型属性为一个新的对象，实例对象的原型的`__proto__`却仍然引用着原来它被创建时构造函数的原型属性。即：**即如果在实例被创建之后，改变了函数的原型属性所指向的对象，也就是改变了创建实例时实例原型所指向的对象，但是这并不会影响已经创建的实例的原型。**

```JavaScript
var A = function(name) {
    this.name = name;
}  
var a = new A('alpha');

a.name;  // 'alpha'

A.prototype = {x: 23};

a.x;     // null
```

> 这个属性也是对象的实例所有的。但这个属性在很多浏览器中是不可访问的。

## 0x01 Constructor 属性

首先来看一下`constrctor`属性。该属性是理解`JavaScript`类和继承的重要基础。

`constructor`属性始终指向创建当前对象的构造函数。下面分别显示每种对象的`constrctor`属性：

```JavaScript
var arr = [1,2,3];
console.log(arr.constructor);   // 输出 function Array(){}

var a = {};
console.log(a.constructor);   // 输出 function Object(){}

var bool = false;
console.log(bool.constructor);  // 输出 function Boolean(){}

var name = "hello";
console.log(name.constructor);  // 输出 function String(){}

var sayName = function(){}
console.log(sayName.constrctor); // 输出 function Function(){}

// 接下来通过构造函数创建 instance
function A () {}
var a = new A();
console.log(a.constructor);     // 输出 function A(){}
```

### 1、函数对象的原型对象的 constructor 属性

既然每个对象都有一个`constructor`属性，那么一个函数对象的原型对象也会有这样一个属性，那这个属性指向哪里呢？

函数对象的原型`prototype`的`constructor`属性指向这个函数本身。

```JavaScript
function Person(name){
    this.name = name;
}
Person.prototype.sayName = function(){
    console.log(this.name);
}

var person = new Person("xl");
console.log(person.constructor);    // 输出 function Person(){}
console.log(Person.prototype.constructor);  // 输出 function Person(){}
console.log(Person.constructor);    // 输出 function Function(){}
```

### 2、自定义原型对象(重写)

前面我们说了，如果给一个对象的原型对象赋予了属性和方法，那么通过这个对象的构造方法生成的实例将共享这些属性和方法。

这意味着，我们完全可以重新自定义原型对象，甚至重写原型对象。那重写之后，原型对象有什么变化呢？

```JavaScript
function Person (name) {
    this.name = name;
}

Person.prototype = {
    sayName:function(){
      console.log(this.name);
    }
}

console.log(person.constructor == Person);      // 输出 false
console.log(Person.constructor == Person);      // 输出 false
console.log(Person.prototype.constructor);      // 输出 function Object(){}
```

为什么第二个输出为`false`，第三个输出为`function Object(){}`呢？还记得前面说的`constructor`属性始终指向创建这个对象的构造函数吗？

```JavaScript
Person.prototype = {
    sayName: function(){
        console.log(this.name);
    }
}
```

这段代码实际上是对原型对象的重写：

```JavaScript
Person.prototype = new Object(){
    sayName: function(){
        console.log(this.name);
    }
}
```

所以，现在`Person.prototype.constructor`属性实际上是指向 Object 的。那么我如何能将`constructor`属性再次指向 Person 呢？再给原型对象的`constrctor`属性给个显示赋值：

```JavaScript
Person.prototype.constructor = Person;
```

那为什么第一个判断也是输出了`false`呢？`person`不是由`Person`创建的吗？这里就涉及到了 JS里面的原型继承：

这个地方是因为`person`实例继承了`Person.prototype`原型对象的所有的方法和属性，包括`constructor`属性。当`Person.prototype`的`constructor`发生变化的时候，相应的`person`实例上的`constructor`属性也会发生变化。根据上面的分析，`Person.prototype.constructor`属性指向的是`Object`，所以上面会输出false。

### 3、如何实现继承

ECMAscript 的发明者为了简化这门语言，同时又保持继承性，采用了链式继承的方法。

前面我们提到了实例的`__proto__`属性，由于这个属性指向了构造这个实例的对象的`prototype`属性，所以实例将会继承`prototype`属性上的对象和方法，而每个对象的`prototype`属性都会继承自其构造对象的`prototype`，一直向上，最终到达`Object`对象的`prototype`，即`Null`。这样就完成了实例对构造对象的继承。

```JavaScript
function Person (name) {
    this.name=name;
}

Person.sayName = function() {
    console.log(this.name);
}

var personOne = new Person("a");
var personTwo = new Person("b");

personOne.sayName(); // 输出 "a"
personTwo.sayName(); // 输出 "b"

console.log(personOne.__proto__ == Person.prototype); // true
console.log(personTwo.__proto__ == Person.prototype); // true
console.log(personOne.constructor == Person); // true
console.log(personTwo.constructor == Person); // true
console.log(Person.prototype.constructor == Person); // true
console.log(Person.constructor);  // function Function(){}
console.log(Person.__proto__.__proto__); // Object{}
```

其对应的继承链式图如下：

![JavaScript Prototype继承](http://cnd.qiniu.lin07ux.cn/2015-08-15%20JavaScript-prototype.png)

### 4、属性查找
当查找一个对象的属性的时候，`JavaScript`会向上遍历原型链，直到找到给定的属性为止。如果到达原型链的顶部，也就是`Object.Prototype`时，仍旧未找到指定的属性，就会返回`undefined`。

```JavaScript
function foo() {
    this.add = function (x, y) {
        return x + y;
    }
}

foo.prototype.add = function (x, y) {
    return x + y + 10;
}

Object.prototype.subtract = function (x, y) {
    return x - y;
}

var f = new foo();
alert(f.add(1, 2));      // 结果是 3，而不是 13
alert(f.subtract(1, 2)); // 结果是 -1
```

通过代码运行，我们发现`subtract`是安装我们所说的向上查找来得到结果的，但是`add`方式有点小不同，这也是我想强调的，就是属性在查找的时候是先查找自身的属性，如果没有再查找原型，再没有，再往上走，一直插到 Object 的原型上，所以在某种层面上说，用`for in`语句遍历属性的时候，效率也是个问题。

还有一点我们需要注意的是，我们可以赋值任何类型的对象到原型上，但是不能赋值原子类型的值，比如如下代码是无效的：

```JavaScript
function Foo() {}
Foo.prototype = 1;  // 无效
```

## 0x02 函数相关

### 1、hasOwnProperty()

`hasOwnProperty`是`Object.prototype`的一个方法，能判断一个对象是否包含自定义属性而不是原型链上的属性。

```JavaScript
// 修改 Object.prototype
Object.prototype.bar = 1;
var foo = {goo: undefined};

foo.bar; // 1
'bar' in foo; // true

foo.hasOwnProperty('bar'); // false
foo.hasOwnProperty('goo'); // true
```

需要注意的是：`JavaScript`不会保护`hasOwnProperty`被非法占用，因此如果一个对象碰巧存在这个属性，就需要使用外部的`hasOwnProperty`函数来获取正确的结果。

```JavaScript
var foo = {
    hasOwnProperty: function() {
        return false;
    },
    bar: 'Here be dragons'
};

foo.hasOwnProperty('bar'); // 总是返回 false

// 使用 {} 对象的 hasOwnProperty，并将其上下为设置为 foo
{}.hasOwnProperty.call(foo, 'bar'); // true
```

### 2、instanceof

如果 a 的原型属于 A 的原型链，表达式`a instanceof A`值为`true`。这意味着我们可以对`instanceof`耍个诡计让它不在起作用：

```JavaScript
var A = function() {}
var a = new A();

a.__proto__ == A.prototype;     // true- so a instanceof A will return true

a instanceof A;     // true;

// mess around with a's prototype
a.__proto__ = Function.prototype;
// a's prototype no longer in same prototype chain as A's prototype property
a instanceof A;    //false
```


## 0x03 使用原型的好处

如果仅仅只是因为一个实例而使用原型是没有多大意义的，这和直接添加属性到这个实例是一样的。

原型真正魅力体现在多个实例共用一个通用原型的时候。原型对象(*注:也就是某个对象的原型所引用的对象*)的属性一旦定义，就可以被多个引用它的实例所继承(*注:即这些实例对象的原型所指向的就是这个原型对象*)

- 1、只需要定义一次，所有的实例都将有定义的方法和属性(继承性)。
- 2、所有的实例共用一份属性和方法，所以需要修改的时候只需修改原型中的代码，就能影响到所有的实例。


