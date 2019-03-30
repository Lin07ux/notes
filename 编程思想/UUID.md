> 转摘：[关于UUID的二三事](https://www.jianshu.com/p/d77f3ef0868a)

## 一、简介

UUID 是 Universally Unique Identifier 的缩写。UUID 出现的目的，是为了让分布式系统可以不借助中心节点，就可以生成 UUID 来标识一些唯一的信息。

> GUID 是 Globally Unique Identifier 的缩写，跟 UUID 是同一种东西，只是来源于微软。

1 个 UUID 是 1 个 16 字节（128 位）的数字；为了方便阅读，1 个 UUID 被连字符分为五段，形式为`8-4-4-4-12`的 32 个字符，其中的字母是16进制表示，大小写无关。下面是一个示例：

```
123e4567-e89b-12d3-a456-426655440000
```

## 二、版本

UUID 本身也经过了[多个版本的演化](https://en.wikipedia.org/wiki/Universally_unique_identifier)，每个版本的算法都不同。但是每个版本的格式都是一样的：

```
xxxxxxxx-xxxx-Vxxx-Nxxx-xxxxxxxxxxxx
```

其中，`V`代表版本号，由于 UUID 的标准实现目前有 5 个版本，所以只会是`1,2,3,4,5`，而`N`只会是`8,9,a,b`。

### 2.1 版本1：基于时间的 UUID

通过当前时间戳、机器 MAC 地址生成。

由于在算法中使用了 MAC 地址，这个版本的 UUID 可以保证在全球范围的唯一性。但与此同时，因为它暴露了电脑的 MAC 地址和生成这个 UUID 的时间，这就是这个版本 UUID 被诟病的地方。

在 Python 里面的使用的例子：

```Python
>>> import uuid
>>> uuid.uuid1()
UUID('444b5cc0-ae5d-11e6-8d22-28924a431726')
>>> uuid.uuid1()
UUID('46a9bf21-ae5d-11e6-9549-28924a431726')
```

其中，最后的 12 个字符`28924a431726`就是电脑网卡的 MAC 地址。

### 2.2 版本2：DCE 安全的 UUID

DCE 安全的 UUID 和基于时间的 UUID 算法相同，但会把时间戳的前 4 位置换为 POSIX 的 UID 或 GID。

不过，在 UUID 的规范里面没有明确地指定，所以基本上所有的 UUID 实现都不会实现这个版本。

### 2.3 版本3：基于名字空间的 UUID（MD5）

由用户指定 1 个 namespace 和 1 个具体的字符串，通过 MD5 散列，来生成 1 个 UUID。

根据规范描述，这个版本的存在是为了向后兼容。平时这个版本也很少用到。

在 Python 里面的使用的例子：

```Python
>>> import uuid
>>> uuid.uuid3(uuid.NAMESPACE_DNS, "myString")
UUID('21fc48e5-63f0-3849-8b9d-838a012a5936')
>>> uuid.uuid3(uuid.NAMESPACE_DNS, "myString")
UUID('21fc48e5-63f0-3849-8b9d-838a012a5936')
```

在 Java 中使用的例子：

```Java
System.out.println(UUID.nameUUIDFromBytes("myString".getBytes("UTF-8")).toString());
```

### 2.4 版本4：基于随机数的 UUID

根据随机数，或者伪随机数生成 UUID。

这种 UUID 产生重复的概率是可以计算出来的，但随机的东西就像是买彩票：你指望它发财是不可能的，但狗屎运通常会在不经意中到来。这个版本应该是平时用得最多的版本了。

在 Python 里面使用的例子：

```Python
>>> import uuid
>>> uuid.uuid4()
UUID('e584539d-a334-4f15-9819-88d73fcf707d')
>>> uuid.uuid4()
UUID('76ec02cc-1b1d-4ad3-bd09-a4f6d67c7af4')
```

在 Java 中使用的例子：

```Java
System.out.println(UUID.randomUUID().toString());
```

### 2.5 版本5：基于名字空间的 UUID（SHA1）

和版本 3 一样，不过散列函数换成了 SHA1。

在 Python 里面的使用的例子：

```Python
>>> import uuid
>>> uuid.uuid5(uuid.NAMESPACE_DNS, "myString")
UUID('cd086011-6aac-5a06-a94a-0b67c59649ba')
>>> uuid.uuid5(uuid.NAMESPACE_DNS, "myString")
UUID('cd086011-6aac-5a06-a94a-0b67c59649ba')
```

### 三、UUID和各个编程语言

* 微软: [http://msdn.microsoft.com/en-us/library/system.guid(v=vs.110).aspx ](http://msdn.microsoft.com/en-us/library/system.guid(v=vs.110).aspx)
* Linux: [http://en.wikipedia.org/wiki/Util-linux](http://en.wikipedia.org/wiki/Util-linux)
* Android: [http://developer.android.com/reference/java/util/UUID.html](http://developer.android.com/reference/java/util/UUID.html)
* PHP: [http://php.net/manual/en/function.uniqid.php#94959](http://php.net/manual/en/function.uniqid.php#94959)
* MySQL: [http://dev.mysql.com/doc/refman/5.1/en/miscellaneous-functions.html#function_uuid](http://dev.mysql.com/doc/refman/5.1/en/miscellaneous-functions.html#function_uuid)
* Java: [http://docs.oracle.com/javase/7/docs/api/java/util/UUID.html](http://docs.oracle.com/javase/7/docs/api/java/util/UUID.html)
* nodejs: [https://github.com/broofa/node-uuid](https://github.com/broofa/node-uuid)

> Java 只支持生成版本 3 和版本 4 的 UUID。

