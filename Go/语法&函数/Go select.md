> 转摘：[最全Go select底层原理，一文学透高频用法](https://mp.weixin.qq.com/s/0oPA4THIcvWjwMGrudKJuQ)

### 1. 是什么

select 是 Go 在语言层面提供的 I/O 多路复用的机制，其专门用来检测多个 Channel 是否准备完毕：可写或可读。

select 原本是 Linux 操作系统中的系统调用函数，操作系统提供 select、poll、epoll 等函数构建 I/O 多路复用模型，来提升程序处理 IO 事件（如网络请求）的性能。

操作系统中 IO 多路复用中的多路就是指多个 TCP 连接，复用就是指共用一个或少量线程。合起来就是多个网络连接的 IO 事件复用一个或少量线程来处理这些连接。概括起来就是：**IO 多路复用就是复用一个线程处理多个 IO 请求**。

之所以需要复用线程处理 IO 请求，是因为普通多线程 IO 模型中，每来一个 IO 事件（比如网络读写请求事件），操作系统都会起一个线程或进程进行处理，这样的处理方式缺点很明显：**对多个 IO 事件，系统需要创建和维护对应的多个线程或进程**。而大多数时候，大部分 IO 事件是处于等待状态，只有少部分会立即操作完成，这会导致对应的处理线程大部分时候处于等待状态。系统为此还需要多做很多额外的线程或进程的管理工作。

通过复用线程可以使一个线程处理多个 IO 事件，操作系统无需对额外的多个线程或进程进行管理，节约了资源，提升了效率。

Go 语言的 select 与操作系统中的 select 比较相似但又不完全相同：

- Linux 操作系统中实现 IO 多路复用主要通过起一个线程来监听并处理多个文件描述符代表的 TCP 链接，用来提高处理网络读写请求的效率；
- Go 语言的 select 命令是用来起一个 goroutine 协程监听多个 Channel（代表多个 goroutine）的读写事件，提高从多个 Channel 获取信息的效率。

虽然 Go 和 Linux 系统对 IO 多路复用的具体目标和实现不同，但本质思想都是相同的。

### 2. 特点

1. Go select 语句采用多路复用思想，本质上是为了达到**通过一个协程同时处理多个 IO 请求（Channel 读写）**的目的。

2. select 的基本用法是：**通过多个 case 监听多个 Channel 的读写操作，任何一个 case 可以执行则选择该 case 执行，否则执行 default**。如果没有 default，且所有的 case 均不能执行，则当前的 goroutine 阻塞。

3. **编译器会对 select 有不同的 case 的情况进行优化以提高性能**。

    - 对没有 case、有一个 case、单 case+default 的 select，编译器会单独处理，或直接调用运行时函数，或直接转成对 Channel 的操作，或以非阻塞的方式访问 Channel，这样来尽量避免对 Channel 的加锁，提高性能。
    - **对最常出现的多 case 的 select，会调用`runtime.selectgo()`函数来获取执行 case 的索引，并声称 if 语句执行该 case 的代码**。

4. `runtime.selectgo()`函数的执行分为如下几个步骤：

    - 首先，随机生成一个遍历 case 的轮询顺序 pollorder，并根据 Channel 地址生成加锁顺序 lockorder。随机顺序能够避免 Channel 饥饿，保证公平性，顺序加锁能避免死锁；
    - 然后，根据 pollorder 的顺序查找 scases 是否有可以立即收发的 Channel：

        * 如果有则获取 case 索引并进行处理；
        * 如果没有则将当前 goroutine 加入到各个 case 的 Channel 对应的收发队列上并等待其他 goroutine 的唤醒。

    - 最后，当调度器唤醒当前 goroutine 的时候，会再次按照 lockorder 遍历所有的 case，从中查找需要被处理的 case 索引进行读写处理。同时从所有 case 的发送接受队列中移除掉当前 goroutine。

### 3. 语法

Go select 的语法和 switch 的语法在形式上基本相同，但是本质上并不同。

select 中的 case 的表达式必须都是 Channel 的读写操作，不能是其他的数据类型。select 通过多个 case 监听多个 Channel 的读写操作，任何一个 case 可以执行则选择该 case 执行，否则执行 default，如果没有 default，且所有的 case 均不能执行，则当前的 goroutine 阻塞。

select 命令的基本语法如下：

```go
select {
  case <- chan1:
    // 如果 chan1 成功读到数据则进行该 case 处理语句
  case chan2 <- 1:
    // 如果成功向 chan2 写入数据则进行该 case 处理语句
  default:
    // 如果上面都没成功，则进入 default 处理流程
}
```

select 中的 case 可以有 0 个或多个，default 语句可以有一个或零个。不同数量的 case 和 default 语句对应的 select 代码的处理方式不同。

#### 3.1 没有 case 和 default

select 中没有任何 case 和 default 则当前 goroutine 永久阻塞而产生失败：

```go
package main

func main() {
  select {
  }
}
```

因为 Go 自带死锁检测机制，当发现当前协程永远不会被唤醒时，就会触发 panic，所以上述程序会产生如下的 panic 错误：

```
fatal error: all goroutines are asleep - deadlock!

goroutine 1 [select (no cases)]:
...
```


#### 3.2 无 default 且全部 case 都无法执行

当没有 default 语句的时候，select 语句会因为全部的 case 都无法执行而阻塞。

```go
package main

import "fmt"

func main() {
  ch1 := make(chan int, 1)
  ch2 := make(chan int)
  select {
  case <- ch1:
    // 从有缓冲的 Channel 中读取数据，由于缓冲区没有数据且没有发送者，该分支会一直阻塞
    fmt.Println("Received from ch")
  case i := <- ch2:
    // 从无缓冲 Channel 中读取数据，由于没有发送者，该分支会阻塞
    fmt.Printf("i is: %d", i)
  }
}
```

上面的程序中，ch1 和 ch2 两个 Channel 都没有机会准备就绪，所以两个 case 都会阻塞。由于 Go 自带死锁检测机制，当发现当前协程再也没有机会被唤醒时就会发生 panic。

#### 3.3 有 case 和 default

如果 select 中有 default 语句，那么如果没有任何 case 准备就绪，那么就会执行 default 语句，而不会因为 case 未准备好而发生阻塞。

比如，对于上面的代码，增加一个 default 语句：

```go
package main

import "fmt"

func main() {
  ch1 := make(chan int, 1)
  select {
  case <- ch1:
    fmt.Println("Received from ch")
  default:
    fmt.Println("this is default")
  }
}
```

select 有一个 case 分支和一个 default 分支，因为 case 分支被阻塞了，所以就会执行到了 default 分支。执行结果如下：

```text
this is default
```

如果 case 的条件满足了，就不会执行 default 分支。比如：

```go
package main

import "fmt"

func main() {
  ch1 := make(chan int, 1)
  ch1 <- 10
  select {
  case <- ch1:
    fmt.Println("Received from ch")
  default:
    fmt.Println("this is default")
  }
}
```

此时因为 ch1 中有数据，可以被读取，所以 case 分支能满足执行，就会进入 case 分支而不会执行 default 分支：

```text
Received from ch1
```

#### 3.4 多个 case 同时可执行

当 select 中的**多个 case 分支同时可执行，那么就会随机选择一个 case 分支去执行**。

比如，对于下面的程序：

```go
package main

import "fmt"

func main() {
  ch := make(chan int, 1)
  ch <- 10
  select {
  case val := <-ch:
    fmt.Println("Received from ch1, val =", val)
  case val := <-ch:
    fmt.Println("Received from ch2, val =", val)
  case val := <-ch:
    fmt.Println("Received from ch3, val =", val)
  default:
    fmt.Println("Run in default")
  }
}
```

在多次运行该程序会发现，三个 case 都有可能执行，而且是随机性的。

### 4. 编译执行

select 在 Go 语言的源代码中不存在对应的结构体，只是定义了一个`runtime.scase`结构体（在`src/runtime/select.go`）表示每个 case 语句和 default 语句：

```go
type scase struct {
  c    *hchan         // case 中使用的 chan
  elem unsafe.Pointer // 指向 case 包含数据的指针
}
```

因为所有的非 default 的 case 基本都要求是对 Channel 的读写操作，所以`runtime.scase`结构体中也包含一个`runtime.hchan`类型的字段，用以存储 case 中使用的 Channel。另一个字段 elem 则指向 case 条件包含的数据指针。

比如，对于`case ch1 <- 1`语句，`scase.elem`指向常量 1，`scase.c`指向`ch1`。

select 语句在编译期间会被转换成`ir.OSELECT`类型的节点(`src/cmd/compile/internal/walk/stmt.go`)：

```go
func walkStmt(n ir.Node) ir.Node {
  ...
  switch n.Op() {
  ...
  case ir.OSELECT:
    n := n.(*ir.SelectStmt)
    walkSelect(n)
    return n
  ...
  }
  ...
}
```

`runtime.walkSelect()`函数位于`src/cmd/compile/internal/walk/stmt.go`：

```go
func walkSelect(sel *ir.SelectStmt) {
  lno := ir.SetPos(sel)
  if sel.Walked() {
    base.Fatalf("double walkSelect")
  }
  sel.SetWalked(true)
  
  // 编译器在中间代码生成期间会根据 select 中 case 的不同对控制语句进行优化
  init := ir.TakeInit(sel)
  init = append(init, walkSelectCases(sel.Cases)...)

  sel.Cases = nil
  sel.Compiled = init
  walkStmtList(sel.Compiled)
  
  base.Pos = lno
}
```

编译器在中间代码生成期间会根据 select 中 case 的不同对控制语句进行优化，这一过程发生在`runtime.walkSelectCases()`函数中（`src/cmd/compile/internal/walk/select.go`）：

```go
func walkSelectCases(cases []*ir.CommClause) []ir.Node {
  ncas := len(cases)
  sellineno := base.Pos
  ...
}
```

`runtime.walkSelectCases()`函数中，会根据 case 分支的数量分别进行处理优化，对应不同的运行时函数，如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1673588320)

