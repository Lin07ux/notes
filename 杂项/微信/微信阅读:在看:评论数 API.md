
### 阅读数 API

微信文章可以通过 API 直接获取阅读数，API 所需参数均从微信文章的原始链接(长链接)中获取。

假如微信文章的原始链接为：

```
https://mp.weixin.qq.com/s?__biz=MzAxMzAyMTQ1NA==&mid=2652612129&idx=2&sn=775eb1f5cf02d8924a20cf21b8738e85&chksm=8047fe40b73077562067987b94f252ee8fcd617f7f4abe24cf8494fc1a8f8690dab78679aac0&key=c86a338f58bd007c6ccca9a3291d367177afd4d578d09a0c8b60f7f2d4de8a5bee208234d92d82bd75c7976959d44352e6d0290ecd0831d7b575efc00c9845efca9a5f4476261c86a6240e4fe6d4f5a2&uin=MjQzMzc1ODI2MA%3D%3D&pass_ticket=91hWz2JAuBQwpv1YIB6X6eLyHQT45zhZGnj2SqaYLEkSCDUEQYCzV2luIrI2vQK9
```

从这个链接中可以获取到多个查询参数，假设该链接的全部查询参数在 ArticleQuery 对象中，则可以通过如下方式获取文章阅读、在看、评论数：

* URL: `https://mp.weixin.qq.com/mp/getappmsgext`
* Method: `POST`
* Headers:
    - `User-Agent` String 需包含 Wechat 相关字段，如`Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) MicroMessenger/2.3.26(0x12031a10) MacWechat Chrome/39.0.2171.95 Safari/537.36 NetType/WIFI WindowsWechat`
* Params:
    - `uin` String 为 `ArticleQuery.uin`
    - `key` String 为 `ArticleQuery.key`
* Body:
    - `__biz` String 为 `ArticleQuery.__biz`
    - `mid` String 为 `ArticleQuery.mid`
    - `sn` String 为 `ArticleQuery.sn`
    - `idx` String 为 `ArticleQuery.idx`
    - `comment_id` String 用户评论用的 ID，在一段时间内固定不变，可以通过访问文章链接后返回的 HTML 代码中提取。从获取。
    - `appmsg_type` Integer 固定为 9
    - `is_only_read` Integer 固定为 1

比如：

```shell
curl https://mp.weixin.qq.com/mp/getappmsgext?uin=MjQzMzc1ODI2MA%253D%253D&key=c86a338f58bd007cbce87eb223df5ee414eebba295abf7534176e8528ea2ef7c504141ebf72b1704f8f3bfa73b45ac33654dc80f80e526a4137e46ed06687a1737113fcdb0f8c03a1a2245bd41f11e1e \
 -d "__biz=MTgwNTE3Mjg2MA%3D%3D&appmsg_type=9&mid=2652561040&sn=f9e90c3acac374ee7a475e135c2045bc&idx=1&is_only_read=1&comment_id=993117672390983680" \
 -H "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) MicroMessenger/2.3.26(0x12031a10) MacWechat Chrome/39.0.2171.95 Safari/537.36 NetType/WIFI WindowsWechat"
```

可获得类似如下数据：

```json
{
    "advertisement_info": [],
    "appmsgstat": {
        "show": true,
        "is_login": true,
        "liked": false,
        "read_num": 100001,
        "like_num": 876,
        "ret": 0,
        "real_read_num": 0,
        "version": 1,
        "prompted": 1,
        "like_disabled": false,
        "style": 1,
        "video_pv": 0,
        "video_uv": 0
    },
    "comment_enabled": 1,
    "reward_head_imgs": [],
    "only_fans_can_comment": true,
    "comment_count": 684,
    "is_fans": 1,
    "nick_name": "林云溪",
    "logo_url": "http://wx.qlogo.cn/mmopen/vtq15BclWI1svHGdtiaDNVLDasAAKQN7kzIzdWPgmhrwjO0TTLbibKNM3VRMhkCYCy2MHbcyvr0ib4gIX9lH84JQDneFsp95QEF/132",
    "friend_comment_enabled": 1,
    "base_resp": {
        "wxtoken": 777
    },
    "more_read_list": [],
    "friend_subscribe_count": 0,
    "related_tag_article": [],
    "original_article_count": 0,
    "video_share_page_tag": [],
    "related_tag_video": []
}
```

其中：

* `appmsgstat.read_num` 表示阅读数，最大为 10001，表示 10 万+
* `appmsgstat.like_num` 在看数
* `comment_count` 评论数


