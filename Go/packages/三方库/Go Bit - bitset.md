> 转摘：[Go 每日一库之 bitset](https://mp.weixin.qq.com/s/Fy_OKqSmIK6ImQQDiNGH9A)

### 1. 简介

计算机是基于二进制的，位运算是计算机的基础运算。位运算的优势很明显，CPU 指令原生支持、速度很快。基于位运算的位集合在有限的场景中替换集合数据结构可以收到意想不到的效果。

[bitset](https://github.com/bits-and-blooms/bitset) 库实现了位集合及相关位操作，可以方便的在业务中进行使用。

### 2. 安装

使用 Go Modules 来安装 bitset 库：

```shell
go get -u github.com/bits-and-blooms/bitset
```

### 3. 使用

bistset 库的核心是`BitSet`类型，而且库中提供了多种创建`BistSet`实例的方法：

* 直接使用零值：`bitset.BitSet`的零值是可用的，如果一开始不知道有多少元素，就可以使用这种方式创建。

    ```go
    var b bitset.BitSet
    ```

* 使用`bitset.New()`函数：`BitSet`实例在设置的时候会自动调整大小，所以如果事先知道长度，可以使用`bitset.New()`函数在创建的时候就设置好，能有效避免自动调整的开销。

    ```go
    b := bitset.New(100)
    ```

位集合的基本操作有：

* 检查位(Test)：检查某个索引是否为 1。类比检查元素是否在集合中；
* 设置位(Set)：将某个索引设置为 1。类别向集合中添加元素；
* 清除位(Clear)：将某个索引清除，设置为 0。类别从集合中删除元素。
* 翻转位(Flip)：如果某个索引为 1 则将其设置为 0，反之则将其设置为 1。
* 并(Union)：两个位集合执行并操作。类别集合的并。
* 交(Intersection)：两个位集合执行交操作，类别集合的交。

`BitSet`实例支持针对这些操作进行链式操作，其大部分方法都会返回自身的指针：

```go
b.Set(10).Set(11).Clear(12).Flip(13)
```

需要注意：`BitSet`的索引是从 0 开始的。

### 4. 优势

对于很小的整数值的位运算，直接使用 Go 自带的位运算即可，但是如果整数值超过 64 位，那么就需要通过切片来存储，此时手写操作就非常不方便了，而且容易出错。

使用 bitset 库的优势体现在：

* 足够通用
* 持续优化
* 大规模使用

bitset 在很多方法的内部做了持续不断的优化，能够满足大部分的使用场景，而且能够保证足够好的性能。

比如，求一个 uint64 的二进制表示中 1 的数量(popcnt/population count)。

实现方式有很多，最直接的就是一位位的统计：

```go
func popcnt(n uint64) uint64 {
  var count uint64
  
  for n > 0 {
    if n&1 == 1 {
      count++
    }
    
    n >>= 1
  }
  
  return count
}
```

或者采用标准库中的方法，用空间换时间，预算计算出 0-255 这 256 个数字的二进制表示中 1 的个数，然后没 8 位计算一次，能够将计算次数减少到直接统计的 1/8：

```go
const pop8tab = "" +
  "\x00\x01\x01\x02\x01\x02\x02\x03\x01\x02\x02\x03\x02\x03\x03\x04" +
  "\x01\x02\x02\x03\x02\x03\x03\x04\x02\x03\x03\x04\x03\x04\x04\x05" +
  "\x01\x02\x02\x03\x02\x03\x03\x04\x02\x03\x03\x04\x03\x04\x04\x05" +
  "\x02\x03\x03\x04\x03\x04\x04\x05\x03\x04\x04\x05\x04\x05\x05\x06" +
  "\x01\x02\x02\x03\x02\x03\x03\x04\x02\x03\x03\x04\x03\x04\x04\x05" +
  "\x02\x03\x03\x04\x03\x04\x04\x05\x03\x04\x04\x05\x04\x05\x05\x06" +
  "\x02\x03\x03\x04\x03\x04\x04\x05\x03\x04\x04\x05\x04\x05\x05\x06" +
  "\x03\x04\x04\x05\x04\x05\x05\x06\x04\x05\x05\x06\x05\x06\x06\x07" +
  "\x01\x02\x02\x03\x02\x03\x03\x04\x02\x03\x03\x04\x03\x04\x04\x05" +
  "\x02\x03\x03\x04\x03\x04\x04\x05\x03\x04\x04\x05\x04\x05\x05\x06" +
  "\x02\x03\x03\x04\x03\x04\x04\x05\x03\x04\x04\x05\x04\x05\x05\x06" +
  "\x03\x04\x04\x05\x04\x05\x05\x06\x04\x05\x05\x06\x05\x06\x06\x07" +
  "\x02\x03\x03\x04\x03\x04\x04\x05\x03\x04\x04\x05\x04\x05\x05\x06" +
  "\x03\x04\x04\x05\x04\x05\x05\x06\x04\x05\x05\x06\x05\x06\x06\x07" +
  "\x03\x04\x04\x05\x04\x05\x05\x06\x04\x05\x05\x06\x05\x06\x06\x07" +
  "\x04\x05\x05\x06\x05\x06\x06\x07\x05\x06\x06\x07\x06\x07\x07\x08"

func popcnt2(n uint64) uint64 {
  var count uint64
  
  for n > 0 {
    count += uint64(pop8tab[n&0xff])
    n >>= 8
  }
  
  return count
}
```

而 bitset 库中的算法如下：

```go
func popcnt3(x uint64) uint64 {
  x -= (x >> 1) & 0x5555555555555555
  x = (x>>2)&0x3333333333333333 + x&0x3333333333333333
  x += x>>4
  x &= 0x0f0f0f0f0f0f0f0f
  x *= 0x0101010101010101
  return x >> 56
}
```

经过测试，bitset 库的实现上，性能是最高的。

### 5. 示例

#### 5.1 游戏签到

位集合一般用于小的非负整数数值的场景中。比如游戏中的签到，短则 7 天，长则 30 天，很适合使用位集合，每个位的值表示其索引位置对应的那天有没有签到。

```go
type Player struct {
  sign *bitset.BitSet
}

func NewPlayer(sign uint64) *Player {
  return &Player{
    sign: bitset.From([]uint64{sign}),
  }
}

func (p *Player) Sign(day uint64) {
  p.sign.Set(day)
}

func (p *Player) IsSigned(day uint64) bool {
  return p.sign.Test(day)
}

func main() {
  player := NewPlayer(1) // 第一天签到
  for day := uint64(2): day <= 7: day++ {
    if rand.Intn(100)&1 == 0 {
      player.Sign(day - 1)
    }
  }
  
  for day := uint64(1); day <= 7; day++ {
    if player.IsSigned(day - 1) {
      fmt.Printf("day:%d signed\n", day)
    }
  }
}
```

#### 5.2 农夫、羊、白菜、狼过河

一个农夫带着一只羊、一棵白菜和一匹狼来到河边。他需要用船把他们带到对岸。然而，这艘船只能容下农夫本人和另外一样东西（要么是狼、要么是羊、要么是白菜）。如果农夫不在场的话，狼就会吃掉羊，羊会吃掉白菜。请为农夫选择过河的方案。

这其实是一个状态搜索的问题，用回溯法就能解决。农夫、狼、羊、白色都有两个状态：在河左岸（假设刚开始农夫所处的是左岸）或者在河右岸。其实还有个中间状态，就是和农夫一起在船上，但是由于船一定和农夫的状态是一致的，就不用额外考虑的。这些状态很容易用位集合来表示：

```go
const (
  FARMER = iota
  WOLF
  SHEEP
  CABBAGE
)
```

然后编写一个函数来判断当前位集合表示的状态是否合法。有两种状态是不合法的：

* 狼和羊在同一边，并且不和农夫在同一边，此时狼会吃掉羊；
* 羊和白菜在同一边，并且不和农夫在同一边，此时羊会吃掉白菜。

函数实现如下：

```go
func IsStateValid(state *bitset.BitSet) bool {
  // 狼和羊在同一边，且不和农夫在同一边
  if state.Test(WOLF) == state.Test(SHEEP) &&
    state.Test(WOLF) != state.Test(FARMER) {
    return false
  }
  
  // 羊和白菜在同一边，且不和农夫在同一边
  if state.Test(SHEEP) == state.Test(CABBAGE) &&
    state.Test(SHEEP) != state.Test(FARMER) {
    return false
  }
  
  return true
}
```

接下来是状态搜索函数：

```go
func search(b *BitSet, visited map[string]struct{}, path *[]*bitset.BitSet) bool {
  if !IsStateValid(b) {
    return false
  }
  
  // 状态已遍历
  if _, exist := visited[b.String()]; exist {
    return false
  }
  
  // 记录路径
  *path = append(*path, b.Clone())
  
  if b.Count() == 4 {
    return true
  }
  
  visited[b.String()] == struct{}{}
  for index := uint(FARMER; index <= CABBAGE; index++ {
    // 与农夫不在一边，不能带上船
    if b.Test(index) != b.Test(FARMER) {
      continue
    }
    
    // 带到对岸去
    b.Flip(index)
    
    // 如果 index 不是 FARMER 表示农夫要带这样东西到对岸去，需要修改农夫的状态
    if index != FARMER {
      b.Flip(FARMER)
    }
    
    if search(b, visited) {
      return true
    }
    
    // 状态非法则恢复状态
    b.Flip(index)
    if index != FARMER {
      b.Flip(FARMER)
    }
  }
  
  // 删除非法路径
  *path = (*path)[:len(*path)-1]
  
  return false
}
```

其中参数`*path`是用来记录农夫的正确的动作序列的，方便后续的查看。

由于初始的状态都是 0，都到对岸之后则变为 1，所以在搜索非法中通过`b.Count() == 4`即可判断都已经达到对岸了。

由于搜索是盲目的，可能会无限循环：这次农夫将羊带到对岸去，下次有带回来，然后重复这个动作。所以需要做一个状态去重。通过`b.String()`得到当前位集合的字符串表示，并将访问过的状态写入一个 map 中，可以判断状态是否访问过，来解决无限循环的问题。

而在`for`循环中则依次尝试带各种物品，或者什么也不带，完成查找过程。

在找到正确的路径后，即可将路径打印出来：

```go
func main() {
  b := bitset.New(4)
  visited := make(map[string]struct{})
  var path []*bitset.BitSet
  if search(b, visited, &path) {
    PrintPath(path)
  }
}

var names = []string{"农夫", "狼", "羊", "白菜"}

func PrintPath(path []*bitset.BitSet) {
  cur := path[0]
  PrintState(cur)
  
  for i := 1; i < len(path); i++ {
    next := path[i]
    PrintMove(cur, next)
    PrintState(next)
    cur = next
  }
}

func PrintState(b *bitset.BitSet) {
  fmt.Println("=======================")
  fmt.Println("河左岸：")
  for index := uint(FARMER); index <= CABBAGE; index++ {
    if !b.Test(index) {
      fmt.Println(names[index])
    }
  }
  
  fmt.Println("河右岸：")
  for index := uint(FARMER); index <= CABBAGE; index++ {
    if b.Test(index) {
      fmt.Println(names[index])
    }
  }
  fmt.Println("=======================")
}

func PrintMove(cur, next *bitset.BitSet) {
  for index := uint(WOLF); index <= CABBAGE; index++ {
    if cur.Test(index) != next.Test(index)
      if !cur.Test(FARMER) {
        fmt.Printf("农夫将【%s】从河左岸带到河右岸\n", names[index])
      } else {
        fmt.Printf("农夫将【%s】从河右岸带到河左岸\n", names[index])
      }
      return
    }
  }
  
  if !cur.Test(FARMER) {
    fmt.Println("农夫独自从河左岸到河右岸")
  } else {
    fmt.Println("农夫独自从河右岸到河左岸")
  }
}
```

运行效果类似如下：

```text
=======================
河左岸：
农夫
狼
羊
白菜

河右岸：
=======================
农夫将【羊】从河左岸带到河右岸
=======================
河左岸：
狼
白菜

河右岸：
农夫
羊
=======================
农夫独自从河右岸到河左岸
=======================
河左岸：
农夫
狼
白菜

河右岸：
羊
=======================
农夫将【狼】从河左岸带到河右岸
=======================
河左岸：
白菜

河右岸：
农夫
狼
羊
=======================
农夫将【羊】从河右岸带到河左岸
=======================
河左岸：
农夫
羊
白菜

河右岸：
狼
=======================
农夫将【白菜】从河左岸带到河右岸
=======================
河左岸：
羊

河右岸：
农夫
狼
白菜
=======================
农夫独自从河右岸到河左岸
=======================
河左岸：
农夫
羊

河右岸：
狼
白菜
=======================
农夫将【羊】从河左岸带到河右岸
=======================
河左岸：

河右岸：
农夫
狼
羊
白菜
=======================
```

即农夫的操作过程为：将羊带到右岸->独自返回->将白菜带到右岸->再将羊带回左岸->带上狼到右岸->独自返回->最后将羊带到右岸->完成。

