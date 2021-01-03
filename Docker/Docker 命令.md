* `docker save` 将镜像保存为一个归档文件

    ```shell
    # 这里的 filename 可以为任意名称甚至任意后缀名，但文件的本质都是归档文件
    docker save alpine -o filename
    # 使用 gzip 压缩
    docker save alpine | gzip > alpine-latest.tar.gz
    ```

* `docker load` 从文件加载镜像

    ```shell
    docker load -i alpine-latest.tar.gz
    ```

* `docker login` 登录 Docker Hub

* `docker logout` 退出登录

* `docker search` 搜索官方仓库中的镜像

    - 可以指定镜像名称进行搜索，如：`docker search centos`，表示搜索 centos 镜像；
    - 可以指定仓库名称进行搜索，如：`docker search tianon/`，表示搜索用户`tianon`提供的镜像；
    - 可以使用过滤条件进行过滤，如：`docker search nginx --filter=stars=1000`，表示搜索收藏数量为 1000 以上的镜像。

* `docker push` 推送镜像到自己的 Docker Hub 账户下

    ```shell
    $ docker tag ubuntu:18.04 username/ubuntu:18.04
    
    $ docker image ls
    REPOSITORY            TAG           IMAGE ID            CREATED             SIZE
    ubuntu                18.04         275d79972a86        6 days ago          94.6MB
    username/ubuntu       18.04         275d79972a86        6 days ago          94.6MB
    
    $ docker push username/ubuntu:18.04
    
    $ docker search username
    NAME                DESCRIPTION            STARS         OFFICIAL         AUTOMATED
    username/ubuntu
    ```


