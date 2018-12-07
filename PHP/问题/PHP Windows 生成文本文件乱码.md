### 问题

在 Window 中使用 PHP 的生成文件的时候，可能会遇到不论是使用`fwrite`还是`file_put_contents`写入，生成的文件总是会乱码的情况。

### 原因

可能会先尝试从编码入手尝试解决，但最终的结果往往是不理想的，尽管我们都将其转换为了 UTF-8 编码了。那么究其根本原因是什么呢？

一句话：**缺少头部 BOM**(当然，这里指的肯定不是浏览器 JavaScript 的 BOM)。

> BOM —— Byte Order Mark，中文名译作“字节顺序标记”。
> 
> UTF-8 不需要 BOM 来表明字节顺序，但可以用 BOM 来表明编码方式。字符 "Zero Width No-Break Space" 的 UTF-8 编码是`EF BB BF`。所以如果接收者收到以`EF BB BF`开头的字节流，就知道这是 UTF-8 编码了。Windows 就是使用 BOM 来标记文本文件的编码方式的。
> 
> 类似 WINDOWS 自带的记事本等软件，在保存一个以 UTF-8 编码的文件时，会在文件开始的地方插入三个不可见的字符（`0xEF 0xBB 0xBF`，即 BOM）。它是一串隐藏的字符，用于让记事本等编辑器识别这个文件是否以 UTF-8 编码。对于一般的文件，这样并不会产生什么麻烦。
> 
> 但对于 PHP 来说，BOM 是个大麻烦。PHP 并不会忽略 BOM，所以在读取、包含或者引用这些文件时，会把 BOM 作为该文件开头正文的一部分。根据嵌入式语言的特点，这串字符将被直接执行（显示）出来。由此造成即使页面的 top padding 设置为 0，也无法让整个网页紧贴浏览器顶部，因为在 html 一开头有这 3 个字符！

### 解决方法

那么如何在 PHP 中输出 BOM 呢？答案是在所有内容输出之前输出如下的内容：

```php
print(chr(0xEF).chr(0xBB).chr(0xBF));
```

当然，如果是在生成文件，可能是下面两种：

```php
fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

file_put_contents($file, chr(0xEF).chr(0xBB).chr(0xBF));
```

