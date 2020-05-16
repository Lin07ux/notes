## 一、错误

### 1.1 undefined symbol

> 转摘：[ImportError _psycopg.so: undefined symbol: lo_truncate64](http://www.leeladharan.com/importerror-psycopg-so:-undefined-symbol:-lo-truncate64)

在引入 psycopg 依赖之后，会提示类似如下的符号不存在错误：

```
ImportError _psycopg.so: undefined symbol: lo_truncate64
```

可能提示的是`lo_truncate64`不存在，也可能提示的是其他的符号不存在。但是这类问题基本都是由于使用了不正确的`libpq.so`文件导致的。解决方法如下：

1. 查看引用的`libpq.so`的位置：

    ```shell
    # 这里假设提示错误的 _psycopg.so 的位置如下
    > ldd /path/to/venv/lib/python2.6/site-packages/psycopg2/_psycopg.so | grep libpq
        libpq.so.5 => /usr/lib64/libpq.so.5 (0x00007f0d6c027000)
    ```

2. 查找下系统中其他的`libpq.so`文件：

    ```shell
    find / -name libpq.so
    ```

3. 对比第一步中`libpq.so.5`文件与找到的其他的`libpg.so`文件的日期。

    一般情况下会发现第一步中的`libpq.so.5`文件比其他找到的文件的版本更低，此时可以对其他位置的`libpg.so`文件重新做软连到第一步中指定的位置。
    
```shell
cd /usr/lib64
rm libpq.so.5
# 假设找到的其他的 libpq.so 的文件的路径为 /usr/pgsql-9.4/lib/libpq.so.5
ln -s /usr/pgsql-9.4/lib/libpq.so.5 libpq.so.5
```