#### 4.1 没有 case

当 select 中没有 case 分支（包括 default 分支）时，select 所在的 goroutine 会永久阻塞，程序会直接 panic。

在编译阶段，编译器会针对这种情况直接调用`runtime.block`函数来阻塞当前 goroutine：

```go
if ncas == 0 {
  return []ir.Node{mkcallstmt("block")}
}
```

`runtime.block()`函数会调用`gopark()`函数以`waitReasonSelectNoCases`的原因挂起当前 goroutine，并且永远无法被唤醒。Go 程序检测到这种情况就会直接 panic。

#### 4.2 只有一个非 default 的 case

select 只有一个非 default 的 case 时，只有一个 Channel，实际会被编译器转换为对 Channel 的读写操作，和实际调用`data := <-ch`或`ch <- data`并没有什么区别。

比如：

```go
ch := make(chan struct{})
select {
case data <- ch:
  fmt.Printf("ch data: %v\n", data)
}
```

会被编译器转换为如下代码：

```go
data := <- ch
fmt.Printf("ch data: %v\n", data)
```

对应的编译逻辑在`walkSelectCases()`函数中代码如下：

```go
if ncas == 1 {
  cas := cases[0] // 获取第一个也是唯一的 case
  ir.SetPos(cas)
  l := cas.Init()
  if cas.Comm != nil { // case 类型不是 default
    n := cas.Comm      // 获取 case 的条件语句
    l = append(l, ir.TakeInit(n)...)
    switch n.Op() {    // 检查 case 对 Channel 的操作类型：读、写
    default:           // 如果既不是读也不是写 Channel，则直接报错
      base.Fatalf("select %v", n.Op())
    case ir.OSEND:
      // 如果对 Channel 的操作是写入类型，编译器无需做任何转换，直接是 chan <- data
    case ir.OSELRECV2:
      r := n.(*ir.AssignListStmt)
      
      // 如果是 <- chan 这种形式，即接收字段 data 和 ok 为空，则直接转换成 <- chan
      if ir.IsBlank(r.Lhs[0]) && ir.IsBlank(r.Lhs[1]) {
        n = r.Rhs[0]
        break
      }
      
      // 否则，是 data, ok := <- chan 这种形式
      r.SetOp(ir.OAS2RECV)
    }
    
    // 把编译器处理后的 case 语句加入到待执行语句列表中
    l = append(l, n)
  }
}
```

