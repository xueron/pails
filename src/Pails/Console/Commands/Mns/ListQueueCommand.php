<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Requests\ListQueueRequest;
use Pails\Console\Command;

class ListQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:list-queue
                            {--prefix= : 按照该前缀开头的 queueName 进行查找}
                            {--max=1000 : 单次请求结果的最大返回个数，可以取1-1000范围内的整数值，默认值为1000}
                            {--next= : 请求下一个分页的开始位置，一般从上次分页结果返回的NextMarker获取}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '列出所有队列Queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $prefix = trim($this->option('prefix'));
        $max = trim($this->option('max'));
        $next = trim($this->option('next'));

        /**
         * @var Client
         */
        $client = $this->mns;
        if (!$client) {
            $this->error("请先配置阿里云MSN服务，并在DI里面注册");
            return;
        }

        $request = new ListQueueRequest($max, $prefix, $next);
        try {
            $data = [];
            $res = $client->listQueue($request);
            if ($res->isSucceed()) {
                foreach ($res->getQueueNames() as $queueName) {
                    $queue['name'] = $queueName;
                    $queueRef = $client->getQueueRef($queueName);
                    $attr = $queueRef->getAttribute()->getQueueAttributes();

                    $queue['maxSize'] = $attr->getMaximumMessageSize();
                    $queue['ttl'] = $attr->getMessageRetentionPeriod();
                    $queue['poolWait'] = $attr->getPollingWaitSeconds();
                    $queue['VisibilityTimeout'] = $attr->getVisibilityTimeout();
                    $queue['delay'] = $attr->getDelaySeconds();
                    $queue['activeMessages'] = $attr->getActiveMessages();
                    $queue['inactiveMessages'] = $attr->getInactiveMessages();
                    $queue['delayMessages'] = $attr->getDelayMessages();
                    $queue['createTime'] = date("Y-m-d H:i:s", $attr->getCreateTime());
                    $queue['updateTime'] = date("Y-m-d H:i:s", $attr->getLastModifyTime());
                    $queue['loggingEnabled'] = $attr->getLoggingEnabled() ? "Y" : "N";
                    $data[] = $queue;
                }
            }
            $queueHeaders = ['Name', 'MaxSize', 'TTL', 'PoolWait', 'VisibilityTimeout', 'DelaySeconds', 'Active', "Inactive", "Delayed", "CreateTime", "UpdateTime", "EnableLog"];
            $this->line("List of Queues:");
            $this->table($queueHeaders, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
