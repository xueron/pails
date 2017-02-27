<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Model\TopicAttributes;
use AliyunMNS\Requests\CreateTopicRequest;
use Pails\Console\Command;

class CreateTopicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:create-topic
                            {name : 主题名称}
                            {--max-size=65536 : 发送到该 Topic 的消息体的最大长度，单位为byte。1024(1KB)-65536（64KB）范围内的某个整数值，默认值为65536（64KB）}
                            {--enable-logging : 是否开启日志管理功能，默认false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建主题Topic';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = trim($this->argument('name'));

        $maxSize = trim($this->option('max-size'));
        $enableLogging = trim($this->option('enable-logging'));


        /**
         * @var Client
         */
        $client = $this->getDI()->getMns();
        if (!$client) {
            $this->error("请先配置阿里云MSN服务，并在DI里面注册");
            return;
        }

        $topicAttr = new TopicAttributes($maxSize);
        $topicAttr->setLoggingEnabled($enableLogging);

        $request = new CreateTopicRequest($name, $topicAttr);

        try {
            $res = $client->createTopic($request);
            if ($res->isSucceed()) {
                $this->line("Topic $name Created");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