从上面这个代码逻辑中可以看出，在 select 只有一个 case 并且这个 case 不是 default 时，select 对 case 的处理就是对普通 Channel 的读写操作。

#### 4.3 有一个 default 和一个 case

在 select 中有一个 default 分支和一个 case 分支的时候，编译器会将其转换为简单的`if...else`语句。

比如，下面的 select 语句：

```go
select {
case ch <- 1:
  fmt.Println("run case 1")
default:
  fmt.Println("run default")
}
```

编译器会将其改写为：

```go
if selectnbsend(ch, 1) {
  fmt.Println("run case 1")
} else {
  fmt.Println("run default")
}
```

在`walkSelectCases()`函数中，这部分对应的代码如下：

```go
if ncas == 2 && dflt != nil {
  // 获取非 default 的 case
  cas := cases[0]
  if cas == dflt {
    cas = cases[1]
  }
  
  n := cas.Comm
  ir.SetPos(n)
  r := ir.NewIfStmt(base.Pos, nil, nil, nil)
  r.SetInit(cas.Init())

  var cond ir.Node
  switch n.Op() {
  default:
    base.Fatalf("select %v", n.Op())
    
  // 如果该 case 是对 channel 的写入操作，则调用运行时的 selectnbsend 函数
  case ir.OSEND:
    n := n.(*ir.SendStmt)
    ch := n.Chan
    cond = mkcall1(chanfn("selectnbsend", 2, ch.Type()), types.Types[types.TBOOL], r.PtrInit(), ch, n.Value)
    
  // 如果该 case 是对 Channel 的读取操作，会调用运行时的 selectnbrecv 函数
  case ir.OSELRECV2:
    n := n.(*ir.AssignListStmt)
    recv := n.Rhs[0].(*ir.UnaryExpr)
    ch := recv.X
    elem := n.Lhs[0]
    if ir.IsBlank(elem) {
      elem = typecheck.NodNil()
    }
    cond = typecheck.Temp(types.Types[types.TBOOL])
    fn := chanfn("selectnbrecv", 2, ch.Type())
    call := makcall1(fn. fn.Type().Results(), r.PtrInit(), elem, ch)
    as := ir.NewAssignListStmt(r.Pos(), ir.OAS2, []ir.Node{cond, n.Lhs[1]}, []ir.Node{call})
    r.PtrInit().Append(typecheck.Stmt(as))
  }
  
  r.Cond = typecheck.Expr(cond)
  r.Body = cas.Body
  r.Else = append(dflt.Init(). dflt.Body...) // 将 default 语句放入 if 语句的 else 分支
  return []ir.Node{r, ir.NewBranchStmt(base.Pos, ir.OBREAK, nil)}
}
```

