阿里云消息服务使用说明
===================

配置文件
-------
消息队列的配置文件是 ~/config/mns.php，完整的配置文件如下：

.. code-block:: php

    <?php
    /**
     * 阿里云MSN消息服务的配置。
     */
    return [
        'development' => [
            /*
             * 是否启用消息服务
             */
            'enable'    => false,
            /*
             * 端点
             */
            'endpoint'  => '',
            /*
             * accessId
             */
            'accessId'  => '',
            /*
             * accessKey
             */
            'accessKey' => '',
        ],
        'testing' => [
            'enable'    => false,
            'endpoint'  => '',
            'accessId'  => '',
            'accessKey' => '',
        ],
        'production' => [
            'enable'    => false,
            'endpoint'  => '',
            'accessId'  => '',
            'accessKey' => '',
        ],
    ];

命令行工具
--------
Pails里面配置好消息服务后，可以通过命令行工具完成消息服务的管理，可用的命令行工具有：

*  mns:create-queue         创建队列Queue
*  mns:create-topic         创建主题Topic
*  mns:delete-queue         删除队列Queue
*  mns:delete-topic         删除主题Topic
*  mns:list-queue           列出所有队列Queue
*  mns:list-subscription    列出主题的所有订阅Subscriptions
*  mns:list-topic           列出所有主题Topic
*  mns:publish              发布主题消息
*  mns:send                 发布队列消息
*  mns:subscribe            订阅一个主题
*  mns:unsubscribe          取消订阅
*  mns:update-queue         设置队列属性
*  mns:update-subscription  更新订阅的属性
*  mns:update-topic         设置主题属性

创建一个HttpEndpoint
-------------------
首先创建一个控制器，配置好路由信息。

其次创建一个方法，在方法内，通过DI获取到相应的主题，监听即可。

.. code-block:: php

    /**
     * @Route("/all")
     */
    public function allAction()
    {
        $this->logger->info('--------------------- from all action ----------------------');
        $topic = $this->di->get('topic', ['orders', 'XML']);
        $data = $topic->receive();
        $this->logger->info(json_encode($data));
        return $this->apiResponse->withSuccess();
    }

创建Worker
---------
Worker是用来处理消息队列中消息的工作程序。一个队列只能写一个处理程序（Queue本来就是一对一消费的）。但是一个Worker可以同时开启多个进程，来并发的处理。

.. code-block:: shell

    # php pails make:worker [--queue QUEUE] [--] <name>


其中：
* --queue 指定监听的队列名称
* name 是Worker程序的名称。比如 OrdersQueue。

这个命令会在 /app/Workers 目录下生产 OrdersQueueWorker 类，在这个里面完成业务逻辑程序。

Worker 要实现 handle 方法。

handle方法如果返回false，或者运行抛出异常，消息将会在超时后重新进入队列成可见状态，会被重复消费。
否则消息成功处理之后，将会被自动删除。

.. code-block:: php

     public function handle(Job $job, ListenerOptions $options)
     {
         $this->log("Job id: " . $job->getId());
         $this->log("Job data: " . $job->getPayload());
         $this->log("Job timeout: " . $job->timeout());
         $this->log("Job attempt: " . $job->attempts());
         $this->log("Job nexttime: " . $job->getInstance()->getNextVisibleTime());
         $this->log("Now: " . microtime(1));
         //throw new \RuntimeException("exception");
         $i = 2;
         while ($i > 0) {
             $this->log("Woring: " . time());
             sleep(1);
             $i --;
         }
     }

运行Worker
---------
.. code-block:: shell

    # php pails queue:listen [--once] [--force] [--tries [TRIES]] [--delay [DELAY]] [--memory [MEMORY]] [--sleep [SLEEP]] [--timeout [TIMEOUT]] [--] <queue>

其中：
* queue : 队列名称
* --once : 仅处理下一个收到的消息
* --force : 尝试强制运行
* --tries=0 : 尝试处理的次数，如果一个消息已经被取出超过该次数，则该消息将被当作失败消息记录，并从队列删除
* --delay=0 : 失败的消息重新投入队列的延迟时间，默认是处理失败立刻重新可用。单位：秒
* --memory=128 : 最大内存使用，单位：MB
* --sleep=30 : 当 Queue 中没有消息时，针对该 Queue 的 ReceiveMessage 请求最长的等待时间，0-30秒范围内的某个整数值，默认为30秒
* --timeout=30 : 消息处理子进程超时时间，超过该时间，消息将重新放入队列。这个时间要小于队列的VisibilityTimeout时间。单位：秒

