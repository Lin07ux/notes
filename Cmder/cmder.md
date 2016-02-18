
Cmder 是一个 Windows 下的命令行工具，能够很大程度上的改善 Windows 系统上的 cmd.exe 的功能。而且提供了一些 Linux 系统上的指令。

官网：[Cmder](http://cmder.net/)


## 配置
### 设置 cmd 命令行命令别名
可以将命令设置一个短的别名，这样能更方便的输入命令。
这里设置的命令只能在 cmd 命令行模式下才能使用。

设置别名有两种方式：

1. 使用 alias 命令设置：`$ alias <alias-name>=<command>`

这种方式一次可以添加一条别名命令，如，添加`gits`为`git status`命令的别名：
	`alias gits=git status`

2. 直接编辑 Cmder/config/alias 文件

这种方式能够直接添加或者编辑命令别名，但是添加之后可能需要重启 Cmder 程序才能生效。

alias 文件中添加的内容如下：

```
st3="D:\Program Files\Sublime Text 3\sublime_text.exe" $*
gitl=git log --oneline --all --graph --decorate  $*
gits=git status  
gita=git add $*
gitp=git push origin master
gitc=git commit -m $1
```

### 设置 PowerShell 命令行命令别名
需要编辑 Cmder/vendor/profile.ps1 文件，做相关的设置才能在 PowerShell 模式中使用。

如下：

```conf
function Git-Status { git status } 
Set-Alias gits Git-Status

function Git-Add { git add }
Set-Alias gita Git-Add

function Git-Commit { git commit -m }
Set-Alias gitc Git-Commit

function Git-Push { git push origin master }
Set-Alias gitp Git-Push

function Git-Log { git log --oneline --all --graph --decorate }
Set-Alias gitl Git-Log

Set-Alias st3 "D:\Program Files\Sublime Text 3\sublime_text.exe"

function go-Work {cd E:\work\web\cdn\}
Set-Alias gw go-Work
```



