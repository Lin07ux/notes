### 1. 调用规约

Go 的函数调用规约在 1.17 版本发生了变化：

* Go 1.17 之前的函数调用，参数和返回值都在栈上传递，这是为了简化在不同架构的计算机上的处理；
* Go 1.17 之后，9 个以内的参数在寄存器传递，9 个以外的在栈上传递；9 个以内的返回值通过寄存器传递，9 个以外的在栈上传递。

切换到基于寄存器的调用惯例后，Go 程序的运行性能提高了约 5%，二进制文件大小减少了约 2%。

CPU 访问寄存器的速度要远高于栈内存，参数在栈上传递会增加栈的内存空间，并且影响栈的扩缩容和垃圾回收。使用寄存器传递参数和返回值，能使这些缺点得到优化。


### 2. 操作系统线程和 goroutine 有什么区别？

* goroutine 运行在用户态，更轻量级，被休眠和唤醒的成本比较低，而操作系统线程同时存在用户态和内核态，在休眠和唤醒时涉及到内核态的切换，成本较高；
* goroutine 数量没有限制，即便数量很多也没有太大影响（除了内存占用），但操作系统线程数量较多时，会对操作系统的调度机制产生较大影响。

### 3. 空结构体

Go 语言中，不包含任何字段的结构体就是空结构体，有两种定义方式：

* 匿名空结构体：`var e struct{}`
* 命名空结构体：先定义命令空类型`type emptyStruct struct{}`，然后进行实例化`var e emptyStruct`

空结构体的特点有：

* 零内存占用：空结构体不占用任何内存空间；
* 地址相同：Go 中所有的空结构体所指向的地址都是相同的；
* 无状态：空结构体不包含任何字段，所以它没有状态。

> 空对象都指向相同的地址，是因为 Go 在进行对象实例化时，如果对象大小为 0 则都返回一个固定的变量的地址：
> 
> ```go
> // src/runtime/malloc.go
> var zerobase uintptr
> func mallocgc(size uintptr, typ *_type, needzero bool) unsafe.Pointer {
>   ...
>   if size == 0 {
>     return unsafe.Pointer(&zerobase)
>   }
>   ...
> }
> ```

空结构体的用处有：

* 实现 Set 集合类型：Go 语言没有内置 Set 集合类型，但是可以利用 map 类型来实现，将元素作为 map 的 key，空结构体作为 value 即可；
* 用作通道信号：对于只关心信号传递而不关心具体内容的通道，可以使用空结构体作为通道值；
* 作为方法接收器：有时候为了将一组方法聚合在一起，而且不需要存储任何数据，但是不想将其作为一个单独的 package，就可以将其都挂载一个命名空结构体实例上。