> 转摘：[用PHP协程实现多任务协作](https://log.zvz.im/2016/07/01/PHP-Coroutine/)

PHP 协程(Coroutine)是基于生成器(Generator)实现的功能，也可以说，PHP 协程是生成器的一种用法。需要注意的是，PHP 中的协程只是一种开发方式，是一种概念思想，而不是由 PHP 提供的具体类或方法。

## 一、基础

在进行 PHP 协程开发之前，需要先了解协程的概念，和 PHP 协程开发的基础生成器 Generator

### 1.1 协程、线程和进程

在计算机系统中，操作系统为了向不同的程序提供服务，提供了进程概念，每个程序开启时创建属于自己的进程，进程中的数据是可以共享的。*进程是计算机分配资源的最小单位*。

进程虽然能够解决不同程序之间的资源隔离，但是由于其新建和销毁的比较耗时，所以操作系统又提供了轻量级的线程。线程归属于进程，一个进程可有多个线程，*线程是计算机调度执行的最小单位*。线程一般有程序内部逻辑自行决定创建和销毁，但是在调度上和进程一样，依旧是由系统进行统一调度，所以还是会涉及到上下文的切换损耗。相对进程来说，线程已经足够轻量。

*协程可以看成“用户态的线程”，需要用户程序实现调度*。线程和进程由操作系统调度“抢占式”(优先调度)交替运行，调度器可以在任务执行期间中断它，无论它是否自愿。协程则是主动让出 CPU，“协作式”交替运行。协程十分的轻量，切换时不需要操作系统提供支持，执行效率高，数目越多，越能体现协程的优势。这里说协程是主动让出 CPU 是指，协程由程序员编写的代码逻辑控制，在遇到需要暂停时可以自动的暂停协程方法的执行，继续执行后面的代码逻辑，在未来某个时候，再由程序员编写的代码将其重启。

协作式多任务被运用于早期版本的 Windows(Win95 之前)和 Mac OS，但之后它们都采用了优先调度的方式。原因很简单：如果依赖于一个程序主动交出控制权，那么恶意软件就能很容易地占用全部的 CPU 资源，而不给其它任务留下任何执行的机会。

整体来看，进程和线程是操作系统级别的，由操作系统管理其执行和暂停，而进程则全是用户态(程序员)代码管理的，由代码自行决定协程的暂停与否。

### 1.2 生成器

生成器是一种升级版的迭代器，最主要的特征是能够暂停执行和与外部进行数据交换。

生成器中的代码可以通过`yield`关键词进行中止和返回数据，同时还能通过生成器对象的`send()`方法向内部传入数据，完成生成器对象与外部程序的数据交换。

关于生成器的更多信息，可以查看 [PHP Generator](./PHP%20Generator.md) 文档。

### 1.3 协程

PHP 协程开发的基础就是：使用生成器与外界进行数据交换。

一个处理器只能处理一个任务（不考虑多核的情况），这样一来，就需要处理器在多个不同的任务之间切换，让每一个任务都“运行一小会儿”。而通过协程就能够让多个任务主动的进行切换，而且不涉及到系统资源的开销。

协程的“协作”部分描述了这种切换的实现方式：它要求当前执行的任务自愿地将控制权返还给调度器，让其它的任务可以执行。此时就可以看出协程和任务调度之间的关联了：`yield`指令提供了一种方式，使得任务可以自我中断，将控制权交还调度器，让别的任务得以执行，而且`yield`指令还可以用于任务和调度器之间的数据交互。

生成器实现的协程属于无栈协程(stackless coroutine)，即生成器函数只有函数帧，运行时附加到调用方的栈上执行。不同于功能强大的有栈协程(stackful coroutine)，生成器暂停后无法控制程序走向，只能将控制权被动的归还调用者；生成器只能中断自身，不能中断整个协程。当然，生成器的好处便是效率高(暂停时只需保存程序计数器即可)，实现简单。

## 二、任务调度器

下面利用协程来开发一个简单的调度器系统，目标是要能够并行地执行多个任务(或者“程序段”)，主要包含任务类和调度器类。

### 2.1 任务类

对于最终实现来讲，一个“任务”将会是对协程函数的简单包裹类：

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

一个任务是一个由任务 ID 标识的协程(生成器对象)。使用`setSendValue()`可以设定下一次获得执行时，传递给生成器对象的数据。`run()`方法实际上什么都不用做，只是调用生成器对象的`send()`方法。为了理解额外的`beforeFirstYield`属性，可以考虑以下的代码片段：

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

// rewind() 调用时会执行到第一个 yield 处(并且忽略它的值)，send() 调用时会前进至第二个 yield 处（并且抛出它的值）。这样的话，就会失去第一个 yield 的值！
```

通过增加额外的`beforeFirstYield`属性，可以保证第一次`yield`的值也可以正确地返回。

### 2.2 调度器

调度器只需要循环执行这些任务即可：

```php
class Scheduler {
  protected $maxTaskId = 0;
  protected $taskMap = []; // taskId => task
  protected $taskQueue;
  
  public function __construct() {
    $this->taskQueue = new SplQueue();
  }
  
  public function newTask(Generator $coroutine) {
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

### 2.3 测试

用两个简单（且无意义的）任务，来测试一下调度器：

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

## 三、多任务协作系统

使用上面调度器的功能基础，可以进行任务与调度器的数据交互的开发，也就是多任务协作系统的开发，该系统将使用进程与操作系统之间一样的交互方法：系统调用(system call)。

之所以需要系统调用，是由于操作系统和进程应当运行在不同的特权级别。为了进行某些特权操作（比如杀死其它的进程），必须得有方法将控制交给内核，让内核去执行。从内部看这又是通过中断指令来实现的，早期使用的是原生的`int`指令，现如今则有了更专业且更快速的`syscall/sysenter`指令。

下面开发的多任务协作系统将会反应出这种设计：不是简单地将调度器传递给任务（这样会使得任务能够为所欲为），而是会通过`yield`表达式让它们以系统调用的方式进行交互。`yield`在这里不仅起到中断的作用，同时也负责传递信息给调度器。

### 3.1 系统调用

用一个`callable`对象的包裹类来描述一个系统调用：

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

它会表现得如任何可调用对象一样（通过`__invoke`方法），但是让调度器将调用的任务和它自己一起传递至函数内。为了使用它，需要小小地修改一下调度器的`run`方法：

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

### 3.2 获取任务 ID 的系统调用

第一个系统调用什么都不做，只是返回任务 ID 号：

```php
function getTaskId() {
  return new SystemCall(function (Task $task, Scheduler $scheduler) {
    $task->setSendValue($task->getTaskId());
    $scheduler->scheduler($task);
  });
}
```

它的实现方式是将`tid`设置为下一个发送的数据并且重排任务。对于系统调用，调度器并不会自动重排任务，需要手动将其加入调度器。

可以利用系统调用重写之前的例子：

```php
function task($max) {
  $tid = (yield getTaskId()); // <-- 这里使用了系统调用获取任务 ID
  
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

代码输出还是和之前一样。注意是如何进行系统调用的，基本上和之前的生成器函数一样，只是前面多加了一个`yield`。

### 3.3 创建和删除任务的系统调用

下面还有两个系统调用分别用于创建任务和删除任务：

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
  // 但暂时不管它了，就假设杀进程不是那么常用
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

子任务在三次迭代后被清除，不再输出“Child is still alive“。有人可能会指出这并不是真正的父子关系，因为在父任务结束运行后，子任务仍然可以继续运行。甚至子任务也可以终止父任务。也许有人可以修改一下调度器，实现一个更有层次的任务结构，不过在本文中不会去做这件事。

还有很多进程管理调用可以去实现，比如`wait`(等待直至某个任务执行完成)，`exec`(用于替代当前任务)，还有`fork`(克隆当前运行的任务)。

## 四、非阻塞 IO

用上面的协作式任务管理系统来实现一个 web 服务器是一件很牛的事情。可以用一个任务用来监听 socket 建立新连接，每当一个新连接建立时，就创建一个新的任务负责处理它。

困难的部分在于一般的`socket`操作是阻塞的（比如读取数据）。PHP 会一直阻塞直到客户端完成数据发送。对于一个 web 服务器来说显然是不能接受的：这相当于它每次只能处理一个连接。

解决办法是在实际读写前，确保 socket 是“就绪”状态。为了找出哪些 socket 处于可读取或可写状态可以使用`stream_select`方法。

### 4.1 等待系统调用

首先，加入两个新的系统调用，可以使得一个任务在 socket 就绪前一直等待。

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
      $scheduler->waitForWrite($socket, $task);
    }
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

`waitingForRead`和`waitingForWrite`两个属性用于记录被等待的 socket 和等待它们的任务。

### 4.2 检查和重排

下面这个方法很有趣，它实现了检查 socket 的就绪状态和重排对应任务的功能：

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

`stream_select`函数接受读/写/排除的 sockets 数组作为检验参数（忽略最后一个数组中的）。这些数组都是引用传参，执行完成后函数会留下状态发生变化的那些。然后我们就可以遍历这些数组，并且重排那些与之关联的任务了。

### 4.3 轮询操作

为了规律地执行上面的轮询操作，需要在调度器中加入一个特别的任务：

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

只有当任务队列为空时，给超时参数传入`null`，使得它会阻塞直到某个 socket 就绪。如果不这样做，就会使得轮询任务不断地被执行直到产生一个新连接。这会造成 CPU 达到 100% 的使用率。所以让操作系统在此等待会更有效率。

### 4.4 服务器代码

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

可以用`ab -n 1000 -c 100 localhost:8000/`来测试这个服务代码。在这种压力下得到的响应时长为十毫秒。但会有一些问题，某些请求处理得非常慢（约五秒），导致总吞吐量只有每秒两千次请求（如果一个请求只耗时十毫秒的话，应当接近每秒一万次请求）。采用更高的并发时(比如`-c 500`)绝大多数时候还是工作正常，除了某些连接抛出了“Connection reset by peer“的错误。由于对这些底层的 socket 事情了解较少，所以也没去探究其原因。

## 四、栈式协程

如果想用前面的调度系统构建一个更大的系统，那么很快就会遇到这个问题：一般会习惯于将代码拆散为更小的功能函数进行调用，使用协程时却无法做到了。

### 4.1 嵌套协程的问题

比如考虑以下代码：

```php
function echoTimes($msg, $max) {
  for ($i = 1; $i <= $max; ++$i) {
    echo "$msg iteration $i\n";
    yield;
  }
}

function task() {
  echoTimes('foo', 10); // 输出 10 次 foo
  echo "---\n";
  echoTimes('bar', 5); // 输出 5 次 bar
  yield; // 强制转换为一个协程
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```

代码想要把反复用到的“输出 n 次”这段代码作为一个独立的协程，然后在主任务中调用它。但是却不能正常工作。就像文章开头提到的生成器（或协程）本身不做任何事情，它只是返回一个对象。上面这个例子中即印证了此事。`echoTimes`调用除了返回一个（未使用）的协程对象外，什么也不会做。

### 4.2 栈式协程

为了使嵌套协程能正常工作，需要为协程对象写一个小小的包裹类，将其命名为“栈式协程”(stacked coroutine)，因为它会管理一个嵌套的协程调用栈。它可以通过`yield`调用子协程：

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

### 4.3 协程代理

要将一个协程转化为一个栈式协程（支持子调用），还需要另一个函数（显然又是一个协程）：

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

### 4.4 改进 web 服务器

现在可以改进 web 服务器的例子，把`wait+read`（还有`wait+write`和`wait+accept`）这些动作组合为函数，用一个类把相关功能整合在一起：

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

## 五、错误处理

上面的例子都缺少错误处理机制！差不多 socket 的每一个操作都可能失败并造成错误的。不过错误处理实在是太无聊(socket 的更是如此)，而且很容易就使得代码量成倍地增长。

协程的一般错误处理方式：使用生成器对象的`throw()`方法在它们内部抛出异常。考虑以下代码：

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

这样就可以在系统调用和子协程中抛出异常了。对于系统调用来说`Scheduler::run()`方法需要一些小小的调整：

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

## 六、总结

本文创建了一个多任务协作的任务调度器，同时具备“系统调用”、执行异步 IO 操作和错误处理的能力。最酷的一点是这些代码看起来是完全同步的，尽管它执行了许多异步操作。如果想从某个 socket 中读取数据，既不必传递回调函数，也不必注册事件监听器。取而代之的只需要写一句`yield $socket->read()`。与常写的代码基本一致，仅仅只用在前面加一个`yield`。



