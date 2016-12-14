> 转自 [浅谈IOC--说清楚IOC是什么](http://www.cnblogs.com/DebugLZQ/archive/2013/06/05/3107957.html)，发表于：2013-06-05 17:53。
> 本文旨在用语言(非代码)说清楚 IOC (Inversion of Control，控制反转) 到底是什么，没有什么高深的技术。

## 1. IOC 的理论背景
我们知道在面向对象设计的软件系统中，它的底层都是由N个对象构成的，各个对象之间通过相互合作，最终实现系统地业务逻辑[1]。

![图1 软件系统中耦合的对象](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468740325536.png)

如果我们打开机械式手表的后盖，就会看到与上面类似的情形，各个齿轮分别带动时针、分针和秒针顺时针旋转，从而在表盘上产生正确的时间。图1中描述的就是这样的一个齿轮组，它拥有多个独立的齿轮，这些齿轮相互啮合在一起，协同工作，共同完成某项任务。我们可以看到，在这样的齿轮组中，如果有一个齿轮出了问题，就可能会影响到整个齿轮组的正常运转。

齿轮组中齿轮之间的啮合关系,与软件系统中对象之间的耦合关系非常相似。对象之间的耦合关系是无法避免的，也是必要的，这是协同工作的基础。现在，伴随着工业级应用的规模越来越庞大，对象之间的依赖关系也越来越复杂，经常会出现对象之间的多重依赖性关系，因此，架构师和设计师对于系统的分析和设计，将面临更大的挑战。对象之间耦合度过高的系统，必然会出现牵一发而动全身的情形。

![图2 对象之间的依赖关系](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468740434039.png)

耦合关系不仅会出现在对象与对象之间，也会出现在软件系统的各模块之间，以及软件系统和硬件系统之间。如何降低系统之间、模块之间和对象之间的耦合度，是软件工程永远追求的目标之一。为了解决对象之间的耦合度过高的问题，软件专家 Michael Mattson 1996 年提出了 IOC 理论，用来实现对象之间的“解耦”，目前这个理论已经被成功地应用到实践当中。


## 2. 什么是 IOC
IOC 是 Inversion of Control 的缩写，多数书籍翻译成“控制反转”。

1996 年，Michael Mattson 在一篇有关探讨面向对象框架的文章中，首先提出了 IOC 这个概念。对于面向对象设计及编程的基本思想，前面我们已经讲了很多了，不再赘述，简单来说就是把复杂系统分解成相互合作的对象，这些对象类通过封装以后，内部实现对外部是透明的，从而降低了解决问题的复杂度，而且可以灵活地被重用和扩展。

**IOC 理论提出的观点大体是这样的：借助于“第三方”实现具有依赖关系的对象之间的解耦**。如下图：

![图3 IOC解耦过程](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468740616657.png)

大家看到了吧，由于引进了中间位置的“第三方”，也就是 IOC 容器，使得 A、B、C、D 这 4 个对象没有了耦合关系，齿轮之间的传动全部依靠“第三方”了，全部对象的控制权全部上缴给“第三方” IOC 容器，所以，IOC 容器成了整个系统的关键核心，它起到了一种类似“粘合剂”的作用，把系统中的所有对象粘合在一起发挥作用，如果没有这个“粘合剂”，对象与对象之间会彼此失去联系，这就是有人把 IOC 容器比喻成“粘合剂”的由来。

我们再来做个试验：把上图中间的 IOC 容器拿掉，然后再来看看这套系统：

![图4 拿掉IOC容器后的系统](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468740729251.png)

我们现在看到的画面，就是我们要实现整个系统所需要完成的全部内容。这时候，A、B、C、D这4个对象之间已经没有了耦合关系，彼此毫无联系，这样的话，当你在实现A的时候，根本无须再去考虑B、C和D了，对象之间的依赖关系已经降低到了最低程度。所以，如果真能实现IOC容器，对于系统开发而言，这将是一件多么美好的事情，参与开发的每一成员只要实现自己的类就可以了，跟别人没有任何关系！

我们再来看看，控制反转(IOC)到底为什么要起这么个名字？我们来对比一下：

* 软件系统在没有引入 IOC 容器之前，如图1所示，对象 A 依赖于对象 B，那么对象 A 在初始化或者运行到某一点的时候，自己必须主动去创建对象 B 或者使用已经创建的对象 B。无论是创建还是使用对象 B，控制权都在自己手上。

* 软件系统在引入 IOC 容器之后，这种情形就完全改变了，如图3所示，由于 IOC 容器的加入，对象 A 与对象 B 之间失去了直接联系，所以，当对象 A 运行到需要对象 B 的地方的时候，IOC 容器会主动创建一个对象 B 注入到对象 A 需要的地方。

通过前后的对比，我们不难看出来：**对象 A 获得依赖对象 B 的过程，由主动行为变为了被动行为，控制权颠倒过来了，这就是“控制反转”这个名称的由来**。


## 3.IOC 也叫依赖注入(DI)
2004 年，Martin Fowler 探讨了同一个问题，**既然 IOC 是控制反转，那么到底是“哪些方面的控制被反转了呢？”，经过详细地分析和论证后，他得出了答案：“获得依赖对象的过程被反转了”。**控制被反转之后，获得依赖对象的过程由自身管理变为了由 IOC 容器主动注入。于是，他给“控制反转”取了一个更合适的名字叫做“依赖注入（Dependency Injection）”。他的这个答案，实际上给出了实现 IOC 的方法：注入。**所谓依赖注入，就是由 IOC 容器在运行期间，动态地将某种依赖关系注入到对象之中。**

所以，依赖注入(DI)和控制反转(IOC)是从不同的角度的描述的同一件事情，就是指通过引入 IOC 容器，利用依赖关系注入的方式，实现对象之间的解耦。

我们举一个生活中的例子，来帮助理解依赖注入的过程。用电脑主机和USB接口来实现一个任务：从外部USB设备读取一个文件。

电脑主机读取文件的时候，它一点也不会关心 USB 接口上连接的是什么外部设备，而且它确实也无须知道。它的任务就是读取 USB 接口，挂接的外部设备只要符合 USB 接口标准即可。所以，如果我给电脑主机连接上一个 U盘，那么主机就从 U盘上读取文件；如果我给电脑主机连接上一个外置硬盘，那么电脑主机就从外置硬盘上读取文件。挂接外部设备的权力由我作主，即控制权归我，至于 USB 接口挂接的是什么设备，电脑主机是决定不了，它只能被动的接受。电脑主机需要外部设备的时候，根本不用它告诉我，我就会主动帮它挂上它想要的外部设备，你看我的服务是多么的到位。这就是我们生活中常见的一个依赖注入的例子。在这个过程中，我就起到了 IOC 容器的作用。

同样的，对于 IOC 来说：对象 A 依赖于对象 B,当对象 A 需要用到对象 B 的时候，IOC 容器就会立即创建一个对象 B 送给对象 A。IOC 容器就是一个对象制造工厂，你需要什么，它会给你送去，你直接使用就行了，而再也不用去关心你所用的东西是如何制成的，也不用关心最后是怎么被销毁的，这一切全部由 IOC 容器包办。

在传统的实现中，由程序内部代码来控制组件之间的关系。我们经常使用 new 关键字来实现两个组件之间关系的组合，这种实现方式会造成组件之间耦合。IOC 很好地解决了该问题，它将实现组件间关系从程序内部提到外部容器，也就是说由容器在运行期将组件间的某种依赖关系动态注入组件中。


学过IOC的人可能都看过 Martin Fowler(老马,2004年post)的这篇文章：《Inversion of Control Containers and the Dependency Injection pattern[2]》。

博客园的园友 EagleFish (邢瑜琨)的文章：《深度理解依赖注入（Dependence Injection）》[3]对老马那篇经典文章进行了解读。

CSDN 黄忠成的《Inside ObjectBuilder》[4]也是，不过他应该来自台湾省，用的是繁体，看不管繁体中文的，可以看园中的吕震宇博友的简体中文版《[转]Object Builder Application Block》[5]。
 　  
 　  
## 4. IOC 的优缺点
In my experience, IoC using the Spring container brought the following advantages[6]:

- flexibility
    * changing the implementation class for a widely used interface is simpler (e.g. replace a mock web service by the production instance)
    * changing the retrieval strategy for a given class is simpler (e.g. moving a service from the classpath to the JNDI tree)
    * adding interceptors is easy and done in a single place (e.g. adding a caching interceptor to a JDBC-based DAO)
- readability
    * the project has one unified and consistent component model and is not littered with factories (e.g. DAO factories)
    * the code is briefer and is not littered without dependency lookup code (e.g. calls to JNDI InitialContext)
- testability
    * dependencies are easy to replace mocks when they're exposed through a constructor or setter
    * easier testing leads to more testing
    * more testing leads to better code quality, lower coupling, higher cohesion

使用 IOC 框架产品能够给我们的开发过程带来很大的好处，但是也要充分认识引入 IOC 框架的缺点，做到心中有数，杜绝滥用框架[1]。

第一、软件系统中由于引入了第三方 IOC 容器，生成对象的步骤变得有些复杂，本来是两者之间的事情，又凭空多出一道手续，所以，我们在刚开始使用 IOC 框架的时候，会感觉系统变得不太直观。所以，引入了一个全新的框架，就会增加团队成员学习和认识的培训成本，并且在以后的运行维护中，还得让新加入者具备同样的知识体系。

第二、由于 IOC 容器生成对象是通过反射方式，在运行效率上有一定的损耗。如果你要追求运行效率的话，就必须对此进行权衡。

第三、具体到 IOC 框架产品(比如：Spring)来讲，需要进行大量的配制工作，比较繁琐，对于一些小的项目而言，客观上也可能加大一些工作成本。

第四、IOC 框架产品本身的成熟度需要进行评估，如果引入一个不成熟的 IOC 框架产品，那么会影响到整个项目，所以这也是一个隐性的风险。

我们大体可以得出这样的结论：一些工作量不大的项目或者产品，不太适合使用 IOC 框架产品。另外，如果团队成员的知识能力欠缺，对于 IOC 框架产品缺乏深入的理解，也不要贸然引入。最后，特别强调运行效率的项目或者产品，也不太适合引入 IOC 框架产品，像 WEB2.0 网站就是这种情况。


## 5. IOC 容器的技术剖析
　　IOC 中最基本的技术就是“反射(Reflection)”编程，目前 .Net C#、Java 和 PHP5 等语言均支持，其中 PHP5 的技术书籍中，有时候也被翻译成“映射”。有关反射的概念和用法，大家应该都很清楚，通俗来讲就是根据给出的类名（字符串方式）来动态地生成对象。这种编程方式可以让对象在生成时才决定到底是哪一种对象。反射的应用是很广泛的，很多的成熟的框架，比如象 Java 中的 Hibernate、Spring 框架，.Net 中 NHibernate、Spring.Net 框架都是把“反射”做为最基本的技术手段。


## 6. 参考博文
[1]. [架构师之路(39)](http://blog.csdn.net/wanghao72214/article/details/3969594)---IoC框架 ,王泽宾，CSDN, 2009.

[2]. [Inversion of Control Containers and the Dependency Injection pattern](http://www.martinfowler.com/articles/injection.html) ,Martin Fowler,2004.

[3]. [深度理解依赖注入（Dependence Injection）](http://www.cnblogs.com/xingyukun/archive/2007/10/20/931331.html),EagleFish(邢瑜琨), 博客园, 2007.

[4]. [Inside ObjectBuilder](http://blog.csdn.net/Code6421/article/details/1282139) ,黄忠成, CSDN, 2006.

[5]. [[转]Object Builder Application Block](http://www.cnblogs.com/zhenyulu/articles/641728.html) ，吕震宇,博客园, 2006.

[6]. [](http://forum.springsource.org/showthread.php?55015-I-still-don-t-get-why-IoC-is-important) link

