> 转摘：[Gopher 需要知道的几个结构体骚操作](https://mp.weixin.qq.com/s/A4m1xlFwh9pD0qy3p7ItSA)

### 1. struct 的初始化

结构体初始化有两种方式：指定字段名称和不指定字段名称。不指定字段名称来初始化的时候，需要按照顺序列出所有字段的值。

比如：

```go
type User struct {
  Age int
  Address string
}

u := &User{21, "beijing"}
```

不指定字段名称初始化的方式有很大的问题：如果后续结构体的字段发生变化（新增、删除、字段类型变化），这样赋值就会出现错误。

比如：

```go
type User struct{
    Age int
    Address string
    Money int
}

func main() {
    // ./struct.go:11:15: too few values in User{...}
    _ = &User{21, "beijing"}
}
```

而且上面的例子能在编译期就报错，但是如果是同类型的字段调整顺序，就会出现隐性问题了。所以就有必要让用户只能采用指定字段名称的初始化方式。

### 2. NoUnkeyedLiterals

定义一个空结构体类型：

```go
// NoUnkeyedLiterals can be embedded in a struct to prevent unkeyed literals.
type NoUnkeyedLiterals struct{}
```

然后在其他结构体中作为空字段来嵌入`NoUnkeyedLiterals`：

```go
type User struct {
  _ NoUnkeyedLiterals
  Age int
  Address string
}

func main(){
    // ./struct.go:10:11: cannot use 21 (type int) as type struct {} in field value
    // ./struct.go:10:15: cannot use "beijing" (type untyped string) as type int in field value
    // ./struct.go:10:15: too few values in User{...}
    _ = &User{21, "beijing"}
}
```

这样`User`结构体就只能选择指定字段名的方式进行初始化了。

当然，虽然可以在初始时写上`struct{}{}`，但是因为占位符`_`字段是不可导出的，所以用来初始化其他包导入进来的内嵌有有`NoUnkeyedLiterals`的结构体时，同样会报错。


