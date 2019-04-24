### 问题

NPM 安装依赖的时候，提示错误：

```
npm ERR! enoent ENOENT: no such file or directory, chmod ...
```

### 原因

排除运行安装依赖命令的用户权限不足的原因，一般是在升级 NPM 之后会有这种情况。

### 解决

重新安装 Node。

