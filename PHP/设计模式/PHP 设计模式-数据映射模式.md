## 模式定义
在了解数据映射模式之前，先了解下数据映射，它是在持久化数据存储层（通常是关系型数据库）和驻于内存的数据表现层之间进行双向数据传输的数据访问层。

数据映射模式的目的是让持久化数据存储层、驻于内存的数据表现层、以及数据映射本身三者相互独立、互不依赖。这个数据访问层由一个或多个映射器（或者数据访问对象）组成，用于实现数据传输。通用的数据访问层可以处理不同的实体类型，而专用的则处理一个或几个。

数据映射模式的核心在于它的数据模型遵循单一职责原则（Single Responsibility Principle）, 这也是和 Active Record 模式的不同之处。最典型的数据映射模式例子就是数据库 ORM 模型 （Object Relational Mapper）。

> 注：更多关于 Data Mapper 与 Active Record 的区别与联系请查看这篇文章了解更多 —— [](http://laravelacademy.org/post/966.html)

准确来说该模式是个架构模式。


## UML 类图
![数据映射模式](http://cnd.qiniu.lin07ux.cn/markdown/1467643226708.png)


## 示例代码

**User.php**

```php
namespace DesignPatterns\Structural\DataMapper;

/**
 *
 * 这是数据库记录在内存的表现层
 *
 * 验证也在该对象中进行
 *
 */
class User
{
    /**
     * @var int
     */
    protected $userID;
    
    /**
     * @var string
     */
    protected $username;
    
    /**
     * @var string
     */
    protected $email;
    
    /**
     * @param null $id
     * @param null $username
     * @param null $email
     */
    public function __construct($id = null, $username = null, $email = null)
    {
        $this->userID = $id;
        $this->username = $username;
        $this->email = $email;
    }
    
    /**
     * @return int
     */
    public function getUserID()
    {
        return $this->userID;
    }
    
    /**
     * @param int $userId
     */
    public function setUserID($id)
    {
        $this->userID = $id;
    }
    
    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}
```

**UserMapper.php**

```php
namespace DesignPatterns\Structural\DataMapper;

/**
 * UserMapper类（数据映射类）
 */
class UserMapper
{
    /**
     * @var DBAL
     */
    protected $adapter;
    
    /**
     * @param DBAL $dbLayer
     */
    public function __construct(DBAL $dbLayer)
    {
        $this->adapter = $dbLayer;
    }
    
    /**
     * 将用户对象保存到数据库
     *
     * @param User $user
     *
     * @return boolean
     */
    public function save(User $user)
    {
        /* $data的键名对应数据库表字段 */
        $data = array(
            'userid' => $user->getUserID(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        );
        
        /* 如果没有指定ID则在数据库中创建新纪录，否则更新已有记录 */
        if (null === $data['userid']) {
            unset($data['userid']);
            $this->adapter->insert($data);
            
            return true;
        } else {
            $this->adapter->update($data, array('userid = ?' => $data['userid']));
            
            return true;
        }
    }
    
    /**
     * 基于ID在数据库中查找用户并返回用户实例
     *
     * @param int $id
     *
     * @throws \InvalidArgumentException
     * @return User
     */
    public function findById($id)
    {
        $result = $this->adapter->find($id);
        
        if (0 == count($result)) {
            throw new \InvalidArgumentException("User #$id not found");
        }
        
        $row = $result->current();
        
        return $this->mapObject($row);
    }
    
    /**
     * 获取数据库所有记录并返回用户实例数组
     *
     * @return array
     */
    public function findAll()
    {
        $result = $this->adapter->findAll();
        $entries = array();
        
        foreach ($result as $row) {
            $entries[] = $this->mapObject($row);
        }
        
        return $entries;
    }
    
    /**
     * 映射表记录到对象
     *
     * @param array $row
     *
     * @return User
     */
    protected function mapObject(array $row)
    {
        $entry = new User();
        
        $entry->setUserID($row['userid']);
        $entry->setUsername($row['username']);
        $entry->setEmail($row['email']);
        
        return $entry;
    }
}
```


## 总结
从代码实现上可以看到，数据映射模式和适配器模式很像，都是完成从一种对象/接口转成另一种对象/接口的操作。不过数据映射模式主要是完成数据记录和对象之间的双向转变，以能方便的处理数据；而适配器模式则主要是完成从一种接口到另一种接口的单向转换。


## 参考
[PHP 设计模式系列 —— 数据映射模式（Data Mapper）](http://laravelacademy.org/post/2739.html)

