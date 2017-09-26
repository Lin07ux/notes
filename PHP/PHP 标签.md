## 标签类型

### 1. XML 型标签

这个标签中的 php 的声明不是大小写敏感的，你可以`<?PhP ... ?>`也是完全可行的。

```php
<?php echo "In PHP Tag~"?>  
```

### 2. 短标签（SGML 型标签）

短标签有两种，一种是需要在 php.ini 配置文件中配置的，另一种是不需要配置的。

**`<? ?>`**

比如：`<? echo "In Tag!" ?>`

这种标签是否发挥作用，取决于你的 PHP 配置是否开启了`short_open_tag`。

需要说明的是，一旦使用关闭了`short_open_tag`的话，`<? ... ?>`的内容是不会显示在用户界面上的，也就是这些东西直接不见了，也不会执行，就当是被 DROP 掉了吧~

**<?=...?>**

比如：`<?="In Tag!"?>`

这个标签并不需要开启`short_open_tag`就可以起作用，缺点就是这个标签相当于一个`echo`语句，所以用法也相当受到限制：

```php
// 输出一个字符串
<?='This short Tag just for echo~'?>
// 函数调用
<?=test()?>
```

### 3. ASP 风格标签

如果想要使用这种风格的标签，需要确保`asp_tags`打开。并且一定要注意的是，这个和短标签的区别是：当短标签配置是关闭的时候，短标签（包括短标签内部）的东西是不会让用户看到的！然而如果`asp_tags`关闭时，你使用这种标签就会造成他的内容被用户看到，包括 ASP 风格标签和标签内部的内容。 

```php
<% echo 'IN TAG!' %>  
```

### 4. Script 风格标签

这个标签类型大家可能之前也还是见过的：

```php
<script language=PhP>Echo 'In Tags'</script>
```

这个用法中`script`、`language`、`php`的大小写可以随意转换。

## 标签的 Trick

根据上面的介绍，可以写出如下的代码：

```php
<?php
FuNcTiON test(){
?>
<?php echo 'This is in the test function'?>
<? Echo '<br>Short Tag may be useful' ;?>

   <script language=Php>echo '<br> Now in script style !';};</script>

<br>

<?=test()?>
```

把一个`test`函数肢解在了三种标签中，最后使用`<?=?>`短标签来调用，函数的定义并没有被破坏，而且可以成功调用。



