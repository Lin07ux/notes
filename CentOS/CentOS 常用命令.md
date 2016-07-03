### 查看系统
- `cat  /etc/redhat-release`  系统版本
- `uname  -r`  内核版本

### 查看 IP
`ifconfig [eth]` 这样能够查看所有的网卡的 IP 相关信息，也可以指定具体的网卡。

这样查看的信息会比较多。可以考虑使用`grep`过滤筛选：

`ifconfig | grep "Bcast"`

这样输出的信息就会比较少。

### 查看大小
`df` 可以查看磁盘的使用情况以及文件系统被挂载的位置
	df -lh

`du` 可以查看文件和文件夹的大小
	# 查看文件大小用 -h 选项
	du -h [filename]
	# 查看文件夹大小用 -sh 选项
	du -sh [directory]

### 查看系统安装包
`cat -n /root/install.log`可以查看系统中安装的软件包

`rpm -qa` 查看现在已经安装了哪些软件包



### 查看 CPU 负载
通过 top 命令来查看 CPU 使用状况。
运行 top 命令后，CPU 使用状态会以全屏的方式显示，并且会处在对话的模式
	-- 用基于 top 的命令，可以控制显示方式等等。

运行 top 命令后，显示的内容解释如下：
顶部：
	第一行(top)：
		系统当前时刻
		系统启动后运行到现在的运行时间
		当前登陆到系统的用户，确切的说是登陆到用户的终端数（同一用户可以在多个终端登陆）
		load average 为当前系统负载的平均值。
			后面跟随的三个值分别表示：1分钟前、5分钟前、15分钟前进程的平均数。
	第二行(task)：
		total 当前系统进程综述
		running 当前运行中的进程数
		sleeping 当前处于等待状态的进程数
		stoped 被停止的进程数
		zombie 被复原的进程数
	第三行(Cpus)：
		分别表示了 CPU 当前的使用率
	第四行(Mem)：
		total 内存总数
		used 当前使用量
		free 空闲内存量
		buffers 缓冲使用中的内存量
	第五行(Swap)：
		类似第四行。
		这里反映着交换分区的使用情况。
		如果交换分区被频繁使用的时候，奖杯视作物理内存不足而造成的。

中间部分：
	内部命令提示行
	在这里可以输入 top 的内部命令。
	内部命令表如下：
		s   改变画面更新频率
		l   关闭或开启顶部第一行 top 信息的表示
		t   关闭或开启顶部第二行 Tasks 和第三行 Cpus 信息的表示
		m   关闭或开启顶部第四行 Mem 和第五行 Swap 信息的表示
		N   以 PID 的大小顺序排列表示进程列表
		P   以 CPU 占用率大小的顺序排列进程列表
		M   以内存占用大小的顺序排列进程列表
		h   显示帮助
		n   设置在进程列表所显示进程的数量
		q   退出 top
		s   改变画面更新周期

底部：
	以 PID 区分的进程列表将根据所设定的画面更新时间定期的更新。


### 查看内存的使用
Linux 里一般使用`free`命令查看内存的使用情况，加参数`-m`表示以 Mb 为单位来显示内容。

显示的内容分为三行：
	Mem  表示内存的使用情况
	-/+ buffers/cache  表示内存 -/+ buffers/cache 后的使用情况
	Swap 表示交换分区的使用情况

需要注意的是：
	第二行中 used 列的值表示的是：已使用的内存数 - buffers/cache 之后所用的内存。
	第二行的 free 列的值表示的是：可使用的内存数 + buffers/cache 之后的空闲内存。

buffer 和 cache 是 linux 中的缓存技术。
第一行中各列的内存数，是系统反映出来的，而对系统来说，buffer 和 cache 都是已经分配出去的内存，所以属于 used（已经使用）。
第二行中各列的内存则不然，它们是 -/+ buffer/cache 后的内存数。对于程序而言，buffer 和 cache 都是可以使用的内存。因为它们原来就是设计成预先分配的用以提高性能的内存，所以它们都是可用的即 free。


### 查看文件
查看文件有多种方式，各有其特点。

**cat**  将文件或标准输入组合完全输出到标准输出中。适合内容较少的文件。
	用法：cat [option] [file] ...
	如果[file]缺省或者为 - ，则读取标准输入。
	选项：可用选项有如下几个
		-A,  --show-all        等价于 -vET
		-b,  --number-nonblacnk   对非空输出行编号
		-e                        等价于 -vE
		-E,  --show-ends          在每行结尾处显示 $
		-n,  --number             对输出的所有行编号
		-s,  --sequeeze-blank     不输出多行空行
		-t                        等价于 -vT
		-u                        (被忽略)
		-v,  --show-nonprinting   使用 ^ 和 M- 引用。除了 LFD 和 TAB 之外
		-- help                   显示帮助信息并退出
		--version                 显示版本信息并退出

