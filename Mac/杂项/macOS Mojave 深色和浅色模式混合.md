> 转摘：[macOS Mojave黑暗深色模式混合改造：深色風格與淺色視窗結合](https://mrmad.com.tw/macos-mojave-dark-mode-mixed-light-mode)

macOS Mojave 系统的深色模式会将系统上所有的 Dock、Finder、系统偏好设定和原生的内建程序都会变成深色底的风格，但是颜色太深会造成眼睛疲乏，可以将其改造成深色和浅色混合模式，类似上一代系统中的设定：导航栏、Dock 维持深色，系统内建应用不再使用深色底。

### 设置混合色模式

1. 在`系统偏好设置 --> 通用`中将外观改成`浅色`模式。

2. 在终端中执行指令：`defaults write -g NSRequiresAquaSystemAppearance -bool Yes`

3. 点击导航栏左上角中的 Apple Logo，选择最下面的`退出登录..`，登出当前账号。

4. 重新登录自己的账号。

5. 在`系统偏好设置 --> 通用`中将外观改成`深色`模式。

这样就会将系统自带的应用改成浅白色的底，而导航栏、Dock 却是深色的。

### 取消混合色模式

1. 在终端中执行指令：`defaults write -g NSRequiresAquaSystemAppearance -bool No`

2. 登出当前账号。

3. 重新登录账号。

这样就会重新变成全深色模式了。

