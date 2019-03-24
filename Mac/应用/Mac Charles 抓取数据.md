> 转摘：[charles抓取微信小程序数据(抓取http和https数据)](https://blog.csdn.net/ManyPeng/article/details/79475870)

## 一、抓取 HTTPS 数据包

安装好 Charles 之后，就可以监控电脑上的 HTTP 请求了，但是对于 HTTPS 请求则会显示为乱码，无法正常进行解析。这需要通过配置 Charles SSL Proxying 来解决。

1. 进入 Charles 的`Help->SSL Proxying`：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095409122.png)

2. 点击`Install Charles Root Certificate`，会打开系统的钥匙串管理页面，找到 Charles 的证书：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095449005.png)
    
    > 正常第一次进去这个证书应该是一个红叉，这里由于已经进行过设置所以显示正常。

3. 右键点击该证书，选择菜单中的`显示简介选项`，接着进入信任栏目，将其全部置为`始终信任`：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095530175.png)

    完成这一步的设置后，这个根证书应该会跟我上面一个截图一样，而不会显示红叉。

4. 接着点击 Charles 的`Proxy->SSL Proxy Settings`，弹出如下页面：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095593277.png)

    弹出的对话框中，勾选`Enable SSL Proxying`，然后点击`add`添加`Host`为`*`和`Port`为`443`，并保存。
    
    > 此处将 Host 设置为`*`的意思是主抓取全部的 https 数据包，如果想针对某个域名抓取可以在此设置。
    
    > 如果只需要在 Mac 上抓取 HTTPS 数据，则下面的就不需要设置了。

5. 要抓取手机端的 HTTPS 数据，在配置好了代理之后，点击 Charles 上的`Help->SSL Proxying->Install Charles Root Certificate on a Mobile Device or Remote Browser`：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095861397.png)

    点击之后弹出如下对话框：
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095882567.png)

6. 在手机浏览器上访问`charlesproxy.com/getssl`这个地址（此处请注意，如果是小米手机，最好不要用自带的浏览器，亲自踩坑的忠告……），输入锁屏密码之后，会弹出如下界面：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535095962339.png)

    然后就可以正常获取手机端的 HTTPS 数据了。

## 二、通过 Charles 抓取手机数据

1. 首先要配置 Charles 代理，在 Charles 上通过`proxy->proxy setting`进入代理设置：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535096069189.png)

2. 设置代理，上一步操作之后，在打开的界面中输入代理端口，和其他配置：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535096132123.png)

    此处的 port，默认为 8888，也可以进行修改，只要不冲突就可以，勾选上`Enable transparent HTTP proxying`。

3. 设置手机代理，手机上进入 wifi 设置，进入 wifi 的配置页面，然后进入代理配置页面，输入相应的 IP 和端口即可：

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1535096300860.png)

    服务器主机名处填写刚才电脑的 IP 地址，服务器端口填写上一步中 Charles 处设置的端口。保存之后就完成配置了。
    
    > 注意要保证手机所连接的 wifi 跟电脑在一个局域网内（连接同一个 wifi 就好了）

