有时上传图片时，需要给图片添加水印，水印一般为文字或图片logo水印，下面就来看看两种添加方法。

### 文字水印
文字水印就是在图片上加上文字，主要使用 gd 库的`imagefttext()`方法，并且需要字体文件。

#### 在已有的图片上添加水印
```php
// 源图片文件
$dst_path = 'dst.jpg'; 
// 创建图片的实例 
$dst = imagecreatefromstring(file_get_contents($dst_path)); 
 
// 字体路径
$font = './simsun.ttc'; 
// 字体颜色
$black = imagecolorallocate($dst, 0x00, 0x00, 0x00);

// 打上文字水印
imagefttext($dst, 13, 0, 20, 20, $black, $font, '快乐编程');

// 输出图片信息 
list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path); 
switch ($dst_type) { 
    case 1:   // GIF 
        header('Content-Type: image/gif'); 
        imagegif($dst); 
        break; 
    case 2:   // JPG 
        header('Content-Type: image/jpeg'); 
        imagejpeg($dst); 
        break; 
    case 3:   // PNG 
        header('Content-Type: image/png'); 
        imagepng($dst); 
        break; 
    default: 
        break; 
}

// 销毁图片
imagedestroy($dst);  
```

#### 新建图片并添加水印
```php
// imagecreatefromstring() / imageCreateFromPng()
// Create a new image from file or URL   创建图片对象 
 
// Create a 500 x 300 image，新创建一张图片 
$im = imagecreatetruecolor(500, 300); 
 
// set color 
$red   = imagecolorallocate($im, 0xFF, 0x00, 0x00); 
$black = imagecolorallocate($im, 0x00, 0x00, 0x00); 
 
// Make the background red 
// function imagefilledrectangle ($image, $x1, $y1, $x2, $y2, $color) {} 
imagefilledrectangle($im, 0, 0, 300, 100, $red); 
 
// Path to our ttf font file 
$font_file = './font/Arial.ttf'; 
 
 
// imagefttext ($image, $size, $angle, $x, $y, $color, $fontfile, $text, $extrainfo = null ) 
// Draw the text 'PHP Manual' using font size 13 
imagefttext($im, 13, 0, 150, 50, $black, $font_file, 'PHP Manual'); 
 
// Output image to the browser 
header('Content-Type: image/png'); 
 
imagepng($im); 
imagedestroy($im); 
```

### 图片水印
图片水印就是将一张图片加在另外一张图片上，主要使用 gd 库的`imagecopy()`和`imagecopymerge()`。

```php
$dst_path = 'myimage.jpg'; 
$src_path = 'http://www.logodashi.com/FileUpLoad/inspiration/636003768803214440.jpg'; 

// 创建图片的实例 
$dst = imagecreatefromstring(file_get_contents($dst_path)); 
$src = imagecreatefromstring(file_get_contents($src_path)); 

// 获取水印图片的宽高 
list($src_w, $src_h) = getimagesize($src_path); 

// 将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果 
imagecopymerge($dst, $src, 10, 10, 0, 0, $src_w, $src_h, 50); 
 
// 如果水印图片本身带透明色，则使用imagecopy方法 
// imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h); 
 
// 输出图片 
list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path); 
switch ($dst_type) { 
    case 1:   // GIF 
        header('Content-Type: image/gif'); 
        imagegif($dst); 
        break; 
    case 2:   // JPG 
        header('Content-Type: image/jpeg'); 
        imagejpeg($dst); 
        break; 
    case 3:   // PNG 
        header('Content-Type: image/png'); 
        imagepng($dst); 
        break; 
    default: 
        break; 
} 
imagedestroy($dst); 
imagedestroy($src);  
```

### 其他有关图像处理的函数
#### getimagesize
* 语法：`array getimagesize($file)`

* 参数：file 参数表示文件的路径，可以是本地文件，也可以是一个 URL 地址。当是一个远程文件的时候，会有一定的延时，因为系统需要先下载这个文件。

* 效果：返回一个包含图像的大小及图像类型等信息的数组。

* 示例：获取一个远程图片的信息

```php
$size = getimagesize("http://image18-c.poco.cn/mypoco/myphoto/20160901/20/17857099520160901203311082.jpg?750x956_120"); 
 
print_r($size); 
/*
Array 
( 
    [0] => 750   # width
    [1] => 956   # height
    [2] => 2     # type
    [3] => 'width="750" height="956"'
    [bits] => 8 
    [channels] => 3 
    [mime] => image/jpeg 
)
*/
```

#### imagecopy
* 语法：`bool imagecopy ( resource $dst_im, resource $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h )`

* 参数：`dst_im`和`src_im`表示两个打开的图片文件资源。

* 效果：将`src_im`图像中坐标从`(src_x, src_y)`开始，宽度为`src_w`，高度为`src_h`的一部分拷贝到`dst_im`图像中坐标为`(dst_x, dst_y)`的位置上。

* 示例：
 
#### imagecopymerge
* 语法：`bool imagecopymerge ( resource $dst_im, resource $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h, int $pct )`

* 参数：`dst_im`和`src_im`表示两个打开的图片文件资源。

* 效果：和`imagecopy()`方法的作用基本相同。


转载：[PHP给图片添加文字或图片水印的实现代码](http://developer.51cto.com/art/201609/516905.htm)

