PHP 在魔术函数`__autoload()`方法出现以前，如要引入某个类文件，需要手动的使用`include`或者`require`将所需要的文件引入。

随着项目规模的不断扩大，使用这种方式会带来一些隐含的问题：如果一个PHP文件需要使用很多其它类，那么就需要很多的require/include语句，这样有可能会造成遗漏或者包含进不必要的类文件。

如果大量的文件都需要使用其它的类，那么要保证每个文件都包含正确的类文件肯定是一个噩梦。

## 一、autoload 机制

### 1.1 概述

首先看一个自动加载的例子：

```php
<?php
 function __autoload($classname) {
  $classpath="./".$classname.'.class.php';
  
  if (file_exists($classpath)) {
    require_once($classpath);
  } else {
    echo 'class file'.$classpath.'not found!';
  }
}
 
 $person = new Person(”Altair”, 6);
 var_dump ($person);
 ```

可以看出`autoload`至少要做三件事情：

* 第一件事是根据类名确定类文件名；
* 第二件事是确定类文件所在的磁盘路径(这个例子是最简单的情况，类与调用它们的 PHP 程序文件在同一个文件夹下)；
* 第三件事是将类从磁盘文件中加载到系统中。这一步最简单，只需要使用`include/require`即可。

要实现第一步，第二步的功能，必须在开发时约定类名与磁盘文件的映射方法，只有这样才能根据类名找到它对应的磁盘文件。所以要**用自动加载的时候需要配合一定的规则来使用**。

但是这样也会遇到问题：如果在一个系统的实现中，需要使用很多其它的类库，这些类库可能是由不同的开发人员编写的，其类名与实际的磁盘文件的映射规则不尽相同。这时如果要实现类库文件的自动加载，就必须在`__autoload()`函数中将所有的映射规则全部实现，这样的话`__autoload()`函数有可能会非常复杂，甚至无法实现。即便能够实现，也会给将来的维护和系统效率带来很大的负面影响。

为了解决这个问题，就需要先了解下 autoload 实现的机制。

### 1.2 实现

PHP 在实例化一个对象时（实际上在实现接口，使用类常数或类中的静态变量，调用类中的静态方法时都会如此），首先会在系统中查找该类（或接口）是否存在，如果不存在的话就尝试使用 autoload 机制来加载该类。而 autoload 机制的主要执行过程为：

1. 检查执行器全局变量函数指针`autoload_func`是否为 NULL。
2. 如果`autoload_func`不为 NULL，则直接执行`autoload_func`指针指向的函数用来加载类，不再进行后续的步骤。
3. 如果`autoload_func == NULL`，则查找系统中是否定义有`__autoload()`函数。
4. 如果没有定义`__autoload()`函数，则报告错误并退出。
5. 如果定义了`__autoload()`函数，则执行`__autoload()`尝试加载类，并返回加载结果。

可以看到，PHP 提供了两种方法来实现自动装载机制：一种是使用用户定义的`__autoload()`函数，这通常在 PHP 源程序中来实现；另外一种就是设计一个函数，将`autoload_func`指针指向它，这通常使用 C 语言在 PHP 扩展中实现。

如果既实现了`__autoload()`函数，又实现了`autoload_func`(将`autoload_func`指向某一 PHP 函数)，那么只执行`autoload_func`函数。

将`autoload_fuunc`指向自定义的一个函数则需要用到 SPL autoload 机制。

## 二、SPL autoload 机制

SPL 是`Standard PHP Library`(标准 PHP 库)的缩写。它是 PHP5 引入的一个扩展库，其主要功能包括 autoload 机制的实现及包括各种 Iterator 接口或类。

SPL autoload 机制的实现是通过将函数指针`autoload_func`指向自己实现的具有自动装载功能的函数来实现的。SPL 有两个不同的函数**`spl_autoload`**、**`spl_autoload_call`**，通过将`autoload_func`指向这两个不同的函数地址来实现不同的自动加载机制。

### 2.1 spl_autoload

`spl_autoload`是 SPL 实现的默认的自动加载函数，功能比较简单，接收两个参数：

* 第一个参数是`$class_name`，表示类名，
* 第二个参数`$file_extensions`是可选的，表示类文件的扩展名，可以在`$file_extensions`中指定多个扩展名，护展名之间用分号隔开即可；如果不指定的话，它将使用默认的扩展名`.inc`或`.php`。

