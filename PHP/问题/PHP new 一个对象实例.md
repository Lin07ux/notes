### 问题起因

下面的这段代码是能够正常的运行的：

```php
<?php
$a = new stdClass;
$b = new $a;

var_dump($a, $b);
```

运行的结果显示：`$a`和`$b`是两个不同的空对象。而且即使在`new $a`之前给`$a`添加属性并赋值，`$b`也始终是一个的空对象。

所以问题就是：为什么空对象还可以跟在`new`后面，`stdClass`有什么特殊的地方吗？

### 实际表现

其实这和`stdClass`并没有什么关系，完全是`new`的行为决定的，比如在 [psysh](http://psysh.org/) 上做一下简单的测试：

```shell
>>> $a = new Reflection;
=> Reflection {#174}
>>> $b = new $a;
=> Reflection {#177}
```

这里`new`了一个 Reflection 类的实例，和`stdClass`的表现没有区别。

当然也可以自定义一个类：

```shell
>>> class Test { public $foo = 1; }
=> null
>>> $a = new Test
=> Test {#178
     +foo: 1,
   }
>>> $a->foo = 2;
=> 2
>>> $b = new $a;
=> Test {#180
     +foo: 1,
   }
```

从这个例子中我们可以清楚的看到，改变`$a`的属性对`$b`没有任何影响（到这里也可以顺便思考一下 PHP 的一个关键字：`clone` ）。

结合上述的表现，可以得到这样的结论：**通过一个类的对象`new`出一个新对象等同于`new`原对象的类**。

### 原因

那么 PHP 是什么样的实现造成了这种表现呢？还是从源码入手来解析这个问题。

从源码中，我们可以直奔`zend_vm_def.h`中找到答案，在关于`ZEND_FETCH_CLASS`这个 opcode 的解释中，我们可以看到以下内容：

```c
ZEND_VM_HANDLER(109, ZEND_FETCH_CLASS, ANY, CONST|TMPVAR|UNUSED|CV)
{
   ...
   if (OP2_TYPE == IS_CONST) {
       ...
   } else if (Z_TYPE_P(class_name) == IS_OBJECT) {
       Z_CE_P(EX_VAR(opline->result.var)) = Z_OBJCE_P(class_name);
   } ...
   ...
}
```

去掉一些干扰的上下文，上面的内容很清晰的呈现出一个解释：如果取到的`class_name`是一个对象，则通过`Z_OBJCE_P`的宏找到它的类。所以上面的表现解释起来就很容易了。

这本身是一个很简单的问题，不用往复杂了去想。

> 如果想知道具体的`new`的实现，可以到`zend_compile.c`文件中去查看`zend_compile_new`的实现。

转摘：[一个关于 PHP 的 new 的小问题的探究](http://0x1.im/blog/php/an-issue-of-php-new.html)

