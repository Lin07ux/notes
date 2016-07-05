## 模式定义
组合模式（Composite Pattern）有时候又叫做部分-整体模式，用于将对象组合成树形结构以表示“部分-整体”的层次关系。组合模式使得用户对单个对象和组合对象的使用具有一致性。

常见使用场景：如树形菜单、文件夹菜单、部门组织架构图等。


## UML 类图
![组合模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467642105352.png)

可以看出，组合对象和单个对象都继承自同一个类，所以他们会有相同的接口，从而使他们能够通过相同的方式进行调用。

## 示例代码

**FormElement.php**

```php
namespace DesignPatterns\Structural\Composite;

/**
 * Form 基础类
 */
abstract class Form
{
    /**
     * renders the elements' code
     *
     * @param int $indent
     *
     * @return mixed
     */
    abstract public function render($indent = 0);
}
```

**FormElement.php**

```php
namespace DesignPatterns\Structural\Composite;

/**
 * 组合节点必须实现组件接口，这对构建组件树而言是强制的
 */
class FormElement extends Form
{
    /**
     * @var array|FormElement[]
     */
    protected $elements;
    
    /**
     * 遍历所有元素并调用它们的render()方法, 然后返回返回完整的表单显示
     *
     * 但是从外部来看, 并没有看见组合过程, 就像是单个表单实例一样
     *
     * @param int $indent
     *
     * @return string
     */
    public function render($indent = 0)
    {
        $formCode = '';
        
        foreach ($this->elements as $element) {
            $formCode .= $element->render($indent + 1) . PHP_EOL;
        }
        
        return $formCode;
    }
    
    /**
     * @param FormElement $element
     */
    public function addElement($element)
    {
        $this->elements[] = $element;
    }
}
```

**InputElement.php**

```php
namespace DesignPatterns\Structural\Composite;

/**
 * InputElement类
 */
class InputElement extends Form
{
    /**
     * 渲染input元素HTML
     *
     * @param int $indent
     *
     * @return mixed|string
     */
    public function render($indent = 0)
    {
        return str_repeat(' ', $indent) . '<input type="text" />';
    }
}
```

**TextElement.php**

```php
namespace DesignPatterns\Structural\Composite;


class TextElement extends Form
{
    /**
     * 渲染文本元素
     *
     * @param int $indent
     *
     * @return mixed|string
     */
    public function render($intent = 0)
    {
        return str_repeat(' ', $indent) . 'This is a text element';
    }
}
```


## 总结
简单来说，组合模式就是：由单个元素组成的复合元素，也具有和单个元素相同的接口，从而使得能够使用相同的方式操作单个元素和任意的复合元素。


## 参考
[PHP 设计模式系列 —— 组合模式（Composite）](http://laravelacademy.org/post/2699.html)


