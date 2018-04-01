从 App Store 升级或更新 Mac OS X 之后，在系统自带的终端中执行某些命令的时候，会出现如下的错误：

```
xcrun: error: invalid active developer path (/Library/Developer/CommandLineTools), missing xcrun at: /Library/Developer/CommandLineTools/usr/bin/xcrun
```

这时候可以在终端中执行如下的命令即可解决：

```shell
xcode-select --install
```

参考：[xcrun: error](http://tips.tutorialhorizon.com/2015/10/01/xcrun-error-invalid-active-developer-path-library-developer-commandline-tools-missing-xcrun/)