`runtime.selectnbrecv()`函数和`runtime.selectnbsend()`函数会分别调用`runtime.chanrecv()`函数和`runtime.chansend()`函数，而且传入的三个参数均为 false，表示采用非阻塞方式进行 Channel 的读写，不会发生阻塞：

```go
func selectnbrecv(elem unsafe.Pointer, c *hchan) (selected, received bool) {
  return chanrecv(c, elem, false)
}

func selectnbsend(c *hchan, elem unsafe.Pointer) (selected bool) {
  return chansend(c, elem, false, getvallerpc())
}
```

#### 4.4 多个 case

当有多个非 default 的 case 分支的时候，编译器会将其转换为调用`runtime.selectgo()`函数。在`walkSelectCases()`函数中的最后就是对这种情况的处理：

```go
// ncas 是 select 的全部分支的个数
if dflt != nil {
  ncas--
}

// 定义 casorder 为 ncas 大小的 case 语句的数组
casorder := make([]*ir.CommClause, ncas)
// 分别定义 nsends 为发送 Channel 的 case 个数，nrecv 为接收 Channel 的 case 的个数
nsends, nrecvs := 0, 0

// 定义 init 为多 case 编译后待执行的语句列表
var init []ir.Node

base.Pos = sellineno

// 定义 selv 为长度为 ncas 的 scase 类型的数组，scasetype() 函数返回的就是 scase 结构体
selv := typecheck.Temp(types.NewArray(scasetype(), int64(ncas)))
init = append(init, typecheck.Stmt(ir.NewAssignStmt(base.Pos, selv, nil)))

// 定义 order 为 2 倍的 ncas 长度的 TUINT16 类型的数组
// selv 和 order 作为 runtime.selectgo() 函数的入参，前者存放 scase 列表内存地址，
// 后者用来做 scase 排序使用，排序是为了便于挑选出待执行的 case
order := typecheck.Temp(types.NewArray(types.Types[types.TUINT16], 2*int64(ncas)))
...
// 第一个阶段：遍历 case 生成 scase 对象放到 selv 中
for _, cas := range cases {
  ir.SetPos(cas)
  init = append(init, ir.TakeInit(cas)...)
  
  n := cas.Comm
  if n == nil { // 如果是 default 分支，先跳过
    continue
  }
  
  var i init
  var c, elem ir.Node
  
  // 根据 case 的发送或接收类型，获取 chan、elem 的值
  switch n.Op() {
  default:
    base.Fatalf("select %v", n.Op())
  case ir.OSEND:
    n := n.(*ir.SendStmt)
    i = nsends  // 对发送 Channel 类型的 case，i 从 0 开始递增
    nsends++
    c = n.Chan
    elem = n.Value
  case ir.OSELECTCV2:
    n := n.(*ir.AssignListStmt)
    nrecvs++
    i = ncas - nrecvs // 对接收 Channel 类型的 case，i 从 ncas 开始递减
    recv := n.Rhs[0].(*ir.UnaryExpr)
    c = recv.X
    elem = n.Lhs[0]
  }
  
  // 编译器对多个 case 排序后，发送 chan 的 case 在左边，接收 chan 的 case 在右边，在 selv 中也如此
  casorder[i] = cas
  // 定义一个函数，写入 chan 或 elem 到 selv 数组
  setField := func(f string, val ir.Node) {
    r := ir.NewAssignStmt(base.Pos. ir.NewSelectorExpr(base.Pos, ir.ODOT, ir.NewIndexExpr(base.Pos. selv, ir.NewInt(int64(i))), typecheck.Lookup(f)), val)
    init = append(init, typecheck.Stmt(r))
  }
  
  // 将 c 代表的 Channel 写入 selv
  c = typecheck.ConvNop(c, types.Types[types.TUNSAFEPTR])
  setField("c", c)
  // 将 elem 写入 selv
  if !ir.IsBlank(elem) {
    elem = typecheck.ConvNop(elem, types.Types[types.TUNSAFEPTR])
    setField("elem", elem)
  }
  ...
}

// 如果发送和接收 Channel 的个数之和不等于 ncas，说明代码有错误，直接报错
if nsends+nrecvs != ncas {
  base.Fatalf("walkSelectCases: miscount: %v + %v != %v", nsends, nrecvs, ncas)
}

// 从这里开始执行 select 动作
base.Pos = sellineno
// 定义 chosen, recvOK 作为 selectgo() 函数的两个返回值
// chosen 表示被选中的 case 的索引，recvOK 表示对于接收操作，是否成功接收
chosen := typechek.Temp(types.Types[types.TINT])
recvOK := typecheck.Temp(types.Types[types.TBOOL])
r := ir.NewAssignListStmt(base.Pos, ir.OSA2, nil, nil)
r.Lhs = []ir.Node{chosen, recvOK}

// 调用 runtime.selectgo() 函数作为运行时实际执行多 case 的 select 动作的函数
fn := typecheck.LookupRuntime("selectgo")

var fnInit ir.Nodes
r.Rhs = []ir.Node{macall1(fn, fn.Type().Results(), &fnInit, bytePtrToIndex(selv, 0), bytePtrToIndex(order, 0), pc0, ir.NewInt(int64(nsends)), ir.NewInt(int64(nrecvs)), ir.NewBool(dflt == nil))}

init = append(init, fnInit...)
init = append(init, typecheck.Stmt(r))
...

// 定义一个函数，根据 chosen 确定的 case 分支生成 if 语句，执行该分支的语句
dispatch := func(cond ir.Node, cas *ir.CommClause) {
  cond = typecheck.Expr(cond)
  cond = typecheck.DefaultLit(cond, nil)
  
  r := ir.NewIfStmt(base.Pos, cond, nil, nil)
  
  if n := cas.Comm; n != nil && n.Op() == ir.OSELRECV2 {
    n := n.(*ir.AssignListStmt)
    if !ir.IsBlank(n.Lhs[1]) {
      x := ir.NewAssignStmt(base.Pos, n.Lhs[1], recvOK)
      r.Body.Append(typecheck.Stmt(x))
    }
  }
  
  r.Body.Append(cas.Body.Take()...)
  r.Body.Append(ir.NewBranchStmt(base.Pos, ir.OBREAK, nil))
  init = append(init, r)
}

// 如果多 case 中有 default 分支，并且 chosen 小于 0，执行该 default 分支
if dflt != nil {
  ir.SetPos(dflt)
  dispatch(ir.NewBinaryExpr(base.Pos, ir.OLT, chosen, ir.NewInt(0)), dflt)
}

// 如果有 chosen 选中的 case 分支，即 chosen 等于 i，则执行该分支
for i, cas := range casorder {
  ir.SetPos(cas)
  dispatch(ir.NewBinaryExpr(base.Pos, ir.OEQ, chosen, ir.NewInt(int64(i))), cas)
}

return init
```

