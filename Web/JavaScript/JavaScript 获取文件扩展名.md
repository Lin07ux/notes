## 方案一 正则表达式
```js
function getFileExtension1 (filename) {
    return (/[.]/.exec(filename)) ? /[^.]+$/.exec(filename)[0] : undefined;
}
```

## 方案二 String.split() 方法
```js
function getFileExtension2 (filename) {
    return filename.split('.').pop();
}
```

## 方案三 String.slice() 和 String.lastIndexOf() 结合
```js
function getFileExtension3 (filename) {
    return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
}
```
工作原理是：

- 首先用 String.lastIndexOf() 找到文件名中最后一个'.'的位置(从 0 开始计数)。如果返回 -1 表示文件名中没有'.'存在。
- 然后将上一步的结果减 1 之后，进行无符号右移 0(就是为了转成无符号数)。这是为了对如`filename`、`.hiddenfile`这类文件名能正确处理。这类文件均没有扩展名，进行这一步操作之后，起始位置就会变成 4294967295 和 4294967294，也就是远大于文件名的长度。
- 用 String.slice() 方法将字符串从上一步处理后得到的位置开始截取到文件名结尾。截取不到字符的就返回空。
- 然后返回最终截取到的结果作为扩展名。


## 测试
前两种方法无法覆盖到一些极端的情况，第三个方案会更稳健一些。

```js
console.log(getFileExtension3(''));                            // ''
console.log(getFileExtension3('filename'));                    // ''
console.log(getFileExtension3('filename.txt'));                // 'txt'   
console.log(getFileExtension3('.hiddenfile'));                 // ''
console.log(getFileExtension3('filename.with.many.dots.ext')); // 'ext'
```


