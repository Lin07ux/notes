> 转摘：[Go 中 “omitempty” 的陷阱](https://mp.weixin.qq.com/s/WuCXo9yNWsRmb-n5jA_yWA)

### 1. 基本示例

Go 中使用`encoding/json`将 struct 转 json 的时候，可以在 struct 标签中设置`json:name,omitempty`，使结构体中为零值的字段不出现在结果中。

比如，对于下面的结构体定义：

```go
type address struct {
  Street  string `json:"street"`  // 街道
  Ste     string `json:"suite"`   // 单元（可以不存在）
  City    string `json:"city"`    // 城市
  State   string `json:"state"`   // 州/省
  Zipcode string `json:"zipcode"` // 邮编
}
```

执行如下代码：

```go
func main() {
  data := `{
  "street": "200 Larkin St",
  "city": "San Francisco",
  "state": "CA",
  "zipcode": "94192"
  }`
  addr := new(address)
  json.Unmarshal([]byte(data), &addr)
  
  // 处理了一番 addr 变量...
  
  addressBytes, _ :=json.MarshalIndent(addr, "", "    ")
  fmt.Printf("%s\n", string(addressBytes))
}
```

运行结果如下：

```json
{
    "street": "200 Larkin St",
    "suite": "",
    "city": "San Francisco",
    "state": "CA",
    "zipcode": "94102"
}
```

可以看到结果中多了一行`"suite": "",`，而这则信息在原本的 json 数据中是没有的。

更好的期望是如果一个地址有 suite 号码的时候才输出，不存在就不输出。这可以通过在结构体的 json 标签定义中增加`omitempty`关键字，来表示这个字段如果没有非零值，在序列化成 json 的时候就不要包含其默认值。

将原本的结构体定义改为如下形式：

```go
type address struct {
  Street  string `json:"street"`
  Ste     string `json:"suite,omitempty"`
  City    string `json:"city"` 
  State   string `json:"state"`
  Zipcode string `json:"zipcode"`
}
```

重新运行即可得到期望的结果。

### 2. 无法忽略空的嵌套结构体

`omitempty`只能忽略字段类型的零值，而对于嵌套结构体来说，如果内嵌的结构体的每个字段都是零值，这时候`omitempty`依旧不会起作用，也就是序列化后这个空的结构体还会出现。

比如：

```go
type address struct {
  Street     string     `json:"street"`
  Ste        string     `json:"suite,omitempty"`
  City       string     `json:"city"` 
  State      string     `json:"state"`
  Zipcode    string     `json:"zipcode"`
  Coordinate coordinate `json:"coordinate,omitempty"`
}

type coordinate struct {
  Lat float64 `json:"latitude"`
  Lng float64 `json:"longitude"`
}
```

执行如下代码：

```go
func main() {
  data := `{
  "street": "200 Larkin St",
  "city": "San Francisco",
  "state": "CA",
  "zipcode": "94192"
  }`
  addr := new(address)
  _ = json.Unmarshal([]byte(data), &addr)
  
  // 处理了一番 addr 变量...
  
  addressBytes, _ :=json.MarshalIndent(addr, "", "    ")
  fmt.Printf("%s\n", string(addressBytes))
}
```

输出结果为：

```json
{
    "street": "200 Larkin St",
    "city": "San Francisco",
    "state": "CA",
    "zipcode": "94102",
    "coordinate": {
        "latitude": 0,
        "longitude": 0
    }
}
```

可以发现结果中依然出现了`"coordinate"`字段，即便已经为其加上了`omitempty`关键字。

其实这也是正常的，因为`omitempty`关键字是用来忽略为零值的字段。在代码中进行反序列化之后，得到的`addr`变量中的`Coordinate`字段是一个包含零值字段`Lat`和`Lng`的结构体，并非结构体的零值 nil，所以在序列化的时候，`Coordinate`这个字段就不会被忽略了。

为了达到想要的结果（把`Coordinate`字段忽略掉），可以把`Coordinate`字段**设置为指针类型**。这样在反序列化的时候，`addr.Coordinate`就为 nil 了。继而在序列化的时候就会被忽略掉了：

```json
{
    "street": "200 Larkin St",
    "city": "San Francisco",
    "state": "CA",
    "zipcode": "94102"
}
```

### 3. 忽略掉与零值相等的字段

同样的，由于`omitempty`会忽略值为零值的字段。那如果一个字段是特意设置为零值的，在序列化的结果中就不会输出了。

比如，对于上面示例中的`coordinate`结构体，为其字段加上`omitempty`关键字：

```go
type coordinate struct {
  Lat float64 `json:"latitude,omitempty"`
  Lng float64 `json:"longitude,omitempty"`
}
```

然后对非洲几内亚湾的“原点坐标”进行处理：

```go
  cData := `{
   "latitude": 0.0,
   "longitude": 0.0
  }`
  c := new(coordinate)
  _ = json.Unmarshal([]byte(cData), &c)

  // 具体处理逻辑...

  coordinateBytes, _ := json.MarshalIndent(c, "", "    ")
  fmt.Printf("%s\n", string(coordinateBytes))
}
```

最终得到的输出是：

```json
{}
```

这个坐标什么也没有输出！但是预期的是，如果一个地点没有经纬度信息才会忽略，而原点坐标是一个有效的坐标，不应该被忽略的。

正确的写法是将结构体内的字段也定义为指针：

```go
type cor=ordinate struct {
  Lat *float64 `json:"latitude,omitempty"`
  Lng *float64 `json:"longitude,omitempty"`
}
```

这样`Lat`和`Lng`字段的空值就从 float64 类型的 0.0 变成了指针类型的 nil。然后就能得到正确的原点坐标的经纬度输出了：

```json
{
    "latitude": 0,
    "longitude": 0
}
```

