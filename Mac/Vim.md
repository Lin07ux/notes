### 命令
- 删除所有内容：`:%d`
- 撤销上一步的删除：`U`
- 恢复上一步撤销的删除：`Ctrl + R`

### Vim 语法高亮
可以在 Vim 中输入命令`:syntax on`激活语法高亮。
若需要 Vim 启动时自动激活语法高亮，在`~/.vimrc`文件中添加一行`syntax on`即可。

一般情况，Vim 的配色最好和终端的配色保持一致，不然会很别扭。复制 solarized 配色方案，并编辑`~/.vimrc`文件：

```shell
cd solarized
cd vim/colors/solarized/colors
mkdir -p ~/.vim/colors
cp solarized.vim ~/.vim/colors/

vim ~/.vimrc
syntax enable
set background=dark
colorscheme solarized
```

