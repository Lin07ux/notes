> 版本：Laravel 5.6.13
> 
> 参考：[Laravel中使用FormRequest进行表单验证及对验证异常进行自定义处理](https://www.jianshu.com/p/658f979abfb7)

## 一、基本

### 1.1 创建

对于复杂的验证场景，就需要从控制器中分离验证逻辑，这时可以创建一个“表单请求”类。

表单请求是包含验证逻辑的自定义请求类，要创建表单验证类，可以使用`Artisan`命令：

```php
php artisan make:request StoreBlogPost 
```

> 生成的类位于`app\Http\Requests`目录下，如果该目录不存在，运行命令时会生成。

### 1.2 结构

默认情况下，创建好的表单请求类中会存在两个方法：

* `public function authorize()`
* `public function rules()`

这两个方法分别表示授权验证和验证规则。如果还需要自定义消息提示，可以增加一个方法：

* `public function messages ()`

## 二、自定义

默认情况下，如果表单请求授权失败，则会抛出`AccessDeniedException`异常；如果验证失败，则会抛出`Illuminate\Validation\ValidationException`异常。这两个异常都会被框架默认设置的异常处理方法捕获和处理。如果想自定义这两种情况下的返回信息，则需要理解这个处理流程，然后进行修改。

### 2.1 异常处理流程

所创建的表单请求类都是继承于`Illuminate\Foundation\Http\FormRequest`的，这个类中有两个方法：`protected function failedAuthorization ()`和`protected function failedValidation(Validator $validator)`，分别用于处理授权失败和验证失败时的处理。而这两个方法则是分别返回`Illuminate\Auth\Access\AuthorizationException`异常和`Illuminate\Validation\ValidationException`异常。

Laravel 框架中，所有的异常处理都在`app\Exceptions\Handler`类中，这个类继承于`Illuminate\Foundation\Exceptions\Handler`。在这个父类中，可以找到对应的处理方法`public function render($request, Exception $e)`。

在`render()`方法中：

* 对`AuthorizationException`异常最终会交给`protected function unauthenticated($request, AuthenticationException $exception)`方法进行处理；

* 对`ValidationException`异常最终会交给`protected function convertValidationExceptionToResponse(ValidationException $e, $request)`进行处理。

通过查看这两个异常的处理方式，可以发现，最终的处理都是基于异常类中包含的信息，分别对 ajax/json 和一般请求进行不同的回复。

所以，如果要自定义异常处理的返回，那么就可以通过设置异常的相关信息，从而定义其返回的内容。当然，也可以通过创建新的类，并完善`render()`方法，也可以实现相同的结果。

> 需要注意的是，对`AuthorizationException`异常的处理中，如果是一般的 http 请求，则会默认跳转到`route('login')`界面。如果需要跳转到不同的页面，那么就只能考虑新建异常类，并自行在`render()`方法中写入处理。

### 2.2 自定义授权错误

对于授权错误的回复，可以在表单请求类中重写`protected function failedAuthorization ()`方法。

在该方法中，可以返回一个自定义的、带有`render()`方法的异常实例，也可以直接根据逻辑传入对应错误信息的`AuthorizationException`实例。

### 2.3 自定义验证失败

验证失败时，默认会返回全部的失败信息。如果想要自定义返回，可以在表单请求类中重写`protected function failedValidation(Validator $validator)`方法。

在该方法中，可以返回一个自定义的、带有`render()`方法的异常实例，也可以根据逻辑修改要返回的`ValidationException`类实例的数据。比如，可以直接为其`public $response`属性设置一个`Illuminate\Contracts\Routing\ResponseFactory`实例。


