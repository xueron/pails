<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Model\QueueAttributes;
use AliyunMNS\Requests\CreateQueueRequest;
use Pails\Console\Command;

class CreateQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:create-queue
                            {name : 队列名称}
                            {--delay-seconds=0 : 发送到该 Queue 的所有消息默认将以DelaySeconds参数指定的秒数延后可被消费，单位为秒。0~604800内的整数}
                            {--max-size=65536 : 发送到该 Queue 的消息体的最大长度，单位为byte。1024(1KB)-65536（64KB）范围内的某个整数值，默认值为65536（64KB）。}
                            {--ttl=345600 : 消息在该 Queue 中最长的存活时间，单位为秒。60 (1分钟)-1296000 (15 天)范围内某个整数值，默认值345600 (4 天)}
                            {--visibility-timeout=30 : 消息从该 Queue 中取出后从Active状态变成Inactive状态后的持续时间，单位为秒。1-43200(12小时)范围内的某个值整数值，默认为30（秒）}
                            {--pooling-wait=0 : 当 Queue 中没有消息时，针对该 Queue 的 ReceiveMessage 请求最长的等待时间，单位为秒。0-30秒范围内的某个整数值，默认为0（秒）}
                            {--enable-logging : 是否开启日志管理功能，默认false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建队列Queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = trim($this->argument('name'));
        $delaySeconds = trim($this->option('delay-seconds'));
        $ttl = trim($this->option('ttl'));
        $maxSize = trim($this->option('max-size'));
        $visibilityTimeout = trim($this->option('visibility-timeout'));
        $poolingWait = trim($this->option('pooling-wait'));
        $enableLogging = trim($this->option('enable-logging'));


        /**
         * @var Client
         */
        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');

            return;
        }
        $queueAttr = new QueueAttributes($delaySeconds, $maxSize, $ttl, $visibilityTimeout, $poolingWait);
        $queueAttr->setLoggingEnabled($enableLogging);

        $request = new CreateQueueRequest($name, $queueAttr);
        try {
            $res = $client->createQueue($request);
            if ($res->isSucceed()) {
                $this->line("Queue $name created");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
