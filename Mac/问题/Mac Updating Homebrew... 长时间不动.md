由于 Homebrew 默认使用的 git 仓库在国内访问较慢，所以会造成在使用 brew 的时候，经常会提示`Updating Homebrew...`并卡住不动。可以通过修改 Homebrew 的安装源来解决这个问题。

```shell
# 替换 brew.git
cd "$(brew --repo)"
git remote set-url origin https://mirrors.ustc.edu.cn/brew.git

# 替换 homebrew-core.git:
cd "$(brew --repo)/Library/Taps/homebrew/homebrew-core"
git remote set-url origin https://mirrors.ustc.edu.cn/homebrew-core.git

# 如果使用了 homebrew-cask 还需要替换 Homebrew Cask 的地址
cd "$(brew --repo)/Library/Taps/homebrew/homebrew-cask"
git remote set-url origin https://mirrors.ustc.edu.cn/homebrew-cask.git
```

> 转摘：[Mac 解决brew一直卡在Updating Homebrew](https://www.jianshu.com/p/7cb05a2b39a5)

