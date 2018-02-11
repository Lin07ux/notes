### 问题

JavaScript (a == 1 && a == 2 && a == 3) 可能为 true 吗？

### 分析

这个问题初看起来，是不可能的：一个变量，不可能即等于 1，又等于 2，还等 3。JavaScript 中最奇特的数应该是`NaN`但是也不符合这个特征。

但是需要注意的是，这里进行判断使用的是`==`符号，也就是说，在判断中，会允许进行隐式变换，而就是这一点，可以使得该等式成立。

当使用`==`时，如果两个参数的类型不一样，那么 JS 会尝试将其中一个的类型转换为和另一个相同。在这里左边对象，右边数字的情况下，会首先尝试调用`valueOf`如果可以调用的话）来将对象转换为数字，如果失败，再调用`toString`。

### 解决

自定义变量 a 的`toString`或`valueOf`方法，每次将变量 a 变成字符串或数值的时候，改变一次返回值，从而就能满足判断条件：

```js
const a = {
    i: 1,
    toString: function () {
        return this.i++
    }
}

if(a == 1 && a == 2 && a == 3) {
    console.log('Hello World!');
}
```

当然，还有其他的方法可以实现，比如将使用如下的方式：

```js
with({
  get a() {
    return Math.floor(Math.random()*4);
  }
}){
  for(var i=0;i<1000;i++){
    if (a == 1 && a == 2 && a == 3){
      console.log("after "+(i+1)+" trials, it becomes true finally!!!");
      break;
    }
  }
}
```

### 转摘

[JavaScript (a == 1 && a == 2 && a == 3) 可能为 true 吗？](https://www.tuicool.com/articles/3uEnIzv)


