> [Go 日志库 zerolog 大解剖](https://mp.weixin.qq.com/s/J9Zwa1BLVTk9UM3rzhPqHg)

[zerolog](https://pkg.go.dev/github.com/rs/zerolog) 包提供了一个专门用于 JSON 输出的简单快速的 Logger，其 API 旨在为开发者提供出色的体验和令人惊叹的性能，独特的链式 API 允许通过避免内存分配和反射来写入 JSON（或 CBOR）日志。uber 的 zap 库开创了这种写法，zerolog 通过更简单的应用编程和更好的性能，将这一概念提升到了更高的层次。

## 一、使用

### 1.1 安装

使用如下命令安装 zerolog：

```shell
go get -u github.com/rs/zerolog/log
```

### 2.2 Contextual Logger

可以创建带有上下文信息的日志记录器：

```go
func TestContextualLogger(t *testing.T) {
  log := zerolog.New(os.Stdout)
  log.Info().Str("content", "Hello world").Int("count", 3).Msg("TestContextualLogger")
  
  // 添加上下文（文件名/行号/字符串）
  log = log.With().Caller().Str("foo", "bar").Logger()
  log.Info().Msg("Hello world")
}
```

输出的日志信息如下：

```
{"level":"info","content":"Hello world","count":3,"message":"TestContextualLogger"}
{"level":"info","caller":"log_example_test.go:29","message":"Hello wrold"}
```

zerolog 与 zap 相同的是，都定义了强类型字段，不同的是，zerolog 采用链式调用。

### 1.3 多级 Logger

zerolog 提供了从 Trace 到 Panic 七个级别：

```go
// 设置日志级别
zerolog.SetGlobalLevel(zerolog.WarnLevel)
log.Trace().Msg("Trace")
log.Debug().Msg("Debug")
log.Info().Msg("Info")
log.Warn().Msg("Warn")
log.Error().Msg("Error")
log.Log().Msg("没有级别")
```

输出的日志如下：

```
{"level":"warn","message":"Warn"}
{"level":"error","message":"Error"}
{"message":"没有级别"}
```

可以看到，warn 级别以下的日志被忽略了，没有输出。

### 1.4 注意事项

1. zerolog 不会删除重复的字段。比如：

    ```go
    logger := zerolog.New(os.Stderr).With().Timestamp().Logger()
    logger.Info().Timestamp().Msg("dup")
    ```
    
    输出结果如下：
    
    ```
    {"level":"info","time":1494567715,"time":1494567715,"message":"dup"}
    ```

2. 链式调用必须调用`Msg/Msgf/Send`才能输出日志，其中`Send`相当于`Msg("")`。

3. 一旦调用`Msg`，日志对应的`Event`将会被处理（放回池中或者丢掉），不允许二次调用。

## 二、源码

本次 zerolog 的源码分析基于 zerolog 1.22.0 版本。

### 2.1 Logger

Logger 的`w`属性类型是`LevelWriter`接口，用于向目标输出事件。`zerolog.New()`函数就是用来创建 Logger 实例的：

```go
type Logger struct {
  w       LevelWriter // 输出对象
  level   Level       // 日志级别
  sampler Sampler     // 采样器
  context []byte      // 上下文
  hooks   []Hoot      // 钩子列表
  stack   bool
}

func New(w io.Writer) Logger {
  if w == nil {
    // ioutil.Discard 所有成功执行的 Write 操作都不会产生任何实际效果
    w = ioutil.Discard
  }
  // 如果传入的不是 LevelWriter 类型，则封装成此类型
  lw, ok := w.(LevelWriter)
  if !ok {
    lw = levelWriterAdapter{w}
  }
  // 默认输出日志级别为 TraceLevel
  return Logger{w: lw, level: TraceLevel}
}
```

`zerolog.New()`函数中，会将传入的`io.Writer`类型的参数封装成`LevelWriter`类型，以便后续的使用。同时，为默认情况下选用不会产生任何效果的`ioutil.Discard`作为输出目标。

#### 2.1.1 日志输出流程

对于前面的示例代码：

![](http://cnd.qiniu.lin07ux.cn/markdown/1643980896943-e20cc0ec6a70.jpg)

第三行代码执行的流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1643980925923-a23fe51d5f6e.jpg)

#### 2.1.2 Info()

Logger 结构体中，对每个日志级别都提供了一个对应的方法名称，用于生成对应的`Event`实例，并且根据日志设置将相关的信息（日志级别、上下文、堆栈信息）预先写入到 buf 中：

```go
// Info 开始记录一条 info 级别的消息
func (l *Logger) Info() *Event {
  return l.NewEvent(InfoLevel, nil)
}

func (l *Logger) newEvent(level Level, done func(string)) *Event {
  // 判断是否是需要记录的级别
  enabled := l.should(level)
  if !enabled {
    return nil
  }
  
  // 创建记录日志的对象，并设置 done 函数和 hook 函数
  e := newEvent(l.w, level)
  e.done = done
  e.ch = l.hooks
  
  // 将日志级别先写入
  if level != NoLevel && LevelFieldName != "" {
    e.Str(LevelFieldName, LevelFieldMarshalFunc(level))
  }
  
  // 再记录上下文
  if l.context != nil && len(l.context) > 1 {
    e.buf = enc.AppendObjectData(e.buf, l.context)
  }
  
  // 记录堆栈跟踪信息
  if l.stack {
   e.Stack()
  }
  
  return e
}
```

这里的`should`函数用于判断是否需要记录本次消息：

```go
// should 如果应该被记录，则返回 true
func (l *Logger) should(lvl Level) bool {
  if lvl < l.level || lvl < GlobalLevel() {
    return false
  }
  // 设置了采样则需要判断当前是否在采样中（后续详细介绍）
  if l.sampler != nil && !samplingDisabled() {
    return l.sampler.Sample(lvl)
  }
  return true
}
```

### 2.2 Event

`Event`是用于真实处理日志内容写入的结构体，而且必须在`Event`上调用`Msg/Msgf/Send`才能真正的发送事件记录日志。

Event 结构体的定义如下：

```go
type Event struct {
  buf        []byte      // 日志内容
  w          LevelWriter // 待写入的目标接口
  level      Level       // 日志级别
  done       func(msg string) // msg 函数结束事件
  stack      bool        // 错误堆栈跟踪
  ch         []Hook      // hook 钩子函数列表
  shipFrame  int         // 跳过的栈帧数量
}
```

#### 2.2.1 newEvent

`newEvent`函数使用`sync.Pool`获取 Event 对象，并将 Event 的参数进行初始化：

```go
func new Event(w LevelWriter, level Level) *Event {
  e := eventPool.Get().(*Event)
  e.buf = e.buf[:0]
  e.ch = nil
  e.buf = enc.AppendBeginMarker(e.buf) // 在开始处添加左大括号
  e.w = w
  e.level = level
  e.stack = false
  e.skipFrame = 0
  return e
}
```

#### 2.2.2 Str

`Str`函数是负责将键值对添加到 buf 中。将字符串类型添加到 JSON 格式的数据中，涉及到特殊字符编码问题，如果是特殊字符，就调用`appendStringComplex`函数来解决：

```go
func (e *Event) Str(key, val string) *Event {
  if e == nil {
    return e
  }
  e.buf = enc.AppendString(enc.AppendKey(e.buf, key), val)
  return e
}
```

这里的`enc`是一个 Encoder 结构体实例，定义在`internal/json`中：

```go
// ============ internal/json/base.go ===
type Encoder struct{}

// 添加一个新的 Key
func (e Encoder) AppendKey(dst []byte, key string) []byte {
  // 非第一个，多加一个逗号
  if dst[len(dst)-1] != '{' {
    dst = append(dst, ',')
  }
  return append(e.AppendString(dst, key), ':')
}

// === internal/json/string.go ===
func (Encocer) AppendString(dst []byte, s string) []byte {
  // 双引号起
  dst = append(dst, '"')
  // 遍历字符
  for i := 0; i < len(s); i++ {
    // 检查字符是否需要编码
    if !noEscapeTable[s[i]] {
      dst = appendStringComplex(dst, s, i)
      return append(dst, '"')
    }
  }
  // 不需要编码的字符，添加到 dst 中
  dst = append(dst, s...)
  // 双引号收
  return append(dst, '"')
}
```

#### 2.2.3 Int

`Int`函数将键值（int 类型）对添加到 buf 中，内部调用`strconv.AppendInt`函数实现：

```go
// ============ event.go ===
func (e *Event) Int(key string, i int) *Event {
  if e == nil {
    return e
  }
  e.buf = enc.AppendInt(enc.AppendKey(e.buf, key), i)
  return e
}

// === internal/json/types.go ===
func (Encoder) AppendInt(dst []byte, val int) []byte {
  return strconv.AppendInt(dst, int64(val), 10)
}
```

#### 2.2.4 Msg

`Msg`函数会将日志 Event 完成数据的写入操作：

```go
// Msg 是对 msg 的封装调用，当指针接收器为 nil 时直接返回即可
func (e *Event) Msg(msg string) {
  if e == nil {
    return
  }
  e.msg(msg)
}

func (e *Event) msg(msg string) {
  // 运行 hook
  for _, hook := range e.ch {
    hook.Run(e, e.level, msg)
  }
  
  // 记录消息
  if msg != "" {
    e.buf = enc.AppendString(enc.AppendKey(e.buf, MessageFieldName), msg)
  }
  
  // 设置了 done 函数的话则在 defer 中执行
  if e.done != nil {
    defer e.done(msg)
  }
  
  // 写入日志
  if err := e.write(); err != nil {
    if ErrorHandler != nil {
      ErrorHandler(err)
    } else {
      fmt.Fprintf(os.Stderr, "zerolog: could not write event: %v\n", err)
    }
  }
}

func (e *Event) write() (err error) {
  if e == nil {
    return nil
  }
  if e.level != Disabled {
    // 大括号收尾
    e.buf = enc.AppendEndMarker(e.buf)
    // 换行符
    e.buf = enc.AppendLineBreak(e.buf)
    // 向目标写入日志
    if e.w != nil {
      _, err = e.w.WriteLevel(e.level, e.buf)
    }
  }
  // 将 Event 对象返回池中
  putEvent(e)
  return
}
```

日志最终的写入很简单，就是调用 levelWriterAdapter 的`Write`函数：

```go
// === writer.go ===
func (lw, levelWriterAdapter) WriteLevel(l Level, p []byte) (n int, err error) {
  // 这里传递的日志级别，在函数内部并没有使用
  return lw.Write(p)
}  
```

### 2.3 采样

zerolog 支持多种采样方式，使得日志内容仅在被采样到的时候才会输出，其他情况则不输出。

使用`Logger.Sample`方法来设置采样器，比如，下面的代码设置了一个每秒允许记录 5 条消息、且超过后则每 20 条仅记录一条的采样器：

```go
func TestSample(t *testing.T) {
  sampled := log.Sample(&zerolog.BurstSampler{
    Burst:       5,
    Period:      1 * time.Second,
    NextSampler: &zerolog.BasicSampler{N: 20},
  })
  for i := 0; i <= 50; i++ {
    sampled.Info().Msgf("logged messages : %2d", i)
  }
}
```

这个测试函数中，循环记录了 50 条日志，但是由于采样器的影响，只输出了最前面的 5 条日志，然后后面就每 20 条仅输出最前面一条日志：

![](http://cnd.qiniu.lin07ux.cn/markdown/1644405697244-5fd5b44538da.jpg)

#### 2.3.1 采样流程

上面示例中，zerolog 采样函数流程说明图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1644405982751-988537f2ea8f.jpg)

#### 2.3.2 源码

下面是定义采样接口及实现函数的源码。在`inc`函数中，使用`sync.atomic`包将竞争在接收器对象的参数变成局部变量，可以很好的避免并发的影响：

```go
// =========== sampler.go ===

// 采样器接口
type Sampler interface {
  // 如果事件是样本的一部分，则返回 true
  Sample(lvl Level) bool
}

// BasicSampler 基本采样器
// 每 N 个事件发送一次，不考虑日志级别
type BasicSampler struct {
  N
  counter uint32
}

// 实现采样器接口
func (s *BasicSampler) Sample(lvl Level) bool {
  n := s.N
  if n == 1 {
    return true
  }
  c := atomic.AddUint32(&s.Counter, 1)
  return c%n == 1
}

// BurstSampler 漏桶采样器
type BurstSampler struct {
  // 调用 NextSamopler 之前，每个时间段（Period）调用的最大事件数量
  Burst uint32
  // 如果为 0 则始终调用 NextSampler
  Period time.Duration
  // 采样器
  NextSampler Sampler
  // 用于计数在一定时间内（Period）的调用数量
  counter uint32
  // 时间段的结束时间（纳秒），即 当前时间 + Period
  resetAt int64
}
// 实现 Sampler 接口
func (s *BurstSampler) Sample(lvl Level) bool {
  // 当设置了 Burst 和 Period 大于 0 时，限制在一定时间内的最大事件数量
  if s.Burst > 0 && s.Period > 0 {
    if s.inc() <= s.Burst {
      return true
    }
  }
  // 没有采样器则直接结束
  if s.NextSampler == nil {
    return false
  }
  // 调用采样器
  return s.NextSampler.Sample(lvl)
}

// 增加采样基数
func (s *BurstSampler) inc() uint32 {
  // 获取当前时间（纳秒）和重置时间（纳秒）
  now := time.Now().UnixNano()
  resetAt := atomic.LoadInt64(&s.resetAt)
  var c uint32
  // 当前时间 > 重置时间，则重置计数器，并设置下一次的重置时间
  if now > resetAt {
    c = 1
    atomic.StoreUint32(&s.counter, c)
    newResetAt := now + s.Period.NanoSeconds()
    
    // 如果已经被其他 goroutine 先设置了重置时间，则累加采样计数器
    reset := atomic.CompareAndSwapInt64(&s.resetAt, resetAt, newResetAt)
    if !reset {
      c = atomic.AddUint32(&s.counter, 1)
    }
  } else {
    c = atomic.AddUint32(&s.counter, 1)
  }
  return c
}
```

#### 2.3.3 采样时机

`Logger.Info`函数及其他级别函数都会调用`newEvent`，在该函数的开头，`Logger.should`函数用来判断是否需要记录的日志级别和采样判断：

```go
// ============ log.go ===
// should 如果应该被记录，则返回 true
func (l *Logger) should(lvl Level) bool {
  if lvl < l.level || lvl < GlobalLevel() {
    return false
  }
  // 如果使用了采样，则调用采样函数，判断本次事件是否需要记录
  if l.sampler != nil && !samplingDisabled() {
    return l.sampler.Sample(lvl)
  }
  return true
}
```

## 三、知识点

### 3.1 内存分配

每一条日志都会产生一个`*Event`对象，当多个 Goroutine 操作日志时，会导致创建的对象数量剧增，进而导致 GC 压力增大。这就会形成**并发大 - 占用内存大 - GC 缓慢 - 处理并发能力降低 - 并发更大**这样的恶心循环。在这个时候，就需要使用一个对象池，程序不再自己单独创建对象，而是从对象池中获取。

使用`sync.Pool`可以将暂时不用的对象缓存起来，下次需要使用的时候从池中取，不用再次经过内存分配。

上面`Event.Msg`代码中调用的`putEvent`函数就是将使用过的 Event 对象放回池中，而且放回时会检查对象中记录消息的 buf 是否超过 64KB，超过则不会放回。这是为了避免动态增加的 buffer 导致大量内存被固定，在活锁的情况下永远不会被释放。

```go
var eventPool = &sync.Pool{
  New: func() interface{} {
    return &Event{
      buf: make([]byte, 0, 500),
    }
  },
}

func putEvent(e *Event) {
  // 选择占用较小内存的 buf，将对象放回池中
  // See https://golang.org/issue/23199
  const maxSize = 1 << 16 // 64KiB
  if cap(e.buf) > maxSize {
    return
  }
  eventPool.Put(e)
}
```

### 3.2 日志级别

日志级别就是一系列的数字，而且日志级别也需要对应一些字符串值，方便输出和引用。zerolog 中还定义了一些获取字符串值的方法，以及解析字符串为日志级别类型的方法：

```go
// 日志级别类型
type Level int8

// 定义所有日志级别
const (
  DebugLevel  Level = iota
  InfoLevel
  WarnLevel
  ErrorLevel
  FatalLevel
  PanicLevel
  NoLevel
  Disabled
  
  TraceLevel  Level = -1
)

// 返回当前级别的 value
func (l Level) String() string {
  switch l {
  case TraceLevel:
    return LevelTraceValue
  case DebugLevel:
    return LevelDebugValue
  case InfoLevel:
    return LevelInfoValue
  case WarnLevel:
    return LevelWarnValue
  case ErrorLevel:
    return LevelErrorValue
  case FatalLevel:
    return LevelFatalValue
  case PanicLevel:
    return LevelPanicValue
  case Disabled:
    return "disabled"
  case NoLevel:
    return ""
 }
 return ""
}

// ParseLevel 将级别字符串解析成 zerolog level value
// 当字符串不匹配任何已知级别时，返回错误
func ParseLevel(levelStr string) (Level, error) {
  switch levelStr {
  case LevelFieldMarshalFunc(TraceLevel):
    return TraceLevel, nil
  case LevelFieldMarshalFunc(DebugLevel):
    return DebugLevel, nil
  case LevelFieldMarshalFunc(InfoLevel):
    return InfoLevel, nil
  case LevelFieldMarshalFunc(WarnLevel):
    return WarnLevel, nil
  case LevelFieldMarshalFunc(ErrorLevel):
    return ErrorLevel, nil
  case LevelFieldMarshalFunc(FatalLevel):
    return FatalLevel, nil
  case LevelFieldMarshalFunc(PanicLevel):
    return PanicLevel, nil
  case LevelFieldMarshalFunc(Disabled):
    return Disabled, nil
  case LevelFieldMarshalFunc(NoLevel):
    return NoLevel, nil
 }
 return NoLevel, fmt.Errorf("Unknown Level String: '%s', defaulting to NoLevel", levelStr)
}
```

每个级别对应的字符串定义在`globals.go`中：

```go
var (
  // ......
  // 级别字段的 key 名称
  LevelFieldName = "level"
  // 各个级别的 value
  LevelTraceValue = "trace"
  LevelDebugValue = "debug"
  LevelInfoValue = "info"
  LevelWarnValue = "warn"
  LevelErrorValue = "error"
  LevelFatalValue = "fatal"
  LevelPanicValue = "panic"
  // 返回形参级别的 value
  LevelFieldMarshalFunc = func(l Level) string {
    return l.String()
  }
  // ......
)
```

### 3.3 全局日志级别

zerolog 支持设置全局的日志级别，而为了支持并发存取全局日志级别，使用了`sync.atomic`来保证原子操作，并保证效率：

* `atomic.StoreInt32` 存储 int32 类型的值
* `atomic.LoadInt32` 读取 int32 类型的值

在源码中，做级别判断时，通过封装好的`GlobalLevel`函数来保证并发安全：

```go
var (
  gLevel = new(int32)
  // ...
)

// SetGlobalLevel 设置全局日志级别
// 如果要全局禁用日志，则入参为 Disabled
func SetGlobalLevel(l Level) {
  atomic.StoreInt32(gLevel, int32(l))
}

// GlobalLevel 返回当前全局日志级别
func GlobalLevel() Level {
  return Level(atomic.LoadInt32(gLevel))
}
```

### 3.4 钩子函数

zerolog 定义了 Hook 接口，包含一个`Run`函数，入参包含`*Event`、日志级别`Level`、消息数据：

```go
type Hook interface {
  Run(e *Event, level Level, message string)
}
```

并且实现了`HookFunc`方法类别和`LevelHook`结构体：

```go
// HookFunc 函数适配器
type HookFunc func(e *Event, level Level, message string)

// Run 实现 Hook 接口
func (h HookFunc) Run(e *Event, level Level, message string) {
  h(e, level, message)
}

// 为每个级别应用不同的 hook
type LevelHook struct {
  NoLevelHook, TraceHook, DebugHook, InfoHook, WarnHook, ErrorHook, FatalHook, PanicHook Hook
}

// Run 实现 Hook 接口
func (h LevelHook) Run(e *Event, level Level, message string) {
  switch level {
  case TraceLevel:
    if h.TraceHook != nil {
      h.TraceHook.Run(e, level, message)
    }
  case DebugLevel:
    if h.DebugHook != nil {
      h.DebugHook.Run(e, level, message)
    }
  case InfoLevel:
    if h.InfoHook != nil {
      h.InfoHook.Run(e, level, message)
    }
  case WarnLevel:
    if h.WarnHook != nil {
      h.WarnHook.Run(e, level, message)
    }
  case ErrorLevel:
    if h.ErrorHook != nil {
      h.ErrorHook.Run(e, level, message)
    }
  case FatalLevel:
    if h.FatalHook != nil {
      h.FatalHook.Run(e, level, message)
    }
  case PanicLevel:
    if h.PanicHook != nil {
      h.PanicHook.Run(e, level, message)
    }
  case NoLevel:
    if h.NoLevelHook != nil {
      h.NoLevelHook.Run(e, level, message)
    }
  }
}

// NewLevelHook 创建一个 LevelHook
func NewLevelHook() LevelHook {
  return LevelHook{}
}
```

添加 Hook 的源码如下：

```go
func (l Logger) Hook(h Hook) Logger {
  l.hooks = append(l.hooks, h)
  return l
}
```

当定义并实例化一个 Hook 接口对象之后，就可以使用`log.Hook`方法将其注入到 Logger 内部的 Hooks 中：

```go
type PrintMsgHook struct{}

func (p PrintMsgHook) Run(e *zerolog.Event, l zerolog.Level, msg string) {
  fmt.Println(msg)
}

func TestContextualLogger(t *testing.T) {
  log := zerolog.New(os.Stdout)
  log = log.Hook(PrintMsgHook{})
  log.Info().Msg("TestContextualLogger")
}
```

### 3.6 获取调用者函数名

`runtime.Caller`可以获取相关调用 goroutine 堆栈上的函数调用的程序计数器、文件位置、行号、是否能恢复等信息。其参数`skup`是堆栈帧的数量，当`skip = 0`时，输出当前函数的信息；当`skip = 1`时，输出调用栈的上一帧，即调用者函数的信息。

```go
// ============ go@1.16.5 runtime/extern.go ===
func Caller(skip int) (pc uintptr, file string, line int, ok bool) {
  rpc := make([]uintptr, 1)
  n := caller(skip+1, rpc[:])
  if n < 1 {
    return
  }
  frame, _ := CallersFrames(rpc).Next()
  return frame.PC, frame.File, frame.Line, frame.PC != 0
}
```

`Event.caller`方法中就是调用`runtime.Caller()`函数来获取调用栈信息的：

```go
// ============ event.go ===
func (e *Event) caller(skip int) *Event {
  if e == nil {
    return e
  }
  _, file, line, ok := runtime.Caller(skip + e.skipFrame)
  if !ok {
    return e
  }
  // CallerFieldName 是默认的 key 名称
  // CallerMarshalFunc 函数用于拼接 file:line
  e.buf = enc.AppendString(enc.AppendKey(e.buf, CallerFieldName), CallerMarshalFunc(file, line))
  return e
}
```




