2017年，很多人已经开始接触 ES6 环境，并且早已经用在了生产当中。我们知道 ES6 在大部分浏览器还是跑不通的，因此我们使用了伟大的 Babel 来进行编译。很多人可能没有关心过，经过 Babel 编译之后，我们华丽的 ES6 代码究竟变成了什么样子？

这篇文章，针对 Babel 对 ES6 里面“类 class”的编译进行分析，你可以 [在线测试编译](https://babeljs.io/repl/#?babili=false&evaluate=true&lineWrap=false&presets=es2015%2Creact%2Cstage-2&code=)结果，毕竟纸上得来终觉浅，自己动手，才能真正体会其中的奥秘。

另外，如果你还不明白 JS 中原型链等 OOP 相关知识，建议出门左转找到经典的《JS高级程序设计》来补课；如果你对 JS 中，通过原型链来实现继承一直云里雾里，安利一下我的同事，前端著名网红 [颜海镜大大早在 2014 年的文章](http://yanhaijing.com/javascript/2014/11/09/object-inherit-of-js/)。

### 为什么使用选择 Babel
Babel：The compiler for writing next generation JavaScript。我们知道，现在大部分浏览器或者类似 NodeJS 的 javascript 引擎还不能直接支持 ES6 语法。但这并不构成障碍，比如 Babel 的出现，使得我们在生产环境中书写 ES6 代码成为了现实，它工作原理是编译 ES6 的新特性为老版本的 ES5，从而得到宿主环境的支持。

### Class 例子
在这篇文章中，我会讲解 Babel 如何处理ES6新特性：Class，这其实是一系列语法糖的实现。

#### Old school 方式实现继承
在探究 ES6 之前，我们先来回顾一下 ES5 环境下，我们如何实现类的继承：

```JavaScript
// Person是一个构造器
function Person(name) {
    this.type = 'Person';
    this.name = name;
}

// 我们可以通过prototype的方式来加一条实例方法
Person.prototype.hello = function() {
    console.log('hello ' + this.name);
}

// 对于私有属性(Static method)，我们当然不能放在原型链上了。我们可以直接放在构造函数上面
Person.fn = function() {
    console.log('static');
};
```

我们可以这么应用：

```JavaScript
var julien = new Person('julien');
var darul = new Person('darul');
julien.hello(); // 'hello julien'
darul.hello(); // 'hello darul'
Person.fn(); // 'static'

// 这样会报错，因为fn是一个私有属性
julien.fn(); //Uncaught TypeError: julien.fn is not a function
```

#### New school 方式(ES6)实现继承
在 ES6 环境下，我们当然迫不及待地试一试 Class：

```JavaScript
class Person {
    constructor(name) {
        this.name = name;
        this.type="person"
    }
    hello() {
        console.log('hello ' + this.name);
    }
    static fn() {
        console.log('static');
    };
}
```

这样写起来当然很 cool，但是经过 Babel 编译，我们的代码是什么样呢？

#### Babel transformation
我们一步一步来看，

**Step1** 定义我们从最简单开始，试试不加任何方法和属性的情况下，

```JavaScript
Class Person{}
```

被编译为：

```JavaScript
function _classCallCheck(instance, Constructor) {
    // 检查是否成功创建了一个对象
    if (!(instance instanceof Constructor)) {  
        throw new TypeError("Cannot call a class as a function"); 
    } 
}

var Person = function Person() {
    _classCallCheck(this, Person);
};
```

你可能会一头雾水，`_classCallCheck`是什么？其实很简单，它是为了保证调用的安全性。比如我们这么调用：

```JavaScript
// ok
var p = new Person();
```
是没有问题的，但是直接调用：

```JavaScript
// Uncaught TypeError: Cannot call a class as a function
Person();
```

就会报错，这就是`_classCallCheck`所起的作用。具体原理自己看代码就好了，很好理解。

我们发现，Class 关键字会被编译成构造函数，于是我们便可以通过`new`来实现实例的生成。


**Step2** Constructor 探秘我们这次尝试加入 constructor，再来看看编译结果：

```JavaScript
class Person() {
    constructor(name) {  
        this.name = name;
        this.type = 'person'
    }
}
```

编译结果：

```JavaScript
var Person = function Person(name) {
    _classCallCheck(this, Person);
    this.type = 'person';
    this.name = name;
};
```

看上去棒极了，我们继续探索。


**Step3**：增加方法我们尝试给 Person 类添加一个方法`hello`：

```JavaScript
class Person {
    constructor(name) {
        this.name = name;
        this.type = 'person'
    }

    hello() {
        console.log('hello ' + this.name);
    }
}
```

编译结果(已做适当省略)：

```JavaScript
// 如上，已经解释过
function _classCallCheck.... 

// MAIN FUNCTION
var _createClass = (function () { 
    function defineProperties(target, props) { 
        for (var i = 0; i < props.length; i++) { 
            var descriptor = props[i]; 
            descriptor.enumerable = descriptor.enumerable || false; 
            descriptor.configurable = true; 
            if ('value' in descriptor) 
            descriptor.writable = true; 
            Object.defineProperty(target, descriptor.key, descriptor); 
        } 
    } 
    return function (Constructor, protoProps, staticProps) { 
        if (protoProps) 
            defineProperties(Constructor.prototype, protoProps); 
        if (staticProps) 
            defineProperties(Constructor, staticProps); 
        return Constructor; 
    }; 
})();

var Person = (function () {
    function Person(name) {
        _classCallCheck(this, Person);

        this.name = name;
    }

    _createClass(Person, [{
        key: 'hello',
        value: function hello() {
            console.log('hello ' + this.name);
        }
    }]);

    return Person;
})();
```

Oh...no，看上去有很多需要消化!不要急，我尝试先把他精简一下，并加上注释，你就会明白核心思路：

```JavaScript
var _createClass = (function () {   
    function defineProperties(target, props) { 
        // 对于每一个定义的属性props，都要完全拷贝它的descriptor,并扩展到target上
    }  
    return defineProperties(Constructor.prototype, protoProps);    
})();

var Person = (function () {
    function Person(name) { // 同之前... }

    _createClass(Person, [{
        key: 'hello',
        value: function hello() {
            console.log('hello ' + this.name);
        }
    }]);

    return Person;
})();
```

如果你不明白`defineProperty`方法，请 [参考这里](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/defineProperty)。

现在，我们知道我们添加的方法：

```JavaScript
hello() {
    console.log('hello ' + this.name);
}
```

被编译为：

```JavaScript
_createClass(
    Person, [{
    key: 'hello',
    value: function hello() {
        console.log('hello ' + this.name);
    }
}]);
```

而`_createClass`接受 2 个 － 3 个参数，分别表示：

```
参数1 => 我们要扩展属性的目标对象，这里其实就是我们的Person
参数2 => 需要在目标对象原型链上添加的属性，这是一个数组
参数3 => 需要在目标对象上添加的属性，这是一个数组
```

这样，Babel 的魔法就一步一步被揭穿了。

### Class 的继承
接下来讲解 Babel 如何处理 ES6 Class 里面的继承功能，同样，这其实是一系列语法糖的实现。

#### ES6 实现继承
首先，我们定义一个父类：

```JavaScript
class Person {
    constructor(){
        this.type = 'person'
    }
}
```

然后，实现一个 Student 类，这个“学生”类继承“人”类：

```JavaScript
class Student extends Person {
    constructor(){
        super()
    }
}
```

从简出发，我们定义的 Person 类只包含了`type`为`person`的这一个属性，不含有方法。所以我们`extends + super()`之后，Student 类也继承了同样的属性。如下：

```JavaScript
var student1 = new Student();
student1.type;  // "person"
```

我们进一步可以验证原型链上的关系：

```JavaScript
student1 instanceof Student // true
student1 instanceof Person // true
student1.hasOwnProperty('type') // true
```

一切看上去 cool 极了，我们实现了 ES6 里面的继承。并且用`instanceof`验证了 ES6 中一系列的实质就是“魔法糖”的本质。那么，经过 Babel 编译，我们的代码是什么样呢？

#### Babel transformation
**Step1** Person 定义：

```JavaScript
class Person {
    constructor(){
        this.type = 'person'
    }
}
```

**Step2** 观察 Student 子类：

```JavaScript
class Student extends Person {
    constructor(){
        super()
    }
}
```

编译结果为：

```JavaScript
// 实现定义 Student 构造函数，它是一个自执行函数，接受父类构造函数为参数
var Student = (function(_Person) {
    // 实现对父类原型链属性的继承
    _inherits(Student, _Person);
    
    // 将会返回这个函数作为完整的 Student 构造函数
    function Student() {
        // 使用检测
        _classCallCheck(this, Student);  
        // _get 的返回值可以先理解为父类构造函数       
        _get(Object.getPrototypeOf(Student.prototype), 'constructor', this).call(this);
    }

    return Student;
})(Person);

// _x 为 Student.prototype.__proto__
// _x2 为 'constructor'
// _x3 为 this
var _get = function get(_x, _x2, _x3) {
    var _again = true;
    _function: while (_again) {
        var object = _x,
            property = _x2,
            receiver = _x3;
        _again = false;
        // Student.prototype.__proto__ 为 null 的处理
        if (object === null) object = Function.prototype;
        
        // 以下是为了完整复制父类原型链上的属性，包括属性特性的描述符
        var desc = Object.getOwnPropertyDescriptor(object, property);
        if (desc === undefined) {
            var parent = Object.getPrototypeOf(object);
            if (parent === null) {
                return undefined;
            } else {
                _x = parent;
                _x2 = property;
                _x3 = receiver;
                _again = true;
                desc = parent = undefined;
                continue _function;
            }
        } else if ('value' in desc) {
            return desc.value;
        } else {
            var getter = desc.get;
            if (getter === undefined) {
                return undefined;
            }
            return getter.call(receiver);
        }
    }
};

function _inherits(subClass, superClass) {
    // superClass 需要为函数类型，否则会报错
    if (typeof superClass !== 'function' && superClass !== null) {
        throw new TypeError('Super expression must either be null or a function, not ' + typeof superClass);
    }
    
    // Object.create 第二个参数是为了修复子类的 constructor
    subClass.prototype = Object.create(superClass && superClass.prototype, {
        constructor: {
            value: subClass,
            enumerable: false,
            writable: true,
            configurable: true
        }
    });
    
    // Object.setPrototypeOf 是否存在做了一个判断，否则使用 __proto__
    if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
}
```

虽然我加上了注释，但是这一坨代码仍然看上去恶心极了！没关系，下面我们进行拆解，你很快就能明白。

**Step3** 抽丝剥茧。我们首先看 Student 的编译结果：

```JavaScript
var Student = (function(_Person) {
    _inherits(Student, _Person);

    function Student() {
        _classCallCheck(this, Student);            
        _get(Object.getPrototypeOf(Student.prototype), 'constructor', this).call(this);
    }

    return Student;
})(Person);
```

这是一个自执行函数，它接受一个参数 Person（就是他要继承的父类），返回一个构造函数 Student。

上面`_inherits`方法的本质其实就是让 Student 子类继承 Person 父类原型链上的方法。它实现原理可以归结为一句话(ES6)：

```JavaScript
Student.prototype = Object.create(Person.prototype);
Object.setPrototypeOf(Student, Person)
```

注意，`Object.create`接收第二个参数，这就实现了对 Student 的`constructor`修复。如果你不了解`Object.create`，那么请 [参考这里](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/create)。

以上通过`_inherits`实现了对父类原型链上属性的继承，那么对于父类的实例属性（就是`constructor`定义的属性）的继承，也可以归结为一句话：

```JavaScript
Person.call(this);
```

如果你还不理解使用`call`或者`apply`或者`bind`来改变 JS 中`this`的指向，那么请[参考这篇文章](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Function/call)。


### 转摘
[揭秘babel的魔法之class魔法处理](https://segmentfault.com/a/1190000008114593)

[揭秘babel的魔法之class继承的处理2](https://segmentfault.com/a/1190000008136903)


