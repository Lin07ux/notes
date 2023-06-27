Postman 中可以定义变量，在请求中引用，从而实现数据的复用情况。

Postman 的变量分为多个层级：

* Global：`pm.globals`
* Collection：`pm.collectionVariables`
* Environment：`pm.environment`
* Data：`pm.iterationData`
* Local：`pm.variables`

各层级变量的存取方式如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/ZKl4qM-20210122145353.jpg)



