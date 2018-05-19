> 转摘：[用PHP协程实现多任务协作](https://log.zvz.im/2016/07/01/PHP-Coroutine/)

PHP 5.5 中最重要的特性之一就是对协程（coroutine）和生成器（generator）的支持。本文会使用协程实现一个任务调度器，以此帮助你理解协程的概念和用法。我会先用几个段落做一些介绍。如果你觉得你已经对生成器和协程的基本概念掌握得很牢固了，那么你可以直接跳至“多任务协作”这一段开始阅读。

## 一、生成器

生成器背后最原始的想法就是一个函数不仅仅返回一次数据，而是能够返回一系列的数据，并且这些数据是逐个返回的。也可以理解为，生成器使你能更方便地实现迭代器。

### 1. 一个简单的生成器

`xrange()`函数就是一个生成器的简单例子：

```php
function xrange($start, $end, $step = 1) {
    for ($i = $start; $i <= $end, $i += $step) {
        yield $i;
    }
}

foreach (xrange(1, 100000) as $num) {
    echo $num . "\n";
}
```

上例中的`xrange()`函数与内置函数`range()`函数的功能相同。唯一的区别在于`range()`会返回一个包含了一百万个数字的数组，而`xrange()`则返回一个可以吐出这些数字的迭代器，不会去老实地计算出一个包含所有数字的数组。

这样做的好处是显而易见的。它使得你可以处理超大规模的数据，而无需一次性将它们载入内存。你甚至可以处理无穷无尽的数据流。

当然并不是只有生成器能做到这一点，你也可以通过实现一个`iterator`接口来完成同样的工作。生成器只是更加方便，避免你为了生成一个迭代器而不得不去实现该接口的五个不同方法。

### 2. 将生成器用作可中断函数

要从对生成器的理解过度到协程的概念，理解它们内部的工作方式是非常重要的：生成器是可中断的函数，而 yield 语句则构成了这些中断点。

接着刚才的例子，当你调用`xrange(1, 1000000)`时，实际上`xrange()`没有执行任何代码。取而代之地，PHP 仅返回了一个`Generator`类的实例，它实现了`Iterator`接口：

```php
$range = xrange(1, 1000000);
var_dump($range); // object(Generator)#1
var_dump($range instanceof Iterator); // bool(true)
```

只有当你调用`iterator`接口相关的方法时代码才会执行。例如，你执行`$range->rewind()`时，`xrange()`函数中的代码就会执行，直到流程中的第一条`yield`语句。如此一来，就意味着`$i = $start`和`yield $i`被执行了。任何传递给`yield`语句的数据都能通过`$range->current()`来获取。

你需要调用`$range->next()`方法来继续执行生成器中的代码。这样它就会继续执行下去，直到下一条`yield`语句。所以只要连续地调用`->next()`和`->current()`方法，你就可以从生成器中获取到所有的返回值，直至最终不再遇到`yield`语句。对于`xrange()`函数来说，就是`$i`超出`$end`的时候。如此一来，流程会继续执行完剩余的代码，直至函数的结尾。若此时调用`->valid()`方法则会返回`false`，这个迭代过程就结束了。

## 二、协程

相对于上述功能，协程最主要的一点就是加入了向生成器中发送数据的能力。这使得从生成器到调用者的单向数据流，变成了两者彼此往来的数据通路。

将数据传递给协程的方法是调用`->send()`方法，而不是`->next()`。下面的这个`logger()`的例子展示了它是如何工作的：

```php
function logger($fileName) {
  $fileHandle = fopen($fileName, 'a');
  while (true) {
    fwrite($fileHandle, yield . "\n");
  }
}

$logger = logger(__DIR__ . '/log');
$logger->send('Foo');
$logger->send('Bar');
```

如你所见，在这里`yield`没有被用作一个语句，而是作为一个表达式，也是就说它有一个返回值。这个返回值是通过`->send()`语句传过来的。此例中`yield`会先往`log`文件中写入`Foo`和换行，再写入`Bar`和换行。

上面的例子中`yield`仅仅只是作为一个接收者。实际上可以把两者结合起来，使其既可以发送也可以接收数据。例子如下：

```php
function gen() {
  $ret = (yield 'yield1');
  var_dump($ret);
  $ret = (yield 'yield2');
  var_dump($ret);
}

$gen = gen();
var_dump($gen->current()); // string(6) "yield1"
var_dump($gen->send('ret1')); // string(4) "ret1" (gen 函数中的第一个 var_dump)
						    // string(6) "yield2" ( ->send() 返回值的 var_dump)
var_dump($gen->send('ret2')); // string(4) "ret2" (又是 gen 函数中的 var_dump)
						    // NULL ( ->send() 的返回值)
```

猛地一看，这些输出的顺序可能会有些难以理解，所以请多阅读几遍以确保你理解了它为何会如此输出。这儿要特别指出的：第一，使用括号将`yield`语句引起来不是随意为之的。由于技术原因这些括号是必须的。第二，你可能已经注意到了，我们在调用`->current()`之前没有调用`->rewrind()`方法。像这样做，`rewind`操作实际被隐式的执行了。第三，每次调用`->send()`之后，就会自动的执行`->next()`。

## 三、协程

上面就是协程的基本概念：可以使生成器与外界进行数据交换。下面会利用协程来开发一个简单的多任务协作系统。

### 1. 多任务协作

如果你看了上文`logger()`的例子，可能会想“为啥我要用协程做这事呢？为何我不直接用使用一个普通的类？”，当然你是绝对没错的。这个例子只是展示了协程的基本用法，但这样做却没有得到任何实际的好处。这只是许多协程示例中的一个而已。我已经说过协程是一个非常 NB 的概念，但是它们的应用却很少而且常常比较复杂，使得它很难举出一个简单而又不装的例子。

本文我决定用一个多任务协作的实现作为例子。目标是要能够并行地执行多个任务（或者“程序段”）。可是一个处理器只能处理一个任务（不考虑多核的情况）。这样一来，就需要处理器在多个不同的任务之间切换，让每一个任务都“运行一小会儿”。

“协作”的部分描述了这种切换的实现方式：它要求当前执行的任务自愿地将控制权返还给调度器，让其它的任务可以执行。这是相对于“优先调度“这种多任务调度方式而言的，优先调度方式下调度器可以在任务执行期间中断它，无论它是否自愿。协作式多任务被运用于早期版本的 Windows（Win95 之前）和 Mac OS，但之后它们都采用了优先调度的方式。原因很简单：如果你依赖于一个程序主动交出控制权，那么恶意软件就能很容易地占用全部的 CPU 资源，而不给其它任务留下任何执行的机会。

此时你应该可以看出协程和任务调度之间的关联了：`yield`指令提供了一种方式，使得任务可以自我中断，将控制权交还调度器，让别的任务得以执行。而且`yield`指令还可以用于任务和调度器之间的数据交互。

#### 1.1 任务类

对于我们的最终实现来讲，一个“任务”将会是对协程函数的简单包裹类：

```php
class Task {
  protected $taskId;
  protected $coroutine;
  protected $sendValue = null;
  protected $beforeFirstYield = true;
  
  public function __construct($taskId, Generator $coroutine) {
    $this->tasId = $taskId;
    $this->coroutine = $coroutine;
  }
  
  public function getTaskId() {
    return $this->taskId;
  }
  
  public function setSendValue($sendValue) {
    $this->sendValue = $sendValue;
  }
  
  public function run () {
    if ($this->beforeFirstYield) {
      $this->beforeFirstYield = false;
      return $this->coroutine->current();
    } else {
      $retval = $this->coroutine->send($this->sendValue);
      $this->sendValue = nulll;
      return $retval;
    }
  }
  
  public function isFinished() {
    return !$this->coroutine->valid();
  }
}
```

一个任务是一个由任务 ID 标识的协程。使用`setSendValue()`可以设定下一次获得执行时，传递给协程的数据（之后你会看到我们需要它做些什么）。`run()`方法实际上什么都不用做，只是调用协程的`send()`方法。为了理解额外的`beforeFirstYield`属性，我们可以考虑以下的代码片段：

```php
function gen() {
  yield 'foo';
  yield 'bar';
}

$gen = gen();
var_dump($gen->send('something'));

// 在 send() 发生时，在第一次 yield 之前，有一次隐式的 rewind() 调用
// 所以实际发生的过程是这样的：
$gen->rewind();
var_dump($gen->send('something'));

// rewind() 调用时会执行到第一个 yield 处(并且忽略它的值)，send() 调用时会前进至第二个 yield 处（并且抛出它的值）。这样的话，我们就会失去第一个 yield 的值！
```

通过增加额外的`beforeFirstYield`属性，可以保证第一次`yield`的值也可以正确地返回。

#### 1.2 调度器

调度器只需要循环执行这些任务即可：

```php
class Scheduler {
  protected $maxTaskId = 0;
  protected $taskMap = []; // taskId => task
  protected $taskQueue;
  
  public function __construct() {
    $this->taskQueue = new SplQueue();
  }
  
  public function new Task(Generator $coroutine) {
    $tid = ++$this->masTaskId;
    $task = new Task($tid, $coroutine);
    $this->taskMap[$tid] = $task;
    $this->schedule($task);

    return $tid;
  }
  
  public function schedule(Task $task) {
    $this->taskQueue->enqueue($task);
  }
  
  public function run() {
    while (!$this->taskQueue->isEmpty()) {
      $task = $this->taskQueue->dequeue();
      $task->run();
      
      if ($task->isFinished()) {
        unset($this->taskMap[$task->getTaskId()]);
      } else {
        $this->schedule($task);
      }
    }
  }
}
```

`newTask()`方法用于创建任务并把它放入任务对照表中。此外它把任务放入任务队列，以此来安排任务的执行。`run()`方法用于遍历这个任务队列，并执行任务。如果一个任务完成了，那么它会被从队列中移除，否则它会被重新排在队列尾部。

#### 1.3 测试

让我们用两个简单（且无意义的）任务，来测试一下调度器：

```php
function task1() {
  for ($i = 1; $i <= 10; ++$i) {
    echo "This is task 1 iteration $i.\n":
    yield;
  }
}

function task2() {
  for ($i = 1; $i <= 5; ++$i) {
    echo "This is task 2 iteration $i.\n";
    yield;
  }
}

$scheduler = new Scheduler;

$scheduler->newTask(task1());
$scheduler->newTask(task2());

$scheduler->run();
```

两个任务都是只是打印出一条信息，然后使用 yield 将控制权交还给调度器。以下是输出结果：

```php
This is task 1 iteration 1.
This is task 2 iteration 1.
This is task 1 iteration 2.
This is task 2 iteration 2.
This is task 1 iteration 3.
This is task 2 iteration 3.
This is task 1 iteration 4.
This is task 2 iteration 4.
This is task 1 iteration 5.
This is task 2 iteration 5.
This is task 1 iteration 6.
This is task 1 iteration 7.
This is task 1 iteration 8.
This is task 1 iteration 9.
This is task 1 iteration 10.
```

与预期一致：前 5 次迭代两个任务是交替进行的，然后当第二个任务结束了，只剩下第一个在继续执行。

### 2. 与调度器进行交互

至此调度器已经可以工作，我们就能开始进行下一事项了：任务与调度器的数据交互。我们将使用进程与操作系统之间一样的交互方法：系统调用（system call）。我们需要系统调用，是由于操作系统和进程应当运行在不同的特权级别。 所以为了进行某些特权操作（比如杀死其它的进程），必须得有方法将控制交给内核，让内核去执行。从内部看这又是通过中断指令来实现的。早期我们使用的是原生的`int`指令，现如今则有了更专业且更快速的`syscall/sysenter`指令。

我们的任务调度系统将会反应出这种设计：不是简单地将调度器传递给任务（这样会使得任务能够为所欲为），我们会通过`yield`表达式让它们以系统调用的方式进行交互。`yield`在这里不仅起到中断的作用，同时也负责传递信息给调度器。

我们用一个`callable`对象的包裹类来描述一个系统调用：

```php
class SystemCall {
  protected $callback;
  
  public function __construct(callable $callback) {
    $this->callback = $callback;
  }
  
  public function __invoke(Task $task, Scheduler $scheduler) {
    $callback = $this->callback;  // 不能从 PHP 中直接调用
    
    return $callback($task, $scheduler);
  }
}
```

它会表现得如任何可调用对象一样（通过`__invoke`方法），但是让调度器将调用的任务和它自己一起传递至函数内。为了使用它，我们需要小小地修改一下调度器的`run`方法：

```php
public function run() {
  while (! $this->taskQueue->isEmpty() ) {
    $task = $this->taskQueue->dequeue();
    $retval = $task->run();
    
    if ($retval instanceOf Systemcall) {
      $retval($task, $this);
      continue;
    }
    
    if ($task->isFinished()) {
      unset($this->taskMap[$task->getTaskId()]);
    } else {
      $this->schedule($task);
    }
  }
}
```

第一个系统调用什么都不做，只是返回任务 ID 号：

```php
function getTaskId() {
  return new SystemCall(function (Task $task, Scheduler $scheduler) {
    $task->setSendValue($task->getTaskId());
    $scheduler->scheduler($task);
  });
}
```

它的实现方式是将`tid`设置为下一个发送的数据并且重排任务。对于系统调用，调度器并不自动重排任务，我们需要手动控制（后面你就会知道为什么）。我们可以利用系统调用重写之前的例子：

```php
function task($max) {
  $tid = (yield getTaskId()); // <-- 这里使用了系统调用
  for ( $i = 1; $i <= $max; ++$i ) {
    echo "This is task $tid iteration $i.\n";
    yield;
  }
}

$scheduler = new Scheduler;

$scheduler->newTask(task(10));
$scheduler->newTask(task(5));

$scheduler->run();
```

代码输出还是和之前一样。注意是如何进行系统调用的，基本上和普通函数一样只是前面多加了一个`yield`。下面还有两个系统调用分别用于创建任务和删除任务：

```php
function newTask(Generator $coroutine) {
  return new SystemCall(
    function(Task $task, Scheduler $scheduler) use ($coroutine) {
      $task->setSendValue($scheduler->newTask($coroutine));
      $scheduler->schedule($task);
    }
  );
}

function killTask($tid) {
  return new SystemCall(
    function(Task $task, Scheduler $scheduler) use ($tid) {
      $task->setSendValue($scheduler->killTask($tid));
      $scheduler->schedule($task);
    }
  );
}
```

`killTask`函数的实现需要在调度器里加一个方法：

```php
public function killTask($tid) {
  if (!isset($this->taskMap[$tid])) {
    return false;
  }
  
  unset($this->taskMap[$tid]);
  
  // 这里的代码有点烂，其实可以优化让它不用遍历整个队列的，
  // 但我这里暂时不管它了，就假设杀进程不是那么常用的吧
  foreach ($this->taskQueue as $i => $task) {
    if ($task->getTaskId() === $tid) {
      unset($this->taskQueue[$i]);
      break;
    }
  }
  
  return true;
}
```

用下面一小段代码来测试新功能：

```php
function childTask() {
  $tid = (yield genTaskId());
  while(true) {
    echo "Child task $tid still alive!\n";
    yield;
  }
}

function task() {
  $tid = (yield getTaskId());
  $childTid = (yield newTask(childTask()));
  
  for ($i = 1; $i <= 6; ++$i) {
    echo "Parent task $tid iteration $i.\n";
    yield;
    
    if ($i == 3) yield killTask($childTid);
  }
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```

这将输出以下内容：

```php
Parent task 1 iteration 1.
Child task 2 still alive!
Parent task 1 iteration 2.
Child task 2 still alive!
Parent task 1 iteration 3.
Child task 2 still alive!
Parent task 1 iteration 4.
Parent task 1 iteration 5.
Parent task 1 iteration 6.
```

子任务在三次迭代后被清除，不再输出“Child is still alive“。有人可能会指出这并不是真正的父子关系，因为在父任务结束运行后，子任务仍然可以继续运行。甚至子任务也可以终止父任务。也许有人可以修改一下调度器，实现一个更有层次的任务结构，不过在本文中我们不会去做这件事。

还有很多进程管理调用可以去实现，比如`wait`（等待直至某个任务执行完成），`exec`（用于替代当前任务），还有`fork`（克隆当前运行的任务）。分路（forking）相当的酷，你可以利用 PHP 的协程来实现它，因为它们也支持克隆操作。

不过我会把这些留给感兴趣的读者，让我们开始下一个话题。

### 3. 非阻塞 IO

用我们的任务管理系统来实现一个 web 服务器是一件很牛的事情。可以用一个任务用来监听 socket 建立新连接，每当一个新连接建立时，就创建一个新的任务负责处理它。

困难的部分在于一般的`socket`操作是阻塞的（比如读取数据）。PHP 会一直阻塞直到客户端完成数据发送。对于一个 web 服务器来说显然是不能接受的：这相当于它每次只能处理一个连接。

解决办法是在实际读写前，确保 socket 是“就绪”状态。为了找出哪些 socket 处于可读取或可写状态，我们可以使用`stream_select`方法。

首先，我们加入两个新的系统调用，可以使得一个任务在 socket 就绪前一直等待。

```php
function waitForRead($socket) {
  return new SystemCall(
    function(Task $task, Scheduler $scheduler) use ($socket) {
      $scheduler->waitForRead($socket, $task);
    }
  );
}

function waitForWrite($socket) {
  return new SystemCall(
    function(Task $task, Scheduler $scheduler) use ($socket) {
      $scheduler
  )
}
```

这些系统调用就是调度器中对应方法的代理：

```php
// resourceID => [socket, tasks]
protected $waitingForRead = [];
protected $waitingForWrite = [];

public function waitForRead($socket, Task $task) {
  if (isset($this->waitingForRead[(int) $socket])) {
    $this->waitingForRead[(int) $socket][1][] = $task;
  } else {
    $this->waitingForRead[(int) $socket] = [$socket, [$task]];
  }
}

public function waitForWrite($socket, Task $task) {
  if (isset($this->waitForWrite[(int) $socket])) {
    $this->waitingForWrite[(int) $socket][1][] = $task;
  } else {
    $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
  }
}
```

`waitingForRead`和`waitingForWrite`两个属性用于记录被等待的 socket 和等待它们的任务。下面这个方法很有趣，它实现了检查 socket 的就绪状态和重排对应任务的功能：

```php
protected function ioPoll($timeout) {
  $rSocks = [];
  foreach ($this->waitingForRead as list($socket)) {
    $rSocks[] = $socket;
  }
  
  $wSocks = [];
  foreach ($this->waitingForWrite as list($sockets)) {
    $wSocks[] = $socket;
  }
  
  $eSocks = []; // dummy
  
  if (!stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
    return;
  }
  
  foreach ($rSocks as $socket) {
    list(, $tasks) = $this->waitingForRead[(int) $socket];
    unset($this->waitingForRead[(int) $socket]);
    
    foreach ($tasks as $task) {
      $this->schedule($task);
    }
  }
}
```

`stream_select`函数接受读／写／排除的 sockets 数组作为检验参数（忽略最后一个数组中的）。这些数组都是引用传参，执行完成后函数会留下状态发生变化的那些。然后我们就可以遍历这些数组，并且重排那些与之关联的任务了。

为了规律地执行上面的轮询操作，我们要在调度器中加入一个特别的任务：\

```php
protected function ioPollTask() {
  while (true) {
    if ($this->taskQueue->isEmpty()) {
      $this->ioPoll(null);
    } else {
      $this->ioPoll(0);
    }
    yield;
  }
}
```

这个任务需要在某处被加入调度，比如可以在`run()`方法开始处加入`$this->newTask($this->ioPollTask())`。然后它就会像其它的任务一样工作，每个完整的任务循环中执行一次（这不一定是最好的处理方案）。`ioPollTask`会调用`ioPoll`，且超时参数设置为 0，使得`stream_select`函数可以立即返回（不会阻塞）。

只有当任务队列为空时，我们给超时参数传入`null`，使得它会阻塞直到某个 socket 就绪。如果不这样做，就会使得轮询任务不断地被执行直到产生一个新连接。这会造成 CPU 达到 100% 的使用率。所以让操作系统在此等待会更有效率。

现在，要完成服务器代码相对容易很多：

```php
function server($port) {
  echo "Strating server as port $port...\n";
  
  $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
  if (!$socket) throw new Exception($errStr, $errNo);
  
  stream_set_blocking($socket, 0);
  
  while (true) {
    yield waitForRead($socket);
    $clientSocket = stream_socket_accept($socket, 0);
    yield newTask(handleClient($clientSocket));
  }
}

function handleClient($socket) {
  yield waitForRead($socket);
  $data = fread($socket, 8192);
  
  $msg = "Received following request:\n\n$data";
  $msgLength = strlen($msg);
  
  $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;
  yield waitForWrite($socket);
  fwrite($socket, $reponse);
  
  fclose($socket);
}

$scheduler = new Scheduler;
$scheduler->newTask(server(8000));
$scheduler->run();
```

这段代码能接受`localhost:8000`的连接，并发回一个 HTTP 响应，包含它发送过来的内容。现实情况则要复杂得多（讨论如何恰当地处理 HTTP 请求已经超出了本文的范围）。以上代码仅是一个通常意义上的示例。

你可以用`ab -n 1000 -c 100 localhost:8000/`来测试这个服务代码。这可以一百并发地发送一万次请求。在这种压力下我得到的响应时长为十毫秒。但会有一些问题，某些请求处理得非常慢（约五秒），导致总吞吐量只有每秒两千次请求（如果一个请求只耗时十毫秒的话，应当接近每秒一万次请求）。采用更高的并发时（比如 -c 500 ）绝大多数时候还是工作正常，除了某些连接抛出了“Connection reset by peer“的错误。由于我对这些底层的 socket 事情了解较少，所以也没去探究其原因。

### 4. 栈式协程

如果你想用我们的调度系统构建一个更大的系统，那么很快就会遇到这个问题：我们习惯于将代码拆散为更小的功能函数进行调用。使用协程时却无法做到了。比如考虑一下代码：

```php
function echoTimes($msg, $max) {
  for ($i = 1; $i <= $max; ++$i) {
    echo "$msg iteration $i\n";
    yield;
  }
}

function task() {
  echoTimes('foo', 10); // 输出10次 foo
  echo "---\n";
  echoTimes('bar', 5); // 输出5次 bar
  yield; // 强制转换为一个协程
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```

代码想要把反复用到的“输出 n 次”这段代码作为一个独立的协程，然后在主任务中调用它。但是却不能正常工作。就像文章开头提到的生成器（或协程）本身不做任何事情，它只是返回一个对象。上面这个例子中即印证了此事。`echoTimes`调用除了返回一个（未使用）的协程对象外，什么也不会做。

为了使其能正常工作，我们需要为协程对象写一个小小的包裹类。我将其命名为“栈式协程”（ stacked coroutine ），因为它会管理一个嵌套的协程调用栈。它可以通过`yield`调用子协程：

```php
$retval = (yield someCoroutine($foo, $bar));
```

子协程也能返回数据，还是使用`yield`：

```php
yield retval("I'm a return value!");
```

这个`retval`函数仅仅只是返回一个数据的包裹对象，表明自己是一个返回值：

```php
class CoroutineReturnValue {
  protected $value;
  
  public function __construct($value) {
    $this->value = $value;
  }
  
  public function getValue() {
    return $this->value;
  }
}

function retval($value) {
  return new CoroutineReturnValue($value);
}
```

要将一个协程转化为一个栈式协程（支持子调用），我们还需要另一个函数（显然又是一个协程）：

```php
function stackedCoroutine(Generator $gen) {
  $stack = new SplStack;
  
  for(;;) {
    $value = $gen->current();
    
    if ($value instanceof Generator) {
      $stack->push($gen);
      $gen = $value;
      continue;
    }
    
    $isReturnValue = $value instanceof CoroutineReturnValue;
    if (!$gen->valid() || $isReturnValue) {
      if($stack->isEmpty()) {
        return;
      }
      
      $gen = $stack->pop();
      $gen->send($isReturnValue ? $value->getValue() : NULL);
      continue;
    }
    
    $gen->send(yield $gen->key() => $value);
  }
}
```

这个方法充当着调用者和当前运行的子协程的代理。它是在这一行`$gen->send(yield $gen->key() => $value);`进行处理的。此外还检验返回值是否生成器，如果是就开始执行它，并将之前的协程压入栈中。一旦得到一个`CoroutineReturnValue`就会从栈中取出，继续执行之前的协程。

要使栈式协程在任务中可以使用，`Task`构造器中的`$this->coroutine = $coroutine;`这一行需要被替换为`$this->coroutine = stackedCoroutine($coroutine);`。

现在我们可以改进 web 服务器的例子，把`wait+read`（还有`wait+write`和`wait+accept`）这些动作组合为函数。我将用一个类把相关功能整合在一起：

```php
class CoSocket {
  protected $socket;
  
  public function __construct($socket) {
    $this->socket = $socket;
  }
  
  public function accept() {
    yield waitForRead($this->socket);
    yield retval(new CoSocket(stream_socket_accept($this->socket, 0)));
  }
  
  public function read($size) {
    yield waitForRead($this->socket);
    yield retval(fread($this->socket, $size));
  }
  
  public function write($string) {
    yield waitForWrite($this->socket);
    fwrite($this->socket, $string);
  }
  
  public function close() {
    @fclose($this->socket);
  }
}
```

这样服务器代码可以写得更加清晰了：

```php
function server($port) {
  echo "Starting server at port $port...\n";
  
  $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
  if (!$socket) throw new Exception($errStr, $errNo);
  
  stream_set_blocking($socket, 0);
  
  $socket = new CoSocket($socket);
  while (true) {
    yield newTask(
      handleClient(yield $socket->accept())
    );
  }
}

function handleClient($socket) {
  $data = (yield $socket->read(8192));
  
  $msg = "Received following request:\n\n$data";
  $msgLength = strlen($msg);
  
  $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;
  
  yield $socket->write($response);
  yield $socket->close();
}
```

### 5. 错误处理

作为一名 NB 的程序员，你一定已经注意到了，上面的例子都缺少错误处理机制。差不多 socket 的每一个操作都可能失败并造成错误的。我显然做了这些事，不过错误处理实在是太无聊（ socket 的更是如此！），而且很容易就使得代码量成倍地增长。

不过我还是愿意分享一下协程的一般错误处理方式：协程提供了使用`throw()`方法在它们内部抛出异常的能力。`throw()`方法接受一个异常实例，并在协程的当前中断点抛出。考虑以下代码：

```php
function gen() {
  echo "Foo\n";
  try {
    yield;
  } catch (Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
  }
  echo "Bar\n";
}

$gen = gen();
$gen->rewind(); // 输出 "Foo"
$gen->throw(new Exception('Test')); // 输出 "Exception: Test" 和 "Bar"
```

对于我们的目的来说这样很 NB，因为我们可以在系统调用和子协程中抛出异常了。对于系统调用来说`Scheduler::run()`方法需要一些小小的调整：

```php
if ($retval instanceof SystemCall) {
  try {
    $retval($task, $this);
  } catch (Exception $e) {
    $task->setException($e);
    $this->schedule($task);
  }
  continue;
}
```

`Task`类也需要处理`throw`调用的情况了：

```php
class Task {
  // ...
  protected $exception = null;
  
  public function setException($exception) {
    $this->exception = $exception;
  }
  
  public function run() {
    if ($this->beforeFirstYield) {
      $this->beforeFirstYield = false;
      return $this->coroutine->current();
    } elseif ($this->exception) {
      $retval = $this->coroutine->throw($this->exception);
      $this->exception = null;
      return $retval;
    } else {
      $retval = $this->coroutine->send($this->sendValue);
      $this->sendValue = null;
      return $retval;
    }
  }
  
  // ...
}
```

走一个：

```php
function task() {
  try {
    yield killTask(500);
  } catch (Exception $e) {
    echo 'Tried to kill task 500 but failed: ', $e->getMessage(), "\n";
  }
}
```

可惜这还不能正常工作，因为`stackedCoroutine`函数还没有正确地处理异常情况。需要修改一下代码：

```php
function stackedCoroutine(Generator $gen) {
  $stack = new SplStack;
  $exception = null;
  
  for (;;) {
    try {
      if ($exception) {
        $gen->throw($exception);
        $exception = null;
        continue;
      }
      
      $value = $gen->current();
      
      if ($value instanceof Generator) {
        $stack->push($gen);
        $gen = $value;
        continue;
      }
      
      $isReturnValue = $value instanceof CoroutineReturnValue;
      if (!$gen->valid() || $isReturnValue) {
        if ($stack->isEmpty()) {
          return;
        }
        
        $gen = $stack->pop();
        $gen->send($isReturnValue ? $value->getValue() : NULL);
        continue;
      }
      
      try {
        $sendValue = (yield $gen->key() => $value);
      } catch (Exception $e) {
        $gen->throw($e);
        continue;
      }
      
      $gen->send($sendValue);
    } catch (Exception $e) {
      if ($stack->isEmpty()) {
        throw $e;
      }
      
      $gen = $stack->pop();
      $exception = $e;
    }
  }
}
```

## 四、总结

本文中我们创建了一个多任务协作的任务调度器，同时具备“系统调用”、执行异步 IO 操作和错误处理的能力。最酷的一点是这些代码看起来是完全同步的，尽管它执行了许多异步操作。如果你想从某个 socket 中读取数据，你既不必传递回调函数，也不必注册事件监听器。取而代之的你只需要写一句`yield $socket->read()`。与你常写的代码基本一致，仅仅只用在前面加一个`yield`。

当我第一次听说这些概念时，我发现这个概念真的 NB，并且直接促使我在 PHP 中也实现这些特性。同时我发现协程真的容易让人担心。协程的使用，让完美的代码和糟糕的代码之间仅有一线之隔。对我而言，很难评价像上文一样的方式去写异步代码是否真的有益。
无论如何，我都认为这是一个有趣的话题，希望你也感兴趣。期待您的评论 : - )



