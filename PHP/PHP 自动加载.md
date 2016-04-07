
## PHP 自动加载说明
PHP 中，如果要引入外部的 class 文件，一般会使用 require，include，require_once，include_once 来手动加载相应的文件。但是如果进行大项目开发或者写一个框架那么再用这种方法就不行了，不利于维护。此时需要动态的自动加载。

PHP5 在自动加载 PHP 类时，如果类不存在，或者没有使用 include 包含进来，Zend 引擎会自动调用系统内置的魔术函数`__autoload()`函数，但是需要自己写这个函数。PHP5.2 以及更高的版本后，可以使用`spl_autoload_register`函数自定义自动加载处理函数。当没有调用此函数，默认情况下会使用 SPL 自定义的`spl_autoload`函数。

> 注意：在 CLI 模式下运行 PHP 脚本的话，`__autoload()`无效。
 
auto load 机制的主要执行过程为：
```
1：检查执行器全局变量函数指针`autoload_func`是否为null。
2：如果`autoload_func==null`， 则查找系统中是否定义有`__autoload()`函数，如果没有，则报告错误并退出。
3：如果定义了`__autoload()`函数，则执行`__autoload()`尝试加载类，并返回加载结果。
4：如果`autoload_func`不为null，则直接执行`autoload_func`指针指向的函数用来加载类。此时并不检查`__autoload()`函数是否定义。
```


## SPL 自动加载
**SPL** 是 Standard PHP Library(标准PHP库) 的缩写，是 PHP5 引入的一个扩展库，主要功能包括 autoload 机制的实现及包括各种 Iterator 接口或类。SPL autoload 机制的实现是通过将函数指针`autoload_func`指向自己实现的具有自动装载功能的函数来实现的。SPL 有两个不同的函数`spl_autoload`,`spl_autoload_call`，通过将`autoload_func`指向这两个不同的函数地址来实现不同的自动加载机制。

### 1、spl_autoload()
`spl_autoload`是 SPL 实现的默认的自动加载函数，功能比较简单。
有两个参数：
* 第一个是`$class_name`，表示类名；
* 第二个参数`$file_extensions`是可选的，表示类文件扩展名。

> 可以在`$file_extensions`中指定多个扩展名，之间用分号隔开。不指定将使用默认的扩展名`.inc`或`.php`。
 
让`spl_autoload`自动起作用是将`autoload_func`指向`spl_autoload`，方法是使用`spl_autoload_register`函数。在 PHP 脚本中第一次调用 `spl_autoload_register()`时不使用任何参数，就可以将`autoload_func`指向`spl_autoload`。

### 2、spl_autoload_call()
由`spl_autoload()`知道`spl_autoload`的功能比较简单，它是在 SPL 扩展中实现的，无法扩充它的功能。如果想实现自己的更灵活的自动加载机制就需要使用`spl_autoload_call`函数。

在 SPL 模块内部，有一个全局变量`autoload_functions`，本质上是一个 HashTable，可以看作一个链表，每个元素都是一个函数指针，指向一个具有自动加载类功能的函数。 

`spl_autoload_call`会按顺序执行这个链表中每个函数，在每个函数执行完成后都判断一次需要的类是否已经加载，如果加载成功就直接返回，不再继续执行链表中的其它函数。如果这个链表中所有的函数都执行完成后类还没有加载，`spl_autoload_call`会直接 退出，并不向用户报告错误。

> 使用了`autoload`机制，不能保证类就一定能正确的自动加载。在 php5 中的标准库方法`spl_autoload`相当于实现自己的`__autoload`。

### 3、spl_autoload_register()
`spl_autoload_register()`函数是用来注册`__autolad`函数到 SPL __autoload 栈中。如果该栈中的函数尚未激活，则激活它们。

函数原型：
`bool spl_autoload_register ([ callback $autoload_function ] )` 

参数`autoload_function`欲注册的自动装载函数。如果没有提供任何参数，则自动注册autoload 的默认实现函数`spl_autoload()`。

如果在你的程序中已经实现了`__autoload`函数，它必须显式注册到`__autoload`栈中。因为`spl_autoload_register()`函数会将 Zend Engine 中的`__autoload` 函数取代为`spl_autoload()`或`spl_autoload_call()`。

如果需要多条 autoload 函数，`spl_autoload_register()`满足了此类需求。它实际上创建了 autoload 函数的队列，按定义时的顺序逐个执行。相比之下，`__autoload()`只可以定义一次。





