### 1. uuid

[gofs/uuid](https://github.com/gofrs/uuid) 是 UUID 的纯 Go 实现。

使用示例：

```go
package main

import (
	"log"
	"github.com/gofrs/uuid"
)

// Create a Version 4 UUID, panicking on error.
// Use this form to initialize package-level variables.
var u1 = uuid.Must(uuid.NewV4())

func main() {
	// Create a Version 4 UUID.
	u2, _ := uuid.NewV4()

	// Parse a UUID from a string.
	s := "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
	u3, _ := uuid.FromString(s)
}
```

### 2. slug

[gosimple/slug](https://github.com/gosimple/slug) 用来生成 URL 友好型 slugify 库，支持多种语言。

使用示例：

```go
package main

import (
	"fmt"
	"github.com/gosimple/slug"
)

func main() {
	text := slug.Make("Hellö Wörld хелло ворлд")
	fmt.Println(text) // Will print: "hello-world-khello-vorld"

	someText := slug.Make("影師")
	fmt.Println(someText) // Will print: "ying-shi"

	enText := slug.MakeLang("This & that", "en")
	fmt.Println(enText) // Will print: "this-and-that"

	deText := slug.MakeLang("Diese & Dass", "de")
	fmt.Println(deText) // Will print: "diese-und-dass"

	slug.Lowercase = false // Keep uppercase characters
	deUppercaseText := slug.MakeLang("Diese & Dass", "de")
	fmt.Println(deUppercaseText) // Will print: "Diese-und-Dass"

	slug.CustomSub = map[string]string{
		"water": "sand",
	}
	textSub := slug.Make("water is hot")
	fmt.Println(textSub) // Will print: "sand-is-hot"
}
```

### 3. limiter

[ulule/limiter](https://github.com/ulule/limiter) 是一款 Go 使用的限流器，可以限制访问的频率，可以用于 HTTP、FastHTTP 和 Gin。

使用分为五步：

1. 创建`limiter.Rate`实例，用于限制访问的频率；
2. 创建`limiter.Store`实例，用于存储访问的次数数据（如 Redis、In-Memory）；
3. 创建`limiter.Limiter`实例，接收`limiter.Rate`和`limiter.Store`实例，用于处理访问限制；
4. 创建 Web 中间件实例；
5. 在中间件实例中使用`limiter.Limiter`。

### 4.goth 第三方认证

[markbates/goth](https://github.com/markbates/goth) 封装了接入第三方认证的方法，并且内置实现了很多第三方认证的实现。

使用 goth 接入 GitHub 授权的实例：[Go 每日一库之 goth](https://mp.weixin.qq.com/s/ghw6Vr8AGXKMos9QfT_Fjg)

