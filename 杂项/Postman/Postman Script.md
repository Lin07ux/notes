Postman 基于 Node.js 提供了一个非常强大的 JavaScript 运行时，允许用户动态的修改请求数据和处理响应数据。使用 Postman 提供的脚本能力，可以实现接口测试、动态参数、在请求间共享数据等需求。

可以设置集合、文件夹、请求三个级别的脚本，影响的请求的范围依次缩小。在请求的`Pre-request Script`和`Tests`标签中编辑脚本的时候，会有弹出窗的补全提示，但在编辑 Collection 的脚本的时候没有补全提示：

![Postman Script Auto Complete](http://cnd.qiniu.lin07ux.cn/markdown/1610528710-postman-script-autocomplete.gif)

## 一、脚本类别

Postman 的脚本分为 Pre-request 脚本和 Test 脚本：前者可以用来在请求前进行相关参数的调整和记录，后者可以用来对响应的数据进行验证。

### 1.1 Pre-request 脚本

Pre-request 脚本会在请求发出之前执行，所以可以在 Pre-request 脚本中调整请求的参数、请求头、环境变量等数据。

如下，即为一个 Pre-request 示例：

```JavaScript
pm.collectionVariables.set("name_email", pm.collectionVariables.get("name") + "_vip");
```

这个示例会在请求前，将集合中的`name_email`变为集合变量`name`与字符串`_vip`拼接后的值：

![Postman Pre-request Script](http://cnd.qiniu.lin07ux.cn/markdown/1610542019-pre-request-script.jpg)

### 1.2 Test 脚本

由于 Test 脚本是在得到响应之后执行，所以一般会在 Test 脚本中编写针对响应数据的验证的代码，从而验证请求是否被正确处理了。

在 Test 脚本中，可以使用`pm.test()`断言来执行相关的判断。对于常用的判断逻辑，Postman 也都提供了脚本片段，直接点击就会插入到当前的 Test 脚本编辑区域中。

`pm.test()`方法需要两个参数：

* 测试的名称，一个自定义的字符串，用来标识测试的内容，会在测试结果输出中用到；
* 测试方法，该方法可以返回`true`或`false`来表示测试是否通过，也可以使用 [ChaiJS BDD](https://www.chaijs.com/api/bdd/) 语法和`pm.expect`语法来测试响应数据。

比如，下面测试响应的状态是否是 200：

```JavaScript
pm.test("Status test", function () {
    pm.response.to.have.status(200);
});
```

配置好 Test 脚本之后，点击 Send 按钮发送请求，得到响应之后，该 Test 脚本就会自动执行，并在 Test Results 中展示测试结果：

![Postman Test Result Status](http://cnd.qiniu.lin07ux.cn/markdown/1610591406-test-result-status.jpg)

测试未通过时的效果类似如下：

![Postman Failed Test Status](http://cnd.qiniu.lin07ux.cn/markdown/1610591435-failed-test-status.jpg)

除了上面提到的测试，还可以测试环境变量：

```JavaScript
pm.test("environment to be production", function () {
    pm.expect(pm.environment.get("env")).to.equal("production");
});
```

或者混杂使用 ChaiJS 语法，并一次设置多个断言：

```JavaScript
pm.test("response should be okay to process", function () {
    pm.response.to.not.be.error;
    
    pm.response.to.be.ok;

    pm.response.to.be.json;

    pm.response.to.be.withBody;

    pm.response.to.have.jsonBody("");
    
    pm.response.to.not.have.jsonBody("error");
});
```

> 设置多个断言的时候，如果任何一个断言失败，则该测试就失败了；只有全部的断言都通过了，该测试才会是通过状态。

## 二、、脚本执行时机和顺序

### 2.1 单请求的脚本执行时机和顺序

在一个请求中，Postman 的脚本能够在请求生命周期中的两个阶段被执行：

1. 请求发送之前：此时会执行在`pre-request script`中配置的脚本；
2. 得到响应之后：此时会执行在`test script`中配置的脚本。

![Postman Execution Order Of Scripts](http://cnd.qiniu.lin07ux.cn/markdown/1610529608-postman-execution-order-of-scripts.png)

### 2.2 集合中的脚本执行时机和顺序

对于每一个从属于集合的请求，其脚本的执行顺序如下：

1. 为所属集合设置的 Pre-request 脚本；
2. 为所属文件夹设置的 Pre-request 脚本；
3. 为请求所设置的 Pre-request 脚本；
4. 为所属集合设置的 Test 脚本。
5. 为所属文件夹设置的 Test 脚本；
6. 为请求所设置的 Test 脚本；

流程图如下所示：

![Postman Collection Script Exec Order](http://cnd.qiniu.lin07ux.cn/markdown/1610540502-postman-exec-order.png)

可以看到，**对于集合中的每个请求来说，不论是 Pre-request 脚本，还是 Test 脚本，都是按照集合级脚本、文件夹级脚本、请求级脚本的顺序来执行。**


