闭包是 JavaScript 中一个非常重要、非常有意义的特性，但是初接触者可能会觉得闭包的概念有点难理解。而一旦明白了闭的意义，就能够很好的理解闭包。而理解闭包之前，你应该对 JavaScript 中的变量作用域和作用域链有一定的了解。

## 0x00 什么是闭包

闭包`Closure`是什么？首先我们来看一个例子：

```JavaScript
function outer() {
    var a = "In Outer()";
    function inner() {
        console.log(a);
    }

    return inner;
}
console.log(a);     // Uncaught ReferenceError: a is not defined
var b = outer();
b();        // In Outer()
```

> 我们知道，Javascript 中，函数是一个特殊的对象(数据)，也是能够被引用和传递的。所以调用上面的`outer()`函数就会把`inner()`函数作为结果返回。

根据 JavaScript 变量的作用域原则，我们分析一下上面的输出：因为变量`a`是在函数`outer()`中定义的变量，所以在`outer()`外面调用的时候，就会显示未定义；而`b`引用的是`inner()`函数，将会执行`console.log(a)`这条语句，而输出的结果是变量`a`的定义值`In Outer()`。

> 了解闭包之前，需要对 JavaScript 的作用域比较了解。关于 JavaScript 变量的作用域和作用域链，请自行搜索。

是否有感觉到执行`b()`的时候的输出有问题？`b()`是外部函数，可是却输出了`outer()`函数内部变量`a`的值！！？？

这里就是闭包的神奇之处！也是闭包被用的最多的地方：**在函数外部引用或设置函数内部的变量**。(有点像是面向对象编程中对对象私有变量的访问和设置。)

闭包是什么？官方的解释是：**闭包是一个拥有许多变量和绑定了这些变量的环境的表达式（通常是一个函数），因而这些变量也是该表达式的一部分**。

简而言之：

- 闭包就是函数的局部变量集合，只是这些局部变量在函数返回后会继续存在。
- 闭包就是函数的“堆栈”在函数返回后并不释放，我们也可以理解为这里函数堆栈并不在栈上分配而是在堆上分配。
- 当在一个函数内定义另一个函数时，就有可能会产生闭包。

上面的第二条是第一条的补充说明，抽取第一条的主谓宾--闭包是**函数的‘局部变量’集合**。只是这个局部变量是可以在函数返回后被访问。

如果再简单一点的说：***闭包就是能够读取其他函数内部变量的函数***。

