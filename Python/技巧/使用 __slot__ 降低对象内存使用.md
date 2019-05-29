Python 是动态语言，会把所有的变量当做对象处理，所以使用`sys.getsizeof()`方法可以查看变量使用的内存，但是该方法看到的其实并不是变量真实占用的内存。

例如，`sys.getsizeof("")`返回 49bytes，`sys.getsizeof(1)`返回 28bytes。

### 1. 为什么 Python 的值会占用这么多内存

Python 对每个类对象都附加了大量的信息，这些会占用很大的内存。比如，有如下一个类：

```python
class DataItem(object):
    def __init__(self, name, age, address):
        self.name = name
        self.age = age
        self.address = address
```

可以通过如下的方式查看该类对象全部的附加信息：

```Python
def dump(obj):
  for attr in dir(obj):
    print("  obj.%s = %r" % (attr, getattr(obj, attr)))

d1 = DataItem("Alex", 42, "middle of nowher")
dump(d1)
```

结果类似如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558794013426.png)

### 2. 类对象实际占用多少内存

那么，类的实例对象真实占用有多少内存呢？下边有一个函数可以通过递归的方式，调用`getsizeof`函数，计算对象实际数据量：

```Python
def get_size(obj, seen=None):
    if seen is None: seen = set()
    
    # Recursively finds size of objects
    size = sys.getsizeof(obj)
    
    obj_id = id(obj)
    
    if obj_id in seen: return 0
    
    # Important mark as seen *before* entering recursion to gracefully handle
    # self-referential objects
    seen.add(obj_id)
    if isinstance(obj, dict):
      size += sum([get_size(v, seen) for v in obj.values()])
      size += sum([get_size(k, seen) for k in obj.keys()])
    elif hasattr(obj, '__dict__'):
      size += get_size(obj.__dict__, seen)
    elif hasattr(obj, '__iter__') and not isinstance(obj, (str, bytes, bytearray)):
      size += sum([get_size(i, seen) for i in obj])
    return size
```

执行示例如下：

```Python
d1 = DataItem("Alex", 42, "-")
print(get_size(d1))
# 460

d2 = DataItem("Boris", 24, "In the middle of nowhere")
print(get_size(d2))
# 484

print(get_size([d1]))
# 532

print(get_size ([d1, d2]))
# 863 (小于 460 + 484)

print(get_size ([d1, d2, d1]))
# 871 (比上面略大一点)
```

#### 如何减少内存占用

Python 中的对象附加了一系列的功能，比如可以随时增加数据，如果不需要这些功能，将其禁用掉，那么就可以减少一些内存占用了。

可以通过强制解释器来指定类的列表对象使用`__slots__`命令来实现减少内存占用的目的：

```Python
class DataItem(object):
    __slots__ = ['name', 'age', 'address']
    def __init__(self, name, age, address):
        self.name = name
        self.age = age
        self.address = address
```

这样设置之后，使用`get_size(d1)`得到的对象的大小就是 64bytes 了，比之前的 460bytes 减小了 7 倍。另外，创建对象时速度还增加了 20%。

#### 缺点

激活`__slots__`会禁止所有元素的创建，包括`__dict__`，也不能动态给这个类添加新类变量。

这意味着，以下代码将结构转换成 json 将不运行:

```Python
def toJSON(self):
        return json.dumps(self.__dict__)
```

这个问题很容易修复，可以通过循环`__slots__`来创建 dict：

```Python
def toJSON(self):
        data = dict()
        for var in self.__slots__:
            data[var] = getattr(self, var)
        return json.dumps(data)
```




