* `docker import` 从 rootfs 压缩包导入。
  
    ```shell
    docker import \
    http://download.openvz.org/template/precreated/ubuntu-16.04-x86_64.tar.gz \
    openvz/ubuntu:16.04
    ```

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


