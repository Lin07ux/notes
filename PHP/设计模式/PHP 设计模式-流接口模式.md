## 模式定义
在软件工程中，流接口（Fluent Interface）是指实现一种面向对象的、能提高代码可读性的 API 的方法，其目的就是可以编写具有自然语言一样可读性的代码，我们对这种代码编写方式还有一个通俗的称呼 —— 方法链。

Laravel 中流接口模式有着广泛使用，比如查询构建器，邮件等等。


## UML 类图
![流接口模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467734799559.png)


## 示例代码

**Sql.php**

```php
namespace DesignPatterns\Structural\FluentInterface;

/**
 * SQL 类
 */
class Sql
{
    /**
     * @var array
     */
    protected $fields = array();
    
    /**
     * @var array
     */
    protected $from = array();
    
    /**
     * @var array
     */
    protected $where = array();
    
    /**
     * 添加 fields 字段
     *
     * @param array $fields
     *
     * @return SQL
     */
    public function field(array $fields = array())
    {
        $this->fields = array_merge($this->fields, $fields);
        
        return $this;
    }
    
    /**
     * 添加 FROM 子句
     *
     * @param string $table
     * @param string $alias
     *
     * @return SQL
     */
    public function from($table, $alias)
    {
        $this->form[] = $table . ' AS '. $alias;
        
        return $this;
    }
    
    /**
     * 添加 WHERE 条件
     *
     * @param string $condition
     *
     * @return SQL
     */
    public function where($condition)
    {
        $this->where[] = $condition;
        
        return $this;
    }
    
    /**
     * 生成查询语句
     *
     * @return string
     */
    public function getQuery()
    {
        return 'SELECT ' . implode(', ', $this->fields)
            . ' FROM ' . implode(', ', $this->from)
            . ' WHERE ' . implode(' AND ', $this->where);
    }
}
```


## 总结
流接口模式是为了能够串联使用类中的各个方法的一种编程模式。关键就在于：在每个能串联使用的方法中，都返回实例本身！

如果了解 Jquery 库，那对这种模式就不会陌生。


## 参考
[PHP 设计模式系列 —— 流接口模式（Fluent Interface）](http://laravelacademy.org/post/2828.html)

