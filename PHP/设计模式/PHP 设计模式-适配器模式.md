## 模式定义
适配器模式英文名称为 Adapter Pattern，又叫做 Wrapper Pattern。

适配器的存在，就是为了将已存在的东西（接口）转换成适合我们需要、能被我们所利用的东西。在现实生活中，适配器更多的是作为一个中间层来实现这种转换作用。比如电源适配器，它是用于电流变换（整流）的设备。

适配器模式将一个类的接口转换成客户希望的另外一个接口，使得原本由于接口不兼容而不能一起工作的那些类可以在一起工作。

也就是说，适配器模式实际的工作是将已有的接口进行包装，使其可以按照另一种接口的方式进行使用。简单说就是重新封装一遍，但实际的行为不被改变。


## UML 类图 
![适配器模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467608821683.png)

这里有两类接口：纸质书接口(PaperBookInterface)和电子书接口(EBookInterface)。为了能像阅读纸质书一样阅读电子书，可以将电子书的接口重新包装一遍，使其符合纸质书的接口规则。于是就出现了电子书的适配器接口(EBookAdapter)。


## 示例代码

**PaperBookInterface.php**

```php
namespace DesignPatterns\Structural\Adapter;

/**
 * PaperBookInterface 是纸质书接口
 */
interface PaperBookInterface
{
    /**
     * 翻页方法
     *
     * @return mixed
     */
    public function turnPage();
    
    /**
     * 打开书的方法
     *
     * @return mixed
     */
    public function open();
}
```

**Book.php**

```php
namespace DesignPatterns\Structural\Adapter;

/**
 * Book 是纸质书实现类
 */
class Book implements PaperBookInterface
{
    public function turnPage()
    {
    }
    
    public function open()
    {
    }
}
```

**EBookInterface.php**

```php
namespace DesignPatterns\Structural\Adapter;

interface EBookInterface
{
    /**
     * 电子书翻页
     *
     * @return mixed
     */
    public function pressNext();
    
    /**
     * 打开电子书
     *
     * @return mixed
     */
    public function pressStart();
}
```

**Kindle.php**

```php
namespace DesignPatterns\Structural\Adapter;

class Kindle implements EBookInterface
{
    public function pressNext()
    {
    }
    
    public function pressStart()
    {
    }
}
```

**EBookAdapter.php**

```php
namspace DesignPatterns\Structural\Adapter;

/**
 * EBookAdapter 是电子书适配器类
 *
 * 该适配器实现了 PaperBookInterface 接口,
 * 但是你不必修改客户端使用纸质书的代码
 */
class EBookAdapter implements PaperBookInterface
{
    /**
     * @var EBookInterface
     */
    protected $eBook;
    
    /**
     * 注意该构造函数注入了电子书接口EBookInterface
     *
     * @param EBookInterface $ebook
     */
    public function __construct(EBookInterface $eBook)
    {
        $this->eBook = $eBook;
    }
    
    /**
     * 电子书将纸质书接口方法转换为电子书对应方法
     */
    public function turnPage()
    {
        $this->eBook->pressNext();
    }
    
    /**
     * 纸质书翻页转化为电子书翻页
     */
    public function open()
    {
        $this->eBook->pressStart();
    }
}
```


## 总结
适配器模式只是为了将一个类的接口转换成另一类接口，从而实现使用统一的接口操作不同的类。

适配器模式一般可以通过简单的重新封装，使不同类的接口对应起来即可实现。


## 参考
[PHP 设计模式系列 —— 适配器模式（Adapter / Wrapper）](http://laravelacademy.org/post/2660.html)

