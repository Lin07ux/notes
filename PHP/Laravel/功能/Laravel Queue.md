Laravel Queue 主要用于执行延时任务，而且可以失败自动重试。

[官方文档](https://laravel.com/docs/5.8/queues)

## 一、基本

Laravel Queue 的实现主要包含三个部分：任务(Job)、分发(dispatch)和执行(queue:work)，这三部分分别对应着延时任务的主要逻辑、延时任务的创建发布和任务的执行。

### 1.1 任务

Laravel 中的任务就是 Job，可以通过如下命令创建：

```shell
php artisan make:job <SomeJobName>
```

创建的 Job 一般会存放在`app/Jobs`路径，并且继承了`Illuminate\Contracts\Queue\ShouldQueue`类。

在创建好的 Job 类中，主要需要实现一个`handle()`方法。在任务被执行的时候，就是调用该方法。如果这个方法还依赖一些参数，那么 Laravel 的 Service Container 可以对其进行依赖解析和注入，就如同控制器的方法一样。

另外，创建任务时提供的数据都可以存储在 Job 类的属性中，它们在将任务压入到队列时会被很好的序列化，并在需要执行时被还原出来。所以，在开发任务的逻辑时，就将其作为一个一般的类即可，不需要过多的考虑其他问题。

### 1.2 分发

任务类创建好之后，就可以在需要的时候实例化一个任务对象，并将其分发到队列中，才有可能被执行。

分发任务有多种方法。比如，先创建了一个名为`ProcessPodcast`的任务类，该任务的构造函数需要一个`$podcast`参数。然后就可以在代码中使用类似如下的方式进行分发：

```php
// 正常分发
ProcessPodcast::dispatch($podcast);
// 延时分发
ProcessPodcast::dispatch($podcast)->delay(Carbon::now()->addMinutes(10));
// 同步分发(会立即执行，同步的)
ProcessPodcast::dispatchNow($podcast);
```

Laravel 也提供了全局的`dispatch()`方法和`dispatchNow()`方法。如果是在控制器中，则可以使用控制器的`Controller::dispatch()`方法和`Controller::dispatchNow()`方法，它们的使用方式是一样的：

```php
dispatch(new ProcessPodcast($pocast));
dispatch((new ProcessPodcast($pocast))->delay(Carbon::now()->addMinutes(10)));
dispatchNow(new ProcessPodcast($pocast));
```

另外，在发布任务的时候，还可以设置任务链、分发到的队列、队列链接等，具体的可以参考官方文档。

### 1.3 执行

任务被创建并分发之后，会被序列化之后放入到队列中，并不会自动执行(`dispatchNow()`同步分发的除外)。

任务被分发到队列之后，需要在系统命令行中执行 artisan 命令开启队列才会被执行：

```shell
php artisan queue:work
```

该命令会开启一个 PHP 进程不断的监听队列并执行队列任务，直到手动退出该命令的执行。而且该命令还支持一些参数来控制任务的执行，具体可以查看该命令的帮助信息：

```shell
php artisan queue:work --help
```

Laravel 还提供了跟多其他的队列命令，可以查看 artisan 相关的文档和资料。

为了能够让队列执行命令一直在后台运行，建议使用 Supervisor 工具。

### 1.4 失败处理

在任务类中，可以定义一个`failed()`公开方法，该方法可以接收一个 Exception 异常参数，表示导致任务处理失败的原因。

这个方法会在任务最终执行失败时被调用，也就是说，任务执行了最大重试次数后依旧失败时会调用该方法，表示任务最终执行失败了，并给出相关原因。

比如说，某个任务被设置为允许重试 3 次，那么前两次失败不会调用该方法，而在第三次执行失败之后会开始调用该方法。

如果需要将一个任务主动失败掉，可以调用任务的`fail()`方法，并为其传递一个异常实例。

## 二、配置

Laravel Queue 有几个常见的配置项，用于控制任务的重试次数、延时和超时等。这些配置可以在 config 文件中设置，也可以在任务类中通过公共属性的方式配置。

### 2.1 基本配置

在`config/queue.php`文件中，可以配置基本的队列设置，先以 Redis 为例：

```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('QUEUE_REDIS_CONNECTION', 'default'),
    'queue' => 'default',
    'retry_after' => 90,
    'block_for' => 5,
],
```

其中：

* `queue` 用于指定默认的队列名称，而且在使用`php artisan queue:work`执行队列任务的时候，如果不指定`--queue`参数，就会使用这里的配置作为该参数值。
* `retry_after` 定义任务在执行以后多少秒后释放回队列。如果设定的值为 90，任务在运行 90 秒后还未完成时，其将被释放回队列而不是删除掉。毫无疑问，需要把该值设定为任务执行时间的最大可能值。
* `block_for` 指定驱动在迭代队列进程循环并重新轮询 Redis 数据库之前等待可用队列任务的时间。根据队列负载来调整此配置值会比轮询 Redis 数据库来查找新任务更加高效。例如，可以设置该值为 5 来告诉驱动在等待可用队列任务时需要阻塞五秒。

### 2.1 延时

在任务类中设置`$delay`属性可以让该任务自动延时一定时间后再被执行，和在发布任务的时候指定延时时间的功能相同：

```php
/**
 * The number of seconds before the job should be made available.
 *
 * @var \DateTimeInterface|\DateInterval|int|null
 */
public $delay = 3;
```

### 2.2 最大失败次数

该属性指定任务的最大失败次数，也就是该任务可以执行的最大次数：

```php
/**
 * The number of times the job may be attempted.
 *
 * @var int
 */
public $tries = 10;
```

在命令行中执行队列的时候，也可以指定`--tries`参数来设置最大失败次数，但是任务类中的`$tries`属性会比命令行中的优先极高：

```shell
php artisan queue:work --tries=3
```

### 2.3 超时时间

使用`$timeout`属性可以指定该任务可以执行的最长时间，超过该时间之后，将会中断任务的执行，可以避免任务的死锁：

```php
/**
 * The number of seconds the job can run before timing out.
 *
 * @var int
 */
public $timeout = 90;
```

类似的，队列任务最大运行时长（秒）可以通过 Artisan 命令上的`--timeout`开关来指定，并且任务类中的`$timeout`属性有更高优先级：

```shell
php artisan queue:work --timeout=30
```

### 2.4 队列

在任务类中设置`$queue`来指定任务被压入到的队列的名称，和发布任务的时候通过`onQueue()`方法指定队列方式相同：

```php
/**
 * The name of the queue the job should be sent to.
 *
 * @var string|null
 */
public $queue = 'query';
```

如果不指定队列名称，那么会使用 config 中配置的默认队列名称。

建议在 config 中将`queue`配置为项目中用到的全部的队列的名称，而每个任务类都设置其所属的队列。这样，在执行 Artisan 命令开启任务队列的时候，就不需要指定队列的名称，而是自动执行 config 中将`queue`配置的队列了。

## 三、原理

Laravel Queue 底层是使用其他的队列服务作为支撑的，如 数据库、Beanstalkd、Amazon SQS 和 Redis 服务。Laravel 提供不同的驱动封装，使得这些底层服务都可以通过统一的接口来调用。

下面以使用 Redis 服务对 Laravel Queue 进行分析。

### 3.1 任务分发

Laravel 中分发任务可以有同步分发和异步分发，他们的执行逻辑并不相同。

#### 3.1.1 ServiceProvider

在进行原理分析之前，需要先了解任务分发器`Illuminate\Contracts\Bus\Dispatcher`的服务注册。该类是在`Illuminate/Bus/BusServiceProvider`服务提供者中注册的，代码如下：

```php
public function register()
{
    $this->app->singleton('Illuminate\Bus\Dispatcher', function ($app) {
        return new Dispatcher($app, function ($connection = null) use ($app) {
            return $app['Illuminate\Contracts\Queue\Factory']->connection($connection);
        });
    });

    $this->app->alias(
        'Illuminate\Bus\Dispatcher', 'Illuminate\Contracts\Bus\Dispatcher'
    );

    $this->app->alias(
        'Illuminate\Bus\Dispatcher', 'Illuminate\Contracts\Bus\QueueingDispatcher'
    );
}
```

所以 Laravel 中的任务分发器是一个`Illuminate\Bus\Dispatcher`类单例，它的第二个参数(也就是`$queueResolver`)是一个闭包，通过队列服务工厂来解析得到相应的底层队列服务类。

#### 3.1.2 dispatchNow

`dispatchNow()`方法是用于发布同步任务，也就是发布任务之后，会立即执行任务的逻辑。该方法执行的是如下代码：

```php
app(Dispatcher::class)->dispatchNow($job, $handler);
```

所以`dispatchNow()`方法是通过`Illuminate\Bus\Dispatcher::dispatchNow()`方法执行的：

```php
public function dispatchNow($command, $handler = null)
{
    if ($handler || $handler = $this->getCommandHandler($command)) {
        $callback = function ($command) use ($handler) {
            return $handler->handle($command);
        };
    } else {
        $callback = function ($command) {
            return $this->container->call([$command, 'handle']);
        };
    }

    return $this->pipeline->send($command)->through($this->pipes)->then($callback);
}
```

通过查看该方法可以知道，同步分发会经过一些处理之后，直接通过线性 pipe 方式调用任务的`handle`方法，也就是在分发时同步执行了任务逻辑。

#### 3.1.3 dispatch

使用`dispatch($job)`分发任务时，执行的是如下的代码：

```php
app(Dispatcher::class)->dispatch($job);
```

> Lumen 5.8 中`dispatch()`方法是返回一个`Laravel\Lumen\Bus\PendingDispatch`对象，而这个对象的析构方法中则调用了`app(Dispatcher::class)->dispatch($this->job)`语句来完成同样的调用。

所以，分发任务最终是通过`Illuminate\Bus\Dispatcher::dispatch()`方法执行的：

```php
public function dispatch($command)
{
    if ($this->queueResolver && $this->commandShouldBeQueued($command)) {
        return $this->dispatchToQueue($command);
    }

    return $this->dispatchNow($command);
}
```

可以看到：根据是否设置了队列驱动器，以及当前任务是否可以入列(也就是任务对象是否为`Illuminate\Contracts\Queue\ShouldQueue`类的实例)，来决定是将任务压入队列，还是立即执行。如果需要立即执行，那么就和`dispatchNow()`的逻辑相同了。对于异步任务就会通过`dispatchToQueue()`方法将其加入到队列中。

> 这里`$this->queueResolver`就是在服务提供者中设置的一个用于解析队列服务驱动的闭包。

#### 3.1.4 pushCommandToQueue

在`dispatchToQueue()`方法中，如果任务对象中有`queue()`方法就直接调用该方法将任务加入队列，如果没有，则会通过`pushCommandToQueue()`方法将任务加入到队列中：

```php
protected function pushCommandToQueue($queue, $command)
{
    if (isset($command->queue, $command->delay)) {
        return $queue->laterOn($command->queue, $command->delay, $command);
    }

    if (isset($command->queue)) {
        return $queue->pushOn($command->queue, $command);
    }

    if (isset($command->delay)) {
        return $queue->later($command->delay, $command);
    }

    return $queue->push($command);
}
```

可以看到，在`pushCommandToQueue()`方法中，会根据配置的任务的`queue`、`delay`属性来执行不同的操作：`laterOn`、`pushOn`、`later`和`push`。这几个操作都是在底层服务驱动上的，根据名称就可以猜出各个方法的作用。

#### 3.1.5 createPayload

在调用者四个方法都会对任务数据进行自定义的序列化，具体是调用`Illuminate\Queue\Queue::createPayload()`方法。该方法会根据任务数据是否是对象来选择不同的方式序列化。

对于任务对象(也就是开发者编写的 Jobs)，会使用`Illuminate\Queue\Queue::createObjectPayload()`方法进行处理：

```php
protected function createObjectPayload($job, $queue)
{
    $payload = $this->withCreatePayloadHooks($queue, [
        'displayName' => $this->getDisplayName($job),
        'job' => 'Illuminate\Queue\CallQueuedHandler@call',
        'maxTries' => $job->tries ?? null,
        'delay' => $this->getJobRetryDelay($job),
        'timeout' => $job->timeout ?? null,
        'timeoutAt' => $this->getJobExpiration($job),
        'data' => [
            'commandName' => $job,
            'command' => $job,
        ],
    ]);

    return array_merge($payload, [
        'data' => [
            'commandName' => get_class($job),
            'command' => serialize(clone $job),
        ],
    ]);
}
```

处理结果是一个数组，其中包含了任务的延时、重试次数、超时等信息。

需要注意的是，其中包含一个`job`属性，该属性指定了调用任务执行的方法，也就是`Illuminate\Queue\CallQueuedHandler@call`，这个属性后面在执行任务的时候会用到。

#### 3.1.6 RedisQueue

`Illuminate\Queue\RedisQueue`继承于`Illuminate\Queue\Queue`，并实现了`Illuminate\Contracts\Queue\Queue`队列接口，是属于 Laravel Queue 底层服务驱动中的一个。

在 RedisQueue 中，`laterOn`、`pushOn`、`later`、`push`是通过`laterRaw`、`pushRaw`来实现的：

```php
protected function laterRaw($delay, $payload, $queue = null)
{
    $this->getConnection()->zadd(
        $this->getQueue($queue).':delayed', $this->availableAt($delay), $payload
    );

    return json_decode($payload, true)['id'] ?? null;
}

public function pushRaw($payload, $queue = null, array $options = [])
{
    $this->getConnection()->eval(
        LuaScripts::push(), 2, $this->getQueue($queue),
        $this->getQueue($queue).':notify', $payload
    );

    return json_decode($payload, true)['id'] ?? null;
}
```

可以看到，对于需要延时的任务，是通过 Redis 的`zadd`命令将任务数据存放到`{$queue}:delayed`有序集合中，并用预计要执行时间进行排序；而对于没有设置延时的任务，则直接加入到了`{$queue}:notify`链表的尾部。

#### 3.1.7 总结

至此，已经清楚了 Redis 作为队列底层服务时的任务分发过程：

* 对于同步任务，会经过处理之后直接执行任务的`handle()`方法。
* 对于异步任务，会根据相关设置，最终压入到 Redis 中的`{$queue}:delayed`有序集合中或`{$queue}:notify`链表的尾部。

### 3.2 队列执行

将任务分发之后，还需要在终端中执行`php artisan queue:work`命令来启动任务的执行。下面对队列任务的执行原理进行分析。

#### 3.2.1 runWorker

启动命令最终会进入到`Illuminate/Queue/Console/WorkCommand`类中的`runWorker()`方法中：

```php
protected function runWorker($connection, $queue)
{
    $this->worker->setCache($this->laravel['cache']->driver());

    return $this->worker->{$this->option('once') ? 'runNextJob' : 'daemon'}(
        $connection, $queue, $this->gatherWorkerOptions()
    );
}
```

> 这里的`$this->worker`是一个`Illuminate\Queue\Worker`实例。

可以看到，该方法会根据是否传入了`once`来决定是只执行一次，还是作为常驻进程一直存在。下面按照`daemon`方式继续分析。

#### 3.2.2 

`Illuminate\Queue\Worker::daemon()`方法是队列执行的核心，它是一个死循环，会不断的进行队列的监听和任务的执行，代码如下：

```php
public function daemon($connectionName, $queue, WorkerOptions $options)
{
    // 判断是否支持 pcntl，如果支持就是用 pcntl 方式来控制进程，效率更好
    if ($this->supportsAsyncSignals()) {
        $this->listenForSignals();
    }

    // 重启时间，用于在终端中执行 php artisan queue:restart 来更新代码
    $lastRestart = $this->getTimestampOfLastQueueRestart();

    while (true) {
        // 如果处于系统维护期、队列暂停、等待事件循环等原因，则暂停队列一段时间
        if (! $this->daemonShouldRun($options, $connectionName, $queue)) {
            $this->pauseWorker($options, $lastRestart);

            continue;
        }

        // 从队列中获取下一个要执行的任务
        $job = $this->getNextJob(
            $this->manager->connection($connectionName), $queue
        );

        // 如果支持 pcntl，则注册一个任务执行时间超时的处理器，避免任务超时死锁
        if ($this->supportsAsyncSignals()) {
            $this->registerTimeoutHandler($job, $options);
        }

        // 如果获取到了任务，则执行任务，否则会让 worker 休眠一段时间
        if ($job) {
            $this->runJob($job, $connectionName, $options);
        } else {
            $this->sleep($options->sleep);
        }

        // 判断是否需要停止：需要退出、需要重启、内存超限、设置了没有任务就停止而且已经没有任务了
        $this->stopIfNecessary($options, $lastRestart, $job);
    }
}
```

这段代码中，除了一些判断条件之外，最主要的就是`getNextJob()`和`runJob()`两个方法的调用，这两个方法分别用于取出要执行的任务和执行任务。下面分别对其进行分析。

#### 3.2.3 getNextJob

该方法用于从指定的队列中取出一个任务：

```php
protected function getNextJob($connection, $queue)
{
    try {
        foreach (explode(',', $queue) as $queue) {
            if (! is_null($job = $connection->pop($queue))) {
                return $job;
            }
        }
    }
    // ...
}
```

这里的`$connection`参数是一个`\Illuminate\Contracts\Queue\Queue`对象。由于假设使用的是 Redis，那么`$connection`其实就是`Illuminate\Queue\RedisQueue`实例。

```php
public function pop($queue = null)
{
    // 整理已经到可执行期间的延时任务
    $this->migrate($prefixed = $this->getQueue($queue));

    // 获取下一个可执行任务
    if (empty($nextJob = $this->retrieveNextJob($prefixed))) {
        return;
    }

    [$job, $reserved] = $nextJob;

    // 对获取到的任务进行封装之后返回：Illuminate\Queue\Jobs\RedisJob
    if ($reserved) {
        return new RedisJob(
            $this->container, $this, $job,
            $reserved, $this->connectionName, $queue ?: $this->default
        );
    }
}
```

其中，`migrate()`方法是调用 Lua 脚本，将 Redis 中`{$queue}:delayed`和`{$queue}:reserved`集合中的到期任务移动到`{$queue}`链表中，并在`{$queue}:notify`链表中加入数据作为通知。

`retrieveNextJob()`方法通过调用 Lua 脚本，将 Redis 中`{$queue}`链表中的左侧第一个任务推出，并将其`attempts`增加 1 之后，在添加到`{$queue}:reserved`有序集合中，以便后续的处理。同时还会将`{$queue}:notify`链表中左侧第一个数据移除。最后，返回原本的任务数据和更改过`attempts`值的任务数据。

另外，当`retrieveNextJob()`取不到任务时，还会根据条件再次尝试获取。

#### 3.2.4 runJob

`runJob()`方法是对`process()`方法的调用，并处理了一些异常和错误情况，所以主要看`Illuminate/Queue/Worker::process()`的代码：

```php
public function process($connectionName, $job, WorkerOptions $options)
{
    try {
        $this->raiseBeforeJobEvent($connectionName, $job);

        $this->markJobAsFailedIfAlreadyExceedsMaxAttempts(
            $connectionName, $job, (int) $options->maxTries
        );

        $job->fire();

        $this->raiseAfterJobEvent($connectionName, $job);
    } catch (Exception $e) {
        $this->handleJobException($connectionName, $job, $options, $e);
    } catch (Throwable $e) {
        $this->handleJobException(
            $connectionName, $job, $options, new FatalThrowableError($e)
        );
    }
}
```

其中，`$job->fire()`语句会先从序列化的任务数据中解析得到执行任务的 Handler，也就是前面提到的`Illuminate\Queue\CallQueuedHandler@call`，通过执行这个方法，来完成任务逻辑的执行和删除。

`process()`方法对异常和错误都提供了处理，避免因任务类逻辑执行中发生的异常和错误导致队列执行终止，而且会自动将任务加入到队列中，进行后续的重试操作：

```php
// If we catch an exception, we will attempt to release the job back onto the queue
// so it is not lost entirely. This'll let the job be retried at a later time by
// another listener (or this same one). We will re-throw this exception after.
if (! $job->isDeleted() && ! $job->isReleased() && ! $job->hasFailed()) {
    $job->release(
        method_exists($job, 'delaySeconds') && ! is_null($job->delaySeconds())
                    ? $job->delaySeconds()
                    : $options->delay
    );
}
```

#### 3.2.5 总结

通过上面的流程，一个任务就执行完成了，而通过不断的循环可以不断的监听队列，实现队列任务的不断执行。

队列任务的执行依旧是落在底层的队列服务上，通过在`{$queue}`、`{$queue}:reserved`、`{$queue}:delayed`、`{$queue}:notify`这几个队列中操作，可以实现任务数据的延时、重试操作，而且能够保证任务数据不会丢失，其中：

* `{$queue}` 存储处理任务
* `{$queue}:reserved` 存储待处理任务
* `{$queue}:delayed` 存储延迟任务
* `{$queue}:notify` 作为任务处理的标识通知

## 四、其他

Diving 文章：

1. [Introduction To The Queue System](https://divinglaravel.com/introduction-to-the-queue-system)
2. [Preparing Jobs For Queue](https://divinglaravel.com/preparing-jobs-for-queue)
3. [Pushing Jobs To Queue](https://divinglaravel.com/pushing-jobs-to-queue)
4. [Queue Workers: How they work](https://divinglaravel.com/queue-workers-how-they-work)
5. [Conditionally pushing event listeners to queue](https://divinglaravel.com/conditionally-pushing-event-listeners-to-queue)

译文：

1. [Before The Dive](https://github.com/yuansir/diving-laravel-zh/blob/master/The%20Queue%20System/Before%20The%20Dive.md)
2. [Preparing Jobs For Queue](https://github.com/yuansir/diving-laravel-zh/blob/master/The%20Queue%20System/Preparing%20Jobs%20For%20Queue.md)
3. [Pushing Jobs To Queue](https://github.com/yuansir/diving-laravel-zh/blob/master/The%20Queue%20System/Pushing%20Jobs%20To%20Queue.md)
4. [Worker](https://github.com/yuansir/diving-laravel-zh/blob/master/The%20Queue%20System/Worker.md)


