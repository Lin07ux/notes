> 转摘：[Go设计模式--解释器模式](https://mp.weixin.qq.com/s/8v0UZWygCvkbye4Y0P-3sQ)

### 1. 简介

解释器模式是一种行为型设计模式，可以用来在程序里创建针对一个特定领域的解释器，处理解释领域语言中的语句。该模式定义了领域语言的抽象语法树，以及用来解释语法树的解释器。

解释器模式常用于解决需要解释语言中的句子或者表达式的问题，例如：

* 处理配置文件：许多应用程序使用配置文件来指定应用程序的行为方式，这些配置文件可以用 YAML、JSON、INI 等 DSL 编写，解释器可用于解析这些配置文件并应用编程语言对象的形式，向应用程序提供配置信息。
* 模板引擎：模板引擎处理模板和一组变量以产生输出。
* 数学表达式计算器：数学表达式在程序里可以使用解释器模式进行解析和解释，例如计算器应用程序。
* 自然语言处理：在更高级的情况下，解释器模式可用于解析和解释自然语言，不过这通常会涉及像机器学习这样的更复杂的技术。

虽然解释器模式可以用来解决上面这些问题，但有时候它并不是最好的解决方案。对于复杂的语言，使用特定的解析库或工具或其他设计模式可能更有效。

### 2. 组成

解释器模式中的关键组件有：

* 表达式接口：表示抽象语法树的元素，并定义解释表达式的方法；
* 具体表达式：实现表达式接口的结构，表示语言语法的各种规则或元素；
* 上下文对象：用于保存解释过程中所需要的任何必要信息或状态；
* Parser 或 Builder：负责根据输入表达式构建抽象语法树的组件。

下面是解释器模式构成的 UML 类图：

![](https://cnd.qiniu.lin07ux.cn/markdown/577824956ac6fb1623f4d1e4d5e5e8e9.jpg)

### 3. 示例

在 Go 语言中实现解释器模式的步骤为：

1. 定义标识抽象语法树中元素的表达式接口；
2. 创建实现 Expression 接口的具体表达式结构；
3. 定义一个上下文结构来保存解释过程中可能需要的任何必要数据或黄铜（可选）；
4. 创建解析器或构建器，以根据输入表达式构造抽象语法树；
5. 使用创建的抽象语法树和上下文解释表达式。

下面用解释器模式实现一个简单的加减算术运算器。对每种运算定义对应的 Expression 对象，并在其方法中实现具体的运算规则，避免所有的运算操作放到一个函数中。这体现了解释器模式的核心思想：将语法解析的工作拆分到各个小类中，以此来避免大而全的解析类。

首先，定义表达式接口：

```go
type Expression interface {
  Interpret() int
}
```

然后实现具体的 Expression 表达式对象，在加减法算术运算中需要实现操作数、加法、减法对应的实现类：

```go
// 操作数
type NumberExpression struct {
  val int
}
// 解释：返回其整数值
func (n *NumberExpression) Interpret() int {
  return n.val
}

// 加法
type AdditionExpression struct {
  left, right Expression
}
// 解释：进行加法操作
func (a *AdditionExpression) Interpret() int {
  return a.left.Interpret() + a.right.Interpret()
}

// 减法
type SubtractionExpression struct {
  left, right Expression
}
// 解释：进行减法操作
func (s *SubtractionExpression) Interpret() int {
  return s.left.Interpret() - s.right.Interpret()
}
```

最后，再创建一个表达式解析器，根据输入的表达式构造出抽象语法树，使用创建的抽象语法树和上下文解释表达式：

```go
type Parser struct {
  exp   []string
  index int
  prev  Expression
}

func (p *Parser) Parse(exp string) {
  p.exp = strings.Split(exp, " ")
  
  for {
    if p.index >= len(p.exp) {
      return
    }
    switch p.exp[p.index] {
    case "+":
      p.prev = p.newAdditionExpression()
    case "-":
      p.prev = p.newSubtractionExpression()
    default:
      p.prev = p.newNumberExpression()
    }
  }
}

func (p *Parser) newAdditionExpression() Expression {
  p.index++
  return &AdditionExpression{
    left:  p.prev,
    right: p.newNumberExpression(),
  }
}

func (p *Parser) newSubtractionExpression() Expression {
  p.index++
  return &SubtractionExpression{
    left:  p.prev,
    right: p.newNumberExpression(),
  }
}

func (p *Parser) newNumberExpression() Expression {
  v, _ := strconv.Atoi(p.exp[p.index])
  p.index++
  return &NumberExpression{
    val: v,
  }
}

// 返回解析结果，也就是最终的 Expression 引用
// 调用 Interpret 方法会从右向左递归计算出公式结果
func (p *Parser) Result() Expression {
  return p.prev
}
```

最后，使用 Parser 把客户端传递进来的加减算术表达式解析成抽象语法树，然后运行解释器计算出表达式的结果：

```go
func main() {
  p := &Parser{}
  p.Parse("1 + 3 + 3 + 3 - 3")
  res := p.Result().Interpret()
  expect := 7
  if res != expect {
    log.Fatalf("error: expect %d got %d", expect, res)
  }
  fmt.Printf("expect %d got %d", expect, res)
}
```

### 4. 总结

在程序中使用解释器模式的目标是：定义特定于领域的语言及其语法，使用 AST（抽象语法树）表示语言中的表达式或语句，让程序能够根据一组规则或操作来解释或评估表达式。

解释器模式的优点是：

* 关注点分离：该模式将解释逻辑与数据表示分开；
* 可扩展：可以通过添加新的表达式结构轻松地扩展模式；
* 可重用：可以在需要解析或解释特定领域语言的不同项目或上下文中重用。

解释器模式的缺点是：

* 复杂性：随着语法规则数量的增加，模式会变得复杂难以理解；
* 性能：对于大型表达式，抽象语法树的递归遍历可能很慢。