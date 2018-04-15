PHP 下载远程图片简单思路就是通过`curl()`函数来请求图片的 URL，然后将请求成功后的结果写入到本地的文件中。

```PHP
class Spider {

    public function downloadImage($url, $path = 'images/')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        
        $this->saveAsImage($url, $file, $path);
    }
    
    private function saveAsImage($url, $file, $path)
    {
        $filename = pathinfo($url, PATHINFO_BASENAME);
        $resource = fopen($path . $filename, 'a');
        fwrite($resource, $file);
        fclose($resource);
    }
}
```

之后就可以这样调用代码来下载图片：

```PHP
$spider = new Spider();

foreach ( $images as $url ) {
    $spider->downloadImage($url);
}
```