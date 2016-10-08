
### file_get_contents
该函数读取一个文件的内容，然后赋值给一个变量。也就是说，这个函数读取的文件内容都存放在内存中的。可以使用这个函数来打开任意类型的文件。

比如，打开一个远程图片，保存到本地：

```php
$image = file_get_contents('http://www.url.com/image.jpg'); 
file_put_contents('/images/image.jpg', $image); 
```

再比如，打开一个图片，并输出给用户：

```php
header('Content-type: image/jpeg');
$image = file_get_contents('/temp/a.jpg');
echo $image;
```

> 虽然 fopen 能够用文件流的方式打开文件，可以节省内容占用，但是如果是想打开文件并上传到远程服务器的时候，fopen 可能会造成上传的文件有问题。而 file_get_contents 则不会。