**more**  显示文件的一屏内容。其从前向后读取文件，因此在启动时就加载整个文件。
	除了可以显示文件内容，还能通过管道对其他输出进行分屏显示，如，`ls -l . | more`
	用法：more [option] [file]
	选项：
	    +n     从第 n 行开始显示
	    -n     定义屏幕大小为 n 行
	    +/pattern 在每个档案显示前搜寻该字符串，然后从该字符串前两行之后开始显示
	    -c        从顶部清屏，然后显示
	    -d        给出操作提示
	    -l        忽略 Ctrl + l (换页)字符
	    -p        通过清除仓库而不是滚屏来对文件进行换页，与 -c 类似
	    -s        把连续的多个空行显示为一行
	    -u        把文件内容中的下划线去掉
	常用操作指令：
		Enter    向下 n 行，默认为 1 行，可以定义。
		Ctrl + F 向下滚动一屏
		Space    向下滚动一屏
		Ctrl + B 返回上一屏
		=        输出当前的行号
		:f       输出文件名和当前的行号
		V        调用 vi 编辑器
		![命令]  调用 Shell 命令并执行
		+[n]     先按 + 号，然后输数字，再按 Enter 之后就能向下翻指定行。
		q        退出 more

**less**  和 more 类似，分屏查看文件。但是在查看时，不会加载整个文件。
	用法：less [option] [file]
	选项：
		-b      <缓冲区大小> 设置缓冲区的大小
		-e  当文件显示结束后，自动离开
		-f  强迫打开特殊文件，例如外围设备代号、目录和二进制文件
		-g  只标志最后搜索的关键词
		-i  忽略搜索时的大小写
		-m  显示类似more命令的百分比
		-N  显示每行的行号
		-o <文件名> 将less 输出的内容在指定文件中保存起来
		-Q          不使用警告音
		-s          显示连续空行为一行
		-S          行过长时间将超出部分舍弃
		-x <数字>   将“tab”键显示为规定数量的空格
	常用操作：
		/字符串     向下搜索“字符串”的功能
		?字符串     向上搜索“字符串”的功能
		n           重复前一个搜索（与 / 或 ? 有关）
		N           反向重复前一个搜索（与 / 或 ? 有关）
		b           向后翻一页
		d           向后翻半页
		h           显示帮助界面
		Q           退出less 命令
		u           向前滚动半页
		y           向前滚动一行
		Space       滚动一行
		Enter       滚动一页
	    pagedown    向下翻动一页
		pageup      向上翻动一页

**tail**  显示文件的最后 10 行。还能用来跟踪日志。
	用法：tail [option] [file]
	选项：
		-f 跟踪日志，将文件里最尾部的内容显示在屏幕上，并且不断刷新。


### 创建交换分区
查看交换分区的信息
	swapon -s
	这里就会显示系统上的交换分区的信息。

	[root@iZ28xvb5f81Z ~]# swapon -s
	Filename                                Type            Size    Used    Priority
	/swapfile                               file            2097148 0       -1
	
添加交换文件
	# if 指定挂载的位置
	# of 指定交换文件的名称
	# bs
	# count 指定交换分区的大小，单位是 kb，可以写成 2048000 或者 2048k
	dd if=/dev/zero of=/swapfile bs=1024 count=2048k

	# 输出如下
	[root@iZ28xvb5f81Z ~]# dd if=/dev/zero of=/swapfile bs=1024 count=2048k
	2048000+0 records in
	2048000+0 records out
	2097152000 bytes (2.1 GB) copied, 3.09593 s, 347 MB/s

> 交换分区一般设置为内存的 1-2 倍即可，太大也无用。

创建交换分区
	# /swapfile 就是上一步添加交换文件时指定的文件名
	mkswap /swapfile

	# 输出如下
	mkswap: /swapfile: warning: don’t erase bootbits sectors
	on whole disk. Use -f to force.
	Setting up swapspace version 1, size = 2097147 KiB
	no label, UUID=9722999f-ae6c-4caa-ac3a-a74369740a17

开启交换分区
	# /swapfile 就是上面创建的交换文件名称
	swapon /swapfile

设置开机自启动
	echo "/swapfile swap swap defaults 0 0" >>/etc/fstab

检查是否生效
	# 使用 free -m 命令看
	# 查看结果中是否有相应的交换分区空间
	free -m

	# 输出如下
	[root@iZ28xvb5f81Z ~]# free -m
	             total       used       free     shared    buffers     cached
	Mem:           994        927         67         78         45         96
	-/+ buffers/cache:        786        208
	Swap:         2047         33       2014


### 查看系统日志
系统日志位于 /var/log/messages 文件中。
可以用 egrep 命令过滤其中的内容。
	# 显示系统日志中关于 oom、kill 以及 mysql 的信息
	egrep -i "oom|kill|mysql" /var/log/messages


### 查看防火墙设置
防火墙配置文件一般位于：`etc/sysconfig/iptables`

修改之后，需要重启一下 iptabels 服务：`service iptables restart`


### 系统时区和时间
1. 查看时间：`date`
2. 修改时间：`date -s <date_time>`。
    - 如：设置日期为 2016-06-07，`date -s 06/07/2016`
    - 如：设置时间为 12:10:43，`date -s 12:10:43`
3. 将时间写入到 COMS：`clock -w`。如果不写入 COMS，那么可能会在重启后时间设置失效。
4. 查看时区：`date -R`。
5. 修改时区：如，将`Asia/shanghai`上海时区设置为当前时区，`#cp -f /usr/share/zoneinfo/Asia/Shanghai  /etc/localtime`。提示是否覆盖。输入Y回车即可。


