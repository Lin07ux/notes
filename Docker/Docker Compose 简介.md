## 一、简介

Compose 项目是 Docker 官方的开源项目，负责实现对 Docker 容器集群的快速编排。Compose 定位是：定义和运行多个 Docker  容器的应用。

使用一个 Dockerfile 模板文件可以让用户很方便的定义一个单独的应用容器。但在日常工作中，经常会碰到需要多个容器相互配合来完成某项任务的情况。例如，要实现一个 Web 项目，除了 Web 服务容器本身，往往还需要加上后端的数据库服务容器，甚至还包括负载均衡容器等。

Compose 恰好满足了这样的需求：它允许用户通过一个单独的`docker-compose.yml`模板文件来定义一组相关联的应用容器为一个项目(project)。

Compose 中有两个重要的概念：

* 服务(service): 一个应用的容器，实际上可以包括若干运行相同镜像的容器实例；
* 项目(project): 由一组关联的应用容器组成的一个完整业务单元，在`docker-compose.yml`文件中进行定义。

Compose 项目由 Python 编写，实现上调用了 Docker 服务提供的 API 来对容器进行管理。因此，只要所操作的平台支持 Docker API，就可以在其上利用 Compose 来进行编排管理。

### 二、使用

在使用 Docker Compose 之前，需要先了解下服务和项目的概念：

* 服务(`service`)：即一个应用容器，实际上也可以运行多个相同镜像的实例来提供一个服务。
* 项目(`project`)：由一组关联的应用容器组成的一个完整业务单元。

可见，一个项目可以由多个服务（容器）关联而成。Compose 面向的就是项目进行管理。也就是说，Compose 是用来对一个项目相关的全部容器进行管理的工具。

比如，对于一个 Web 站点，一般会包含 Web 服务和缓存服务，而这两个服务需要使用两个容器来提供，而 Docker Compose 就可以统一管理这两个容器，从而实现项目相关容器的统一管理。

下面使用 Python 来建立一个能够记录页面访问次数的 Web 网站。先新建一个文件夹，然后在该文件夹中进行如下的操作。

### 2.1 Web 应用代码

创建`app.py`文件，并编写如下代码：

```py
from flask import Flask
from redis import Redis

app = Flask(__name__)
redis = Redis(host='redis', port=6379)

@app.route('/')
def hello():
    count = redis.incr('hits')
    return 'Hello World! 该页面已被访问 {} 次。\n'.format(count)

if __name__ == "__main__":
    app.run(host="0.0.0.0", debug=True)
```

### 2.2 Dockerfile

为 Python Flask 应用创建`Dockerfile`文件：

```Dockerfile
FROM python:3.6-alpine
WORKDIR /code
COPY app.py .
RUN pip install redis flask
CMD ["python", "app.py"]
```

### 2.3 docker-compose.yml

为 Docker Compose 编写`docker-compose.yml`文件，这是 Compose 使用的主模板文件：

```yaml
version: '3'
services:

  web:
    build: .
    ports:
     - "5000:5000"

  redis:
    image: "redis:alpine"
```

### 2.4 运行 Compose

准备好以上三个文件之后，就可以运行 Compose 来编排文件了：

```shell
docker-compose up
```

成功启动之后，访问本地的 [http://0.0.0.0:5000/](http://0.0.0.0:5000) 页面，每次刷新页面，计数就会加 1。

