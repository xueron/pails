<?php
namespace Pails\Console\Commands\Queue;

use Pails\Console\Command;
use Pails\Queue\Job;
use Pails\Queue\Listener;
use Pails\Queue\ListenerOptions;

class ListenCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:listen
                            {queue : 队列名称}
                            {--once : 仅处理下一个收到的消息}
                            {--force : 尝试强制运行}
                            {--tries=0 : 尝试处理的次数，如果一个消息已经被取出超过该次数，则该消息将被当作失败消息记录，并从队列删除}
                            {--delay=0 : 失败的消息重新投入队列的延迟时间，默认是处理失败立刻重新可用。单位：秒}
                            {--memory=128 : 最大内存使用，单位：MB}
                            {--sleep=3 : 没有新消息时最大等待时间。单位：秒}
                            {--timeout=30 : 消息处理子进程超时时间，超过该时间，消息将重新放入队列。单位：秒}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '监听消息队列处理程序';

    /**
     * The queue listener instance.
     *
     * @var \Pails\Queue\Listener
     */
    protected $listener;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = trim($this->argument('queue'));

        $this->listener = $this->di->get(Listener::class, [$queue]);

        $this->listenForEvents();

        $this->runWorker();
    }

    /**
     * Run the worker instance.
     *
     * @return array
     */
    protected function runWorker()
    {
        return $this->listener->{$this->option('once') ? 'runNextJob' : 'daemon'}($this->gatherListenerOptions());
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Pails\Queue\ListenerOptions
     */
    protected function gatherListenerOptions()
    {
        return new ListenerOptions(
            $this->option('delay'), $this->option('memory'),
            $this->option('timeout'), $this->option('sleep'),
            $this->option('tries'), $this->option('force')
        );
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * 可以通过挂载独立的事件监听方法处理这些事件
     */
    protected function listenForEvents()
    {
        // -- Listener的事件
        //listener:beforeDaemonLoop
        //listener:beforeGetJob
        //listener:afterGetJob
        //listener:stop
        //listener:afterDaemonLoop

        // -- Worker处理的事件
        //worker:beforeJobHandle
        //worker:afterJobHandle
        //worker:jobException
        //worker:jobFailed
    }

    /**
     * Write the status output for the queue worker.
     *
     * @param string $payload
     * @param bool   $failed
     */
    protected function writeOutput($payload, $failed)
    {
        if ($failed) {
            $this->output->writeln('<error>[' . date('Y-m-d H:i:s') . '] Failed:</error> ' . $payload);
        } else {
            $this->output->writeln('<info>[' . date('Y-m-d H:i:s') . '] Processed:</info> ' . $payload);
        }
    }

    /**
     * Store a failed job event.
     *
     * @param Job $job
     *
     * @internal param $payload
     */
    protected function logFailedJob(Job $job)
    {
        //$this->logger->error($event->connectionName, $event->job->getQueue(), $event->job->getRawBody(), $event->exception);
    }
}
