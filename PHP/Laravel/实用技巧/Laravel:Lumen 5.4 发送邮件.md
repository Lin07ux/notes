> 转摘：[Laravel/Lumen 5.4 发送邮件](https://www.tuicool.com/articles/bEvyQfv)

### 1. 准备

以 163 邮箱为例，首先需要开启邮箱的 POP3/SMTP 服务：

![](http://cnd.qiniu.lin07ux.cn/markdown/1560136616044.png)

开启服务时，会提示设置客户端授权码，这个授权码可以建议单独设置，和登录密码不相同，只用于客户端连接使用：

![](http://cnd.qiniu.lin07ux.cn/markdown/1560136689016.png)

### 2. 配置

邮箱设置完成之后，就可以将邮箱账号相关信息配置到 Laravel/Lumen 的`.env`文件中了：

```ini
MAIL_DRIVER=smtp
MAIL_HOST=smtp.163.com
MAIL_PORT=465
MAIL_USERNAME=邮箱地址
MAIL_PASSWORD=授权码
MAIL_FROM_ADDRESS=邮箱地址
MAIL_FROM_NAME=自定义发件人名称
MAIL_ENCRYPTION=ssl
```

> 如果是 Lumen，还需要安装对应版本的`illuminate/mail`依赖。

### 3. 发送邮件

发送邮件的方式很简单，`illuminate/mail`都做了很完善的封装，只需要传入相应的参数即可。

下面建立一个发送邮件的命令：

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class sendMailCommandextends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:sendMailCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送邮件命令';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $content = '这是一封来自 Laravel 的测试邮件.';
        $toMail  = 'example@qq.com';

        Mail::raw($content, function ($message) use ($toMail) {
            $message->subject('[ 测试 ] 测试邮件 SendMail - ' .date('Y-m-d H:i:s'));
            $message->to($toMail);
        });
    }
}
```

将命令加入到`app/Console/Kernel.php`之后，就可以在命令行中执行该命令，发送邮件了：

```php
protected $commands = [
    sendMailCommand::class
];
```

发送邮件：

```shell
php artisan mail:sendMailCommand 
```


