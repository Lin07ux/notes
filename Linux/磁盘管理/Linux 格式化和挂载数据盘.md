> 转摘：[Linux 格式化和挂载数据盘](https://help.aliyun.com/document_detail/25426.html)

对于新增加的磁盘，需要先格式化数据盘并挂载文件系统后才能正常使用该磁盘。

> *注意*：磁盘分区和格式化是高风险行为，慎重操作。如果磁盘上有数据，请先做好备份。

本文描述如何用一个新的数据盘创建一个单分区数据盘并挂载文件系统。本文仅适用于使用`fdisk`命令对一个不大于 2 TB 的数据盘执行分区操作。如果需要分区的数据盘大于 2 TB，请参考 [32TB 块存储分区](https://help.aliyun.com/document_detail/34377.html)。

> 阿里云中单独购买的数据盘，需要先挂载到服务器上，才能进行格式化和分区。随实例时一起购买的数据盘，无需挂载，直接格式化。

下面用一个新的 20 GiB 数据盘（设备名为`/dev/xvdb`）创建一个单分区数据盘并挂载一个 ext3 文件系统。使用的实例是 I/O 优化实例，操作系统为 CentOS 6.8。

### 1. 查看磁盘是否已经挂载

远程连接到服务器中，使用`fdisk -l`命令查看服务器是否已经挂载了新的数据盘。如果执行该命令之后，没有发现`/dev/vdb`，则表示磁盘没有挂载成功，需先进行磁盘挂载。

### 2. 创建分区

挂载磁盘成功之后，依次执行如下的命令，就可以创建一个单分区的数据盘了：

1. 运行`fdisk /dev/vdb`对数据盘进行分区；
2. 输入`n`并按回车键，创建一个新的分区；
3. 输入`p`并按回车键，选择主分区。因为创建的是一个单分区数据盘，所以只需要创建主分区。

    > 说明：如果要创建 4 个以上的分区，您应该创建至少一个扩展分区，即选择`e`。

4. 输入分区编号并按回车键。因为这里仅创建一个分区，可以直接按回车键使用默认值。
5. 输入第一个可用的扇区编号。按回车键采用默认值。
6. 输入最后一个扇区编号。因为这里仅创建一个分区，所以按回车键采用默认值。
7. 输入`wq`并按回车键，开始分区。

整个流程类似如下：

```
[root@iZbp10og9bpm8715g6i9noZ:~]# fdisk /dev/vdb

Welcome to fdisk (util-linux 2.27.1).
Changes will remain in memory only, until you decide to write them.
Be careful before using the write command.

Device does not contain a recognized partition table.
Created a new DOS disklabel with disk identifier 0xccd29329.

Command (m for help): n
Partition type
   p   primary (0 primary, 0 extended, 4 free)
   e   extended (container for logical partitions)
Select (default p): p
Partition number (1-4, default 1):
First sector (2048-1048575999, default 2048):
Last sector, +sectors or +size{K,M,G,T,P} (2048-1048575999, default 1048575999):

Created a new partition 1 of type 'Linux' and of size 500 GiB.

Command (m for help): wq
The partition table has been altered.
Calling ioctl() to re-read partition table.
Syncing disks.
```

### 3. 查看分区

创建分区完成之后，使用`fdisk -l`命令查看当前系统的分区情况，如果能看到类似`/dev/vdb1`这样的数据，则说明已经成功创建了：

```
[root@iZbp10og9bpm8715g6i9noZ:~]# fdisk -l
Disk /dev/vda: 40 GiB, 42949672960 bytes, 83886080 sectors
Units: sectors of 1 * 512 = 512 bytes
Sector size (logical/physical): 512 bytes / 512 bytes
I/O size (minimum/optimal): 512 bytes / 512 bytes
Disklabel type: dos
Disk identifier: 0xd6804155

Device     Boot Start      End  Sectors Size Id Type
/dev/vda1  *     2048 83884031 83881984  40G 83 Linux


Disk /dev/vdb: 500 GiB, 536870912000 bytes, 1048576000 sectors
Units: sectors of 1 * 512 = 512 bytes
Sector size (logical/physical): 512 bytes / 512 bytes
I/O size (minimum/optimal): 512 bytes / 512 bytes
Disklabel type: dos
Disk identifier: 0xccd29329

Device     Boot Start        End    Sectors  Size Id Type
/dev/vdb1        2048 1048575999 1048573952  500G 83 Linux
```

### 4. 创建分区系统

成功分区之后，就可以在新的分区上创建文件系统了。这里创建的是一个 ext3 系统，也可以根据自己的需要，选择创建其他文件系统。例如，如果需要在 Linux、Windows 和 Mac 系统之间共享文件，可以使用`mkfs.vfat`创建 VFAT 文件系统。

使用`mkfs.ext3 /dev/vdb1`在新分区上创建文件系统，所需要的时间取决于磁盘的大小：

```
[root@iZbp10og9bpm8715g6i9noZ:~]# mkfs.ext3 /dev/vdb1
mke2fs 1.42.13 (17-May-2015)
Creating filesystem with 131071744 4k blocks and 32768000 inodes
Filesystem UUID: c95b6d9c-508b-4adf-b112-d62fc61b2630
Superblock backups stored on blocks:
	32768, 98304, 163840, 229376, 294912, 819200, 884736, 1605632, 2654208,
	4096000, 7962624, 11239424, 20480000, 23887872, 71663616, 78675968,
	102400000

Allocating group tables: done
Writing inode tables: done
Creating journal (32768 blocks): done
Writing superblocks and filesystem accounting information: done
```

### 5. 写入新分区信息

还需要将该分区写入到系统的`/etc/fstab`文件中，以便系统能够找到该分区。

写入新分区信息之前，建议先对该文件进行备份：

```shell
# 备份
cp /etc/fstab /etc/fstab.bak
# 写入
echo /dev/vdb1 /mnt ext3 defaults 0 0 >> /etc/fstab
# Ubuntu 12.04 不支持 barrier，需要使用下面的命令
# echo '/dev/vdb1 /mnt ext3 barrier=0 0 0' >> /etc/fstab
```

> 命令中的`/mnt`是分区系统将要挂载的位置，可以根据自己的情况进行设置，比如专门存放网页的数据盘可以挂载成`/wwwroot`等。

写入之后，就可以使用`cat /etc/fstab`命令查看分区信息了：

```
[root@iXXXXXXX ~]# cat /etc/fstab
#
# /etc/fstab
# Created by anaconda on Thu Feb 23 07:28:22 2017
#
# Accessible filesystems, by reference, are maintained under '/dev/disk'
# See man pages fstab(5), findfs(8), mount(8) and/or blkid(8) for more info
#
UUID=3d083579-f5d9-4df5-9347-8d27925805d4 /                       ext4    defaults        1 1
tmpfs                   /dev/shm                tmpfs   defaults        0 0
devpts                  /dev/pts                devpts  gid=5,mode=620  0 0
sysfs                   /sys                    sysfs   defaults        0 0
proc                    /proc                   proc    defaults        0 0
/dev/vdb1 /mnt ext3 defaults 0 0
```

### 6. 挂载文件系统

现在就可以将创建的文件系统挂载到系统中了。挂载使用`mount`命令，挂载位置可以自定，但是要和上面写入到`/etc/fstab`中的一致：

```shell
mount /dev/vdb1 /mnt
```

挂载之后，就可以使用`df -h`命令来查看现在的文件系统信息了，如果能正常看到挂载点(在这里是`/mnt`)则说明挂载成功了，可以使用该文件系统了：

```
[root@iXXXXXXX ~]# df -h
Filesystem      Size  Used Avail Use% Mounted on
/dev/vda1        40G  6.6G   31G  18% /
tmpfs           499M     0  499M   0% /dev/shm
/dev/vdb1        20G  173M   19G   1% /mnt
```

