> [Go 自定义 Json 序列化规则](https://mp.weixin.qq.com/s/MW2fXzRWHl5vxc15MHR_Bw)

### 1. 接口

开发过程中常会使用 JSON 作为数据传输格式，这少不了对 JSON 数据的编码解码工作。在 Go 中，标准库`encoding/json`包提供了这些能力：

* `Encoder.Encode()`和`Marshal()`实现 JSON 序列化；
* `Decoder.Decode()`和`Unmarshal()`实现 JSON 反序列化。

如果有自定义序列化和反序列化需求，则可以通过为类型实现 encoding/json 包中的 Marshaler 和 Ummarshaler 接口来完成。

```go
// Marshaler is the interface implemented by types that
// can marshal themselves into valid JSON.
type Marshaler interface {
  MarshalJSON() ([]byte, error)
}

// Unmarshaler is the interface implemented by types
// that can unmarshal a JSON description off themselves.
// The input can be assumed to be a valid encoding of
// a JSON value. UnmarshalJSON must copy the JSON data
// if it wishes to retain the data after returning.
//
// By convention, to approximate the behavior of Unmarshal itself,
// Unmarshalers implement UnmarshalJSON([]byte("null")) as a no-op.
type Unmarshaler interface {
  UnmarshalJSON([]byte) error
}
```

通过这两个接口的注释可以知道：如果类型 T 实现了 Marshaler 或 Unmarshaler 接口，就能够在对 T 类型的数据进行序列化/反序列化的时候使用自定义的逻辑来处理数据。

### 2. 实例

比如下面的 Metric 类型定义：

```go
type Metric struct {
  Name  string `json:"name"`
  Value int64  `json:"value"`
}
```

由于 Metric 中的`Value`字段定义为 int64 类型。所以从外部 JSON 获取数据时，如果数据中的`value`字段为浮点数，反序列化操作就会出现错误：

```go
func main() {
  var metric Metric
  err := json.Unmarshal([]byte(`{"name": "tq", "value": 13.14}`), &metric)
  if err != nil {
    panic(err)
  }
  fmt.Println(metric)
}
```

> 正常情况不应该有这种数据情况，但现实中是可能会出现的。

错误信息如下：

```
panic: json: cannot unmarshal number 13.14 into Go struct field Metric.value of type int64
```

在不改变原有结构体定义的情况下，可以通过为结构体定义`UnmarshalJSON()`方法，以实现 Unmarshaler 接口，从而可以使用自定义的逻辑来处理数据。

#### 2.1 简单实现

可以考虑再定义一个含有 float64 类型的`Value`字段的类型，然后对它进行反序列，之后再将得到的结果赋值到 Metric 类型的变量中：

```go
func (m *Metric) UnmarshalJSON(data []byte) error {
  type tmp struct {
    Name  string  `json:name`
    Value float64 `json:value`
  }
  t := &tmp{
    Name: m.Name,
    Value: float64(m.Value),
  }
  err := json.Unmarshal(data, t)
  if err != nil {
    return err
  }
  
  m.Name = t.Name
  m.Value = int64(t.Value)
  return nil
}
```

这样做能解决问题，但是并不优雅，特别是当原始结构体的字段较多时，临时结构体也要有大量的字段定义。但其实只需要定义需要更改类别的字段即可。

#### 2.2 继承实现

可以使用结构体的继承特性，只为新的临时结构体定义需要更改类别的字段：

```go
func (m *Meteric) UnmarshalJSON(data []byte) error {
  t := &struct{
    Value float64 `json:"value"`
    *Metric
  }{
    Value: float64(m.Value),
    Metric: m,
  }
  if err := json.Unmarshal(data, &t); err != nil {
    return err
  }
  
  m.Value = int64(t.Value)
  return nil
}
```

但是这样会引出新的问题：新结构体在继承原结构体的字段的同时，也会继承原结构体的方法，包括`UnmarshalJSON()`方法。这将造成无限循环引用，最终导致堆栈溢出。

```go
fatal error: stack overflow
```

#### 2.3 仅继承字段实现

最佳解决方案是让新结构体仅继承原结构体的字段，而不继承原结构体的方法。这可以通过从原结构体直接定义一个新的类别的方式来实现。

```go
func (m *Metric) UnmarshalJSON(data []byte) error {
  type AliasMetric Metric
  t := &struct{
    Value float64 `json:"value"`
    *AliasMetric
  }{
    Value:       float64(m.Value),
    AliasMetric: (*AliasMetric)(u),
  }
  if err := json.Unmarshal(data, &t); err != nil {
    return err
  }
  m.Value = float64(t.Value)
  return nil
}
```