从对多 case 的编译器处理逻辑可以看到，整个逻辑分为三个极端：

1. 生成 scase 对象数据，定义 selv 和 order 数组，前者存放 scase 数组内存地址，后者用来做 scase 排序使用。对 scase 数组排序是为了以某种机制选出待执行的 case；
2. 编译器生成调用`runtime.selectgo()`的逻辑，并将 selv 和 order 数组作为入参传入，同时定义该函数的返回值。chosen 和 recvOK 表示被选中的 case 的索引，recvOK 表示对于接收操作是否成功接收；
3. 根据 selectgo 的返回值 chosen 来生成 if 语句来执行相应索引的 case。

### 5. selectgo 选择逻辑

`runtime.selectgo()`函数选择 case 分支的逻辑流程图如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1673595151)

#### 5.1 生成轮询顺序和加锁顺序

`runtime.selectgo()`函数首先会执行必要的初始化操作，并生成处理 case 的两种顺序：**轮询顺序 pollorder 和加锁顺序 lockorder**。

```go
// cas0 指向一个类型为 [ncases]scase 的数组
// order0 是一个指向 [2*ncases]uint16 且值都为 0 的数组
// 返回值 chosen 和 recvOK 分别表示选中的 case 的序号，和对接收操作是否成功的布尔值
func selectgo(cas0 *scase, order0 *uint16, pc0 *uintptr, nsends, nrecvs int,block bool) (int, bool) {
  ...
  // 为了将 scase 分配到栈上，这里直接给 cas1 分配了 64KB 大小的数组，同理，给 order1 分配了 128KB 大小的数组
  cas1 := (*[1 << 16]scase)(unsafe.Pointer(cas0))
  order1 := (*[1 << 17]uint16)(unsafe.Pointer(order0))
  
  // ncases 个数是发送 chan 个数 nsends 加上接收 chan 个数 nrecvs
  ncases := nsends + nrecvs
  // scases 切片是上面分配 cas1 数组的前 ncases 个元素
  scases := cas1[:ncases:ncases]
  // 顺序列表 pollorder 是 order1 数组的前 ncases 个元素
  pollorder := order1[:ncases:ncases]
  // 加锁列表 lockorder 是 order1 数组的第二批 ncase 个元素
  lockorder := order1[ncases:][:ncases:ncases]
  ...
  
  // 生成排序顺序
  norder := 0
  for i := range scases {
    case := &scases[i]
    
    // 处理 case 中 Channel 为空的请
    if cas.c == nil {
      cas.elem = nil // 将 elem 置空，便于 GC
      continue
    }
    
    // 通过 fastrandn 函数引入随机性，确定 pollorder 列表中 case 的随机顺序索引
    j := fastrandn(uint32(norder + 1))
    pollorder[norder[ = pollorder[j]
    pollorder[j] = uint16(i)
    norder++
  }
  
  pollorder = pollorder[:norder]
  lockorder = pollorder[:norder]
  
  // 根据 chan 地址确定 lockorder 加锁排序列表的顺序
  // 通过简单的堆排序，以 nlogn 时间复杂度完成排序
  for i := range lockorder {
    j := 1
    // Start with the pollorder to permute cases on the same channel.
    c := scases[pollorder[i]].c
    for j > 0 && scases[lockorder[(j-1)/2]].c.sortkey() < c.sortkey() {
      k := (j - 1) / 2
      lockorder[j] lockorder[k]
      j = k
    }
    lockorder[j] = pollorder[i]
  }
  
  for i := len(lockorder) - 1; i >= 0; i-- {
    o := lockorder[i]
    c := scases[o].c
    lockorder[i] = lockorder[0]
    j := 0
    for {
      k := j*2 + 1
      if k >= i {
        break
      }
      if k+1 < i && scases[lockorder[k]].c.sortkey() < scases[lockorder[k+1]].c.sortkey() {
        k++
      }
      if c.sortkey() < scases[lockorder[k]].c.sortkey() {
        lockorder[j] = lockorder[k]
        j = k
        continue
      }
      break
    }
    lockorder[j] = o
  }
  ...
}
```

