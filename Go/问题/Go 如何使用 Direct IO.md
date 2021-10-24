> 转摘：[Go 存储 | 怎么使用 direct io ？](https://mp.weixin.qq.com/s/fr3i4RYDK9amjdCAUwja6A)

### 1. 前言

操作系统对文件进行 IO 的时候，**偶人会使用到 page cache，并且采用的是 write back 方式，由操作系统异步刷盘**。由于是异步的，所以如果在数据还未刷盘之前掉电了的话，就会导致数据丢失。

如果想要明确的将数据进行落盘，有两种方式：要么每次写完主动 sync 一下，要么就使用 direct io 的方式指明每一笔 io 数据都要写到磁盘才返回。

在 Go 中也可以使用 direct io 来写入数据，很简单：在 open 文件的时候指定`O_DIRECT`即可。

但是这里涉及到两个问题：

1. `O_DIRECT`这个定义是在 Go 标准中的哪个文件中？
2. direct io 需要 io 的大小和偏移扇区对齐，且还要满足内存 buffer 地址的对齐，这个是怎么做到的？

### 2. O_DIRECT 的知识点

direct io 也就常说的 DIO，一般解决两个问题：

1. 数据落盘，确保掉电不丢失数据；
2. 减少内核 Page Cache 的内存使用，由业务层自己控制内存，更加灵活。

Go 中使用`Open`打开文件时，指定 flag 参数包含`O_DIRECT`即可使用 DIO 模式了。之后数据的 write/read 都是绕过 Page Cache 直接和磁盘进行操作的，从而避免了掉点数据丢失的问题，同时也让应用层可以自己决定内存的使用（避免不必要的 Cache 消耗）。

direct io 模式需要用户保证对齐规则，否则 IO 会报错（抛出“无效参数”的错误），有 3 个需要对齐的规则：

1. IO 的大小必须与扇区大小；
2. IO 偏移按照扇区大小对齐；
3. 内存 buffer 的地址也必须与扇区对齐。

> 机械硬盘的 IO 要扇区对齐，绝大部分扇区都是 512 字节，磁盘的读写最小单元就是扇区；而 SSD 硬盘的读写单元是 page，一个 page 大小一般为 4k，所以需要 4k 对齐。

在 Go 中使用 DIO 模式还需要注意如下两个问题：Go 中`O_DIRECT`是平台不兼容的，而且 Go 中无法精确的控制内存分配地址；

### 3. Go O_DIRECT 平台不兼容

**Go 标准库 os 中是没有 O_DIRECT 这个常量的**。

因为 Go os 库实现的是各个操作系统兼容的实现，direct io 这个在不同的操作系统下实现形态不一样。其实`O_DIRECT`这个打开参数本身就只存在于 Linux 系统中。

以下是各个平台兼容的 Open flag 参数：

```go
// os/file.go
const (
   // Exactly one of O_RDONLY, O_WRONLY, or O_RDWR must be specified.
   O_RDONLY int = syscall.O_RDONLY // open the file read-only.
   O_WRONLY int = syscall.O_WRONLY // open the file write-only.
   O_RDWR   int = syscall.O_RDWR   // open the file read-write.
   // The remaining values may be or'ed in to control behavior.
   O_APPEND int = syscall.O_APPEND // append data to the file when writing.
   O_CREATE int = syscall.O_CREAT  // create a new file if none exists.
   O_EXCL   int = syscall.O_EXCL   // used with O_CREATE, file must not exist.
   O_SYNC   int = syscall.O_SYNC   // open for synchronous I/O.
   O_TRUNC  int = syscall.O_TRUNC  // truncate regular writable file when opened.
)
```

可以看到，`O_DIRECT`并不在其中，因为`O_DIRECT`是和系统平台强相关的一个参数。

那么，`O_DIRECT`定义在哪里呢？跟操作系统强相关的自然是定义在 syscall 库中：

```go
// syscall/zerrors_linux_amd64.go
const (
    // ...
    O_DIRECT         = 0x4000
)
```

所以，在 Go 中使用 DIO 模式打开文件需要使用如下的方式：

```go
// +build linux
// 指明在 linux 平台系统中进行编译
fp := os.OpenFile(name, syscall.O_DIRECT | flag, perm)
```

### 4. Go 无法精确控制内存分配地址

direct io 必须满足三种对齐规则，在 C 语言中，libc 库是调用`posix_memalign`来直接分配出符合要求的内存块，但是 Go 的标准库或内置函数没有提供分配对齐内存的函数。

先思考下 Go 中如何分配 buffer 内存？io 的 buffer 其实就是字节数组，最常见的自然是用 make 来分配，如下：

```go
buffer := make([]byte, 4096)
```

但是这种方式分配的内存并不一定能满足对齐要求。

那么怎么样才能获取到满足对齐要求的地址呢？方法很简单：**先分配一个比预期要大的内存块，然后在这个内存块中找到符合要求的对齐位置**。这是一个任何语言都通用的方法，在 Go 中自然也是可以用的。

比如，现在需要一个 4096 大小的内存块，要求地址按照 512 对齐，可以按照下面的步骤操作：

1. 先分配 4096 + 512 大小的内存块，假设得到的地址是 p1；
2. 然后在`[p1, p1+512]`这个地址范围内查找，一定能够找到 512 对齐的地址，假设这个地址是 p2；
3. 返回 p2 这个地址给用户，用户就能正常使用`[p2, p2+4096]`这个范围内的内存块而不越界，而且是对齐 512 的。

代码如下：

```go
const AlignSize = 512

// 在 block 这个字节数组首地址往后找，找到符合 AlignSize 对齐的地址，并返回
// 这是使用位操作，速度很快
func alignment(block []byte, alignSize int) int {
  return int(uintptr(unsafe.Pointer(&block[0])) & uintptr(AlignSize-1))
}

// 分配 BlockSize 大小的内存块，地址按照 512 对齐
func AlignedBlock(BlockSize int) []byte {
  // 分配一个 []bytep 的切片，大小要比实际需要的大
  block := make([]byte, BlockSize + AlignSize)
  
  // 计算这个 block 内存块往后偏移多少才能对齐到 512
  a := alignment(block, AlignSize)
  offset := 0
  if a != 0 {
    offset = AlignSize - a
  }
  
  // 偏移指定位置，生成一个新的 block，这个 block 将满足地址对齐 512 的要求
  block = block[offset : offset+AlignSize]
  if BlockSize != 0 {
    // 最后醉一次校验
    a = alignment(block, AlignSize)
    if a != 0 {
      log.Fatal("Failed to align block")
    }
  }
  
  return block
}
```

所以，通过以上 AlignedBlock 函数分配出来的内存一定是 512 地址对齐的。

可以看到，这种对齐分配的方式，**缺点就是浪费空间**：明明需要 4k 的内存，实际分配了 4k+512 的内存。

已经有封装好的 direct io 库了(如：[dorectio](https://github.com/ncw/directio))，内部实现及其简单，就跟上面的一样。使用方式也很简单：

```go
// 创建句柄
fp, err := directio.OpenFile(file, os.O_RDONLY, 0666)

// 创建按照 4k 对其的内存块
buffer := directio.AlignedBlock(directio.BlockSize)

// 把文件读取到内存块中
_, err := io.ReadFull(fp, buffer)
```

### 5. 总结

1. direct io 必须满足 io 大小、偏移、内存 buffer 地址三者都满足与扇区大小对齐；
2. `O_DIRECT`不在 os 标准库中，而在于与操作系统相关的 syscall 库中；
3. Go 中无法直接使用`make`来分配对其内存，一般做法是分配一块大一点的内存，然后在里面找到对齐的地址。


