## 模式定义
Repository 是一个独立的层，介于领域层与数据映射层（数据访问层）之间。它的存在让领域层感觉不到数据访问层的存在，它提供一个类似集合的接口给领域层进行领域对象的访问。Repository 是仓库管理员，领域层需要什么东西只需告诉仓库管理员，由仓库管理员把东西拿给它，并不需要知道东西实际放在哪。

Repository 模式是架构模式，在设计架构时，才有参考价值。应用 Repository 模式所带来的好处，远高于实现这个模式所增加的代码。只要项目分层，都应当使用这个模式。


## UML 类图
![资源库模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468080648919.png)


## 示例代码

**Post.php**

```php
namespace DesignPatterns\More\Repository;

/**
 * Post 类
 * @package DesignPatterns\Repository
 */
class Post
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $author;

    /**
     * @var \DateTime
     */
    private $created;
    
    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
```

**PostRepository.php**

```php
namespace DesignPatterns\More\Repository;

/**
 * PostRepository 类
 * 
 * Post 对应的 Repository
 * 该类介于数据实体层(Post) 和访问对象层(Storage)之间
 *
 * Repository 封装了持久化对象到数据存储器以及在展示层显示面向对象的视图操作
 * Repository 还实现了领域层和数据映射层的分离和单向依赖
 *
 * @package DesignPatterns\Repository
 */
class PostRepository
{
    private $persistence;
    
    public function __construct(Storage $persistence)
    {
        $this->persistence = $persistence;
    }
    
    /**
     * 通过指定id返回Post对象
     *
     * @param int $id
     * @return Post|null
     */
    public function getById($id)
    {
        $arrayData = $this->persistence->retrieve($id);
        
        if (is_null($arrayData)) {
            return null;
        }
        
        $post = new Post();
        $post->setId($arrayData['id']);
        $post->setAuthor($arrayData['author']);
        $post->setCreated($arrayData['created']);
        $post->setText($arrayData['text']);
        $post->setTitle($arrayData['title']);
        
        return $post;
    }
    
    /**
     * 保存指定对象并返回
     *
     * @param Post $post
     * @return Post
     */
    public function save(Post $post)
    {
        $id = $this->persistence->persist(array(
            'author' => $post->getAuthor(),
            'created' => $post->getCreated(),
            'text' => $post->getText(),
            'title' => $post->getTitle()
        ));

        $post->setId($id);
        return $post;
    }
    
    /**
     * 删除指定的 Post 对象
     *
     * @param Post $post
     * @return bool
     */
    public function delete(Post $post)
    {
        return $this->persistence->delete($post->getId());
    }
}
```

**Storage.php**

```php
namespace DesignPatterns\More\Repository;

/**
 * Storage接口
 *
 * 该接口定义了访问数据存储器的方法
 * 具体的实现可以是多样化的，比如内存、关系型数据库、NoSQL数据库等等
 *
 * @package DesignPatterns\Repository
 */
interface Storage
{
    /**
     * 持久化数据方法
     * 返回新创建的对象ID
     *
     * @param array() $data
     * @return int
     */
    public function persist($data);
    
    /**
     * 通过指定id返回数据
     * 如果为空返回null
     *
     * @param int $id
     * @return array|null
     */
    public function retrieve($id);
    
    /**
     * 通过指定id删除数据
     * 如果数据不存在返回false，否则如果删除成功返回true
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
```

**MemoryStorage.php**

```php
namespace DesignPatterns\More\Repository;

/**
 * MemoryStorage 类
 * @package DesignPatterns\Repository
 */
class MemoryStorage implements Storage
{
    private $data;
    private $lastId;
    
    public function __construct()
    {
        $this->data   = array();
        $this->lastId = 0;
    }
    
    public function persist($data)
    {
        $this->data[++$this->lastId] = $data;
        return $this->lastId;
    }
    
    public function retrieve($id)
    {
        return isset($this->data[$id]) ? $this->data[$id] : null;
    }
    
    public function delete($id)
    {
        if (!isset($this->data[$id])) {
            return false;
        }
        
        $this->data[$id] = null;
        unset($this->data[$id]);
        
        return true;
    }
}
```


## 总结
资源库模式可以分离数据的实体层和存储层，还可以简化接口操作。要实现资源库模式，也就是需要在数据实体层和数据存储层之间加入新的一层，来专门做数据的转化操作。

实例可以查看[在 Laravel 5 中使用 Repository 模式实现业务逻辑和数据访问的分离](http://laravelacademy.org/post/3063.html)。


## 参考
[PHP 设计模式系列 —— 资源库模式（Repository）](http://laravelacademy.org/post/3053.html)


