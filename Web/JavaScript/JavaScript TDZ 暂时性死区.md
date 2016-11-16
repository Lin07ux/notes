JavaScript 规范规定：一个*已经声明但未初始化*的变量不能被赋值，甚至不能被引用。

规范里用来声明`var/let`变量的内部方法是`CreateMutableBinding()`，初始化变量用`InitializeBinding()`，为变量赋值用`SetMutableBinding()`，引用一个变量用`GetBindingValue()`。在执行完`CreateMutableBinding()`后没有执行`InitializeBinding()`就执行`SetMutableBinding()`或者`GetBindingValue()`是会报错的，这种表现有个专门的术语（非规范术语）叫**TDZ(Temporal Dead Zone)**，通俗点说就是一个变量在声明后且初始化前是完完全全不能被使用的。

因为 var 变量的声明和初始化（成`undefined`）都是在“预处理”过程中同时进行的，所以永远不会触发 TDZ 错误。

let 的话，声明和初始化是分开的，只有真正执行到 let 语句的时候，才会被初始化。如果只声明不赋值，比如`let foo`，foo 会被初始化成`undefined`，如果有赋值的话，只有等号右侧的表达式求值成功（不报错），才会初始化成功。一旦错过了初始化的机会，后面再没有弥补的机会。这是因为赋值运算符`=`只会执行`SetMutableBinding()`，并不会执行`InitializeBinding()`，所以如果在定义并初始化的 let 变量的语句中如果错误了，那么这个变量就被永远困在了 TDZ 里，不能读取和赋值了。

比如，可以在浏览器的调试工具中进行如下的操作：

![TDZ 示例](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1479273008633.png)

在第一步中，声明并赋值一个 let 变量 map。但是在赋值的时候，由于`Map()`不带 new 不能被调用，所以就出错了。此时变量 map 就已经被困在了 TDZ 里面而无法再对其进行操作了。

于是，在第二步、第三步中，都会出错。其中，第二步的错误是因为变量 map 已经被声明过了，无法重复声明。但是第三步的错误提示其实是有问题的，应该是提示 map 未初始化错误。

const 声明的变量也有类似的问题。

转摘：[不要在控制台上使用 let/const](http://www.cnblogs.com/ziyunfei/p/6063426.html)