从代码中可以看到，轮询顺序 pollorder 是通过`runtime.fastrandn`函数引入了随机性。随机的轮询顺序可以避免 Channel 的饥饿问题，保证公平性。

加锁顺序 lockorder 是按照 Channel 的地址排序后确定的加锁顺序，这样能够避免死锁的发生。

加锁和解锁调用是`runtime.sellock()`和`runtime.selunlock()`函数。从代码逻辑中可以看到，这两个函数分别是按 lockorder 顺序对 Channel 加锁，以及按 lockorder 逆序释放锁：

```go
func sellock(scases []scase, lockorder []uint16) {
  var c *hchan
  for _, o := range lockorder {
    c0 := scases[o].c
    if c0 != c {
      c = c0
      lock(&c.lock)
    }
  }
}

func selunlock(scases []scase, lockorder []uint16) {
  for i := len(lockorder) - 1; i >= 0; i-- {
    c := scases[lockorder[i]].c
    if i > 0 && c == scases[lockorder[i-1]].c {
      comtinue
    }
    unlock(&c.lock)
  }
}
```

#### 5.2 主逻辑

`runtime.selectgo()`的主逻辑分为三个阶段查找或等待某个 Channel 准备就绪：

1. 首先，根据 pollorder 的顺序查找 scases 是否有可以立即首发的 Channel；
2. 其次，将当前 goroutine 加入各 case 的 Channel 对应的首发队列上，并等待其他 goroutine 将其唤醒；
3. 最后，当前 goroutine 被唤醒之后找到满足条件的 Channel 并进行处理。

需要说明的是，`runtime.selectgo`函数会根据不同情况通过 goto 语句跳转到函数内部的不同标签执行相应的逻辑。其中包括：

* bufrecv：从 Channel 缓冲区读取数据；
* bufsend：向 Channel 缓冲区写入数据；
* recv：可以从休眠的发送方获取数据；
* send：可以向休眠的接收方发送数据；
* rclose：可以从关闭的 Channel 读取 EOF；
* sclose：可以向关闭的 Channel 发送数据；
* retc：结束调用并返回。

**第一阶段**

根据 pollorder 的顺序查找 scases 是否有可以立即收发的 Channel，代码如下：

```go
func selectgo(cas0 *scase, order0 *uint16, pc0 *uintptr, nsends, nrecvs int, block bool) (int, bool) {
  ...
  sellock(scases, lockorder)
  ...
  
  // 阶段一：查找可以立即处理的 Channel
  var casi int
  var cas *scase
  var caseSuccess bool
  var caseReleaseTime int64 = -1
  var recvOk bool
  for _, casei := range pollorder {
    casi = int(casei)  // case 的索引
    cas = &cases[casi] // 当前的 case
    c = cas.c
    
    // 处理接收 Channel 的 case
    if casi >= nsends {
      sg = c.sendq.dequeue()
      // 如果当前 Channel 的 sendq 上有等待的 goroutine，就会跳到 recv 标签
      // 并从缓冲区读取数据后将等待 goroutine 中的数据放入到缓冲区中相同的位置
      if sg != nil {
        goto recv
      }
      
      // 如果当前 Channel 的缓冲区不为空，就会跳到 bufrecv 标签处从缓冲区获取数据
      if c.qcount > 0 {
        goto bufrecv
      }
      
      // 如果当前 Channel 已经被关闭，就会跳到 rclose 做一些清除的收尾工作
      if c.closed != 0 {
        goto rclose
      }
    } else { // 处理发送 Channel 的 case
      ...
      // 如果当前 Channel 已经被关闭就会直接跳到 sclose 标签，触发 panic 尝试中止程序
      if c.closed != 0 {
        goto sclose
      }
      // 如果当前 Channel 的 recvq 上有等待的 goroutine，就会跳到 send 标签向 Channel 发送数据
      sg = c.recvq.dequeue()
      if sg != nil {
       goto send
      }
      // 如果当前 Channel 的缓冲区存在空闲位置，就会将待发送的数据存入缓冲区
      if c.qcount < c.dataqsiz {
        goto bufsend
      }
    }
  }
  
  // 如果是非阻塞，即包含 default 分支，会解锁所有的 Channel 并返回
  if !block {
    selunlock(scases, lockorder)
    casi = -1
    goto retc
  }
  ...
}
```

