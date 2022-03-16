> 转摘：[你知道的Go切片扩容机制可能是错的](https://mp.weixin.qq.com/s/Dm_KSTrLc3NXDrLBf1Ealg)

### 1. 基础结论

在 Go 1.16 之前，Slice 的扩容机制主要有如下结论：

1. 当需要的容量超过原切片容量的两倍时，会使用所需要的容量作为切片的新容量；
2. 当原切片长度小于 1024 时，新切片的容量会直接翻倍；
3. 当原切片的容量大于等于 1024 时，会反复的增加 25%，直到新容量超过所需要的容量；
4. 在以上结论的基础上（切片的预估容量阶段），考虑内存的高效利用，进行**内存对齐**。

其中，前三点是最被熟知的，而内存对齐知道的就较少。

而且，在 Go 1.18 之后，扩容的算法也发生了变化：

* 临界线从 1024 改成了 256；
* 扩增公式从`newcap += newcap / 4`变成了`newcap += (newcap + 3*threshold) / 4`。

可以预见的是，随着 Go 的迭代升级，Slice 的扩容方式和算法也会继续保持更新。

### 2. 扩容实例

**示例一**：

```go
s1 := make([]int, 0)
s1 = append(s1, 1)
fmt.Println("cap s1", cap(s1)) // cap s1 1
s1 = append(s1, 2)
fmt.Println("cap s1", cap(s1)) // cap s1 2
s1 = append(s1, 3)
fmt.Println("cap s1", cap(s1)) // cap s1 4
s1 = append(s1, 4)
fmt.Println("cap s1", cap(s1)) // cap s1 4
s1 = append(s1, 5)
fmt.Println("cap s1", cap(s1)) // cap s1 8
```

从代码结果可以看到，在 Slice 容量较小时容量是翻倍扩容的，符合前面的结论 2。

**示例二**：

```go
s2 := make([]int, 1024)
fmt.Println("cap s2", cap(s2)) // cap s2 1024
s2 = append(s2, 1)
fmt.Println("cap s2", cap(s2)) // cap s2 1280
s2 = append(s2, 2)
fmt.Println("cap s2", cap(s2)) // cap s2 1280
```

从这段代码的结果可以看到，在 Slice 容量大于 1024 时，会使用`newcap += newcap / 4`公式来扩容，直到满足最新的容量需求。

**示例三**：

```go
s3 := make([]int, 0)
s3 = append(s3, 1, 2, 3, 4, 5)
fmt.Println("cap s3", cap(s3)) // cap s3 6
```

这段代码的运行结果并不是 8，而是 6，这是因先触发了结论 1，之后再进行内存对齐导致的。

### 3. 扩容源码

Slice 的扩容是在`src/runtime/slice.go`文件中的`growslice()`方法来实现的。

#### 3.1 基础计算

下面是 Go 1.16.5 中，扩容时新容量的基础计算的源码：

```go
func growslice(et *_type, old slice, cap int) slice {
  // ... 前置检查

  // 新容量的基础计算
  newcap := old.cap
  doublecap := newcap + newcap
  if cap > doublecap {
    newcap = cap
  } else {
    if old.cap < 1024 {
      newcap = doublecap
    } else {
      // Check 0 < newcap to detect overflow
      // and prevent an infinite loop.
      for 0 < newcap && newcap < cap {
        newcap += newcap / 4
      }
      // Set newcap to the requested cap when
      // the newcap calculation overflowed.
      if newcap <= 0 {
        newcap = cap
      }
    }
  }
  
  // ... 后续的内存对齐计算
  // ... 后置处理
}
```

可以看到基础容量计算中，就是前面提到的前三点结论对应的代码。

#### 3.2 Go 1.18 新基础运算

而在 Go 1.18 中，基础运算中的代码变成如下：

```go
func growslice(et *_type, old slice, cap int) slice {
  // ... 前置检查

  // 新容量的基础计算
  newcap := old.cap
  doublecap := newcap + newcap
  if cap > doublecap {
    newcap = cap
  } else {
    const threshold = 256
    if old.cap < thresold {
      newcap = doublecap
    } else {
      for 0 < newcap && newcap < cap {
        // Transition from growing 2x fro small slices
        // to growing 1.25x for large slices. This formula
        // gives a smooth-ish transition between the two.
        newcap += newcap / 4
      }
      if newcap <= 0 {
        newcap = cap
      }
    }
  }
  
  // ... 后续的内存对齐计算
  // ... 后置处理
```

从注释中可以知道，Go 1.18 版本中的改动是为了使大 Slice 的扩容更加平缓。

#### 3.3 内存对齐计算

除了基础的容量计算，后续还要根据元素的大小进行内存对齐处理。源码如下：

```go
func growslice(et *_type, old slice, cap int) slice {
  // ... 前置检查
  // ... 新容量的基础计算
  
  // 内存对齐计算
  var overflow bool
  var lenmem, newlenmem, capmem uintptr
  // Specialize for common values of et.size.
  // For 1 we don't need any division/multiplication.
  // For sys.PtrSize, compiler will optimize division/multiplication into a shift by a constant.
  // For powers of 2, use a variable shift.
  switch {
  case et.size == 1:
	  lenmem = uintptr(old.len)
	  newlenmem = uintptr(cap)
	  capmem = roundupsize(uintptr(newcap))
	  overflow = uintptr(newcap) > maxAlloc
	  newcap = int(capmem)
  case et.size == sys.PtrSize:
	  lenmem = uintptr(old.len) * sys.PtrSize
	  newlenmem = uintptr(cap) * sys.PtrSize
	  capmem = roundupsize(uintptr(newcap) * sys.PtrSize)
	  overflow = uintptr(newcap) > maxAlloc/sys.PtrSize
	  newcap = int(capmem / sys.PtrSize)
  case isPowerOfTwo(et.size):
	  var shift uintptr
	  if sys.PtrSize == 8 {
		 // Mask shift for better code generation.
		 shift = uintptr(sys.Ctz64(uint64(et.size))) & 63
	  } else {
		 shift = uintptr(sys.Ctz32(uint32(et.size))) & 31
	  }
	  lenmem = uintptr(old.len) << shift
	  newlenmem = uintptr(cap) << shift
	  capmem = roundupsize(uintptr(newcap) << shift)
	  overflow = uintptr(newcap) > (maxAlloc >> shift)
	  newcap = int(capmem >> shift)
  default:
	  lenmem = uintptr(old.len) * et.size
	  newlenmem = uintptr(cap) * et.size
	  capmem, overflow = math.MulUintptr(et.size, uintptr(newcap))
	  capmem = roundupsize(capmem)
	  newcap = int(capmem / et.size)
  }

  // ... 后置处理
}
```

这里根据元素的大小分为 1、`sys.PtrSize`、2 的幂值、默认四种情况进行处理。其中，`sys.PtrSize`的定义为：

```go
// PtrSize is the size of a pointer in bytes - unsafe.Sizeof(uintptr(0)) but as an ideal constant.
// It is also the size of the machine's native word size (that is, 4 on 32-bit systems, 8 on 64-bit).
const PtrSize = 4 << (^uintptr(0) >> 63)
```

也就说，`sys.PtrSize`表示的系统的字长，在 32 位系统中就是 4 字节，在 64 位系统中则为 8 字节。

#### 3.4 roundupsize()

在内存对齐处理中，都会调用`runtime.roundupsize()`方法进行内存对齐处理：

```go
const (
	_MaxSmallSize   = 32768
	smallSizeDiv    = 8
	smallSizeMax    = 1024
	largeSizeDiv    = 128
	_NumSizeClasses = 68
	_PageShift      = 13
	_PageSize       = 1 << _PageShift
)

// runtime/msize.go
// Returns size of memory block that mallocgc will allocate if you ask for the size.
func roundupsize(size uintptr) uintptr {
  if size < _MaxSmallSize {
    if size <= smallSizeMax-8 {
      return uintptr(class_to_size[size_to_class8[divRoundUp(size, smallSizeDiv)]])
    } else {
      return uintptr(class_to_size[size_to_class128[divRoundUp(size-smallSizeMax, largeSizeDiv)]])
    }
  }
  if size+_PageSize < size {
    return size
  }
  return alignUp(size, _PageSize)
}

// divRoundUp returns ceil(n / a).
func divRoundUp(n, a uintptr) uintptr {
	// a is generally a power of two. This will get inlined and
	// the compiler will optimize the division.
	return (n + a - 1) / a
}
```

### 4. 内存对齐示例分析

#### 4.1 []int 类型 Slice 内存对齐分析

下面以前面的示例三进行内存对齐的分析：

```go
s3 := make([]int, 0)
s3 = append(s3, 1, 2, 3, 4, 5)
fmt.Println("cap s3", cap(s3)) // cap s3 6
```

初始时，`s3`的容量为 0，当进行`append`的时候，会进行扩容。扩容的目的容量为 5（也就是需要能容纳 5 个元素）。

此时，在基础容量计算后，得到如下的局部变量值：

```go
doublecap = 0
newcap = 5
```

由于`int`类型的元素，其大小就是系统的字长，所以在内存对齐处理阶段，就会进入到`et.size == sys.PtrSize`这个 case 中：

```go
case et.size == sys.PtrSize:
  lenmen = uintptr(old.len) * sys.PtrSize // 0 * 8 = 0
  newlenmem = uintptr(cap) * sys.PtrSize  // 5 * 8 = 40
  capmem = roundupsize(uintptr(newcap) * sysPtrSize) // ?
  overflow = uintptr(newcap) > maxAlloc/sys.Ptrsize  // false
  newcap = int(capmem / sys.PtrSize) // capmem / 8
```

这里的关键点就在于`roundupsize()`方法，此时调用时传入的参数为`5 * 8 = 40`。由于 40 小于`_MaxSmallSize = 32768`且小于`smallSizeMax = 1024`，所以就会使用如下方式计算最终的对齐大小：

```go
uintptr(class_to_size[size_to_class8[divRoundUp(size, smallSizeDiv)]])
```

其中：

```go
var class_to_size = [_NumSizeClasses]uint16{0, 8, 16, 24, 32, 48, 64, 80, 96, 112, 128, 144, 160, 176, 192, 208, 224, 240, 256, 288, 320, 352, 384, 416, 448, 480, 512, 576, 640, 704, 768, 896, 1024, 1152, 1280, 1408, 1536, 1792, 2048, 2304, 2688, 3072, 3200, 3456, 4096, 4864, 5376, 6144, 6528, 6784, 6912, 8192, 9472, 9728, 10240, 10880, 12288, 13568, 14336, 16384, 18432, 19072, 20480, 21760, 24576, 27264, 28672, 32768}

var size_to_class8 = [smallSizeMax/smallSizeDiv + 1]uint8{0, 1, 2, 3, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 12, 12, 13, 13, 14, 14, 15, 15, 16, 16, 17, 17, 18, 18, 19, 19, 19, 19, 20, 20, 20, 20, 21, 21, 21, 21, 22, 22, 22, 22, 23, 23, 23, 23, 24, 24, 24, 24, 25, 25, 25, 25, 26, 26, 26, 26, 27, 27, 27, 27, 27, 27, 27, 27, 28, 28, 28, 28, 28, 28, 28, 28, 29, 29, 29, 29, 29, 29, 29, 29, 30, 30, 30, 30, 30, 30, 30, 30, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 31, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32}
```

其中：

* `divRoundUp(size, smallSizeDiv)`即为`divRoundUp(40, 8) = 5`；
* 带入到`size_to_class8`数组，得到`size_to_class8[5] = 5`；
* 带入到`class_to_size`数组，得到`class_to_size[5] = 48`。

所以经过`roundupsize()`方法之后，`cammem = 48`，那么最终的容量大小就是`newcap = int(48 / 8) = 6`。

#### 4.2 其他类型 Slice 示例

而对于其他类型，以及其他预期容量的情况，扩容的结果也是不同的。

比如，下面的两个切片的扩容结果就不一样：

```go
s1 := make([]int32, 0)
s1 = append(s1, 1)
fmt.Println("cap s1", cap(s1)) // cap s1 2

s2 := make([]int, 0)
s2 = append(s2, 1)
fmt.Println("cap s2", cap(s2)) // cap s2 1
```

可以看到，前面的结论 1 和 2 也并非总是正确的。

### 5. 总结

1. 不同的切片类型，扩容结果可能是不同的。Go 的 runtime 对 Slice 进行扩容时，会调用`roundupsize()`方法对内存进行对齐取整，会造成扩容结果发生变化；
2. 不同版本的 Go 在 Slice 的扩容算法上会不同，需要保持更新；
3. Go 切片的扩容机制比较复杂，会受到 Go 版本、操作系统、数据类型等因素的影响，机制都会有所不同，要具体情况具体分析。

