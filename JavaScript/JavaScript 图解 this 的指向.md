JavaScript 中的 this 关键字，在不同的场景下，会化身不同的对象，是一个比较容易混乱的概念。有一种观点认为，只有正确掌握了 JavaScript  中的 this 关键字，才算是迈入了 JavaScript 这门语言的门槛。在主流的面向对象的语言中（Java、C# 等)，this 含义是明确且具体的，即指向当前对象。一般在编译期绑定。而 JavaScript 中 this 在运行期进行绑定的，这是 JavaScript 中 this 具备多重含义的本质原因。

## 0x00 JavaScript this 决策树

JavaScript 由于其在运行期进行绑定的特性，this 可以是全局对象、当前对象或者任意对象，这完全取决于函数的调用方式。

JavaScript 中函数的调用有以下几种方式：作为对象方法调用、作为函数调用、作为构造函数调用、通过 apply 或 call 调用。常言道，字不如表，表不如图。为了让人更好的理解 JavaScript this 到底指向什么？下面用一张图来进行解释：

![JavaScript this 决策树](http://cnd.qiniu.lin07ux.cn/2015-10-29%20this-in-javascript.png)

上图我称之为"JavaScript this决策树"（非严格模式下）。这里没有考虑 ES6 中的箭头函数，因为箭头函数是一个特例：**箭头函数永远不会绑定属于自己的 this，它们的 this 总是绑定在定义时所在的作用域上。**也就是说，箭头函数的 this 是固定不可变的，永远是定义时的 this，哪怕使用了`apply`、`call`、`bind`方法。

下面通过例子来说明这个图如何对 this 进行判断。

## 0x01 示例

### 1. new 和 dot 调用

```JavaScript
var point = {
    x : 0,
    y : 0,
    moveTo : function(x, y) {
        this.x = this.x + x;
        this.y = this.y + y;
    }
};

point.moveTo(1, 1);
```

`point.moveTo()`函数在“JavaScript this 决策树”中进行判定的过程是这样的：

1. `point.moveTo()`函数是通过 new 调用的吗？ -- 不是，进入“否”分支；
2. `point.moveTo()`函数是通过 dot(.) 调用的吗？ -- 是的，进入“是”分支；
3. `point.moveTo()`函数里面的 this 指向 point 对象。


### 2. dot 调用和非 dot 调用

```JavaScript
var point = {
    x : 0,
    y : 0,
    moveTo : function(x, y) {
        // 内部函数
        var moveX = function(x) {
            this.x = x;
        }
        var moveY = function(y) {
            this.y = y;
        }
        moveX(x);
        moveY(y);
    }
};

point.moveTo(1, 1);

console.log(point.x, point.y, x, y);
```

此时，`point.moveTo()`函数在“JavaScript this 决策树”中进行判定的过程和上面的例子1是相同的，但区别在于：这个例子中，最终的结果是通过`point.moveTo()`函数中的`moveX()`函数和`moveY()`函数来得出的，this 是在这两个函数中进行调用的，所以最终我们应该分析的是`moveX()`和`moveY()`这两个函数的在“JavaScript this 决策树”中进行判定的过程。这两个函数的决策过程是相同的，就以`moveX()`函数来分析：

1. `moveX()`函数是通过 new 调用的吗？ -- 不是，进入“否”分支；
2. `moveX()`函数是通过 dot(.) 调用的吗？ -- 不是；
3. `moveX()`函数里面的 this 指向全局 window 对象。

最终，通过`console.log`输出的时候，`point.x`和`point.y`两个值未曾被改变，仍旧是 0；由于在`moveX()`和`moveY()`函数中，this 指向全局的 window 对象，所以 x 和 y 均被定义为全局变量，并赋值为 1。输出的结果就是：`0 0 1 1`。

### 3. new 和非 new 调用

```JavaScript
function Point(x, y) {
    this.x = x;
    this.y = y;
}

var mp = new Point(1, 1);
var np = Point(2, 2);

console.log(mp.x);
console.log(np.x);
console.log(window.x);
```

对于 mp 对象的`Point(1, 1)`函数中的 this 在“JavaScript this决策树”中进行判定的过程是这样的：

1. `Point(1, 1)`是通过 new 进行调用的吗？ -- 是的，进入“是”分支；
2. 所以`Point(1, 1)`中的 this 指向新创建的对象，也就是 mp。

相应的，`Point(2, 2)`函数中的 this 在“JavaScript this决策树”中进行判定的过程是这样的：

1. `Point(2, 2)`是通过 new 进行调用的吗？ -- 不是，进入“否”分支；
2. `Point(2, 2)`是通过 dot(.) 进行调用的吗？ -- 不是，进入“否”分支；
3. 所以，`Point(2, 2)`中的 this 指向的是全局 window 对象。（那么 np 对应的就是声明了定义，但是未赋值。）

那么，最终的输出就明朗了： `1 error 2`。

### 4. call && apply

下面是一个函数用 call 和 apply 进行调用的例子。

```JavaScript
function Point(x, y) {
    this.x = x;
    this.y = y;
    this.moveTo = functon(x, y) {
        this.x = x;
        this.y = y;
    }
}

var p1 = new Point(0, 0);
var p2 = {x: 0, y: 0};
p1.moveTo.apply(p2, [10, 10]);
console.log(p2.x);
```

在分析`p1.moveTo.apply(p2,[10,10])`函数在“JavaScript this决策树”中进行判定的过程之前，我们需要知道的一点知识就是：apply、call 函数会切换函数执行的上下文环境，改变 this 指向。下面是过程分析：

1. apply 改变了 this 指向，所以`p1.moveTo.apply(p2,[10,10])`函数实际上执行的就是：`p2.moveTo(10, 10)`（这里的`moveTo()`就是`Point()`对象中定义的函数）；
2. `p2.moveTo(10, 10)`是通过 new 进行调用的吗？ -- 不是，进入“否”分支；
3. `p2.moveTo(10, 10)`是通过 dot(.) 进行调用的吗？ -- 是的，进入“是”分支；
3. 所以这里的 this 就指向 p2 对象。

那么，最终将会重置了 p2 对象中的 x、y 属性的值，所以，输出就是：10。

### 5. 箭头函数

```JavaScript
const a = {
  a: 'a'
};
const obj = {
  getThis: () => this,
  getThis2 () {
    return this;
  }
};

obj.getThis3 = obj.getThis.bind(obj);
obj.getThis4 = obj.getThis2.bind(obj);

obj.getThis();
obj.getThis.call(a);
obj.getThis2();
obj.getThis2.call(a);
obj.getThis3();
obj.getThis3.call(a);
obj.getThis4();
obj.getThis4.call(a);
```

这个示例涉及到箭头函数以及`call`方法，分析如下：

* `obj.getThis()` 这个函数是一个箭头函数，其 this 永远指向定义时的 this，也就是 window，所以这个结果是 window。
* `obj.getThis.call(a)` 依旧是 window，因为箭头函数的 this 不可被更改。
* `obj.getThis2()` 这是使用对象的 dot 调用，所以指向的 obj 自身，结果就是 obj。
* `obj.getThis2.call(a)` 使用 call 改变了方法的 dot 调用，所以结果是 a。
* `obj.getThis3()` 这个方法是通过`obj.getThis()`方法调用`bind`方法得到的，而箭头方法不可更改 this，所以结果依旧是 window。
* `obj.getThis3.call(a)` 结果也是 window。
* `obj.getThis4()` 这个方法是`obj.getThis2()`使用`bind()`之后得到的，所以结果 obj。
* `obj.getThis4.call(a)` 由于`obj.getThis4()`已经使用过了`bind()`方法，那么他的 this 就是指向 obj 了，所以结果是 obj。

### 6. 类和箭头函数

当通过实例化一个类，并调用类中的方法时，则个别地方和上面示例 5 中会有所不同。

```JavaScript
class Obj {
  getThis = () => this
  getThis2 () {
    return this;
  }
}

const a = {
  a: 'a'
};
const obj2 = new Obj();
obj2.getThis3 = obj2.getThis.bind(obj2);
obj2.getThis4 = obj2.getThis2.bind(obj2);

obj2.getThis();
obj2.getThis.call(a);
obj2.getThis2();
obj2.getThis2.call(a);
obj2.getThis3();
obj2.getThis3.call(a);
obj2.getThis4();
obj2.getThis4.call(a);
```

这里，obj2 是一个通过 new 操作符实例化出来的 Obj 类的对象，通过他来调用 Obj 类中的方法时，依旧会遵循前面的规则：

* `obj2.getThis()` 这个方法是箭头函数，所以结果是 Obj 类的实例，也就是 obj2 自身。
* `obj2.getThis.call(a)` 箭头函数无法更改 this 指向，所以依旧是 obj2 自身。
* `obj2.getThis2()` 类中的方法中，this 指向自身，所以结果是 obj2。
* `obj2.getThis2.call(a)` 通过 call 改变了方法的 this 指向，结果是 a 对象。
* `obj2.getThis3()` 同样的，箭头函数无法改变 this 指向，结果是 obj2。
* `obj2.getThis3.call(a)` 同样，结果是 obj2。
* `obj2.getThis4()` 这个方法是经过 bind 方法修改过的，所以结果是 obj2。
* `obj2.getThis4.call(a)` 同样，结果是 obj2。

## 0x02 函数执行环境

关于 JavaScript 中，函数执行环境创建的过程，IBM developerworks 文档库中的一段描述感觉很不错，摘抄如下：

> JavaScript 中的函数既可以被当作普通函数执行，也可以作为对象的方法执行，这是导致 this 含义如此丰富的主要原因。一个函数被执行时，会创建一个执行环境（ExecutionContext），函数的所有的行为均发生在此执行环境中。
>
> 1、构建该执行环境时，JavaScript 首先会创建 arguments 变量，其中包含调用函数时传入的参数。
>
> 2、接下来创建作用域链。
>
> 3、然后初始化变量，首先初始化函数的形参表，值为 arguments 变量中对应的值。如果 arguments 变量中没有对应值，则该形参初始化为   undefined。
>
> 4、如果该函数中含有内部函数，则初始化这些内部函数。如果没有，继续初始化该函数内定义的局部变量，需要注意的是此时这些变量初始化为 undefined ，其赋值操作在执行环境（ExecutionContext）创建成功后，函数执行时才会执行。这点对于我们理解 JavaScript 中的变量作用域非常重要。
>
> 5、最后为 this 变量赋值，如前所述，会根据函数调用方式的不同，赋给 this 全局对象、当前对象等。
>
> 至此函数的执行环境（ExecutionContext）创建成功，函数开始逐行执行，所需变量均从之前构建好的执行环境（ExecutionContext）中读取。

理解这段话对于理解 Javascript 函数将大有好处。

## 0x03 转摘

1. [图解 javascript this 指向什么？ —— JackWang-CUMT](http://www.cnblogs.com/isaboy/p/javascript_this.html)
2. [【译】this 是什么？JavaScript 对象的内部工作原理](https://segmentfault.com/a/1190000018960420)


