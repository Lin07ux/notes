> 转摘：[Go设计模式--桥接模式，让代码既能多维度扩展又不会臃肿](https://mp.weixin.qq.com/s/O8shSU46TcgFPx3h7NGFAA)

### 1. 介绍

桥接模式(Bridge Pattern)又叫做桥梁模式、接口模式或柄体模式(Handle and Body Pattern)，指：**将抽象部分与具体实现部分分离，使它们都可以独立地变化**。属于结构型设计模式。

桥接模式的核心在于接口的抽象定义。通过组合不同接口的不同实现，可以得到不同的功能逻辑。桥接模式能够在**系统多维度扩展和臃肿度降低**方便发挥作用。

桥接模式适用于以下几种业务场景：

1. 在抽象和具体实现之间需要增加更多灵活性的场景；
2. 一个负责某块逻辑的类存在两个或多个独立变化的维度，而这些维度都需要独立进行扩展；
3. 不希望使用继承，或者因为多层继承导致系统类的个数剧增。

### 2. 组成

对于需要支持从不同的数据库导出，并生成不同类型文件的需求，可以将数据获取和生成导出文件分成两个独立的接口，通过不同的接口组合就能得到不同的导出功能。

下面是使用桥接模式生成的 UML 图：

![](https://cnd.qiniu.lin07ux.cn/markdown/1679024075-897813b4f7150030a6c6852b809765f5.png)

在有 M 个数据获取方式，N 个文件导出方式的情况下，使用桥接模式需要`M + N`个类。而如果使用类继承模式，需要将不同的实现两两组合，就要生成`M * N`个类。

### 3. 实例

针对上面的数据导出例子，首先需要定义数据获取和数据导出的相关接口：

```go
// 数据获取接口
type IDataFacter interface {
  Fetch(sql string) []interface{}
}

// 数据导出接口
type IDataExporter interface {
  Export(sql string, writer io.Writer) error
  SetFetcher(fetcher IDataFetcher)
}
```

数据导出接口中除了导出功能，还设置了`SetFetcher()`方法，是为了统一每个导出器引入数据获取器的方式。

然后再实现两个数据获取器，以支持 MySQL 和 Oracle 数据库：

```go
// MySQL 数据获取器
type MySQLDataFetcher struct {
  Config string
}

func (mf *MySQLDataFetcher) Fetch(sql string) []interface{} {
  fmt.Println("Fetch data from mysql source: " + mf.Config)
  rows := make([]interface{}, 0)
  // 插入两个随机数组成的切片，模拟要返回的数据集
  rows = append(rows, rand.Perm(10), rand.Perm(10))
  return rows
}

func NewMySQLDataFetcher(configStr string) IDataFetcher {
  return &MySQLDataFetcher{
    Config: configStr,
  }
}

// Oracle 数据获取器
type OracleDataFetcher struct {
  Config string
}

func (of *OracleDataFetcher) Fetch(sql string) []interface{} {
  fmt.Println("Fetch data from oracle source: " + of.Config)
  rows := make([]interface{}, 0)
  // 插入两个随机数组成的切片，模拟查询返回的数据集
  rows = append(rows, rand.Perm(10), rand.Perm(10))
  return rows
}

func NewOracleDataFetcher(configStr string) IDateFetcher {
  return &OracleDataFetcher {
    Config: configStr,
  }
}
```

再实现两个数据导出器，以支持 CSV 和 JSON 格式文件的导出：

```go
// CSV 文件导出器
type CsvExporter struct {
  fetcher IDataFetcher
}

func NewCsvExporter(fetcher IDataFetcher) IDataExporter {
  return &CsvExporter{fetcher}
}

func (ce *CsvExporter) SetFetcher(fetcher IDataFetcher) {
  ce.fetcher = fetcher
}

func (ce *CsvExporter) Export(sql string, writer io.Writer) error {
  rows := ce.fetcher.Fetch(sql)
  fmt.Printf("CsvExporter.Export, got %v rows\n", len(rows))
  for i, v := range rows {
    fmt.Printf("   行号: %d 值: %s\n", i+1, v)
  }
  return nil
}

// JSON 文件导出器
type JsonExporter struct {
  fetcher IDataFetcher
}

func NewJsonExporter(fetcher IDataFetcher) IDataExporter {
  return &JsonExporter{fetcher}
}

func (je *JsonExporter) SetFetcher(fetcher IDataFetcher) {
  je.fetcher = fetcher
}

func (je *JsonExporter) Export(sql string, writer io.Writer) error {
  rows := je.fetcher.Fetch(sql)
  fmt.Printf("JsonExporter.Export, got %v rows\n", len(rows))
  for i, v := range rows {
    fmt.Printf("   行号: %d 值: %s\n", i+1, v)
  }
  return nil
}
```

这两个维度的抽象接口和实现都定义好之后，客户只需要根据`IDataExporter`交互就能把整个模块运行起来，而且能够方便的使用不同的数据获取器：

```go
func main() {
  var writer bytes.Buffer

  // 从 MySQL 导出数据到 CSV 文件
  mfetcher := NewMySQLDataFetcher("mysql://localhost:3306")
  csvExporter := NewCsvExporter(mfetcher)
  csvExporter.Export("select * from xxx", &writer)
  
  // 从 Oracle 导出数据到 CSV 文件
  ofetcher := NewOracleDataFetcher("oracle://localhost:1001")
  csvExporter.SetFetcher(ofetcher)
  csvExporter.Export("select * from xxx", &writer)
  
  // 从 MySQL 中导出数据到 JSON 文件
  jsonExporter := NewJsonExporter(mfetcher)
  jsonExporter.Export("select * from xxx", &writer)
}
```

### 4. 总结

对于桥接模式而言，当不同的事物被联系到一起时，可以更换它们中的任意一个而使用不受影响。在上面的例子中，导出器是一个抽象维度，数据查询器是一个抽象维度。这两个抽象的实现类通过桥接的形式连接在一起。在这种情况下，可以替换两个抽象维度中的实现类从而搭配出不同的组合，与此同时，整体系统却不受影响。

桥接模式的优点：

* 分离抽象部分和具体实现部分；
* 提高了系统的扩展性，支持向两个或多个维度的扩展；
* 符合开闭原则；
* 利用组合，提高了代码复用率。

桥接模式的缺点：

* 增加了系统的理解和设计难度；
* 需要正确地识别系统中两个或多个独立变化的维度。（这也是桥接模式的难点）
