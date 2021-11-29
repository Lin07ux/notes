在 Go 的结构体类型声明里面，字段声明后可以跟一个可选的字符串标签，类似于 Java 程序中类属性的注解。

通常情况下，结构体标签被用于提供结构体字段如何被编码为或者解码自另外一种格式的转换信息，或者是以何种形式被保存至/获取自数据库。当然，也可以使用它存储任何想要设置的“元信息”，供特定的包或自己使用。

### 1. 声明标签

**标签值使用反引号包裹，由一个空格分隔的`key:"value"`对列表**：

```go
type User struct {
  Name string `json:"name" xml:"name"`
}
```

**标签值中的`key`通常表示后面跟着的`value`是被哪个包使用的**。例如，`json`这个键会被`encoding/json`包处理使用。

**如果要在`key`中传递多个信息，通常通过逗号(`,`)分隔**。比如。使用`omitempty`来在转换时忽略空值：

```go
type User struct {
  Name string `json:"name,omitempty"`
}
```

按照惯例，如果一个字段的结构体标签里某个键的`value`被设置成了短横线`-`，那么就意味着告诉处理该结构体标签值的进程排除该字段。

如下面的设置就是在进行 JSON 编码/解码时忽略`Name`字段：

```go
type User struct {
  Name string `json:"-"`
}
```

### 2. 获取标签

结构体的标签是给反射准备的，所以要获取结构体的标签就需要使用反射相关的方法。

结构体字段类型相关的信息在反射中，使用`reflet.StructField`类型来表示：

```go
type StructField struct {
  Name string
  Type Type      // field type
  Tag  StructTag // field tag string
  ...
}
```

从`reflect.StructField`类型的定义可知，要获取结构体字段的标签信息，就需要先从反射中得到该字段，然后再从中取出`Tag`字段，或者使用`StructTag`类型的方法获取指定的标签值。

`reflect.StructTag`类型有如下方法可以用来获取标签：

* `Get(key string) string`
* `Lookup(key string) (value string, ok bool)` 获取指定标签`key`对应的

这两个方法都会获取指定标签`key`对应的`value`，区别在于，当`key`不存在时：前者返回空字符串，后者的返回值`ok`为`false`。

如下代码展示如何获取自定义标签的过程：

```go
package main

import (
	"fmt"
	"reflect"
)

type User struct {
	Name  string `json:"MyName",xml:"name",mytag:"MyName"`
	Email string `json:"email,omitempty" mytag:"MyEmail"`
}

func main() {
	u := User{"Bob", "bob@example.com"}
	t := reflect.TypeOf(u)

	for i := 0; i < t.NumField(); i++ {
		field := t.Field(i)
		fmt.Printf("Field: User.%s\n", field.Name)
		fmt.Printf("\tWhole tags: %s\n", field.Tag)
		fmt.Printf("\tTag 'mytag': %s\n", field.Tag.Get("mytag"))
		fmt.Printf("\tTag 'json': %s\n", field.Tag.Get("json"))
		fmt.Printf("\tTag 'xml': %s\n", field.Tag.Get("xml"))
	}
}
```

执行结果如下：

```
Field: User.Name
        Whole tags: json:"MyName" xml:"name" mytag:"MyName"
        Tag 'mytag': MyName
        Tag 'json': MyName
        Tag 'xml': name
Field: User.Email
        Whole tags: json:"email,omitempty" mytag:"MyEmail"
        Tag 'mytag': MyEmail
        Tag 'json': email,omitempty
        Tag 'xml': 
```

### 3. 常用标签键

常用结构体标签的键，指的是被一些常用的开源包声明使用的结构体标签`key`：

* `json` 由`encoding/json`包使用，详见`json.Marshal()`的使用方法和实现逻辑；
* `xml` 由`encoding/xml`包使用，详见`xml.Marshal()`；
* `bson` 由`gobson`包和`mongo-go`包使用；
* `protobuf` 由`github.com/golang/protobuf/proto`使用；
* `yaml` 由`gopkg.in/yaml.v2`包使用，详见`yaml.Marshal()`；
* `gorm` 由`gorm.io/gorm`包使用。


