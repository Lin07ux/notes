在日常开发中遇到一个数字千分位格式化，所谓的数字千分位格式化，即从个位数起，每三位之间加一个逗号。例如“10,000”。

下面的几个方法可以将整数格式化成这样的千分位格式。

转摘：[JS货币格式化](https://github.com/lishengzxc/bblog/issues/15)

### 方法一
首先把数字转换成字符串，然后打散为数组，再从末尾开始，逐个把数组中的元素插入到新数组（result）的开头。每插入一个元素，counter 就计一次数（加1），当 counter 为 3 的倍数时，就插入一个逗号，但是要注意开头（i 为 0 时）不需要逗号。最后通过调用新数组的 join 方法得出结果。

```js
function toThousands(num) {
    var result = [ ], counter = 0;
    num = (num || 0).toString().split('');
    for (var i = num.length - 1; i >= 0; i--) {
        counter++;
        result.unshift(num[i]);
        if (!(counter % 3) && i != 0) { result.unshift(','); }
    }
    return result.join('');
}
```

### 方法二
改良版方法一，不把字符串打散为数组，始终对字符串操作。

```js
function toThousands(num) {
    var result = '', counter = 0;
    num = (num || 0).toString();
    for (var i = num.length - 1; i >= 0; i--) {
        counter++;
        result = num.charAt(i) + result;
        if (!(counter % 3) && i != 0) { result = ',' + result; }
    }
    return result;
}
```

### 方法三
也可以使用正则表达式来匹配字符串。

通过正则表达式循环匹配末尾的三个数字，每匹配一次，就把逗号和匹配到的内容插入到结果字符串的开头，然后把匹配目标（num）赋值为还没匹配的内容（RegExp.leftContext）。此外，还要注意：

* 如果数字的位数是 3 的倍数时，最后一次匹配到的内容肯定是三个数字，但是最前面的三个数字前不需要加逗号；
* 如果数字的位数不是 3 的倍数，那num变量最后肯定会剩下 1 到 2 个数字，循环过后，要把剩余的数字插入到结果字符串的开头。

```js
function toThousands(num) {
    var num = (num || 0).toString(), re = /\d{3}$/, result = '';
    while ( re.test(num) ) {
        result = RegExp.lastMatch + result;
        if (num !== RegExp.lastMatch) {
            result = ',' + result;
            num = RegExp.leftContext;
        } else {
            num = '';
            break;
        }
    }
    if (num) { result = num + result; }
    return result;
}
```

### 方法四
截取末尾三个字符的功能可以通过字符串类型的slice、substr或substring方法做到。这样就可以避免使用正则表达式。

```js
function toThousands(num) {
    var num = (num || 0).toString(), result = '';
    while (num.length > 3) {
        result = ',' + num.slice(-3) + result;
        num = num.slice(0, num.length - 3);
    }
    if (num) { result = num + result; }
    return result;
}
```

### 方法五
先把数字的位数补足为3的倍数，通过正则表达式，将其切割成每三个数字一个分组，再通过join方法添加逗号，最后还要把补的0移除。

```js
function toThousands(num) {
    var num = (num || 0).toString(), temp = num.length % 3;
    switch (temp) {
        case 1:
            num = '00' + num;
            break;
        case 2:
            num = '0' + num;
            break;
    }
    return num.match(/\d{3}/g).join(',').replace(/^0+/, '');
}
```

### 方法六
正则表达式正向前瞻，(javascript是不支持后瞻)

```js
function toThousands(num) {
    return (num || 0).toString().replace(/(\d)(?=(?:\d{3})+$)/g, '$1,');
}
```