这部分代码的主要逻辑是：

* 当 case 会从 Channel 中接收数据时：

    - 如果当前 Channel 的 sendq 上有等待的 goroutine，就会跳到 recv 标签，并从缓冲区读取数据后将等待 goroutine 中的数据放入到缓冲区中相同的位置；
    - 如果当前 Channel 的缓冲区不为空，就会跳到 bufrecv 标签处从缓冲区获取数据；
    - 如果当前 Channel 已经被关闭，就会跳到 rclose 做一些清除的收尾操作。

* 当 case 会向 Channel 发送数据时：

    - 如果当前 Channel 已经被关闭，就会直接跳到 sclose 标签，触发 panic 尝试中止程序；
    - 如果当前 Channel 的 recvq 上有等待的 goroutine，就会跳到 send 标签向 Channel 发送数据；
    - 如果当前 Channel 的缓冲区存在空闲位置，就会将待发送的数据存入缓冲区。

当 select 语句中包含 default（即 block 为 false）分支时，表示前面的所有 case 都没有被执行，这里会解锁所有 Channel 并返回，意味着当前 select 结构中的收发都是非阻塞的。

**第二阶段**

如果没有可以立即处理的 Channel，则进入主逻辑的下一个阶段，根据需要将当前 goroutine 加入 Channel 对应的收发队列上并等待其他 goroutine 的唤醒。

```go
func selectgo(cas0 *scase, order0 *uint16, pc0 *uintptr, nsends, nrecvs int, block bool) (int, bool) {
  ...
  // 阶段 2：将当前 goroutine 根据需要挂在 chan 的 sendq 和 recvq 上
  gp = getg()
  if gp.waiting != nil {
    throw("gp.waiting != nil")
  }
  
  nextp = &gp.waiting
  for _, casei := range lockorder {
    casi = int(casei)
    cas = &scases[casi]
    c = cas.c
    
    // 获取 sudog，将当前 goroutine 绑定到 sudog 上
    sg := acquireSudog()
    sg.g = gp
    sg.isSelect = true
    sg.elem = cas.elem
    sg.releasetime = 0
    if t0 != 0 {
      sg.releasetime = -1
    }
    sg.c = c
    *nextp = sg
    nextp = &sg.waitlink
    
    // 加入相应的等待队列
    if casi < nsends {
      c.sendq.enqueue(sg)
    } else {
      c.recvq.enqueue(sg)
    }
  }
  
  ...
  // 被唤醒后会根据 param 来判断是否由 close 操作唤醒的，所以先置为 nil
  gp.param = nil
  ...
  // 挂起当前 goroutine
  gopark(selparkcommit, nil, waitReasonSelect, traceEvGoBlockSelect, 1)
  ...
}
```

**第三阶段**

等到 select 中的一些 Channel 准备就绪之后，当前 goroutine 就会被调度器唤醒，这时会继续执行`runtime.selectgo`函数的第三部分：

```go
func selectgo(cas0 *scase, order0 *uint16, pc0 *uintptr, nsends, nrecvs int, block bool) (int, bool) {
  ...
  // 加锁所有的 Channel
  sellock(scases, lockorder)
  
  gp.selectDone = 0
  // param 存放唤醒 goroutine 的 sudog，如果是关闭操作唤醒的，那么就为 nil
  sg = (*sudog)(gp.param)
  gp.param = nil
  
  casi = -1
  cas = nil
  caseSuccess = false
  
  // 当前 goroutine 的 waiting 链表按照 lockorder 顺序存放着 case 的 sudog
  sglist = gp.waiting
  
  // 从 gp.waiting 取消 case 的 sudog 链接之前清除所有元素，便于 GC
  for sg1 := gp.waiting; sg1 != nil; sg1 = sg1.waitlink  {
    sg1.isSelect = false
    sg1.elem = nil
    sg1.c = nil
  }
  
  // 清除当前 goroutine 的 waiting 链表，因为被 sg 代表的协程唤醒了
  gp.waiting = nil
  
  for _, casei := range lockorder {
    k = &scases[casei]
    // 如果相等则说明 goroutine 是被当前 case 的 Channel 收发操作唤醒的
    if sg == sglist {
      // sg 唤醒了当前的 goroutine，则当前 G 已经从 sg 的队列中出队，这里不需要再次出队
      casi = int(casei)
      cas = k
      caseSuccess = sglist.success
      if sglist.releasetime > 0 {
        caseReleaseTime = sglist.releasetie
      }
    } else {
      // 不是此 case 唤醒当前 goroutine，则将 goroutine 从此 case 的发送队列或接收队列中出队
      c = k.c
      if int(casei) < nsends {
        c.sendq.dequeueSudoG(sglist)
      } else {
        c.recvq.dequeueSudoG(sglist)
      }
    }
    
    // 释放当前 case 的 sudog，然后处理下一个 case 的 sudog
    sgnext = sglist.waitlink
    sglist.waitlink = nil
    releaseSudog(sglist)
    sglist = sgnext
  }
  ...
}
```

