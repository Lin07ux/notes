如果是单用户(single-user)，默认拿 id_rsa 与你的 github 服务器的公钥对比即可进行登录验证；如果是多用户（multi-user）如 user1、user2，那么默认的密钥就不能用在 user2 的身上了，这个时候就要进行一些配置。

## 新建 SSH Key
对于新的用户，需要创建一个新的密钥对。

```shell
cd ~/.ssh/
ssh-keygen -t rsa -C '<comment>'
# 设置名称为id_rsa_work
Enter file in which to save the key (~/.ssh/id_rsa): <new_name>
```

在新建密钥对的时候，需要指定一个特定的名称，而不能是默认的(id_rsa)，否则会影响其他的用户。


## 修改 SSH 配置文件
修改`~/.ssh/config`文件(没有则需要新建这个文件)，添加如下的配置：

```conf
# 用于设置默认的账户仓库
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_rsa

 # second user(second@mail.com)
 # 建一个github别名，新建的帐号使用这个别名做克隆和更新
Host github2
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_rsa_work
```

上面的配置是完全基于 ssh 的原理的，使用的就是 ssh 的配置，所以完全可以使用 ssh 配置来解释。

给服务器一个别名之后，我们就能在链接服务器的时候，就需要指定服务器别名进行连接，从而使得 ssh 使用我们指定的配置进行验证，从而实现不同账户使用不同的配置，类似如下的方式：

`git clone git@github2:git/test.git`

如果不使用服务器别名去连接，那么就会使用默认的`id_rsa`验证文件和默认的设置去连接服务器，这样就会出现无权访问的问题。

对于上面的配置，需要注意的是：

这里第一个设置的意义在于，当你之前的账户已经使用了一段时间时，设置其服务器的别名为服务器的名称，能平滑的添加第二个账户，而不用修改第一个用户本地仓库的配置。因为，一般情况下，我们只有一个账户的时候，不会配置 ssh 的 config 文件，那就是直接使用默认的 id_rsa 密钥对和服务器名来和服务器联系的。添加了第二个仓库后，我们设置服务器的别名依旧是服务器的名称(github.com)则就无需更改配置了。

## 配置服务器公钥
然后需要将刚才生成的密钥对中的公钥(.pub)文件中的内容添加到 git 服务器中对应的账号下。

## 测试
上面的操作之后，基本就能正常使用了，我们可以使用`ssh -T`指令来测试：

```shell
$ ssh -T git@github.com
Hi BeginMan! You've successfully authenticated, but GitHub does not provide shell access.

$ ssh -T github2
Hi funpeng! You've successfully authenticated, but GitHub does not provide shell access.
```

## 应用
设置好后，就可以和平常一样使用 git 了，唯一需要注意的就是连接服务器的时候服务器的地址(名称)的时候需要使用设置的别名。


