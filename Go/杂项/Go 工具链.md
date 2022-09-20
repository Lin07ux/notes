### 1. 环境变量

* `GOOS` 指定编译的操作系统，如指定编译的程序用于 Linux 系统上，可以设置为`GOOS=linux`；
* `GOARCH` 指定编译的 CPU 架构，常见的如`GOARCH=amd64`；

### 2. 编译工具

* `go build -gcflags="-N -l" main.go` 禁止内联编译代码；
* `go tool compile -S -N -l main.go` 禁止内联并生成伪汇编代码；
* `go tool objdump -S -s main main.go` 反汇编，生成伪汇编代码，这样生成的汇编更完整，并翻译了很多符号表。



