<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Requests\ListQueueRequest;
use Pails\Console\Command;

class SubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:subscribe
                            {topic : 主题的名称}
                            {--name= : 订阅的名称}
                            {--endpoint= : 终端地址，http开头的为HttpEndpoint，其他视为QueueEndpoint}
                            {--filter= : 消息过滤的标签（标签一致的消息才会被推送），不超过16个字符的字符串，默认不进行消息过滤}
                            {--strategy=BACKOFF_RETRY : 推送消息出现错误时的重试策略，BACKOFF_RETRY 或者 EXPONENTIAL_DECAY_RETRY}
                            {--content-format=JSON : 推送的消息格式，XML 、JSON 或者 SIMPLIFIED}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个订阅';

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
        $client = $this->getDI()->getMns();
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
