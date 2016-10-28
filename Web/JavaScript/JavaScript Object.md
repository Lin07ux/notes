JavaScript 中，一切皆对象。作为最基础的 Object，在新版的 ES5、ES6 中也逐渐增加了更多的方法。

### Object.assign
该方法可以深拷贝一个对象。属于 ES6 规范。格式为：

```javascript
Object.assign(target, source);
```

这个方法会将所有可枚举的自由属性从`source`复制到`target`。并且它返回(修改后的)`target`。关于这个函数最终签名至今还在争论，最终还有可能支持多个来源(被复制的对象)。即便是使用简单的签名(signature)，也可以处理多个来源，使用`Array.prototype.reduce`：

```javascript
[source1, source2 source3].reduce(Object.assign, target);
```

在 JavaScript 中，对象是通过引用的方式调用的，所以如果将一个值为对象的变量赋值给另一个变量，那么这两个变量就会指向同一个对象，修改任一变量，另一个变量也会受到影响。如果需要两不相干，就需要用到这个方法来进行深拷贝。

```javascript
var a = { x: 0, y: 1, z: 2 };
var b = Object.assign({}, a);

b.x = 3;
console.log(a.x); // 0
```

上面的例子可以看到，b 和 a 已经引用的不是同一个对象了。

实现同样功能的，在 jQuery 中可以使用`$.extend(target, source...)`

