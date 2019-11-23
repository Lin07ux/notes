> 转摘：[浅析 JWT](https://mp.weixin.qq.com/s/wVYMUWsGKTUP_H5hGvoPKQ)

JSON Web Token，简称 JWT，读音是`[dʒɒt]`(jot 的发音)，是一种当下比较流行的「跨域认证解决方案」。它是一套 RFC 规范，相关的还有 JWE/JWS/JWK/JOSE。它有很多优点，也有局限性，但可以配合其他方案做出适合自己业务的一套方案。本篇是对 JWT 做一个简单的介绍和简单实践总结。

> JSON Web Token (JWT) is a compact claims representation format intended for space constrained environments such as HTTP Authorization headers and URI query parameters.

## 一、基础

传统网站如果要追踪一个用户，一般会通过 session 和 cooike 的方式进行，但是这种方式受限于 session 数据需要存储在服务器本地，造成扩展性差的问题，单机当然没有问题，如果是服务器集群，或者是跨域的服务导向架构，就要求 session 数据共享，每台服务器都能够读取 session。

一种解决方案是 session 数据持久化，写入数据库或别的持久层。各种服务收到请求后，都向持久层请求数据。这种方案的优点是架构清晰，缺点是工程量比较大。另外，持久层万一挂了，就会单点失败。

另一种方案是服务器索性不保存 session 数据了，所有数据都保存在客户端，每次请求都发回服务器。JWT 就是这种方案的一个代表。

JWT 类似一个 token，而且其中包含有全部的用户认证的信息。在每次发送请求的时候都带上 JWT 数据，服务器就可以直接解开获取其中的数据，就完成了用户识别。这样即解决了会话的连续又解决了扩展问题。

当然，直接在 token 中存放用户数据，还需要考虑到数据泄露和被篡改的这问题。JWT 则通过签名方式来解决这些问题。

### 1.1 特点

JWT 的特点如下：

1. Stateless **无状态**，一方面可以有效减少服务端保存 Session 的负载；另一方面可以方便的进行扩平台的横向扩展，如 SSO 单点授权。
2. 可以有效携带**必要但不敏感**的信息，且是 JSON 这种非常通用的格式。

### 1.2 安全性

为了保证 JWT 数据的安全性，需要注意如下几点：

1. 因为 JWT 的前两个部分仅是做了 Base64 编码处理并非加密，所以在存放数据上不能存放敏感数据。
2. 用来签名/加密的密钥需要妥善保存。
3. 尽可能采用 HTTPS，确保不被窃听。
4. 如果存放在 Cookie 中则强烈建议开启 Http Only，其实官方推荐是放在 LocalStorage 里，然后通过 Header 头进行传递。

### 1.3 缺点

虽然 JWT 解决了会话扩展等问题，但是它也有一些特有的问题：

1. 数据臃肿
    因为 payload 只是用 Base64 编码，所以一旦存放数据大了，编码之后 JWT 会很长，cookie 很可能放不下，所以还是建议放 LocalStorage，但是每次 HTTP 请求都带上这个臃肿的 Header 开销也随之变大。

2. 无法废弃
    如果有效期设置过长，意味着这个 Token 泄漏后可以被长期利用，危害较大，所以一般都会设置一个较短的有效期。由于有效期较短，意味着需要经常进行重新授权的操作。

3. 无法续签
    假设在用户操作过程中升级/变更了某些权限，势必需要刷新以更新数据。

要解决这些问题，需要在服务端部署额外逻辑，常见的做法是增加刷新机制和黑名单机制，通过 Refresh Token 刷新 JWT，将需要废弃的 Token 加入到黑名单。

## 二、组成

JWT 由三部分组成：头部、数据体、签名/加密。这三部分以`.`(英文句号)连接，这三部分顺序是固定的，即`header.payload.signature`，如下示例：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766361031.png"/>

### 2.1 头部 The Header

这部分用来描述 JWT 的元数据，比如该 JWT 所使用的签名/加密算法、媒体类型等。

这部分原始数据是一个 JSON 对象，经过 Base64Url 编码方式进行编码后得到最终的字符串。其中只有一个属性是必要的：`alg`——加密/签名算法，默认值为 HS256。

最简单的头部可以表示成这样：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766488288.png"/>

其他可选属性：

- `typ` 描述 JWT 的媒体类型，该属性的值只能是 JWT，它的作用是与其他 JOSE Header 混合时表明自己身份的一个参数（很少用到）。
- `cty` 描述 JWT 的内容类型。只有当需要一个 Nested JWT 时，才需要该属性，且值必须是 JWT。
- `kid` KeyID，用于提示是哪个密钥参与加密。

### 2.2 数据体 The Payload

这部分用来描述JWT的内容数据，即存放些什么。

原始数据仍是一个 JSON 对象，经过 Base64url 编码方式进行编码后得到最终的 Payload。这里的数据默认是不加密的，所以不应存放重要数据（当然你可以考虑使用嵌套型 JWT）。官方内置了七个属性，大小写敏感，且都是可选属性，如下：

- `iss` (Issuer) 签发人，即签发该 Token 的主体
- `sub` (Subject) 主题，即描述该 Token 的用途
- `aud` (Audience) 作用域，即描述这个 Token 是给谁用的，多个的情况下该属性值为一个字符串数组，单个则为一个字符串
- `exp` (Expiration Time) 过期时间，即描述该 Token 在何时失效
- `nbf` (Not Before) 生效时间，即描述该 Token 在何时生效
- `iat` (Issued At) 签发时间，即描述该 Token 在何时被签发的
- `jti` (JWT ID) 唯一标识

除了这几个内置属性，我们也可以自定义其他属性，自由度非常大。

这里对`aud`做一个说明，有如下 Payload：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766649900.png"/>

那么如果拿这个 JWT 去`http://www.c.com`获取有访问权限的资源，就会被拒绝掉，因为`aud`属性明确了这个 Token 是无权访问`www.c.com`的。

### 2.3 签名/加密 The signature/encryption data

这部分是相对比较复杂的，因为 JWT 必须符合 JWS/JWE 这两个规范之一，所以针对这部分的数据如何得来就有两种方式。

看一个简单的例子，有如下 JWT：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766720872.png"/>

对前两部分用 Base64url 解码后能得出相应原始数据。

Header 部分：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766747005.png"/>

Payload 部分：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766767247.png"/>

根据 Header 部分的`alg`属性可以知道该 JWT 符合 JWS 中的规范，且签名算法是 HS256，也就是 HMAC SHA-256 算法，那么就可以根据如下公式计算最后的签名部分：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555766796100.png"/>

其中的密钥是保证签名安全性的关键，所以必须保存好，在本例中密钥是`123456`。因为有这个密钥的存在，所以即便调用方偷偷的修改了前两部分的内容，在验证环节就会出现签名不一致的情况，所以保证了安全性。

在实现过程中，遇到了这样一个问题：如果使用 RS256 这类非对称加密算法，加密出来的是一串二进制数据，所以第三部分还是用 Base64 编码了一层，这样最终的 JWT 就是可读的了。

## 三、应用

JWT 并不对数据进行加密，而是对数据进行签名，保证不被篡改。除了在登录中可以用到，在进行邮箱校验、图形验证码和短信验证码时也可以用到。

### 3.1 邮箱校验

网站在用户注册成功之后会进行邮箱校验，具体做法是给邮箱发一个链接，用户点开链接就校验成功。

利用 JWT 可以将用户的 id、校验码有效期等内容一起生成一个 token，然后用户访问带有这个 token 的验证链接的时候就可以通过直接校验 token 的内容即可实现邮箱校验，而无需在服务器端存储内容。

```javascript
const jwt = require('jsonwebtoken')

// 把邮箱和用户 id 绑定在一起
const code = jwt.sign({ email, userId }, secret, { expiresIn: 60 * 30 })

// 生成校验链接
const link = `https://example.com/code=${code}`
```

### 3.2 验证码

在登录时，输入密码错误次数过多会出现图形验证码。图形验证码的原理是给客户端一个图形，并且在服务器端保存与这个图片配对的字符串。

之前基本都是通过 session 来实现，利用 JWT 时，可以将验证码配对的字符串作为 secret 生成校验 token。在提交时将验证码和该 token 一起提交，并进行无状态校验。

> 短信验证码同理。

```javascript
const jwt = require('jsonwebtoken')
const code = 'ACDE'

// 生成验证码图形
const codeImage = getImageFromString(code)

// 生成校验 token，10 分钟失效
const token = jwt.sign({}, secret + code, { expiresIn: 60 * 10 })

// 给前端的响应
const res = { token, codeImage }
```

### 3.3 JWT token 主动失效

JWT 的 token 设置有有效期，默认是无法主动失效的。如果需要主动失效某个 token，则需要将该 token 放入到一个黑名单中(如由 Redis 提供的缓存)，并将其在黑名单中的时间设置为其自动失效时间。

### 3.4 如何允许用户只能在一个设备登录，如微信

* `session` 使用 sql 类数据库，对用户数据库表添加 token 字段并加索引，每次登录重置 token 字段，每次请求时根据 token 查找用户。

* `jwt` 使用 sql 类数据库，对用户数据库表添加 token 字段(不需要添加索引)，每次登录重置 token 字段，每次请求时根据 jwt 中的用户 id 查找用户，并比对查到找的用户的 token 和当前 token 是否一致，当然也可以使用计数器方法，如下一个问题。

### 3.5 如何允许用户只能在最近五个设备登录，如视频网站

* `session` 使用 sql 类数据库，创建 token 数据表，包含`id`、`token`、`user_id`三个字段，一个用户可以有多个 token 记录。每登录一次添加一行记录，并删除该用户的除最后五个的 token 记录。请求时根据 token 获取 user_id，再根据 user_id 获取用户信息。

* `jwt`  使用计数器，使用 sql 类数据库，在用户表中添加字段`count`，默认值为 0，每次登录 count 字段自增 1。每次登录创建的 jwt 的 Payload 中携带数据`current_count`为用户当前的 count 值。每次请求权限接口时，使用 jwt 中的 user_id 查询用户信息，并比较用户信息中的 count 和 jwt 中的 current_count 的差值是否小于 5，小于的话则登录成功，否则失败。

### 3.6 如何允许用户只能在最近五个设备登录，而且使某一用户剔除掉现有设备外的其他所有设备，如视频网站

* `session` 在上一个问题的基础上，删除掉该用户该设备以外的其他所有 token 记录。

* `jwt` 在上一个问题的基础上，将 count 增加 5，并对该设备的 jwt 中的 count 重新赋值。

### 3.7 如何显示该用户的登录设备列表/如何踢掉特定用户

* `session` 在 token 表中增加设备标识列

* `jwt` 需要服务器端保持设备列表信息，做法和 session 相同，使用 jwt 意义不大。


