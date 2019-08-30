> 转摘：[JavaScript 之 import export Vs require module.exports](http://www.jeffjade.com/2019/08/28/159-js-import-export-vs-require-module-exports/)

一直以来，JavaScript 没有模块(module)体系，无法将大程序拆分成互相依赖的小文件，再用简单的方法拼装起来。这使得针对开发大型的、复杂的项目形成了巨大障碍。

在 ES6 之前，社区制定了一些模块加载方案，最常用的有 CommonJS 和 AMD 两种：前者用于服务器(Node)，后者用于浏览器。CommonJS 和 AMD 模块，都只能在运行时确定这些东西。比如，CommonJS 模块就是对象，输入时必须查找对象属性。

ES6 在语言标准的层面上，实现了模块功能，而且实现得相当简单，完全可以取代 CommonJS 和 AMD 规范，成为浏览器和服务器通用的模块解决方案。ES6 模块的设计思想是尽量的静态化，使得编译时就能确定模块的依赖关系，以及输入和输出的变量。

但截止目前，各类引擎还未完全实现 ES6，现在之所以能够使用，是借助 Babel 工具，将 ES6 转换 ES5 再执行，`import`语法会被转码为`require`，这就导致`import`与`module.exports`，`require`与`export`出现了可以混用的理论基础。

## 一、各种导入导出模块用法

### 1.1 module.exports VS exports

为了方便，Node 为每个模块提供一个`exports`变量，指向`module.exports`（注意，这在浏览器端，是不存的，请勿用）。这其实等同在每个模块头部，有一行这样的代码：`let exports = module.exports`。这完全是为了简便而已。

通过下面的打印，可以发现两者是全等的：

```JavaScript
console.log(exports === module.exports); // true
```

![exports 与 module.exports 的关系](http://cnd.qiniu.lin07ux.cn/markdown/1479006903041.png)

在使用时更推荐的方式是使用`module.exports`而不用`exports`。因为使用`exports`的时候可能会将切断`exports`与`module.exports`的联系，使得无法正常导出模块数据：

```JavaScript
// 切断了 exports 与 module.exports 的联系
exports = (param) => { console.logparamx) }

// 因为 module.exports 被重新赋值，sayHello 无法对外输出
module.exports = 'Hello world'
exports.sayHello = function(){
  return 'hello'
}
```

有以下的一些使用技巧：

1. 如果要导出的属性可以直接在空对象上进行扩展的时候，直接使用`exports`就行了，省时省力；
2. 如果要导出的模块需要完全覆盖原本的空对象的时候，就需要使用`module.exports`；
3. 如果有不确定的地方，使用`module.exports`就行了。

### 1.2 使用方式组合

* import 与 export(const)
    
    ```JavaScript
    // export.js
    export const exportsObj = { site: "倾城之链 https://nicelinks.site" };
    
    // index.js
    import { exportsObj } from "./export";
    console.log(exportsObj.site); // 倾城之链 https://nicelinks.site
    
    // 上面也可以用 * 来整体加载
    import * as custom from "./export";
    console.log(custom.exportsObj.site);
    ```

* import 与 export default

    ```JavaScript
    // export.js
    export default { site: "倾城之链 https://nicelinks.site" };
    
    // index.js
    import exportsObj from "./export";
    console.log(exportsObj.site);
    ```

* import 与 module.exports

    ```JavaScript
    // export.js
    module.exports = { site: "倾城之链 https://nicelinks.site" };
    
    // index.js
    import exportsObj from "./export";
    console.log(exportsObj.site);
    ```

* require 与 module.exports

    ```JavaScript
    // export.js
    module.exports = { site: "倾城之链 https://nicelinks.site" };
    
    // index.js
    const exportsObj = require("./export");
    console.log(exportsObj.site); // 倾城之链 https://nicelinks.site
    ```

* require 与 export(const/var)

    ```JavaScript
    // export.js
    export const exportsObj = { site: "倾城之链 https://nicelinks.site" };
    
    // index.js
    const { exportsObj } = require("./export");
    console.log(exportsObj.site); // 倾城之链 https://nicelinks.site
    ```

* require 与 export default

    ```JavaScript
    // export.js
    export default { site: "倾城之链 https://nicelinks.site" };
    
    // index.js
    const exportsObj = require("./export").default;
    console.log(exportsObj.site); // 倾城之链 https://nicelinks.site
    ```

## 二、ES6 与 CommonJS 模块的差异

### 2.1 CommonJS 模块是运行时加载，ES6 模块是编译时输出接口

CommonJS 加载的是一个对象(即`module.exports`属性)，该对象只有在脚本运行完才会生成。而 ES6 模块不是对象，它的对外接口只是一种静态定义，在代码静态解析阶段就会生成。

因为`require`是运行时加载模块(异步)，`import`是编译时加载模块(同步)，所以`import`命令无法取代`require`的动态加载功能。如下代码可以看二者区别：

```JavaScript
// Okay
const currentModule = require(process.cwd() + moduleName);

// SyntaxError
const currentModule = import(process.cwd() + moduleName);
```

### 2.2 CommonJS 模块输出的是一个值的拷贝，ES6 模块输出的是值的引用

CommonJS 模块输出的是值的拷贝，即一旦输出一个值，而模块内部的变化就影响不到这个值。而 ES6 模块的运行机制与 CommonJS 不一样：JS 引擎对脚本静态分析的时候，遇到模块加载命令`import`，就会生成一个只读引用。等到脚本真正执行时，再根据这个只读引用，到被加载的那个模块里面去取值。因此，ES6 模块是动态引用，并且不会缓存值，模块里面的变量绑定其所在的模块。

举例说明：

```JavaScript
// export.js
export let counter = 276;
export const makeCounterIncrease = () => {
  counter++;
};

// index.js (用 require 方式)
let { counter, makeCounterIncrease } = require("./export");
console.log(counter); // 276
makeCounterIncrease();
console.log(counter); // 277
counter += 1; // 不会报错；

// index.js (用 import 方式)
import { counter, makeCounterIncrease } from "./export";
console.log(counter); // 276
makeCounterIncrease();
console.log(counter); // 277
counter += 1; // 报错：Error: "counter" is read-only.
```

