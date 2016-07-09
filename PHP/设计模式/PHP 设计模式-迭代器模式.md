## 模式定义
迭代器模式（Iterator），又叫做游标（Cursor）模式。提供一种方法访问一个容器（Container）对象中各个元素，而又不需暴露该对象的内部细节。

当你需要访问一个聚合对象，而且不管这些对象是什么，都需要遍历的时候，就应该考虑使用迭代器模式。另外，当需要对聚集有多种方式遍历时，可以考虑去使用迭代器模式。迭代器模式为遍历不同的聚集结构提供如开始、下一个、是否结束、当前哪一项等统一的接口。

PHP 标准库（SPL）中提供了迭代器接口 Iterator，要实现迭代器模式，实现该接口即可。


## UML 类图
![迭代器模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467899552621.png)


## 示例代码

**Book.php**

```php
namespace DesignPatterns\Behavioral\Iterator;

class Book
{
    protected $author;
    
    protected $title;
    
    public function __construct($author, $title)
    {
        $this->author = $author;
        $this->title  = $title;
    }
    
    public function getAuthor()
    {
        return $this->author;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getAuthorAndTitle()
    {
        return $this->getTitle() . ' by ' . $this->getAuthor();
    }
}
```

**BookList.php**

```php
namespace DesignPatterns\Behavioral\Iterator;

class BookList implements \Countable
{
    protected $books;
    
    public function addBook(Book $book)
    {
        $this->books[] = $book;
    }
    
    public function getBook($index)
    {
        if (isset($this->books[$index])
        {
            return $this->books[$index];
        }
        
        return null;
    }
    
    public function removeBook(Book $bookToRemove)
    {
        foreach ($this->books as $key => $book) {
            if ($book->getAuthorAndTitle() === $bookToRemove->getAuthorAndTitle()) {
                unset($this->books[$key];
            }
        }
    }
    
    public function count()
    {
        return count($this->books);
    }
}
```

**BookListIterator.php**

```php
namespace DesignPatterns\Behavioral\Iterator;

/**
 * 书清单遍历器
 * 使用的是 PHP 自带的 Iterator 接口
 */
class BookListIterator implements \Iterator
{
    /**
     * @var BookList
     */
    protected $bookList;
    
    /**
     * @var int
     */
    protected $currentBook = 0;
    
    public function __construct(BookList $bookList)
    {
        $this->bookList = $bookList;
    }
    
    /**
     * Return the current book
     * @link http://php.net/manual/en/iterator.current.php
     * @return Book Can return any type.
     */
    public function current()
    {
        $this->bookList->getBook($this->currentBook);
    }
    
    /**
     * (PHP 5 >= 5.0.0)
     *
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->currentBook++;
        
        return $this->current();
    }
    
    /**
     * (PHP 5 >= 5.0.0)
     *
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->currentBook;
    }
    
    /**
     * (PHP 5 >= 5.0.0)
     *
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted 
     *      to boolean and then evaluated.
     *      Returns true on success or false on failure.
     */
    public function valid()
    {
        return null !== $this->current();
    }
    
    /**
     * (PHP 5 >= 5.0.0)
     *
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->currentBook = 0;
    }
}
```

**BookListReverseIterator.php**

```php
namespace DesignPatterns\Behavioral\Iterator;

class BookListReverseIterator extends BookListIterator
{
    public function __construct(BookList $bookList)
    {
        $this->bookList = $bookList;
        $this->currentBook = $bookList->count() - 1;
    }
    
    public function next()
    {
        $this->currentBook--;
        
        return $this->current();
    }
    
    public function rewind()
    {
        $this->currentBook = $this->bookList->count() - 1;
    }
}
```


## 总结
迭代器模式就是为了遍历对象中的每一个元素而存在。为了能够遍历对象中的元素，需要两方面：对象自身提供一个获取自身元素的方法；迭代器根据一定的规则，调用对象的方法，提供一系列的接口供外界调用。

迭代器模式和 PHP 中的数组的行为模式很像。


## 参考
[PHP 设计模式系列 —— 迭代器模式（Iterator）](http://laravelacademy.org/post/2882.html)

