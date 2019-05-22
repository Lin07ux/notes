> 转摘：[重学前端学习笔记（二十）--try里面放return，finally还会执行吗？](https://segmentfault.com/a/1190000019224768)

### try 中有 return，finally 还会执行吗

**对于`try...catch...finally`语句，不论什么情况，`finally`中的语句都是会执行的**，这也是其存在的意义。

所以，即便`try`语句块中使用`return`返回了值，`finally`依旧会执行，只是`try`语句块中使用`return`后，并不立即返回值，而是在执行完`finally`语句块之后再返回。

同样的，即便`catch`语句抛出了错误，也是要在`finally`语句块执行完之后再抛出。

`try`语句块中的`break`和`continue`语句也和`return`语句一样被处理。

```JavaScript
function kaimo(){
  try{
    return 0;
  } catch(err) {
    console.log(err)
  } finally {
    console.log("a")
  }
}

console.log(kaimo());
// a
// 0
```

### try 和 finally 都有 return 语句会怎么样

前面说了，`finally`语句永远会被执行，而如果在**`finally`语句块中出现`return`语句，那么它就会覆盖`try`语句块中的`return`的结果**。

相应的，**如果抛出了错误，而`finally`语句块中有`return`语句，那么该错误就会被吞没**，而是返回`finally`中返回的值。

```JavaScript
function kaimo(){
  try{
    return 0;
  } catch(err) {
    console.log(err)
  } finally {
    return 1;
  }
}

console.log(kaimo());
// 1
```


