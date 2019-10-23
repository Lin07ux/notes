比如，用户可以收藏文章，如何按照用户的收藏时间进行排序？

在`User`模型中，可以如下构建喜欢的文章的关联：

```php
public function favorites()
{
    return $this->belongsToMany(Article::class, 'favorites')
                         ->orderBy('favorites.created_at', 'desc')
                         ->withTimestamps();
}
```

这里的`orderBy`语句就是设置按照中间表的`created_at`字段进行倒序排序。

