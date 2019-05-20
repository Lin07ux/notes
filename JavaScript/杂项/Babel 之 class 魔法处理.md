2017 年，很多人已经开始接触 ES6 环境，并且早已经用在了生产当中。但 ES6 在大部分浏览器还是跑不通的，因此需要使用 Babel 来进行编译。很多人可能没有关心过，经过 Babel 编译之后 ES6 代码究竟变成了什么样子？

这篇文章，针对 Babel 对 ES6 里面“类 class”的编译进行分析，可以[在线测试编译](https://babeljs.io/repl/#?babili=false&evaluate=true&lineWrap=false&presets=es2015%2Creact%2Cstage-2&code=)结果，毕竟纸上得来终觉浅，自己动手，才能真正体会其中的奥秘。

另外，如果还不明白 JS 中原型链等 OOP 相关知识，建议找经典的《JS高级程序设计》来补课；如果对 JS 中通过原型链来实现继承一直云里雾里，可以查看[颜海镜大大早在 2014 年的文章](http://yanhaijing.com/javascript/2014/11/09/object-inherit-of-js/)。

在这篇文章中，将会讲解 Babel 如何处理 ES6 新特性：Class，这其实是一系列语法糖的实现。

### 0. ES5 和 ES6 中的类

**ES5 环境下实现类的继承**：

```JavaScript
// Person 是一个构造器
function Person(name) {
    this.type = 'Person';
    this.name = name;
}

// 可以通过 prototype 的方式来加一条实例方法
Person.prototype.hello = function() {
    console.log('hello ' + this.name);
}

// 对于私有属性(Static method)，当然不能放在原型链上了，可以直接放在构造函数上面
Person.fn = function() {
    console.log('static');
};
```

可以这么应用：

```JavaScript
var julien = new Person('julien');
var darul = new Person('darul');

julien.hello(); // 'hello julien'
darul.hello(); // 'hello darul'
Person.fn(); // 'static'

// 这样会报错，因为 fn 是一个私有属性
julien.fn(); //Uncaught TypeError: julien.fn is not a function
```

**ES6 方式实现继承**

在 ES6 环境下，就可以直接使用 Class：

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
    }
}
```

这样写起来当然很 cool，但是经过 Babel 编译后的代码是什么样呢？

### 1. 基础转换

在不加任何方法和属性的情况下：

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

这里`_classCallCheck`为了保证类是通过`new`调用的，而不是当做函数执行。比如：

```JavaScript
// ok
var p = new Person();

// Uncaught TypeError: Cannot call a class as a function
Person();
```

### 2. constructor 编译

ES6 中的`constructor`是 ES5 中类执行的主体代码：

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

### 3. 方法和属性

ES6 class 中可以为类定义方法(实例方法和静态方法)和属性(静态属性和 getter、setter)。前面分析过，实例方法就是定义在类的 prototype 中，而静态方法则是直接定义在类中，这两者在 Babel 编译中基本使用相同的步骤，只是按照 ES5 分别定义在不同的对象中而已。

```JavaScript
class Person {
    constructor(name) {
        this.name = name;
        this.type = 'person'
    }

    hello() {
        console.log('hello ' + this.name);
    }
    
    static onlySayHello() {
        return 'hello'
    }

    get name() {
        return 'kevin';
    }

    set name(newName) {
        console.log('new name 为：' + newName);
    }
}
```

编译结果(已做适当省略)：

```JavaScript
// 如上，已经解释过
function _classCallCheck....

