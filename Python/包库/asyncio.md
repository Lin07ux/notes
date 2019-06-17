> 转摘：[深入理解Python中的asyncio](http://www.lylinux.net/article/2019/6/9/57.html)

Python asyncio 是用来编写并发代码的库，可以使用`async/await`语法，并被用作多个提供高性能异步框架的基础，包括网络和网站服务，数据库连接库，分布式任务队列等等。asyncio 往往是构建 IO 密集型和高层级结构化网络代码的最佳选择。

## 一、基本概念

在 asyncio 中有四个基本概念，分别是：

* Eventloop 是 asyncio 应用的核心、中央总控。
* Coroutine 协程，也就是一个函数，用于用协程方式运行程序。
* Future 异步的底层实现，类似 JavaScript 中的 Promise。
* Task 是 Future 的封装子类，用于实现协程。

这四个概念是互相关联、相互递进的关系。

### 1.1 Eventloop

Eventloop 实例提供了注册、取消、执行任务和回调的方法。简单来说，就是可以把一些异步函数注册到这个事件循环上，事件循环会循环执行这些函数（每次只能执行一个），如果当前正在执行的函数在等待 I/O 返回，那么事件循环就会暂停它的执行去执行其他函数。当某个函数完成 I/O 后会恢复，等到下次循环到它的时候就会继续执行。

### 1.2 Coroutine

协程本质就是一个函数，该函数使用`async/await`关键词进行定义，这使其与一般的函数不同：协程函数会在遇到`await`的时候，暂停执行，让出 CPU，使调用者继续运行其他的代码。

```Python
import asyncio
import time

async def a():
    print('Suspending a')
    await asyncio.sleep(3)
    print('Resuming a')


async def b():
    print('Suspending b')
    await asyncio.sleep(1)
    print('Resuming b')


async def main():
    start = time.perf_counter()
    await asyncio.gather(a(), b())
    print(f'{main.__name__} Cost: {time.perf_counter() - start}')


if __name__ == '__main__':
    asyncio.run(main())
```

执行上述代码，可以看到类似这样的输出：

```
Suspending a
Suspending b
Resuming b
Resuming a
main Cost: 3.0023356619999997
```

可以看到，方法`a()`和`b()`是交替运行的，而且程序的总共运行时间略略大于 3s，而远不到两个方法总共休眠的时间 4s。

### 1.3 Future

Future 是对协程的封装，表示一个“未来”对象，类似于 Javascript 中的 Promise，当异步操作结束后会把最终结果设置到这个 Future 对象上。

```Python
import asyncio

def fun():
    print("inner fun")
    return 111

loop = asyncio.get_event_loop()
future = loop.run_in_executor(None, fun)
# 这里没有使用 await，输出：
# inner fun

print(future)
# 可以看到，fun 方法状态是 pending
# 输出：<Future pending cb=[_chain_future.<locals>._call_check_cancel() at /usr/local/Cellar/python/3.7.3/Frameworks/Python.framework/Versions/3.7/lib/python3.7/asyncio/futures.py:348]>

future.done()
# 输出：
# Fasle

print([m for m in dir(future) if not m.startswith('_')])
# 输出：
# ['add_done_callback', 'cancel', 'cancelled', 'done', 'exception', 'get_loop', 'remove_done_callback', 'result', 'set_exception', 'set_result']

future.result()
# 这个时候如果直接调用 result() 方法会报错，输出：
# Traceback (most recent call last):
#   File "<input>", line 1, in <module>
# asyncio.base_futures.InvalidStateError: Result is not set.

async def runfun():
    result = await future
    print(result)

loop.run_until_complete(runfun())
# 也可以通过 loop.run_until_complete(future) 来执行，这里只是为了演示 await，输出：
# 111

print(future)
# 输出：
# <Future finished result=111>

future.done()
# 输出：
# True

future.result()
# 输出：
# 111
```

### 1.4 Task

Future 对象提供了很多任务方法（如完成后的回调，取消，设置任务结果等等），但是一般情况下开发者不需要操作 Future 这种底层对象，而是直接用 Future 的子类 Task 协同的调度协程来实现并发。

Task 是一个与 Future 类似的对象，被用来在事件循环中运行协程，是非线程安全的。如果一个协程在等待一个 Future 对象，Task 对象会挂起该协程的执行并等待该 Future 对象完成，当该 Future 对象完成被打包的协程将恢复执行。

事件循环使用协同日程调度：一个事件循环每次运行一个 Task 对象，而一个 Task 对象会等待一个 Future 对象完成，该事件循环会运行其他 Task、回调或执行 IO 操作。

```Python
import asyncio

async def a():
    print('Suspending a')
    await asyncio.sleep(3)
    print('Resuming a')

task = asyncio.ensure_future(a())
loop.run_until_complete(task)
```

输出：

```
Suspending a
Resuming a
```

## 二、常见方法

### 2.1 asyncio.gather 和 asyncio.wait

`asyncio.gather`和`asyncio.wait`可以让多个协程并发执行，但是两者的用法和返回值是不相同的，一般情况下，用`asyncio.gather`就足够了：

1.	`asyncio.gather`能收集协程的结果，而且会按照输入协程的顺序保存对应协程的执行结果，而`asyncio.wait`的返回值有两项：第一项是完成的任务列表，第二项表示等待完成的任务列表。
2. `asyncio.wait`支持接受一个参数`return_when`，在默认情况下，`asyncio.wait`会等待全部任务完成(`return_when='ALL_COMPLETED'`)，还支持`FIRST_COMPLETED`（第一个协程完成就返回）和`FIRST_EXCEPTION`（出现第一个异常就返回）。

```Python
import asyncio

async def a():
    print('Suspending a')
    await asyncio.sleep(3)
    print('Resuming a')
    return 'A'

async def b():
    print('Suspending b')
    await asyncio.sleep(1)
    print('Resuming b')
    return 'B'

# asyncio.gather 执行
async def fun1():
    return_value_a, return_value_b = await asyncio.gather(a(), b())
    print(return_value_a, return_value_b)
  
asyncio.run(fun1())
# 输出：
# Suspending a
# Suspending b
# Resuming b
# Resuming a
# A B

# asyncio.wait 执行
async def fun2():
    done, pending = await asyncio.wait([a(),b()])
    print(done)
    print(pending)
    task = list(done)[0]
    print(task)
    print(task.result())

asyncio.run(fun2())
# 输出：
# Suspending b
# Suspending a
# Resuming b
# Resuming a
# {<Task finished coro=<a() done, defined at <input>:1> result='A'>, <Task finished coro=<b() done, defined at <input>:8> result='B'>}
# set()
# <Task finished coro=<a() done, defined at <input>:1> result='A'>
# A

# asyncio.wait 使用不同的 return_when 参数
async def fun3():
    done, pending = await asyncio.wait([a(),b()],return_when=asyncio.tasks.FIRST_COMPLETED)
    print(done)
    print(pending)
    task = list(done)[0]
    print(task)
    print(task.result())

asyncio.run(fun3())
# 输出：
# Suspending a
# Suspending b
# Resuming b
# {<Task finished coro=<b() done, defined at <input>:8> result='B'>}
# {<Task pending coro=<a() running at <input>:3> wait_for=<Future pending cb=[<TaskWakeupMethWrapper object at 0x10757bf18>()]>>}
# <Task finished coro=<b() done, defined at <input>:8> result='B'>
# B
```

### 2.2 asyncio.create_task、loop.create_task 以及 asyncio.ensure_future

这三种方法都可以创建 Task，但`asyncio.create_task`是对后两者更高阶的封装，从 Python 3.7 开始可以统一的使用该方法了。

`asyncio.ensure_future`根据接收的参数的类别不同，会调用和执行不同的方法和逻辑：

1.	如果参数是协程，其底层使用`loop.create_task`，返回 Task 对象；
2.	如果是 Future 对象会直接返回；
3.	如果是一个 awaitable 对象，会 await 这个对象的`__await__`方法，再执行一次`asyncio.ensure_future`方法，最后返回 Task 或者 Future。

所以一般情况下直接用`asyncio.create_task`就可以了。

### 2.3 Task.add_done_callback 添加回调

可以使用 Task 对象实例的`add_done_callback`方法添加任务执行成功后的回调。

```Python
import asyncio
import functools

def callback(future):
    print(f'Result: {future.result()}')

def callback2(future, n):
    print(f'Result: {future.result()}, N: {n}')

async def funa():
    await asyncio.sleep(1)
    return "funa"

async def main():
    task = asyncio.create_task(funa())
    task.add_done_callback(callback)
    await task
    # 这样可以为 callback 传递附加参数
    task = asyncio.create_task(funa())
    task.add_done_callback(functools.partial(callback2, n=1))
    await task

if __name__ == '__main__':
    asyncio.run(main())
```

运行结果类似如下：

```Python
Result: funa
Result: funa, N: 1
```

### 2.4 loop.run_in_executor 异步执行同步代码

如果有同步逻辑，想要用 asyncio 来实现并发，那么可以使用`loop.run_in_executor`方法将同步函数逻辑转化成一个协程，该方法接收两个参数：

* 第一个参数是要传递`concurrent.futures.Executor`实例的，传递`None`会选择默认的`executor`；
* 第二个参数则是同步方法。

```Python
import asyncio
import time
def a1():
    time.sleep(1)
    return "A"

async def b1():
    await asyncio.sleep(1)
    return "B"

async def main():
    start = time.perf_counter()
    
    loop = asyncio.get_running_loop()
    await asyncio.gather(loop.run_in_executor(None, a1), b1())
    
    print(f'main method Cost: {time.perf_counter() - start}')

if __name__ == '__main__':
    asyncio.run(main())
```

输出结果类似如下：

```
main method Cost: 1.003049782000005
```

可以看到，虽然有一个同步方法一起执行，但是总的时间开销依旧只略多于 1s，也就是同步方法也是异步执行的了。

