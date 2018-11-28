在 Python 项目中，如果使用了虚拟环境，则一般会有单独的虚拟环境目录和文件，而且项目所需的依赖也都在虚拟环境中。此时使用 Git 进行版本控制的时候，虚拟环境和依赖如何管理也就需要考虑了。

有三种可能的方案：

* 将虚拟环境也添加到 Git 里面
* 用`.gitignore`忽略掉 venv 目录
* 用 venv 建立一个大的目录，然后在下面建立项目子目录，Git 只管理这个子目录

由于虚拟环境和库依赖在不同的环境中会有所不同，如果直接使用 Git 追踪全部的虚拟环境文件和库依赖，那么会造成部署项目到其他机器时，可能无法正常运行。如果不追踪虚拟环境目录，那么需要有一个方法能够知道该项目所需要的库依赖有哪些，否则否则正常部署了。

综合考虑，推荐使用如下的最佳实践方式：

1. 不追踪虚拟环境目录和文件；
2. 使用`pip freeze > requirements.txt`将依赖都添加到`requirements.txt`文件中，包含依赖的版本信息；
3. 部署项目时，重建虚拟环境，并通过`pip install -r requirements.txt`来安装所需的依赖。

> 参考：[When working with a venv virtual environment, which files should I be commiting to my git repository?](https://stackoverflow.com/questions/45394653/when-working-with-a-venv-virtual-environment-which-files-should-i-be-commiting)