> 注意：并非函数的所有局部变量都会出现在闭包中，只有被闭包函数使用的变量才会出现在闭包，不会被 GC 回收；而未被闭包函数引用的变量则在函数运行完成后就被回收了。关于这部分内容，可以查看[Javascript闭包的一些研究](http://www.cnblogs.com/antineutrino/p/4218902.html)文章中的程序7。*

> 上面的定义并不是官方定义，引用自[《理解Javascript的闭包》](http://coolshell.cn/articles/6731.html)。本文大部分内容来自该文章。

作为局部变量，是可以被函数内的代码访问的，这个和静态语言是没有差别的。而闭包的差别在于局部变量可以在函数执行结束后仍然被函数外的代码访问。这意味着，函数必须返回一个指向闭包的“引用”或将这“引用”赋值给某个外部变量，才能保证闭包中局部变量被外部代码访问。当然包含这个引用的实体应该是一个对象，因为在 JavaScript 中除了基本类型剩下的就是对象了。可惜的是，ECMAScript 并没提供相关的成员和方法来访问闭包中的局部变量。但是在 ECMAScript 中，函数对象中定义的`内部函数(inner  function)`是可以直接访问外部函数的局部变量，而正是通过这种机制，就可以通过上面的方式完成对闭包的访问了。

## 0x01 闭包是如何实现的

ECMAScript 是如何实现闭包的呢？

根据 ECMAScript 规范，每个函数运行的时候，都会关联一个执行上下文场景，这个场景包含三个部分：

- 文法环境(The LexicalEnvironment)
- 变量环境(The VariableEnvironment)
- this 绑定

其中第三点与闭包无关，就不讨论了。

函数的文法环境是静态的，是由函数代码在源代码中的位置决定的。我们可以将文法环境想象成一个对象，该对象包含了两个组件：`环境记录(Enviroment Recode)`，和`外部引用(Out Referennce)`。环境记录包含了函数内部声明的`局部变量`和`参数变量`，外部引用指向了函数对象外部的上下文执行场景。

```JavaScript
function greeting (name) {
    var text = 'Hello ' + name; // local variable
    // 每次调用时，产生闭包，并返回内部函数对象给调用者
    return function () { alert(text); }
}
var sayHello = greeting("Closure");
sayHello()  // 通过闭包访问到了局部变量 text
```

对于上面的闭包，`sayHello`函数在最下层，上层是函数`greeting`，最外层是全局场景。如下图：

![上下文执行环境](http://cnd.qiniu.lin07ux.cn/2015-08-05%20javascript-closure.png)

因此当`sayHello`被调用的时候，`sayHello`会通过上下文场景找到局部变量`text`的值，因此在屏幕的对话框中显示出”Hello Closure”。

可能你会问，为什么`greeting`函数执行完了之后，他的执行环境还存在，没有被 GC 回收呢？这是和 GC 的回收机制有关的：

*当一个变量还在被引用，那么这个变量所在的执行环境就不会被 GC 回收。*

在上面的例子中，当调用了`greeting`函数(第一者)之后，由于其返回的一个函数(第二者)是被赋值给了`sayHello`变量(第三者)，所以`greeting`的执行环境还不能被释放，于是，执行`sayHello`的时候，还是能够找到变量`text`的定义的。

## 0x02 闭包的应用场景

1、保护函数内的变量安全。以文章开头的例子为例，函数`outer()`中`a`变量只有通过函数`inner()`才能访问，而无法通过其他途径访问到，因此保护了 i 的安全性。

2、在内存中维持一个变量。依然如前例，由于闭包，函数`outer()`中`a`变量一直存在于内存中，因此每次执行`c()`，都能找到这个变量。

> 由于闭包会使 GC 不能回收内存，所以最好在闭包使用完成之后，将引用闭包的变量赋值为`null`来释放内存。

3、保护命名空间，避免污染全局变量。由于闭包中调用的变量是在一个函数中定义的局部变量，所以并不会对全局变量造成影响。

## 0x03 闭包实例

对闭包有了以上的了解之后，基本上都能正确的分析闭包了。但是可能还不太会实际应用。下面将使用几个实例来做展示，以便更好的理解闭包。

**例子1：闭包中局部变量是引用而非拷贝**

```JavaScript
function say667() {
    // Local variable that ends up within closure
    var num = 666;
    var alert = function() { alert(num); }
    num++;
    return alert;
}

var sayAlert = say667();
sayAlert();     // 667
```

代码中，虽然`num++`是在`alert`定义之后才执行的，但是输出的仍然是执行了`num++`之后的结果，因为在执行`sayAlert()`的时候，通过闭包引用了局部变量`num`，而此时的`num`已经执行了自增的操作了。

下面的代码中，则会由于执行上下文环境一直未被释放，所以每次执行`bar()`的时候，`tmp`变量都会自增一次：

```JavaScript
function foo(x) {
    var tmp = 3;
    return function (y) {
        alert(x + y + (++tmp));
    }
}
var bar = foo(2); // bar 现在是一个闭包
bar(10);
```

**例子2：在同一个函数中定义的函数，会绑定同一个闭包**

```JavaScript
function setupSomeGlobals() {
    // Local variable that ends up within closure
    var num = 666;
    // Store some references to functions as global variables
    gAlertNumber = function() { alert(num); }
    gIncreaseNumber = function() { num++; }
    gSetNumber = function(x) { num = x; }
}
setupSomeGlobals(); // 为三个全局变量赋值
gAlertNumber();     // 666
gIncreaseNumber();
gAlertNumber();     // 667
gSetNumber(12);
gAlertNumber();     // 12
```

代码中，`gAlertNumber`、`gIncreaseNumber`和`gSetNumber`这三个变量没有使用`var`操作符，所以这三个变量是全局变量，能够在`setupSomeGlobals`函数外部引用。而通过这个例子，我们可以明显看出，三个函数操作的都是同一个变量引用，也就是说，他们绑定了同一个闭包。

**例子3：当在一个循环中赋值函数时，这些函数将绑定同样的闭包**

```JavaScript
function buildList(list) {
    var result = [];
    for (var i = 0; i < list.length; i++) {
        var item = 'item' + list[i];
        result.push( function() { alert(item + ' ' + list[i]) } );
    }
    return result;
}

function testList() {
    var fnlist = buildList([1,2,3]);
    // using j only to help prevent confusion - could use i
    for (var j = 0; j < fnlist.length; j++) {
        fnlist[j]();
    }
}
testList();
```

在上面的代码中，当执行`testList()`的时候，会循环三次，执行了`buildList()`函数中压入`result`数组中的三个函数`alert(item + ' ' + list[i])`代码，但是结果都是`item3 undefined`。

这是因为，这三个函数都绑定了同一个闭包。当执行`testList()`函数的时候，`buildList()`函数已经执行完成了，此时`buildList()`函数中的局部变量`item`的值都被赋值为了`item3`，而`i`的值则被自增为`3`了。由于是一个闭包环境，所以`buildList()`函数的执行上下文环境不会被释放，保持这个状态，于是，执行`alert(item + ' ' + list[i])`代码的时候，`list[i]`就是`list[3]`，所以就是`undefined`。

这里其实就是闭包最容易引起问题的地方，而且调试的时候也很难发现。比如下面这个很常见的例子：

```HTML
<div id="divTest">
    <span>0</span> <span>1</span> <span>2</span> <span>3</span>
</div>
```

```JavaScript
$(document).ready(function() {
    var spans = $("#divTest span");
    for (var i = 0; i < spans.length; i++) {
        spans[i].onclick = function() {
            alert(i);
        }
    }
});
```

这样给每个`span`绑定了一个点击事件，但是当我们点击的时候，会发现弹出的总是“4”。结合上面的分析，你应该能够很清楚这是为什么。而如何使其不会弹出一样的值呢？也简单，通过匿名函数：

```JavaScript
$(document).ready(function() {
    var spans = $("#divTest span");
    for (var i = 0; i < spans.length; i++) {
        (function(num) {
            spans[i].onclick = function() {
                alert(num);
            }
        })(i);
    }
});
```

为什么这样就能够正常执行了呢？因为我们使用了一个匿名函数，并在定义匿名函数的时候就使其执行了(使用了`(function(){})()`的方式)。这样，在每个`for`循环中，都立即执行了一次一个匿名函数，而由于这个匿名函数的`num`变量被各个`span`元素的点击事件引用了，所以每个匿名函数都生成了一个闭包。另外，这个匿名函数被传入了变量`i`的当前值，所以最终给每个`span`绑定的事件函数中，`num`的值就各不相同了。

**例子4：外部函数的所有变量都在闭包内，即使这个变量是在内部函数定义之后声明的**

```JavaScript
function sayAlice() {
    var sayAlert = function() { alert(alice); }
    // Local variable that ends up within closure
    var alice = 'Hello Alice';
    return sayAlert;
}
var helloAlice = sayAlice();
helloAlice();       // Hello Alice
```

这个可以理解一下前面讲到的执行上下文环境。

**例子5：每次外部函数被调用的时候，都会创建一个新的闭包**

```JavaScript
function newClosure (someNum, someRef) {
    // Local variables that end up within closure
    var num = someNum;
    var anArray = [1,2,3];
    var ref = someRef;
    return function(x) {
        num += x;
        anArray.push(num);
        alert('num: ' + num +
        '\nanArray ' + anArray.toString() +
        '\nref.someVar ' + ref.someVar);
    }
}
closure1=newClosure(40,{someVar:'closure 1'});
closure2=newClosure(1000,{someVar:'closure 2'});

closure1(5);    // num:45 anArray[1,2,3,45] ref:'someVar closure1'
closure2(-10);  // num:990 anArray[1,2,3,990] ref:'someVar closure2'
```

这是由于，每次一个函数被调用的时候，宿主环境都会创建一个执行上下文环境，从而就会新创建一个闭包。所以上面的`closure1`和`closure2`两个变量并不会相互影响。

**例子6：闭包函数的作用域是在编译它的时候确定的**

```JavaScript
var f = (function() {
  var n = 10;
  return function() {
    ++n;
    console.log(n);
  }
})();

f();    // 11
```

根据我们前面的讲解，这里会输出“11”。但是下面的例子却输出了"Uncaught ReferenceError"错误，'n'变量不存在：

```JavaScript
var f0 = function() {
  ++n;
  console.log(n);
}
var f = (function() {
  var n = 10;
  return f0;
})();

f();
```

这是因为，闭包函数的作用域不是在引用或运行他的时候确定的，而是在编译他的时候确定的。为了更好的理解，看下面的这个例子：

```JavaScript
var f = (function() {
     var n = 10;
     return new Function('++n;console.log(n);');
})();

f();
```

这个已然和上面的代码一样，输出了"Uncaught ReferenceError"错误。

这是因为，使用`Function`构造器可以创建一个`function 对象`，函数体使用一个字符串指定，它可以像普通函数一样执行，并在首次执行时编译。

因此，虽然在匿名函数内部定义了此`function 对象`，但一直要到调用它的时候才会编译，即执行到“f()”时，而此时原函数的作用域已经不存在了。

而下面的代码则能正常输出：

```JavaScript
var f = (function() {
     var n = 10;
     return eval('(function(){++n;console.log(n);})');
})();

f();
```

这是因为，`eval()`函数会立即对传递给他的字符串进行解析(编译、执行)，隐藏使用`eval()`定义的函数和直接定义的效果是等价的。

> 注意：`eval()`中的`function(){...}`必须用括号扩起来，否则会报错。

## 0x04 误区

### 1. 闭包是在一个内层函数作为结果返回后才生成的

当一个函数被创建时，就被赋予了`[[scope]]`属性，该属性引用了外层语法域中的变量并防止它们被回收。所以闭包是在函数创建时就生成的。并不是说一个函数必须在它被返回之后才成为闭包。

以下就是一个函数没有被返回却是一个闭包的例子：

```js
var callLater = function(fn, args, context) {
  setTimetout(function(){fn.apply(context, args)}, 2000);
}

callLater(alert, ['hello']);
```

### 2. 外层变量的值会被复制或固化在闭包中

如下例所见，闭包会引用变量而不是变量的值。

```js
//Bad Example
//Create an array of functions that add 1,2 and 3 respectively
var createAdders = function() {
  var fns = [];
  for (var i = 0; i < 4; i ++) {
    fns[i] = (function(n) {
      return i + n;
    });
  }
  return fns;
}

var adders = createAdders();
adders[1](7); //11 ??
adders[2](7); //11 ??
adders[3](7); //11 ??
```

这三个加数函数都引用了同一个变量`i`。等到三个函数被调用的时候，`i的值已经是 4 了。

有一种解决方案是通过调用匿名函数传递每个参数。由于每次函数调用都发生在一个独立的执行上下文中，即使连续调用，我们也可以保证参数变量的唯一性。

```js
//Good Example
//Create an array of functions add 1,2 and 3 respectively
var createAdders = function() {
  var fns = [];
  for (var i = 1; i < 4; i ++) {
    (function(i){
      fns[i] = (function(n) {
        return i + n;
      });
    })(i)
  }
  return fns;
}

var adders = createAdders();
adders[1](7); //8 :-)
adders[2](7); //9 :-)
adders[3](7); //10 :-)
```

### 3. 闭包一定是内层函数

并非如此，不过，不可否认外层函数所创建的闭包毫无意思，因为它的`[[scope]]`属性只是引用了全局变量域而已，在任何情况下都是可以访问到的。对于每个函数来说闭包的产生过程都是一样的，而且每个函数都会产生闭包，记住这点很重要。

### 4. 闭包一定是匿名函数

闭包也是可以有函数名称的，只是大多数情况下，并不需要给其设置名称而已。

### 5. 闭包会导致内存泄漏

闭包本身并不会产生循环引用。只是闭包会将一些外层的变量继续保存下来，而使其暂时不会被回收而已。



