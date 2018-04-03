## private

父类中定义为 private 的属性，子类是无法直接访问的，但是如果父类中定义了非 private 权限的访问方法，那么子类就可以调用这个方法来获取到这个属性了。

```php

class A
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}

class B extends A
{
    public function plus($num)
    {
        return $this->getData() + $num;
    }
}

class C extends A
{
    public function sub($num)
    {
        return $this->data - $num;
    }
}

// 这里父类 A 中定义了一个 private 属性 $data
// 还定义了一个 public 的 getDate() 方法来访问这个属性
// 那么子类中虽然不能直接访问这个 $data 属性，
// 但是能够通过 getData() 来获取到其值

// B 子类中的 plus() 方法能正常执行
$b = new B(1);
echo $b->getData();  // 1
echo $b->plus(1);    // 2

// C 子类中的 sub() 方法由于是直接调用 $data 属性
// 所以会有错误提示信息出现
$c = new C(1);
echo $c->getData();  // 1
echo $c->sub(1);     // -1 PHP Notice:  Undefined property: C::$data
```

