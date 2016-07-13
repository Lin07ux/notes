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


## 附加：ssh-keygen 用法
ssh-keygen 用于生成、管理和转换认证密钥。包括 RSA 和 DSA 两种密钥。
这个命令会生成一个密钥对，并要求指定一个文件存放私钥，同时将公钥存放在附加了".pub"后缀的同名文件中。

密语和口令(password)非常相似，但是密语可以是一句话，里面有单词、标点符号、数字、空格或任何你想要的字符。好的密语要30个以上的字符，难以猜出，由大小写字母、数字、非字母混合组成。密语可以用 -p 选项修改。
丢失的密语不可恢复。如果丢失或忘记了密语，用户必须产生新的密钥，然后把相应的公钥分发到其他机器上去。

在 Git 中，一般使用 Git-bash 程序来生成。

使用方法如下：

```
ssh-keygen [-q] [-b bits] -t type [-N new_passphrase] [-C comment] [-f output_keyfile]

ssh-keygen -p [-P old_passphrase] [-N new_passphrase] [-f keyfile]

ssh-keygen -i [-f input_keyfile]

ssh-keygen -e [-f input_keyfile]

ssh-keygen -y [-f input_keyfile]

ssh-keygen -c [-P passphrase] [-C comment] [-f keyfile]

ssh-keygen -l [-f input_keyfile]

ssh-keygen -B [-f input_keyfile]

ssh-keygen -D reader

ssh-keygen -F hostname [-f known_hosts_file]

ssh-keygen -H [-f known_hosts_file]

ssh-keygen -R hostname [-f known_hosts_file]

ssh-keygen -U reader [-f input_keyfile]

ssh-keygen -r hostname [-f input_keyfile] [-g]

ssh-keygen -G output_file [-v] [-b bits] [-M memory] [-S start_point]

ssh-keygen -T output_file -f input_file [-v] [-a num_trials] [-W generator]
```

参数说明：

```
-t   指定密钥类型。如果没有指定则默认生成用于 SSH-2 的 RSA 密钥。

-a trials  在使用 -T 对 DH-GEX 候选素数进行安全筛选时需要执行的基本测试数量。

-B   显示指定的公钥/私钥文件的 bubblebabble 摘要。

-b bits  指定密钥长度。对于RSA密钥，最小要求768位，默认是2048位。DSA密钥必须恰好是1024位(FIPS 186-2 标准的要求)。

-C comment  提供一个新注释

-c   要求修改私钥和公钥文件中的注释。本选项只支持 RSA1 密钥。程序将提示输入私钥文件名、密语(如果存在)、新注释。

-D reader  下载存储在智能卡 reader 里的 RSA 公钥。

-e  读取OpenSSH的私钥或公钥文件，并以 RFC 4716 SSH 公钥文件格式在 stdout 上显示出来。该选项能够为多种商业版本的 SSH 输出密钥。

-F hostname  在 known_hosts 文件中搜索指定的 hostname ，并列出所有的匹配项。
 	这个选项主要用于查找散列过的主机名/ip地址，还可以和 -H 选项联用打印找到的公钥的散列值。

-f filename  指定密钥文件名。

-G output_file  为 DH-GEX 产生候选素数。这些素数必须在使用之前使用 -T 选项进行安全筛选。

-g  在使用 -r 打印指纹资源记录的时候使用通用的 DNS 格式。

-H  对 known_hosts 文件进行散列计算。这将把文件中的所有主机名/ip地址替换为相应的散列值。
	原来文件的内容将会添加一个".old"后缀后保存。这些散列值只能被 ssh 和 sshd 使用。
    这个选项不会修改已经经过散列的主机名/ip地址，因此可以在部分公钥已经散列过的文件上安全使用。

-i  读取未加密的SSH-2兼容的私钥/公钥文件，然后在 stdout 显示OpenSSH兼容的私钥/公钥。
    该选项主要用于从多种商业版本的SSH中导入密钥。

-l  显示公钥文件的指纹数据。它也支持 RSA1 的私钥。对于RSA和DSA密钥，将会寻找对应的公钥文件，然后显示其指纹数据。

-M memory  指定在生成 DH-GEXS 候选素数的时候最大内存用量(MB)。

-N new_passphrase  提供一个新的密语。

-P passphrase  提供(旧)密语。

-p   要求改变某私钥文件的密语而不重建私钥。程序将提示输入私钥文件名、原来的密语、以及两次输入新密语。

-q   安静模式。用于在 /etc/rc 中创建新密钥的时候。

-R hostname  从 known_hosts 文件中删除所有属于 hostname 的密钥。这个选项主要用于删除经过散列的主机(参见 -H 选项)的密钥。

-r hostname  打印名为 hostname 的公钥文件的 SSHFP 指纹资源记录。

-S start  指定在生成 DH-GEX 候选模数时的起始点(16进制)。

-T output_file  测试 Diffie-Hellman group exchange 候选素数(由 -G 选项生成)的安全性。

-t type  指定要创建的密钥类型。可以使用："rsa1"(SSH-1) "rsa"(SSH-2) "dsa"(SSH-2)

-U reader  把现存的RSA私钥上传到智能卡 reader

-v   详细模式。ssh-keygen 将会输出处理过程的详细调试信息。常用于调试模数的产生过程。
     重复使用多个 -v 选项将会增加信息的详细程度(最大3次)。

-W generator  指定在为 DH-GEX 测试候选模数时想要使用的 generator

-y   读取OpenSSH专有格式的公钥文件，并将OpenSSH公钥显示在 stdout 上。
```


