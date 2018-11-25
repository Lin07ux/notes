微信多图片上传必须挨个上传，也就是不能并行，得串行。如果需要上传多个图片，可以写一个函数来进行处理。

**选择图片**

```JavaScript

//选择图片
$('#uploadImages img').on('click', function () {
    var $img = $(this);
    wx.chooseImage({
        // 指定可选图片数量，默认9
        count: 1,
        // 指定是原图还是压缩图，默认二者都有
        sizeType: ['original', 'compressed'],
        // 可以指定来源是相册还是相机，默认二者都有
        sourceType: ['album', 'camera'],
        success: function (res) {
            // 返回选定照片的本地ID列表
            // localId 可以作为 img 标签的 src 属性显示图片
            var localIds = res.localIds;

            $img.attr('src', localIds[0]).addClass('uploaded');
        },
        fail: function (res) {
            alert(JSON.stringify(res));
        }
    });
});
```

**上传函数**

```JavaScript
var serverIds = [];

function uploadImages (localImagesIds) {
    if (localImagesIds.length === 0) {
        $.showPreloader('正在提交数据...');
        $('form').submit();
        
    } else {
    
        var localId = localImagesIds[0];
        // 解决IOS无法上传的坑
        if (localId.indexOf("wxlocalresource") != -1) {
            localId = localId.replace("wxlocalresource", "wxLocalResource");
        }
    
        wx.uploadImage({
            // 需要上传的图片的本地ID，由chooseImage接口获得
            localId: localId,
            // 默认为1，显示进度提示
            isShowProgressTips: 1,
            success: function (res) {
                // 返回图片的服务器端ID
                serverIds.push(res.serverId);
                
                // 从本地ID中去除这个已上传的ID，然后继续上传下一个
                localImagesIds.shift();
                uploadImages(localImagesIds);
            },
            fail: function (res) {
                $.alert('上传失败，请重新上传！');
            }
        });
    }
}
```

**上传事件**

```JavaScript
$('#btnSubmit').on('click', function () {
    var $chooseImages = $('#uploadImages img.uploaded');
    
    if ($chooseImages.length === 0) {
        $.alert('请上传照片！');
        return;
    }
    
    $.showPreloader('正在上传照片...');
    
    var localImagesIds = [];
    $chooseImages.each(function () {
        localImagesIds.push($(this).attr('src'));
    });
    
    uploadImages(localImagesIds);
});
```

[微信JSSDK多图片上传并且解决IOS系统上传一直加载的问题](http://www.cnblogs.com/codelove/p/5247090.html)

