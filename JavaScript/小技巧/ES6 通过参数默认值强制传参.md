ES6 中可以为函数、方法的参数提供默认值，而且参数的默认值是在被实际使用(也就是调用了方法，但是未给该参数传值)时才会被计算得到具体的值。

利用这个特性，可以为参数设置函数执行的结果作为默认值，从而实现强制传参的目的。

比如：

```JavaScript
/**
* Called if a parameter is missing and the default value is evaluated.
*/
function mandatory () {
    throw new Error('Missing parameter')
}

function foo (mustBeProvided = mandatory()) {
    return mustBeProvided
}

foo()  // Uncaught Error: Missing parameter
foo(1) // 1
```

更进一步的，还可以在设置参数默认值的时候，传入相关的参数，从而提供更准确的错误信息。如下：

```JavaScript
/**
* Called if a parameter is missing and the default value is evaluated.
*/
function mandatory (name) {
    throw new Error(`Missing parameter: the ${name} parameter needs to pass in a value`)
}

function foo (mustBeProvided = mandatory('mustBeProvided')) {
    return mustBeProvided
}

foo()  // Uncaught Error: Missing parameter: the mustBeProvided parameter needs to pass in a value
foo(1) // 1
```