这部分代码的主要逻辑是：

* 首先，释放当前 goroutine 的等待队列，因为已经被某个 case 的 sudog 唤醒了；
* 其次，遍历全部的 case 的 sudog，找到唤醒当前 goroutine 的 case 的索引并返回，后面会根据它做 Channel 的收发操作；
* 最后，剩下的不是唤醒当前 goroutine 的 case，需要将当前 goroutine 从这些 case 的发送你队列或接收队列出队，并释放这些 case 的 sudog。

#### 5.3 跳转标签

`runtime.selectgo()`函数的最后一段代码，是循环第一阶段用到的跳转标签代码段：

* bufsend 和 bufrecv 两个代码段，这两段代码的执行过程都很简单，就是向 Channel 的缓冲区中发送数据或者从缓冲区中获取数据。
* recv 和 send 是直接收发 Channel 的代码，会调用运行时函数`runtime.send()`和`runtime.recv()`，它们会与处于休眠状态的 goroutine 打交道；
* sclose 和 rclose 两部分是向关闭的 Channel 发送数据或从关闭的 Channel 中接收数据的处理。向关闭的 Channel 发送数据会触发 panic 造成崩溃，而从关闭的 Channel 中接收数据则会清除 Channle 中的相关内容；
* retc 阶段则是退出程序前的清理处理。

```go
bufrecv:
  ...
  recvOK = true
  qp = chanbuf(c, c.recvx)
  if cas.elem != nil {
    typedmemmove(c.elemtype, cas.elem, qp)
  }
  typedemclr(c.elemtype, gp)
  c.recvx++
  if c.recvx == c.dataqsiz {
    c.recvx = 0
  }
  c.qcount--
  selunlock(scases, lockorder)
  goto retc

bufsend:
  ...
  typedmemmove(c.elemtype, chanbuf(c, c.sendx), cas.elem)
  c.sendx++
  if c.sendx == c.dataqsiz {
    c.sendx = 0
  }
  c.qcount++
  selunlock(scases, lockorder)
  goto retc

recv:
  // 可以直接从休眠的 goroutine 获取数据
  recv(c, sg, cas.elem, func() { selunlock(scases, lockorder) }, 2)
  ...
  recvOK = true
  goto retc

rclose:
  // 从一个关闭 Channel 中接收数据会直接清除 Channel 中的相关内容
  selunlock(scases, lockorder)
  recvOK = false
  if cas.elem != nil {
    typedmemclr(c.elemtype, cas.elem)
  }
  ...
  goto retc
  
send:
  ...
  send(c, sg, cas.elem, func() { selunlock(scases, lockorder) }, 2)
  if debugSelect {
    print("syncsend: cas0=", cas0, " c=", c, "\n")
  }
  goto retc

retc:
  // 退出 selectgo() 函数
  if caseReleaseTime > 0 {
    blockevent(caseReleaseTime-t0, 1)
  }
  return casi, recvOK

sclose:
  // 向一个关闭的 Channel 发送数据就会直接 panic 造成程序崩溃
  selunlock(scases, lockorder)
  panic(plainError("send on closed channel"))
```

### 6. 总结

从上面的分析可知，**编译器会对 select 有不同的 case 的情况进行优化以提高性能**：

* 编译器对 select 没有 case、有单个 case 和单个 case + default 的情况进行单独处理，这些处理或直接调用运行时函数，或直接转换成对 Channel 的操作，或以非阻塞的方式访问 Channel，以不加锁的方式提升性能。
* 对更常见的 select 有多个 case 的情况，会调用`runtime.selectgo()`函数来获取执行 case 的索引，并生成 if 语句执行该 case 的代码。

`runtime.selectgo()`函数的执行分为四个步骤：

1. 随机生成一个遍历 case 的轮询顺序 pollorder，并根据 Channel 地址生成加锁顺序 lockorder，速记机顺序能避免 Channel 饥饿，保证公平性；加锁顺序能够避免死锁和重复加锁；
2. 根据 pollorder 的顺序查找 scases 是否有可以立即首发的 Channel，如果有则获取case 的索引进行处理；
3. 如果 pollorder 顺序上没有可以直接处理的 case，则将当前 goroutine 加入各 case 的 Channel 对应的收发队列上，并等待其他的 goroutine 的唤醒；
4. 当调度器唤醒当前 goroutine 时，会再次按照 lockorder 遍历所有的 case，从中查找需要被处理的 case，从中查找需要被处理的 case 索引进行读写处理，同时从所有 case 的发送接收队列中移除掉当前 goroutine。


