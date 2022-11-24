### HTTP 方法

所有针对资源的操作都是使用 HTTP 方法指定的，常见的方法有（括号中为对应的 SQL 命令）：

Verd            |  描述
----------------|-----------------------
HEAD（SELECT）   | 只获取某个资源的头部信息
GET（SELECT）    | 获取资源，得到资源对象
POST（CREATE）   | 创建资源，得到新生成的资源对象
PATCH（UPDATE）  | 更新资源的部分属性（很少用，一般用POST代替）
PUT（UPDATE）    | 更新资源，客户端需要提供新建资源的所有属性，得到完整的资源对象
DELETE（DELETE） | 删除资源，得到响应为空

另外：

* `GET`请求不允许有 body，所有参数通过拼接在 URL 之后传递，所有的请求参数都要进行遵循 RFC 3986 的 URL Encode。
* `DELETE`删除单个资源时，资源标识通过 path 传递，批量删除时，通过在 body 中传递JSON。
* `POST/PUT`请求的参数通过JSON传递，可选的请求参数，只传有值的，无值的不要传递。

`GET`、`PUT`、`DELETE`是幂等的，只有`POST`是非幂等的。幂等操作的特点是其任意多次执行所产生的影响均与一次执行的影响相同。 是否为非幂等是判断接口使用`POST`还是`PUT`的决定条件。

对于那些不匹配任何 CRUD 操作的请求，没有明确的规定，但是有一些可借鉴的方式：

* 重新调整这些请求的操作，使其变为操作资源的一种子资源。

    这在变更资源的状态的时候比较有用，比如对于点赞、收藏一类的行为，可以将其当做是资源的点赞、收藏资资源进行操作。
    
    比如，GitHub 中点赞和取消点赞，其 API 接口为`PUT /gists/:id/star`和`DELETE /gists/:id/star`。

* 将请求动作作为一类特殊的资源。

    当请求动作无法被准确的当做子资源时，可以直接将行为本身当做一种资源设置到 API 路径中。
    
    比如，对于搜索操作，其本身无法简单的通过 HTTP Verb 和资源名称设计成一个明确的 API，此时就可以直接在资源名称后面增加`/search`来表示是搜索动作。

> What about actions that don't fit into the world of CRUD operations?
> 
> This is where things can get fuzzy. There are a number of approaches:
> 
> Restructure the action to appear like a field of a resource. This works if the action doesn't take parameters. For example an activate action could be mapped to a boolean activated field and updated via a PATCH to the resource.
> Treat it like a sub-resource with RESTful principles. For example, GitHub's API lets you star a gist with  PUT /gists/:id/star and unstar with DELETE /gists/:id/star.
> 
> Sometimes you really have no way to map the action to a sensible RESTful structure. For example, a multi-resource search doesn't really make sense to be applied to a specific resource's endpoint. In this case, /search would make the most sense even though it isn't a resource. This is OK - just do what's right from the perspective of the API consumer and make sure it's documented clearly to avoid confusion.




