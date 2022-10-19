> 转摘：[env: python: No such file or directory when building app with Xcode](https://stackoverflow.com/questions/71468590/env-python-no-such-file-or-directory-when-building-app-with-xcode)

### 问题

在新版本的 Mac 命令行中执行命令的时候，总是会出现如下的错误：

```text
env: python: No such file or directory
```

### 原因

根据提示信息可以知道，是因为新版本的 Mac 系统中默认已经不安装 Python 了，而 Xcode 确要求使用 Python（而不是 Python3）。所以即便在 Mac 中安装了 Python3，依旧不能解决这个问题。

### 解决

通过 Homebrew 安装 Python3 的时候，名字中不包含版本的 python、python-config 等程序也会被安装在一个特定的文件夹中，所以只需要将这个文件夹路径也放在系统的 PATH 环境变量中即可解决这个问题。

1. 首先，通过`brew info python`命令来查看相关提示：

    ```shell
    $ brew info python
    ==> python@3.10: stable 3.10.6 (bottled)
    Interpreted, interactive, object-oriented programming language
    https://www.python.org/
    /usr/local/Cellar/python@3.10/3.10.6_2 (3,112 files, 56.5MB)
      Poured from bottle on 2022-09-22 at 23:44:13
    From: https://github.com/Homebrew/homebrew-core/blob/HEAD/Formula/python@3.10.rb
    License: Python-2.0
    ==> Dependencies
    Build: pkg-config ✔
    Required: gdbm ✔, mpdecimal ✔, openssl@1.1 ✔, readline ✔, sqlite ✔, xz ✔
    ==> Caveats
    Python has been installed as
      /usr/local/bin/python3
    
    Unversioned symlinks `python`, `python-config`, `pip` etc. pointing to
    `python3`, `python3-config`, `pip3` etc., respectively, have been installed into
      /usr/local/opt/python@3.10/libexec/bin
      
    ...
    ```

2. 将输出信息中 python 二进制程序的路径配置到 PATH 系统变量中（可以直接编辑配置文件也可以使用下面的命令）：

    ```shell
    echo 'export PATH="/usr/local/opt/python@3.10/libexec/bin:$PATH"' >>~/.bash_profile
    ```

3. 重新加载配置即可

    ```shell
    source ~/.bash_profile
    ```


