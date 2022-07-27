> 转摘：[使用 expvar 暴露 Go 程序运行指标](https://mp.weixin.qq.com/s/ezs9ipJBwS_7ToMOd_wZ3g)

### 1. 简介

获取应用程序的运行指标，可以更好地了解应用的实际状况。将这些指标对接到 Prometheus、zabbix 等监控系统，能对应用程序进行持续检测，发现异常可以及时得到告警并得到处理。

与监控系统对接方式有两种：一种是 Pull（拉取），另一种是 Push（推送）。

以 Prometheus 为例，应用程序通过暴露出 HTTP 接口，让 Prometheus 周期性地通过该接口抓取指标，这就是 Pull。而 Push 是应用程序主动将指标推送给 PushGateway，然后让 Prometheus 去 PushGateway 抓取数据。

Go 标准库中有一个名为`expvar`的包，它的名字由`exp`和`var`两部分组合而成，意味着导出变量。expvar 为公共变量提供了标准化的接口，并通过 HTTP 以 JSON 的格式将这些变量暴露出去，很适合采用 Pull 的方式与监控系统进行对接。

### 2. 初步使用

expvar 是标准库，而且提供了一些开箱即用的指标。下面介绍一下该库的使用。

当`import expvar`引入该库的时候，一下`init`函数将被自动执行：

```go
func init() {
  http.HandleFunc("debug/vars", expvarHandler)
  Publish("cmdline", Func(cmdline))
  Publish("memstats", Func(memstats))
}
```

该函数在默认的 HTTP 服务上注册了路径为`/debug/vars`的 HTTP 服务，访问该路径将得到一个 JSON 格式的指标数据。因此，在开发的程序中，需要开启 HTTP 服务，才能得到 expvar 暴露出来的指标数据。

完整代码如下：

```go
package main

import (
  _ "expvar"
  "net/http"
)

func main() {
  http.ListenAndServe(":8080", nil)
}
```

将程序运行起来时候，通过 curl 请求即可得到类似如下的结果：

```shell
$ curl localhost:8080/debug/vars
{
    "cmdline":[
        "/private/var/folders/zr/75h8wk1n5czb_0dldjkxhnbc0000gn/T/GoLand/___go_build_learn_t"
    ],
    "memstats":{
        "Alloc":238816,
        "TotalAlloc":238816,
        "Sys":8997904,
        "Lookups":0,
        "Mallocs":1125,
        "Frees":87,
        ...
    }
}
```

可以看到，expvar 默认已经提供了两项指标，分别是程序执行命令(`os.Args`)和运行时内存分配(`runtime.Memstats`)。

### 3. 重点内容

expvar 中最重要的是`Publish`函数和`Var`接口，分类用于向指标接口中增加数据和定义其他的可导出数据接口。

#### 3.1 Publish

`Publish`函数可以向指标路径中添加指定的数据，这些数据在访问指标 HTTP 接口的时候将会自动展示出来。所以如果用户需要增加自己的指标数据就可以通过这个函数来添加。

例如，expvar 包的`init`函数中的`Publish("cmdline", Func(cmdline))`就是向指标中增加名称为`cmdline`的字段，其对应的数据为实现了`Var`解耦的`expvar.Func`类型的变量。

`Publish`函数的源码如下(Go 1.17.2)：

```go
// All published variables.
var (
  vars      sync.Map // map[string]Var
  varKeysMu sync.RWMutex
  varKeys   []string // sorted
)

// Publish declares a named exported variable. This should be called from a 
// package's init function when it creates its Vars. If the name is already
// registered then this will log.Panic.
func Publish(name string, v Var) {
  if _, dup := vars.LoadOrStore(name, v); dup {
    log.Panicln("Reuse of exported var name:", name)
  }
  varKeysMu.Lock()
  defer varKeysMu.Unlock()
  varKeys = append(varKeys, name)
  sort.Strings(varKeys)
}
```

从`Publish`函数的注释和源码中可知，expvar 导出的指标数据的字段名称不能重复（也就是不能重复添加相同名称的指标），否则将会出现`log.Panic`异常。

#### 3.2 Var

`Publish`函数的第二个参数是一个`Var`接口类型的数据。`Var`接口用来定义导出数据类型，该接口只定义了一个`String()`方法，而该方法应该返回一个有效的 JSON 字符串。

`Var`接口源码如下：

```go
// Var is an abstract type for all exported variables.
type Var interface {
  // String returns a valid JSON value for the variable.
  // Types with String methods that do not return valid JSON
  // (such as time.Time) must not be used as a Var.
  String() string
}
```

在实现`Var`接口的时候，应该保证其`String()`的方法值是一个有效的 JSON 字符串，否则可能会出现问题。

为了方便使用，expvar 库中提供了五种导出变量类型，它们均实现了`Var`接口：

```go
type Int struct {
  i int64
}

type Float struct {
  f uint64
}

type String struct {
  s atomic.Value // string
}

type Map struct {
  m      sync.Map // map[string]var
  keysMu sync.RWMutex
  keys   []string // sorted
}

type Func func() any
```

前四种类型除了实现`Var`接口，还提供了其他的一些变更值的方法。而且这四中类型可以通过调用对应的`expvar.NewXXX`函数来完成对应变量的创建和指标注册，这些函数都接受一个`string`类型的参数作为字段名称：

```go
intVar = expvar.NewInt("metricInt")
floatVar = expvar.NewFloat("metricFloat")
stringVar = expvar.NewString("metricString")
mapVar = expvar.NewMap("metricMap")
```

这些函数内部都在实例化对应`Var`接口实现类型后调用`Publish()`函数完成指标名和变量的绑定：

```go
func NewInt(name string) *Int {
  v := new(Int)
  Publish(name, v)
  return v
}
```

#### 3.3 expvar.Func

`expvar.Func`类型是为了让用户可以自定义导出类型。

例如，想要暴露以下定义的结构体：

```go
type MyStruct struct {
  Field1 string
  Field2 int
  Field3 float64
}
```

首先要创建一个数据生成函数，用于在每次访问指标 HTTP 服务路径时，通过该函数导出这里面的数据：

```go
func MyStructData() interface{} {
  return MyStruct{
    Field1: "Gopher",
    Field2: 22,
    Field3: 19.99,
  }
}
```

然后通过`Publish`函数将这个数据生成函数注册到指标名即可：

```go
expvar.Publish("metricMyStructData", expvar.Func(MyStructData))
```

`expvar.Func`类型的实现比较简单，其主要数据是一个可以返回任意数据的方法，且该类型实现了`Value()`方法和`Var.String()`方法：

```go
// Func implements Var by calling the function
// and formatting the returned value using JSON.
type Func func() interface{}

func (f Func) Value() interface{} {
  return f()
}

func (f Func) String() string {
  v, _ := json.Marshal(f())
  return string(v)
}
```

#### 3.4 导出方式

在 expvar 包的`init`函数中，注册 HTTP 路由的时候，使用了`expvar.expvarHandler()`函数来实现路由对应的导出逻辑。该函数的逻辑很简单，就是通过`expvar.Do()`函数遍历注入进来的指标，并将数据组合成 JSON 字符串输出：

```go
// KeyValue represents a single entry in a Map.
type KeyValue struct {
  Key   string
  Value Var
}

func expvarHandler(w http.ResponseWriter, r *http.Request) {
  w.Header().Set("Cotent-Type", "application/json; charset=utf-8")
  fmt.Fprintf(w, "{\n")
  first := true
  Do(func(kv KeyValue) {
    if !first {
      fmt.Fprintf(w, ",\n")
    }
    first = false
    fmt.Frpintf(w, "%q: %s", kv.Key, kv.Value)
  })
  fmt.Fprintf(w, "\n}\n")
}

// Do calls f for each exported variable.
// The global variable map is locked during the iteration,
// but existing entries may be concurrently updated.
func Do(f func(KeyValue) {
  varKeysMu.RLock()
  defer varKeysMu.RUnlock()
  for _, k := range varKeys {
    val, _ := vars.Load(key)
    f(KeyValue{k, val.(Var)})
  }
})
```

### 4. 完整示例

下面给出一个覆盖五种导出变量类型的完整示例：

```go
package main

import (
  "expvar"
  "github.com/shirou/gopsutil/v3/host"
  "github.com/shirou/gopsutil/v3/load"
  "github.com/shirou/gopsutil/v3/mem"
  "net/http"
  "time"
)

type Load struct {
  Load1  float64
  Load5  float64
  Load15 float64
}

func AllLoadAvg() interface{} {
  return Load{
    Load1:  LoadAvg(1),
    Load5:  LoadAvg(5),
    Load15: LoadAvg(15),
  }
}

func LoadAvg(loadNumber int) float64 {
  avg, _ := load.Avg()
  switch loadNumber {
  case 5:
    return avg.Load5
  case 15:
    return avg.Load15
  default:
    return avg.Load1
  }
}

func main() {
  var (
    aliveOfSeconds = expvar.NetInt("aliveOfSeconds")
    hostId         = expvar.NewString("hostID")
    lastLoad       = expvar.NewFloat("lastLoad")
    virtualMemory  = expvar.NewMap("virtualMemory")
  )
  expvar.Publish("allLoadAvg", expvar.Func(AllLoadAvg))
  h, _ := host.HostID()
  hostID.Set(h)
  
  go http.ListenAndServe(":8080", nil)
  
  for {
    aliveOfSeconds.Add(1)
    lastLoad.Set(LoadAvg(1))
    vm, _ := mem.VirtualMemory()
    virtualMemory.Add("active", int64(vm.Active))
    virtualMemory.Add("buffer", int64(vm.Buffers))
    time.Sleep(1 * time.Second)
  }
}
```

在上面的示例中，通过 [gopsutil](https://github.com/shirou/gopsutil) 库获取了一些系统信息，并展示了如何通过 expvar 中的各种变量类型将这些信息进行导出。

然后访问指标导出链接的时候，结果类似如下：

```shell
$ curl localhost:8080/debug/vars
{
"aliveOfSeconds": 1,
"allLoadAvg": {"Load1":1.69580078125,"Load5":1.97412109375,"Load15":1.90283203125},
"cmdline": ["/var/folders/xk/gn46n46d503dsztbc6_9qb2h0000gn/T/go-build3566019824/b001/exe/main"],
"hostID": "7a1a74f2-30fc-5bc1-b439-6b7aef22e58d",
"lastLoad": 1.69580078125,
"memstats": {"Alloc":256208,"TotalAlloc":256208,"Sys":8735760...},
"virtualMemory": {"active": 1957449728, "buffer": 0}
}
```

### 5. 总结

标准库 expvar 为需要导出的公共变量提供了一个标准化的接口，使用比较简单，而且在自己的包中需要使用的话，直接在包的`init`函数中进行注册即可。

expvar 包内定义的几种寄出类型都相应给出了并发安全的操作方法，不需要去重复实现一遍，能够开箱即用。


