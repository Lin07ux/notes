> 转摘：[10+ 条 Go 官方谚语，你知道几条？](https://mp.weixin.qq.com/s/cJON1H68eBFeuBpWhFbXXw)

1. Don't communicate by sharing memory, share memory by communicating.

    不要通过共享内存来通信，通过通信来共享内存。
    
2. Concurrency is not parallelism.

    并发不是并行。
    
3. Channels orchestrate; mutexes serialize.

    通道是协调的，互斥是串行的。
    
4. The bigger the interface, the weaker the abstraction.

    接口越大，抽象性越弱。
    
5. Make the zero value useful.

    让零值变的有用。
    
6. interface{} says nothing.

    interface{} 什么也没有说明。
    
7. Gofmt's style is no one's favorite, yet gofmt is everyone's favorite.

    Gofmt 的风格没有人喜欢，但是 Gofmt 却是大家的最爱。
    
8. A little copying is better than a little dependency.

    复制一点总比依赖一点好。
    
9. Syscall must always be guarded with build tags.

    Syscall 必须始终用 build 标签来保护。
    
10. Cgo must always be guarded with build tags.

    Cgo 必须使用用 build 标签来保护。
    
11. Cgo is not Go.

    Cgo 不是 Go。
    
12. With the unsafe package there are no guarantees.

    使用 unsafe 包没有任何保证。
    
13. Clear is better than clever.

    清晰比聪明的好。
    
14. Reflection is never clear.

    反射从来不是清晰的。
    
15. Errors are values.

    错误就是一个值（把错误作为值来处理）。
    
16. Don't just check errors, handle them gracefully.

    不要只是检查错误，要优雅的处理它们。
    
17. Design the architecture, name the components, document the details.

    设计架构，命名组件，文档化细节。
    
18. Documentation is for users.

    文档是为用户准备的。
    
19. Don't panic.

    不要用恐慌。