// MAIN FUNCTION
var _createClass = (function () {
    // 对于每一个定义的属性 props，都要完全拷贝它的 descriptor，并扩展到 target 上
    function defineProperties(target, props) {
        for (var i = 0; i < props.length; i++) {
            var descriptor = props[i];
            
            descriptor.enumerable = descriptor.enumerable || false;
            descriptor.configurable = true;
            
            if ('value' in descriptor) descriptor.writable = true;

            Object.defineProperty(target, descriptor.key, descriptor);
        }
    }

    return function (Constructor, protoProps, staticProps) {
        if (protoProps) defineProperties(Constructor.prototype, protoProps);
        if (staticProps) defineProperties(Constructor, staticProps);

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
    }, {
        key: 'name',
        get: function get() {
            return 'kevin';
        },
        set: function set(newName) {
            console.log('new name 为：' + newName);
        }
    }], [{
        key: 'onlySayHello',
        value: function onlySayHello() {
            return 'hello';
        }
   }]);

    return Person;
})();
```

这里的`defineProperties()`辅助方法就是将 ES6 中定义的各个方法(静态方法和实例方法)以及变量(静态变量和 getter、setter)扩展到编译后的类中。而且通过这个辅助函数处理之后，类内部所有定义的方法，都是不可枚举的(non-enumerable)。

> [defineProperty 文档](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/defineProperty)。

`_createClass()`辅助方法接受 2 个 － 3 个参数，分别表示：

* 参数1`Constructor` => 要扩展属性的目标对象，这里其实就是 Person
* 参数2`protoProps` => 需要在目标对象原型链上添加的属性，也就是实例方法和属性，这是一个数组
* 参数3`staticProps` => 需要在目标对象上添加的属性，也就是静态方法和属性，这是一个数组

### 4. 继承

接下来讲解 Babel 如何处理 ES6 Class 里面的继承功能，同样，这其实是一系列语法糖的实现。

#### 4.1 ES6 实现继承

首先，定义一个父类：

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

从简出发，定义的 Person 类只包含了`type`为`person`的这一个属性，不含有方法，所以`extends + super()`之后，Student 类也继承了同样的属性。如下：

```JavaScript
var student1 = new Student();
student1.type;  // "person"
```

可以进一步验证原型链上的关系：

```JavaScript
student1 instanceof Student // true
student1 instanceof Person // true
student1.hasOwnProperty('type') // true
```

看上去已经实现了 ES6 里面的继承，并且用`instanceof`验证了 ES6 中一系列的实质就是“魔法糖”的本质。那么，经过 Babel 编译的代码是什么样呢？

#### 4.2 Babel 转换

主要编辑结果为：

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
```

这是一个自执行函数，它接受一个参数 Person（就是他要继承的父类），返回一个构造函数 Student。并在这个自执行函数中使用了两个新的辅助方法`_inherits`和`_get`，分别看着这两个方法的作用和实现。

**_inherits**

`_inherits`方法的本质其实就是让 Student 子类继承 Person 父类原型链上的方法。它实现原理可以归结为如下的代码(ES6)：

```JavaScript
Student.prototype = Object.create(Person.prototype);
Student.prototype.constructor = Student;
Object.setPrototypeOf(Student, Person)
```

`Object.create`用于修改子类对象的`__proto__`属性，而`Object.setPrototypeOf`则用于设置子类对象的原型。可以分别查看[Object.create() 文档](https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object/create)和[Object.setPrototypeOf() 文档](https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Object/setPrototypeOf)。

具体实现为：

```JavaScript
function _inherits(subClass, superClass) {
    // superClass 需要为函数类型或 null，否则会报错
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

**_get**

`_get`用于对父类的实例属性（就是`constructor`定义的属性）的继承，也可以归结为如下代码：

```JavaScript
Person.call(this);
```

具体实现如下：

```JavaScript
// _x 为 Student.prototype.__proto__ 也就是父类原型
// _x2 为 'constructor'
// _x3 为 this
var _get = function get(_x, _x2, _x3) {
    var _again = true;
    
    _function: while (_again) {
        var object = _x,
            property = _x2,
            receiver = _x3;
        
        _again = false;
        
        // 对 Student.prototype.__proto__ 为 null 的处理
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
}
```

### 转摘

* [揭秘babel的魔法之class魔法处理](https://segmentfault.com/a/1190000008114593)
* [揭秘babel的魔法之class继承的处理2](https://segmentfault.com/a/1190000008136903)


