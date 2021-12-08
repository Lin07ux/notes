> 转摘：[都说Linux很重要，你会几个Linux命令？来看看这道面试题目。](https://mp.weixin.qq.com/s/BGh4eQ6zf8NNem29ou4kqg)

`grep`可以用于文件内容的搜索器，从文件中搜索出符合指定规则的内容，语法如下：

```shell
grep [-abcdDEFGHhIiJLlmnOopqRSsUVvwxZ] [-A num] [-B num] [-C[num]]
     [-e pattern] [-f file] [--binary-files=value] [--color[=when]]
     [--colour[=when]] [--context[=num]] [--label] [--line-buffered]
     [--null] [pattern] [file ...]
```

### 1. 选项

* `-r` 递归查找
* `-n` 显示匹配到的内容在文件中的行号
* `-R` 查找所有文件，包含子目录
* `-i` 匹配时忽略大小写，默认情况下会区分大小写
* `-l` 只列出匹配的文件名
* `-L` 列出不匹配的文件名
* `-w` 只匹配整个单词，而不是字符串中的一部分
* `-A <num>` 显示每个匹配的行及其之后的 num 行内容
* `-B <num>` 显示每个匹配的行及其之前的 num 行内容
* `-C <num>` 显示每个匹配的行及其之前的 num 行和之后的 num 行的内容
* `<pattern1> | <pattern2>` 显示匹配 pattern1 或 pattern2 的行

如果要显示同时包含 pattern1 和 pattern2 的行，可以使用级联的 grep 命令：

```shell
grep pattern1 files | grep pattern2
```

### 2. pattern 的特殊字符

用于搜索的 pattern 中，可以使用一些特殊的符号，来达到部分正则表达式的功能：

* `\<` 标注单词的开始
* `\>` 标注单词的结尾
* `^` 标注匹配行首
* `$` 标注匹配行尾

例如：

```shell
grep yuan* files # 匹配 chenyuan、yuannic、yuan 等
grep '\<yuan' files # 只匹配 yuanic、yuan，而不匹配 chenyuan 等其他的字符串
grep '\<yuan\>' files # 只匹配 yuan，而不匹配 chenyuan、yuanic 等其他的字符串
```


