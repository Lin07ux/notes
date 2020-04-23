在 JavaScript，new 操作符用于创建一个对象实例。在 JavaScript 中创建一个对象很简单，直接使用`{}`即可，而之所以要使用 new 操作符来创建对象实例，是由于 new 操作符会为实例对象和构造函数之间建立一条原型链，从而完成实例对象继承构造函数的能力。

### 1. new 的原理

new 运算符在内部做了如下四个操作：

1. 创建一个空的简单对象(即`{}`)；
2. 连接新对象到函数对象(即设置该新对象的构造函数)；
3. 将新创建的对象作为构造函数的运行上下文来执行构造函数；
4. 如果构造函数没有返回对象，那么就返回新创建的对象。

### 2. new 的实现

根据 new 的原理步骤，可以实现一个与 new 操作符相同功能的方法：

```JavaScript
function newOperator (fnContructor, ...args){
    var obj = {};
    obj.__proto__ = fnContructor.prototype;
    
    var res = fnContructor.apply(obj, args);
    
    return res || obj;
}
```

精简一下代码，也就是如下实现：

```JavaScript
function newOperator (fnContructor, ...args){
    var obj = Object.create(fnContructor.prototype);
    
    return fnContructor.apply(obj, args) || obj;
}
```

测试表明该自定义的方法能完全实现 new 操作符的功能：

```JavaScript
function Person(name) {
  this.name = name
}

Person.prototype.getName = function () {
  console.log(this.name)
}

var joe = newOperator(Person, 'joe')

joe.sayHello = function () {
  console.log('Hello!')
}

joe.getName() // joe
joe.sayHello() // Hello!

Person.sayHello() // Uncaught TypeError: Person.sayHello is not a function

console.log(new Person('joe'), newOperator(Person, 'joe')); // 两次输出的信息是一样的
```

