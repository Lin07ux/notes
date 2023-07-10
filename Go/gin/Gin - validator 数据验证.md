> 转摘：[「Go框架」深入解析gin中使用validator包对请求体进行验证](https://mp.weixin.qq.com/s/2YJsrK5Ifyy7x7CDbGXTtw)

gin 框架在通过 bind 类方法解析数据的时候，可以通过为接收数据的结构体字段增加 tag 的方式自动完成验证，如果验证不通过则会返回相关的错误信息。

### 0. 示例

gin 框架的数据验证借助的是 [go-playground/validator](https://github.com/go-playground/validator) 三方包来完成的，为了完成数据验证，需要经过如下步骤：

1. 定义结构体，并为字段设置验证规则；
2. 定义验证该结构体数据的函数；
3. 注册该结构体的验证方法；
4. 调用 bind 类解析方法。

下面定义了一个 User 结构体和其对应的验证方法，而且在结构体定义中，为`Email`字段定义了`binding:"required,email"`的验证规则：

```go
import (
  "net/http"
  
  "github.com/gin-gonic/gin"
  "github.com/gin-gonic/gin/binding"
  "github.com/go-playground/validator/v10"
)

// 1. 定义结构体
type User struct {
  FirstName string `json:"fname"`
  LastName  string `json:"lname"`
  Email     string `binding:"required,email"`
}

// 2. 定义验证函数
func UserStructLevelValidation(sl validator.StructLevel) {
  user := sl.Current().Interface().(User)
  
  if len(user.FirstName) == 0 && len(user.LastName) == 0 {
    sl.ReportError(user.FirstName, "FirstName", "fname", "fnameorlname", "")
    sl.ReportError(user.LastName, "LastName", "lname", "fnameorlname", "")
  }
  
  // plus can to more, even with different tag than "fnameorlname"
}


func main() {
  route := gin.Default()

  // 3. 注册验证方法
  if v, ok := binding.Validator.Engine().(*validator.Validate); ok { 
    v.RegisterStructValidation(UserStructLevelValidation, User{})
  }
  
  route.POST("/user", validateUser)
  route.Run(":8080")
}

func validateUser(c *gin.Context) {
  var u User
  // 4. 调用 bind 方法解析并验证
  if err := c.ShouldBindJSON(&u); err == nil {
    c.JSON(http.StatusOK, gin.H{"message": "User validation successful."})
  } else {
    c.JSON(http.StatusBadRequest, gin.H{
      "message": "User validation failed!",
      "error": err.Error(),
    })
  }
}
```

### 1. 结构体验证规则定义

gin 框架中，要为结构体字段设置验证规则，需要为其设定`binding`标签，标签值即为验证规则。之所以是`binding`标签，这是在 gin-gonic/gin/binding 中定义的：

```go
func (v *defaultValidator) Engine() any {
  v.lazyinit()
  return v.validate
}

func (v *defaultValidator) lazyinit() {
  v.once.Do(func() {
    v.validate = validator.New()
    v.validate.SetTagName("binding")
  })
}
```

由于 gin 的数据验证借助于 go-playground/validator 包来实现的，所以 gin 支持的验证规则可以参考 go-playground/validator 文档，部分规则如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/255ff5242fd6fbe5828111cdd0c86172.jpg)

### 2. 定义验证函数

用于结构体字段验证的函数，需要定义为 go-playgroun/validator 包中的`type StructLevelFunc func(sl StructLevel)`类型，也就是其参数应该为`StructLevel`接口类型。

`StructLevel`接口中定义用于 struct 验证所需要的方法，在自定义的验证方法中，可以通过参数的`StructLevel.ReportError()`和`StructLevel.ReportValidationErrors()`方法传递验证失败的错误信息。

### 3. 注册验证函数

在定义好结构体和其对应的验证函数之后，需要在 gin 启动之前将两者关联，这样 gin 才能调用该验证函数对结构体数据进行验证。

注册验证函数需要使用 gin-gonic/gin/binding 包，它提供了一个全局的 StructValidator 类型的`Validator`变量作为数据验证器。默认情况下，该变量被初始化为了`defaultValidator`类型实例：

```go
var Validator StructValidator = &defaultValidator{}

type defaultValidator struct {
	once     sync.Once
	validate *validator.Validate
}

func (v *defaultValidator) Engine() any {
  v.lazyinit()
  return v.validate
}

func (v *defaultValidator) lazyinit() {
  v.once.Do(func() {
    v.validate = validator.New()
    v.validate.SetTagName("binding")
  })
}
```

在注册验证函数的时候，就是获取到`defaultValidator.validate`字段，而该字段会被初始化为一个`validator.Validate`实例，并且将验证规则用的 tag 的名称设置为了`binding`，这也是为何定义结构体的时候需要在其`binding` tag 中设置验证规则的原因。

随后，通过`validator.Validate.RegisterStructValidation()`方法来进行自定义类型和相关的自定义验证函数的关联：

```go
func (v *Validate) RegisterStructValidation(fn StructLevelFunc, types ...interface{}) {
  v.RegisterStructValidationCtx(wrapStructLevelFunc(fn), types...)
}

func (v *Validate) RegisterStructValidationCtx(fn StructLevelFunCtx, types ...interface) {
  if v.structLevelFuncs == nil {
    v.structLevelFuncs = make(map[reflect.Type]StructLevelFuncCtx
  }
  
  func _, t := range types {
    tv := relfect.ValueOf(t)
    if tv.Kind() == reflect.Ptr {
      t = relfect.Indirect(tv).Interface()
    }
    
    v.structLevelFuncs[reflect.TypeOf(t)] = fn
  }
}
```

可以看到，在注册验证函数的时候，可以多个结构体类型共用一个验证函数。而且注册时，接收的函数类型即为`StructLevelFunc`，这就是为何要将自定义验证函数的参数设置为`sl StructLevelFunc`的原因。

### 4. 触发数据验证

gin 中，触发数据验证是在进行数据解析的时候，也就是调用 gin 的 bind 类方法的时候。

比如，在上面的示例中，使用了`c.ShouldBindJSON(&u)`方法来解析数据，在底层上实际调用的是 gin-gonic/gin/binding 包中的`decodeJSON()`方法，它在完成数据的解析之后，调用`validate()`函数进行数据验证：

```go
func decodeJSON(r io.Reader, obj any) error {
    ...
    return validate(obj)
}

func validate(obj any) error {
  if Validator == nil {
    return nil
  }
  return Validator.ValiadteStruct(obj)
}
```

这里的`Validator`即为注册验证函数中提到的 binding 包的全局变量`Validator`，默认是一个`binding.defaultValidator`类型实例。所以结构体的验证会通过`defaultValidator.validateStruct`方法进行验证：

```go
func (v *defaultValidator) ValidateStruct(obj any) error {
  if obj == nil {
    return nil
  }
  
  value := reflect.ValueOf(obj)
  switch value.Kind() {
  case reflect.Ptr:
    retrun v.ValidateStruct(value.Elem().Interface())
  case relfect.Struct:
    return v.validateStruct(obj)
  ...
  }
}

func (v *defaultValdator) validateStruct(obj any) error {
  v.lazyinit()
  return v.validate.Struct(obj)
}
```

可以看到，最终的验证还是通过 go-playground/validator 包来完成结构体的验证的。