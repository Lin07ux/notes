## 使用示例

### 1、 使 post 请求发送的是 formdata 格式数据

axios 默认的请求数据是 json 数据类型，如果要更改请求数据的类型，就需要修改请求头中的`content-type`的值，并拦截请求以对数据做相关的修改。

比如，更改请求数据的类型为 formdata，则需要：

* 首先必须设置请求头

```JavaScript
axios.defaults.headers['Content-Type'] = 'application/x-www-form-urlencoded
';
```

* 其次在发送之前需要处理一下数据

```JavaScript
// 发送请求前处理 request 的数据
axios.defaults.transformRequest = [function (data) {
    // Do whatever you want to transform the data
    let newData = ''
    for (let k in data) {
        newData += encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) + '&'
    }
    return newData
}]
```

### 2、 拦截器

拦截器可以截取请求或响应，在被 then 或者 catch 处理之前做一些特殊的处理。

比如，常见的会在请求前显示一个加载动画，在请求后隐藏这个动画。

```JavaScript
// 添加请求拦截器
axios.interceptors.request.use(config => {
  // 在发送请求之前做某事，比如说 设置 loading 动画显示
  return config
}, error => {
  // 请求错误时做些事
  return Promise.reject(error)
})

// 添加响应拦截器
axios.interceptors.response.use(response => {
  // 对响应数据做些事，比如说把 loading 动画关掉
  return response
}, error => {
  // 请求错误时做些事
  return Promise.reject(error)
})

// 如果不想要这个拦截器也简单，可以删除拦截器
var myInterceptor = axios.interceptors.request.use(function () {/*...*/})
axios.interceptors.request.eject(myInterceptor)
```

