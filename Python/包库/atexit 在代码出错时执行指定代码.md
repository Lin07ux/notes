> 转摘：[如何优雅地实现在 Python 退出时强制运行一段代码](https://mp.weixin.qq.com/s/Q65F49ZhFV02mqVPyxILHg)

### 1. 需求

设想这样一个场景：要给一个项目开发测试程序，程序开始运行的时候会创建初始环境，测试完成之后会清理环境。

这段逻辑本身非常简单：

```python
setup()
test()
clean()
```

但由于测试的代码比较复杂，在调试时程序可能会出现异常，导致每次`clean()`函数还没来得及运行，程序就崩溃退出了。

### 2. try...except...

可以考虑使用如下的方式来运行：

```Python
setup()

try:
    test()
except Exception as e:
    print('运行异常：', e)

clean()
```

使用`try...expect...`会导致无法直观的获取到异常栈，只能看到一个基本的异常提示。为了找到问题，必须让程序把错误爆出来。但这样一来，`clean()`又不能正常运行了。

### 3. atexit

为了能让程序在报错后还能继续运行`clean()`函数，可以使用 Python 自带的`atexit`模块：

```Python
import atexit

@atexit.register
def clean():
    pass

setup()
test()
```

这样，就不需要显式的调用`clean()`函数，就可以在程序无论是否正常结束，`clean()`函数都会执行。

### 4. 注意事项

`atexit`在使用中，有以下几个注意事项：

* 可以注册多个退出函数，会按照注册时间从晚到早依次执行。
* 如果`clean()`函数有参数，那么可以不用装饰器，而是直接调用`atexit.register(clean_1, 参数1, 参数2, 参数3='xxx')`。
* 如果程序是被没有处理过的系统信号杀死的，那么注册的函数无法正常执行。
* 如果发生了严重的 Python 内部错误，注册的函数无法正常执行。
* 如果你动调用了`os._exit()`，注册的函数无法正常执行。

比如：

```Python
import atexit

@atexit.register
def clean_1():
    ...

@atexit.register
def clean_2():
    ...
```

会先运行`clean_2()`后运行`clean_1()`。

