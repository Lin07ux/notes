目前，几乎所有层面上的软件和硬件中都需要缓存。Python 3 将 LRU（最近最少使用算法）缓存作为一个名为`lru_cache`的装饰器，使得对缓存的使用非常简单。这种优化技术被称为`memoization`。

使用`lru_cache`装饰器修饰一个方法后，Python 会自动缓存该方法最近的执行结果，并在下次相同调用的时候直接返回缓存值，而不需要再次计算。该装饰器可以指定最多缓存的数量，超过数量之后，会使用 LRU 算法进行淘汰。

> `lru_cache`装饰器属于`functools`标准模块，使用前需要先引入，且 Python 版本至少要为 3.2。

比如，对于一个计算斐波那契数列的方法，一般实现和调用如下：

```Python
import time

def fib(number: int) -> int:
    if number == 0: return 0
    if number == 1: return 1

    return fib(number-1) + fib(number-2)

start = time.time()
fib(40)
print(f'Duration: {time.time() - start}s')
# Duration: 30.684099674224854s
```

现在，可以使用`lru_cache`来优化斐波那契函数，将执行时间从几秒降低到了几纳秒：

```python
import time
from functools import lru_cache

@lru_cache(maxsize=512)
def fib_memoization(number: int) -> int:
    if number == 0: return 0
    if number == 1: return 1

    return fib_memoization(number-1) + fib_memoization(number-2)

start = time.time()
fib_memoization(40)
print(f'Duration: {time.time() - start}s')
# Duration: 6.866455078125e-05s
```

