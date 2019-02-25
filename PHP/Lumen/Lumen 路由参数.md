Lumen 中的路由参数会优先按照名称来生成对应的回调的参数列表。如果路由参数名称和回调参数的名称不同，则会优先获取回调参数的默认值。在调用前，还会将获取到的回调依赖参数值数组和剩余的路由参数值数组进行合并，合并后的数组作为调用时的参数值。

如，对于如下的路由：

```php
$router->get('companies/{cId}/employees/{id}', function ($companyId, $employeeId = null) {
    return $companyId . '-' . $employeeId;
})
```

在访问`companies/1/employees/2`时，回调函数会接收到三个参数，分别为：`null`、`'1'`、`'2'`。这是由于路由参数和回调参数的名称不匹配，而且回调中的参数`$employeeId`有默认值`null`，解析得到的回调依赖参数的值为`[null]`，与路由参数`['1', '2']`合并后，就得到实际调用时回调函数的列表`null`、`'1'`、`'2'了。

如果将路由改成如下形式：

```php
$router->get('companies/{companyId}/employees/{employeeId}', function ($companyId, $employeeId = null) {
    return $companyId . '-' . $employeeId;
})
```

此时再访问`companies/1/employees/2`，回调函数实际只会接受到两个参数，而且值分别为`'1'`、`'2'`。这样就是正常预期的结果了。

综上：**Lumen 中，路由参数和相应回调的参数名称应尽量保持相同，以避免意外情况**。

