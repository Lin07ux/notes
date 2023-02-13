Gin 框架的路由是基于 Radix Trees 结构实现的，利用字符串公共前缀的方式完成注册和匹配，极大的提升了路由匹配的效率，减少字符串比较次数。

### 1. 路由限制请求方法的实现

在 Gin 框架中，路由树的构建是基于请求方法的，每个 HTTP 请求方法建立一颗路由树。路由树的定义如下：

```go
type methodTree struct {
  method string
  root   *node
}

type methodTrees []methodTree
```

对应的结构图如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676273706)

例如，对于通过`g.POST("/abc/info", InfoHandler)`方式定义的路由，就只会注册到 POST 方法的路由树中。当通过`GET`方法请求该路径时，在进行路由匹配搜索的时候，只会在`GET`方法对应的路由树中进行搜索，就无法找到该路由。这样就起到了路由限制请求方法的作用。

而通过`g.Any()`方法注册的路由，其实是相当于在所有的方法路由树中都注册一遍该路由，从而使得每种请求方法都能找到对应的路由：

```go
var anyMethods = []string{
  http.MethodGet, http.MethodPost, http.MethodPut, http.Method.Patch,
  http.MethodHead, http.MethodOptions, http.MethodDelete, http.MethodConnect,
  http.MethodTrace,
}

// Any registers a route that matches all the HTTP methods.
// GET, POST, PUT, PATCH, HEAD, OPTIONS, DELETE, CONNECT, TRACE.
func (group *RouterGroup) Any(relativePath string, handlers ...HandlerFunc) IRoutes {
  for _, method := range anyMethods {
    group.handle(method, relativePath, handlers)
  }
  
  return group.returnObj()
}
```

### 2. 路由树节点数据结构

Gin 的路由树中的路由都是基于一个 node 结构体来进行构建的：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676274311)

该结构体中包含了一个路由的基本元素：路径(fullPath)、处理函数(handlers)、子节点(children)等。其中：

* 处理函数包含了中间件处理函数，因此这里使用都是 一个`HandlersChain`结构，也就是一个处理器链表；
* 子节点是与当前节点具有相同路径前缀的一系列路由节点，它们之间通过`children`这个 node slice 构成父子关系。

### 3. 路由树的构建

Gin 框架在注册路由的时候，会进行路由树的构建，期间会涉及到路由节点的分级、拆分等，以构建成正确的前缀树。

下面分步进行路由注册的解释说明。

**第一个路由**

```go
g.POST("/abc/info", InfoHandler)
```

初始情况下，Gin 实例的路由树是空的，在注册第一个路由的时候，就直接构建一个 node 节点，并将其作为请求方法(也就是`POST`方法)路由树的根节点。

如下图：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676274647)

**第二个路由**

```go
g.POST("/abc/info/detail", DetailHanlder)
```

这个路由和前面注册的第一个路由具有详情的前缀`/abc/info`，而且当前路由更长一些，所以会将当前路由作为第一个路由的子节点，加入到第一个路由的`children`属性中。

如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676274787)

这一步还会对第一个注册的路由节点进行三处更新：

* `indices` 该字段的值从空字符串变味了`"/"`，该值为其第一个子节点(刚注册的这个路由)的`path`字段的第一个字符，在路由匹配的时候作为索引使用，提升匹配效率；
* `priority` 该字段的值从 1 变为了 2，表示该节点及其下级节点总共有 2 级；
* `children` 该字段从空切片变为长度为 1 的切片，也就是加入了一个子节点。

需要注意的是：在加入第二个路由的时候，其对应的`node.path`的值为`"/detail"`，因为它与其父节点共用前缀`"/abc/info"`，但是该子节点的`node.fullPath`依然是注册时的完整路径。

**第三个路由**

```go
g.POST("/abc/list", ListHandler)
```

这个路由和前面两个路由具有相同的前缀`/abc/`，所以会对现有的根节点（也就是第一个注册的路由节点）进行拆分，得到`/abc/`和`info`两个路由节点。此时`/abc/list`路由就会和`info`路由节点一样成为`/abc/`路由节点的子节点。路由结构如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676275450)

对应的，POST 方法的路由树的节点组成如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1676275495)

这一步中，因为涉及到节点拆分，而拆分出的`info`路由继承了拆分前节点的大部分属性，所以可以将拆分后的根节点也作为一个新插入的节点看待，其字段值解释如下：

* `path` 因为从根节点中分拆出了`info`路由，所以根节点的 path 就变为了前缀`/abc`；
* `indices` 该字段的值为`li`，分别表示路由`list`和`info`节点的 path 的第一个字符；
* `priority` 因为目前总共有 3 级路由了，所以根节点的`priority`的值就为 3；
* `children` 根节点下直属的子节点有两个，分别是拆分得到的`info`路由节点和新注册的`list`路由节点；
* `handlers` 该字段值为 nil，因为该节点并不是一个具体的路径，只是是一个前缀路由，不包含任何具体的业务逻辑。

另外，拆分得到的`info`路由节点中，其`nType`字段的值变为了 0，表示它不再是一个根节点了；而且其`path`也相应的要去除前缀，变为了`info`。该节点的其他属性都保持不变。


