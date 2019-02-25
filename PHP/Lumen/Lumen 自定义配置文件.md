在 Lumen 框架中实现自定义配置文件，需要如下步骤：

* 首先在根目录下新建`config`文件夹，在文件夹中放入配置文件，例如`times.php`；
* 在`bootstrap/app.php`中通过`$app->configure('times')`进行全局引入；
* 然后在需要调用配置文件时，直接使用`Config::get('times')`或`config('times')`即可获取。




