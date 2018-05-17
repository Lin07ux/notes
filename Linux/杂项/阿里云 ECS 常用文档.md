### 一、VPC 网络和经典网络

VPC 网络是虚拟专属网络，与其他的网络互相隔离，有相对较高的安全性。

VPC 网络和经典网络之间是不能通过内网/私有 IP 进行访问的，只能通过各自的公网 IP 进行访问。

经典网络可以迁移至 VPC 网络，有两种方式：

* 混挂和混访方案：适用于服务器上有依赖于其他经典网络资源的情况。
* 单 ECS 迁移方案：适用于服务器上的所有服务都在该服务器上，不依赖其他经典网络资源的情况。

ECS 迁移到 VPC 网络之后，需要设置安全组的规则，设置相关端口和 IP 的联通情况，相当于服务器端安装的 iptables 服务。

**资料**

1. [VPC通信](https://help.aliyun.com/document_detail/53597.html)
2. [迁移方案概述](https://help.aliyun.com/document_detail/55051.html)
3. [单ECS迁移示例](https://help.aliyun.com/document_detail/57954.html)

