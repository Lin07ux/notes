### 表单方法伪造

HTML 表单不支持`PUT`、`PATCH`或`DELETE`行为。所以当需要从 HTML 表单中直接调用定义了`PUT`、`PATCH`或`DELETE`的路由时，需要在表单中增加隐藏的`_method`输入标签，使用`_method`字段的值作为 HTTP 的请求方法。

> 当然，如果使用 JavaScript 来调用的话，就不会有这个问题了。

```html
<form action="/foo/bar" method="POST">
    <input type="hidden" name="_method" value="PUT">
    ...
</form>
```

也可以使用辅助函数`method_field()`来生成隐藏的`_method`标签：

```html
{{ method_field('PUT') }}
```

### CSRF 保护

指向 web 路由文件中定义的`POST`、`PUT`或`DELETE`路由的任何 HTML 表单都应该包含一个 CSRF 令牌字段，否则这个请求将会被拒绝：

```html
<form method="POST" action="/profile">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
</form>
```

或者使用`csfr_field()`方法来生成：

```html
<form method="POST" action="/profile">
    {{ csrf_field() }}
</form>
```

