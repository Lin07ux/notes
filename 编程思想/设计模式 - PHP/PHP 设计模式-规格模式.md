## 模式定义
规格模式（Specification）可以认为是组合模式的一种扩展。有时项目中某些条件决定了业务逻辑，这些条件就可以抽离出来以某种关系（与、或、非）进行组合，从而灵活地对业务逻辑进行定制。另外，在查询、过滤等应用场合中，通过预定义多个条件，然后使用这些条件的组合来处理查询或过滤，而不是使用逻辑判断语句来处理，可以简化整个实现逻辑。

这里的每个条件就是一个规格，多个规格/条件通过串联的方式以某种逻辑关系形成一个组合式的规格。


## UML 类图
![规格模式](http://cnd.qiniu.lin07ux.cn/markdown/1468058717037.png)


## 示例代码

**Item.php**

```php
namespace DesignPatterns\Behavioral\Specification;

class Item
{
    protected $price;
    
    /**
     * An item must have a price
     *
     * @param int $price
     */
    public function __construct($price)
    {
        $this->price = $price;
    }
    
    /**
     * Get the items price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }
}
```

**SpecificationInterface.php**

```php
namespace DesignPatterns\Behavioral\Specification;

/**
 * 规格接口
 */
interface SpecificationInterface
{
    /**
     * 判断对象是否满足规格
     *
     * @param Item $item
     *
     * @return bool
     */
    public function isSatisfiedBy(Item $item);
    
    /**
     * 创建一个逻辑与规格（AND）
     *
     * @param SpecificationInterface $spec
     */
    public function plus(SpecificationInterface $spec);
    
    /**
     * 创建一个逻辑或规格（OR）
     *
     * @param SpecificationInterface $spec
     */
    public function either(SpecificationInterface $spec);
    
    /**
     * 创建一个逻辑非规格（NOT）
     */
    public function not();
}
```

**AbstractSpecification.php**

```php
namespace DesignPatterns\Behavioral\Specification;

abstract class AbstractSpecification implements SpecificationInterface
{
    /**
     * 检查给定Item是否满足所有规则
     *
     * @param Item $item
     *
     * @return bool
     */
    abstract public function isSatisfiedBy(Item $item);
    
    /**
     * 创建一个新的逻辑与规格（AND）
     *
     * @param SpecificationInterface $spec
     *
     * @return SpecificationInterface
     */
    public function plus(Specification $spec)
    {
        return new Plus($this, $spec);
    }
    
    /**
     * 创建一个新的逻辑或组合规格（OR）
     *
     * @param SpecificationInterface $spec
     *
     * @return SpecificationInterface
     */
    public function either(Specification $spec)
    {
        return new Either($this, $spec);
    }
    
    /**
     * 创建一个新的逻辑非规格（NOT）
     *
     * @return SpecificationInterface
     */
    public function not()
    {
        return new Not($this);
    }
}
```

**Plus.php**

```php
namespace DesignPatterns\Behavioral\Specification;

/**
 * 逻辑与规格（AND）
 */
class Plus extends AbstractSpecification
{
    protected $left;
    protected $right;
    
    /**
     * 在构造函数中传入两种规格
     *
     * @param SpecificationInterface $left
     * @param SpecificationInterface $right
     */
    public function __construct(SpecificationInterface $left, SpecificationInterface $right)
    {
        $this->left  = $left;
        $this->right = $right;
    }
    
    /**
     * 返回两种规格的逻辑与评估
     *
     * @param Item $item
     *
     * @return bool
     */
    public function isSatisfiedBy(Item $item)
    {
        return $this->left->isSatisfiedBy($item) && $this->right->isSatisfiedBy($item);
    }
}
```

**Either.php**

```php
namespace DesignPatterns\Behavioral\Specification;

/**
 * 逻辑或规格
 */
class Either extends AbstractSpecification
{
    protected $left;
    protected $right;
    
    /**
     * 两种规格的组合
     *
     * @param SpecificationInterface $left
     * @param SpecificationInterface $right
     */
    public function __construct(SpecificationInterface $left, SpecificationInterface $right)
    {
        $this->left  = $left;
        $this->right = $right;
    }
    
    /**
     * 返回两种规格的逻辑或评估
     *
     * @param Item $item
     *
     * @return bool
     */
    public function isSatisfiedBy(Item $item)
    {
        return $this->left->isSatisfiedBy($item) || $this->right->isSatisfiedBy($item);
    }
}
```

**Not.php**

```php
namespace DesignPatterns\Behavioral\Specification;

class Not extends AbstractSpecification
{
    protected $spec;
    
    public function __construct(Specification $spec)
    {
        $this->spec = $spec;
    }
    
    public function isSatisfiedBy(Item $item)
    {
        return !$this->spec->isSatisfiedBy($item);
    }
}
```

**PriceSpecification.php**

```php
namespace DesignPatterns\Behavioral\Specification;

/**
 * 判断给定 Item 的价格是否介于最小值和最大值之间的规格
 */
class PriceSpecification extends AbstractSpecification
{
    protected $maxPrice;
    protected $minPrice;
    
    /**
     * 设置最大值
     *
     * @param int $maxPrice
     */
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;
    }
    
    /**
     * 设置最小值
     *
     * @param int $minPrice
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }
    
    /**
     * 判断给定Item的定价是否在最小值和最大值之间
     *
     * @param Item $item
     *
     * @return bool
     */
    public function isSatisfiedBy(Item $item)
    {
        if (!empty($this->maxPrice) && $item->getPrice() > $this->maxPrice) {
            return false;
        }
        
        if (!empty($this->minPrice) && $item->getPrice() < $this->minPrice) {
            return false;
        }
        
        return true;
    }
}
```

**Test.php**

```php
use DesignPatterns\Behavioral\Specification\PriceSpecification;
use DesignPatterns\Behavioral\Specification\Item;

$item = new Item(100);
$spec = new PriceSpecification();
$spec->isSatisfiedBy($item);   // true

$spec->setMaxPrice(50);
$spec->isSatisfiedBy($item);   // false

$spec->setMaxPrice(150);
$spec->isSatisfiedBy($item);   // true

$spec->setMinPrice(120);
$spec->isSatisfiedBy($item);   // false;

$spec->setMinPrice(80);
$spec->isSatisfiedBy($item);   // true

// 测试 not
$item = new Item(100);
$spec = new PriceSpecification();
$not = $spec->not();
$not->isSatisfiedBy($item);    // false

$spec->setMaxPrice(50);
$not->isSatisfiedBy($item)；    // true

$spec->setMaxPrice(150);
$not->isSatisfiedBy($item);     // false

$spec->setMinPrice(101);
$not->isSatisfiedBy($item);     // true

$spec->setMinPrice(100);
$not->isSatisfiedBy($item);     // false

// 其他的测试也都类似
```


## 总结
规格模式主要是将一些判断逻辑抽象成一个个的方法，然后通过调用这些方法就能判断出条件发符合与否。


## 参考
[PHP 设计模式系列 —— 规格模式（Specification）](http://laravelacademy.org/post/2960.html)

