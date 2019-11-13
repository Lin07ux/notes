> 转摘：[PostgreSQL Partial Index](https://blog.huoding.com/2016/04/28/510)

在 PostgreSQL 中，Partial Index 的含义是指：通过查询条件索引选定的行，而不是全部行。

> MySQL 中也有这个概念，但是其更接近前缀索引的含义。比如想索引一个`VARCHAR(255)`的字段，根据数据分布情况，可以仅索引前面若干个字符，如此通过降低索引体积来达到提升性能的目的。

### 1. 示例一

有一个 users 表，包含一个 name 字段，缺省值为`anonymous`。最简单的 Partial Index 索引就是对值不为`anonymous`的 name 建立部分索引：

```sql
CREATE UNIQUE INDEX ON users (name) WHERE name <> 'anonymous';
```

### 2. 示例二

有一个 questions 表，包含一个 answer_count 字段表示答题数量，一个 created_at 字段表示创建时间。

现在需要查询不同时间范围里答案数量大于 10 个的问题，SQL 语句如下

```sql
SELECT * FROM questions WHERE answer_count > 10 AND created_at > 1455555555 LIMIT 100;
```

这个 SQL 语句涉及到两个区间查询(range)，而一个索引里只能用到一个区间查询字段，所以复合索引在这里不适合。此时使用 PostgreSQL 中的 Partial Index 就很合适了：对于答案数量多于 10 个的数据的 created_at 列建立索引即可。

先看看没有使用 Partial Index 时查询的效果：

```sql
CREATE INDEX created_at ON questions (created_at);
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1573633158565.png)

再看看使用 Partial Index 时的查询效果：

```sql
CREATE INDEX partial_index ON questions (created_at) WHERE answer_count > 10;
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1573633227638.png)

对比前后两次的结果可以发现，没有使用 Partial Index 的时候，只能先通过索引拿到结果，然后再通过 Filter 使用另一个条件(`answer_count > 10`)进行过滤；而使用 Partial Index 之后，可以直接使用这个 Partial Index 拿到结果，无需二次过滤，也使得查询效率有百倍的提升。

当然了，只有那些相对固定的条件适合用在 Partial Index 中，比如本例中答案数量大于 10 的条件是作为热门问题的判断依据存证的，是明确的业务逻辑，如此则适合使用这个条件创建 Partial Index；而另一个创建时间的条件则是频繁变化的，并不适合使用它作为条件创建 Partial Index。

