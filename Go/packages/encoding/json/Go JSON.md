### 1. 忽略某个字段

如果想在 json 序列化/反序列化的时候忽略掉结构体中的某个字段，可以在字段的 tag 中使用`json:"-"`来忽略该字段。如：

```go
type Person struct {
  Name   string  `json:"name"`
  Age    int64
  Weight float64 `json:"-"` // Weight 字段在序列化/反序列化时将会被忽略
}
```

### 2. 忽略空值字段

当 struct 中年的字段没有有效值时，可以使用`json:"name,omitempty"`tag 来将其忽略掉。

其含义是：当字段值为其所属类型的零值时，在进行 JSON 序列化和反序列化的时候会忽略该字段。

所以，当字段是指针类型的时候，字段值为 nil 就会被忽略；当字段是 int 类型时，字段值为 0 就会被忽略。以此类推。

示例：

```go
type User struct {
  Name  string   `json:"name"`
  Email string   `json:"email,omitempty"`
  Hobby []string `json:"hobby,omitempty"`
}
```

### 3. 处理字符串格式的数字

有些时候，数据传输过程中会使用字符串类型的数值，可以使用`json:"name,string"`的方式来从字符串中解析相应的值，或者将数值序列化为字符串格式。

例如：

```go
// ID 和 Score 字段指定使用 string 类型序列化和反序列化
type Card struct {
  ID    int64   `json:"id,string"`
  Score float64 `json:"score,string"`
}

func intAndStringDemo() {
  jsonStr := `{"id": "1234567","score": "88.50"}`
  var c1 Card
  if err := json.Unmarshal([]byte(jsonStr), &c1); err != nil {
    fmt.Printf("json.Unmarshal jsonStr failed, err: %v\n", err)
    return
  }
  fmt.Printf("c1: %#v\n", c1) // c1:main.Card{ID:1234567, Score:88.5}
}
```