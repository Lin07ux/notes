为了便于操作基本类型值，ECMAScript 提供了 3 个特殊的引用类型：Boolean、Number、String。这些类型与其他的引用类型类似，但也具有与各自的基本类型相应的特殊行为。

### 1. 基本类型属性和方法的实现原理

每当读取一个基本类型值的时候，后台就会创建一个对应的基本包装类型的对象，从而能够调用一些方法来操作这些数据。后台自动完成的处理如下：

1. 创建一个基本包装类型对应的实例；
2. 在这个实例上调用指定的方法；
3. 销毁这个实例。

比如，以 String 类型为例：

```JavaScript
var s1 = 'some text';
var s2 = s1.substring(2);
```

这段代码中，变量 s1 是一个字符串基本类型，而基本类型是没有其他属性和方法的。在第二行中通过 s1 调用`substring()`方法时，会先读取 s1 变量的值，生成一个 String 实例，然后在这个实例上调用`sbustring()`方法，然后将处理的结果赋值给 s2 后销毁这个实例。可以想象这个处理步骤如下：

```JavaScript
var s1 = new String('some text');
var s2 = s1.substring(2);
s1 = null;
```

### 2. 引用类型与基本包装类型的区别

引用类型和基本包装类型的主要区别就是两者实例的生存期：

* 使用`new`操作符创建的引用类型的实例，在执行流离开当前作用域之前都会一直保存在内存中。
* 自动创建的基本包装类型的实例，则只存在于一行代码的执行瞬间，然后立即被销毁。

这个特性意味着：**不能在运行时为基本类型值添加属性和方法**。

比如：

```JavaScript
var s1 = 'some text';
s1.color = 'red';
console.log(s1.color); // undefined
```

上面的代码，虽然在第二行为基本类型变量 s1 添加了一个属性`color`，但是由于这行代码执行完成之后，其对应创建的基本包装类型实例被自动销毁了，而第三行代码由自动创建了一个新的基本包装类型实例，此时其`color`属性是不存在的，所以结果就是输出了 undefined。

### 3. 特殊性

虽然可以通过 new 方式显式的创建基本包装类型实例，但是一般不建议，除非有必要情况。

#### 3.1 typeof

对基本包装类型的实例调用`typeof`操作会返回`object`，因为其确实是一个对象。

同理，由于基本包装类型实例是对象，那么将其转换成布尔值时都会是 true。

#### 3.2 Object 构造

Object 构造函数会像工厂方法一样，根据传入的参数的类型返回相应基本包装类型的实例：把字符串传给 Object 构造函数，就会创建一个 String 实例；把数值传入进去，则可以得到一个 Number 实例；把布尔值传入进去，就会得到一个 Boolean 实例。

比如：

```JavaScript
var obj = new Object('some text');
console.log(obj instanceof String);  // true
```

#### 3.3 new 和直接调用

使用 new 调用基本包装类型的构造函数，与直接调用同名的转型函数是不一样的：前者返回的是一个对象，后者返回的是一个基本类型值。

比如：

```JavaScript
var value = '25';
var number = Number(value);
console.log(typeof number); // "number"

var obj = new Number(value);
console.log(typeof obj);    // "object"
```

