> 转摘：[Go设计模式--备忘录模式，带暂存的业务功能可以参考它来实现](https://mp.weixin.qq.com/s/RikZAeI2Pic4vYwVNh4HnA)

### 1. 介绍

备忘录模式（Memento Pattern）又叫做快照模式（Snapshot Pattern），或令牌模式（Token Pattern），指在不破坏封装的前提下，捕获一个对象的内部状态，并在对象之外保存这个状态。之后就可以在需要的时候将该对象恢复为保存的状态。

备忘录模式属于行为型设计模式，用来保存对象在某些关键节点的必要信息，主要适用于以下场景：

* 需要保存历史快照的场景；
* 希望在对象之外保存状态，且除了自己，其他类对象无法访问状态保存的具体内容。

备忘录模式通常用来帮助设计撤销/恢复操作。

### 2. 组成

备忘录模式的核心是对原对象信息的保存备份，并在需要时候进行恢复，其结构类图如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/3a6fb8b326fca8221f6c42d1377d0fd1.jpg)

备忘录模式中主要有三个角色：

* Originator 发起者：是当前的基础对象，它会将自己的状态保存进备忘录，此角色可以类别博客系统中的文章对象；
    * 发起者中要有保存方法和从备忘录中恢复状态的方法，保存方法会返回当时状态组成的备忘录对象。
* Memento 备忘录：存储着 Originator 的状态的对象，可以累加为文章对象的不同版本；
* Caretaker 管理人：保存着多条备忘录的对象，维护备忘录的索引，在需要的时候用返回对应的备忘录，可以理解为博客系统中的编辑器对象。
    * 管理者的保存和恢复操作，会代理给其持有的发起者对象的保存和恢复操作，在这些代理方法中会增加对备忘录对象列表、当前备忘录版本的维护；
    * Caretaker 可以作为整个功能的汇集点，通过 Caretaker 的操作即可完成对 Originator 的处理，可以认为它俩组成了一个代理系统。

上面的类图结构是实现备忘录模式最简单的方式，真实使用的时候可以根据需要将 Caretaker、Originator、Memento 这些角色继续抽象出对应的接口和实现。

### 3. 示例

在线上博客平台中，需要为用户提供在线编辑文章的功能。为避免异常情况导致文章内容丢失，需要提供版本暂存和 Undo、Redo 功能。

版本暂存问题可以应用备忘录模式，将编辑器的状态完整保存起来（主要就是编辑内容）。而 Undo、Redo 的本质就是在历史版本中的前后移动，恢复对应版本的文章内容。

这个例子比较简单，可以直接将备忘录模式中的 Caretaker 和 Originator 作为一个 Editor 对象提供，然后为 Memento 单独定义一个对象。如果需要完整的备忘录模式实现，就需要拆分 Editor 对象为 Editor 和 Article 对象，而 Article 对象中保存文章的标题和内容，并实现保存和恢复功能。

首先定义 IEditor 编辑器接口：

```go
type IEditor interface {
  Title(title string)
  Content(content string)
  Save()
  Undo() error
  Redo() error
  Show()
}
```

然后定义编辑器的备忘录，也就是编辑器的内部状态数据模型，对应着一个历史版本：

```go
type Memento struct {
  title      string
  content    string
  createTime int64
}

func newMemento(title, content string) *Memento {
  return &Memento{ title, content, time.Now().Unix() }
}
```

最后是 Editor 的实现，它需要实现 IEditor 定义的所有行为，其中的 Undo、Redo 方法需要依赖内部记录的一组 Memento 对象：

```go
type Editor struct {
  title    string
  content  string
  versions []*Memento
  index    int
}

func NewEditor() IEditor {
  return &Editor{"", "", make([]*Memento, 0), 0}
}

func (editor *Editor) Title(title string) {
  editor.title = content
}

func (editor *Editor) Content(content string) {
  editor.content = content
}

func (editor *Editor) Save() {
  it := newMemento(editor.title, editor.content)
  editor.versions = append(editor.versions, it)
  editor.index = len(editor.versions) - 1
}

func (editor *Editor) Undo() error {
  return editor.load(editor.index - 1)
}

fuc (editor *Editor) Redo() error {
  return editor.load(editor.index + 1)
}

func (editor *Editor) load(i int) error {
  size := len(editor.versions)
  if size <= 0 {
    return errors.New("no history versions")
  }
  
  if i < 0 || i >= size {
    return errors.New("no more history versions")
  }
  
  it := editor.versions[i]
  editor.title = it.title
  editor.index = i
  return nil
}

func (editor *Editor) Show() {
  fmt.Printf("MockEditor.Show, title=%s, content=%s\n", editor.title, editor.content)
}
```

> 这里的 Undo、Redo 操作和 Save 操作直接没有考虑覆盖和清除的情况，而只是简单的追加。

有了这些实现，就可以执行文章的保存、恢复等操作了：

```go
func main() {
  editor := NewEditor()
  
  editor.Title("唐诗")
  editor.Content("白日依山尽")
  editor.Save()
  
  editor.Title("唐诗 登鹳雀楼")
  editor.Content("白日依山尽，黄河入海流。")
  editor.Save()
  
  editor.Title("唐诗 登鹳雀楼 王之涣")
  editor.Content("白日依山尽，黄河入海流。欲穷千里目，更上一层楼。")
  editor.Save()
  
  fmt.Println("-------------Editor 当前内容-----------")
  editor.Show()
  
  fmt.Println("-------------Editor 回退内容-----------")
  for {
    e := editor.Undo()
    if e != nil {
      break
    } else {
      editor.Show()
    }
  }
  
  fmt.Println("-------------Editor 前进内容-----------")
  for {
    e := editor.Redo()
    if e != nil {
      break
    } else {
      editor.Show()
    }
  }
}
```

运行显示内容如下：

```text
-------------Editor 当前内容-----------
MockEditor.Show, title=唐诗 登鹳雀楼 王之涣, content=白日依山尽，黄河入海流。欲穷千里目，更上一层楼。
-------------Editor 回退内容-----------
MockEditor.Show, title=唐诗 登鹳雀楼, content=白日依山尽，黄河入海流。
MockEditor.Show, title=唐诗, content=白日依山尽
-------------Editor 前进内容-----------
MockEditor.Show, title=唐诗 登鹳雀楼, content=白日依山尽，黄河入海流。
MockEditor.Show, title=唐诗 登鹳雀楼 王之涣, content=白日依山尽，黄河入海流。欲穷千里目，更上一层楼。
```

### 4. 总结

备忘录模式比较简单，其核心的功能就是对一个对象主要信息的保存和恢复，这需要被保存的对象提供备份和恢复接口，而且有地方能存储备份出来的信息。