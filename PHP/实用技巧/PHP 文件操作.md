
### file_get_contents

该函数读取一个文件的内容，然后赋值给一个变量。也就是说，这个函数读取的文件内容都存放在内存中的。可以使用这个函数来打开任意类型的文件。

比如，打开一个远程图片，保存到本地：

```php
$image = file_get_contents('http://www.url.com/image.jpg'); 
file_put_contents('/images/image.jpg', $image); 
```

再比如，打开一个图片[PHP 小技巧](media/PHP%20%E5%B0%8F%E6%8A%80%E5%B7%A7.md)，并输出给用户：

```php
header('Content-type: image/jpeg');
$image = file_get_contents('/temp/a.jpg');
echo $image;
```

> 虽然 fopen 能够用文件流的方式打开文件，可以节省内容占用，但是如果是想打开文件并上传到远程服务器的时候，fopen 可能会造成上传的文件有问题。而 file_get_contents 则不会。


### file_put_contents

将一个字符串写入文件，有4个参数。其中前两个分别表示要被写入数据的文件名和要写入的数据。其余两个使用的较少。

由于写入的数据是一个字符串，所以处理一维或者多维数组时，建议使用 json 格式封装数组数据，读取的时候解析 json 即可。

```php
<?php
    // * 写入文本
    // 写入文本时file_put_contents方法只能将一个字符串写入文件，如选择json格式
    $data_json = json_encode( $data );

    // 检查目录是否存在                                                                       
    FileClass::checkDir( AUTO_SYNC_PATH . 'rongxDemo/' ); 

    // 组装文件绝对路径，数据写入文本时文本的路径必须是绝对路径，不能是相对路径
    $fileName = AUTO_SYNC_PATH . 'rongxDemo/' . "data.txt"; 

    $file = file_put_contents ( $fileName, $data_json ); // 写入数据
?>
```