spl_autoload 首先将`$class_name`变为*小写*，然后在所有的`include path`中搜索`$class_name.inc`或`$class_name.php`文件(如果不指定`$file_extensions`参数的话)。如果找到，就加载该类文件。

比如，可以手动使用`spl_autoload(”Person”, “.class.php”)`来加载`Person`类。

实际上，它跟`require/include`差不多，不同的它可以指定多个扩展名。同样，它也有`require/include`一样的弊端。

那么怎样让 spl_autoload 自动起作用呢，也就是将 autoload_func 指向 spl_autoload？答案是使用**`spl_autoload_register`**函数。在 PHP 脚本中第一次调用`spl_autoload_register()`时不使用任何参数，就可以将 autoload_func 指向 spl_autoload。

通过上面的说明可以知道，spl_autoload 的功能比较简单，而且它是在 SPL 扩展中实现的，无法扩充它的功能。如果想实现自己的更灵活的自动加载机制就需要用到`spl_autoload_call`函数了。

### 2.2 spl_autoload_call

在 SPL 模块内部，有一个全局变量`autoload_functions`，它本质上是一个 HashTable，不过我们可以将其简单的看作一个链表，链表中的每一个元素都是一个函数指针，指向一个具有自动加载类功能的函数。

spl_autoload_call 本身的实现很简单，只是简单的按顺序执行这个链表中每个函数。在每个函数执行完成后都判断一次需要的类是否已经加载，如果加载成功就直接返回，不再继续执行链表中的其它函数。如果这个链表中所有的函数都执行完成后类还没有加载，spl_autoload_call 就直接退出，并*不向用户报告错误*。因此，**使用了 spl_autoload_call 机制，并不能保证类就一定能正确的自动加载**，关键还是要看你的自动加载函数如何实现。

那么自动加载函数链表`autoload_functions`是谁来维护呢？就是前面提到的**`spl_autoload_register()`**函数。它可以将用户定义的自动加载函数注册到这个链表中，并将 autoload_func 函数指针指向 spl_autoload_call 函数（注意有一种情况例外，具体是哪种情况留给大家思考）。我们也可以通过`spl_autoload_unregister()`函数将已经注册的函数从`autoload_functions`链表中删除。

前面说过，当 autoload_func 指针非空时，就不会自动执行`__autoload()`函数了，现在 autoload_func 已经指向了`spl_autoload_call`，如果我们还想让`__autoload()`函数起作用应该怎么办呢？当然还是使用`spl_autoload_register(__autoload)`调用将它注册到`autoload_functions`链表中。


## 三、自动加载总结

通过上面的介绍，可以找到一个足以应对很多人编写的不同类的自动加载的实现：根据每个类库不同的命名机制实现各自的自动加载函数，然后使用`spl_autoload_register`分别将其注册到 SPL 自动加载函数队列中就可了。这样就不用维护一个非常复杂的`__autoload`函数了。

也即是说：每个开发者、机构发布的代码，都自行实现一个能自动加载自身类的函数，然后使用`spl_autoload_register`函数将其注册到 SPL 自动加载函数队列中。从而将原本可能很复杂的、难以维护的`__autoload()`函数拆解成一个个的独立的自动加载函数了，可以由每个开发者自行维护。这样就提高了代码的可复用性和可维护性。

关于自动加载的效率问题，很多人认为使用 autoload 会降低系统效率，甚至干脆提议为了效率不要使用 autoload。其实 autoload 机制本身并不是影响系统效率的原因，甚至它还有可能提高系统效率，因为它不会将不需要的类加载到系统中。

实际上，影响 autoload 机制效率本身恰恰是用户设计的自动加载函数。如果它不能高效的将类名与实际的磁盘文件(注意，这里指实际的磁盘文件，而不仅仅是文件名)对应起来，系统将不得不做大量的文件是否存在的判断(需要在每个`include path`中包含的路径中去寻找)，而判断文件是否存在需要做磁盘 I/O 操作，众所周知磁盘 I/O 操作的效率很低，因此这才是使得 autoload 机制效率降低的罪魁祸首!

因此，在系统设计时，需要定义一套清晰的将类名与实际磁盘文件映射的机制。这个规则越简单越明确，autoload 机制的效率就越高。这可以查看和遵循 PSR-0 甚至 PSR-4 来实现。

> PSR 标准参考网站：
> [PHP-FIG 官网](http://www.php-fig.org/)
> [中文翻译 Github](https://github.com/hfcorriez/fig-standards)

本文转摘自：[PHP autoload 机制详解](https://segmentfault.com/a/1190000006188247)

