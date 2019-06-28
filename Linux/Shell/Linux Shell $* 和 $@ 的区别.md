`$*`和`$@`都表示传递给函数或脚本的所有参数，而且不包含脚本参数中的文件名(`$0`)，但是在被双引号包裹时，两者之间有些区别：

**`"$*"`会将所有的参数作为一个整体，以`"$1 $2 … $n"`的形式输出所有参数；`"$@"`会将各个参数分开，以`"$1" "$2" … "$n"`的形式输出所有参数**。


下面通过示例展示两者的不同：

```shell
#!/bin/bash
echo "\$*=" $*
echo "\"\$*\"=" "$*"

echo "\$@=" $@
echo "\"\$@\"=" "$@"

echo "print each param from \$*"
for var in $*
do
    echo "$var"
done

echo "print each param from \$@"
for var in $@
do
    echo "$var"
done

echo "print each param from \"\$*\""
for var in "$*"
do
    echo "$var"
done

echo "print each param from \"\$@\""
for var in "$@"
do
    echo "$var"
done
```

在命令行中通过`./test.sh "a" "b" "c" "d"`，看到下面的结果：

```
$*=  a b c d
"$*"= a b c d

$@=  a b c d
"$@"= a b c d

print each param from $*
a
b
c
d

print each param from $@
a
b
c
d

print each param from "$*"
a b c d

print each param from "$@"
a
b
c
d
```

重点查看最后两段代码的输出，可以看到，`$*`和`#@`基本相同，只是后者在双引号包裹时会将各个参数当做单独的变量进行输出。


