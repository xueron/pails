<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Model\TopicAttributes;
use Pails\Console\Command;

class UpdateTopicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:update-topic
                            {name : 主题名称}
                            {--max-size=65536 : 发送到该 Topic 的消息体的最大长度，单位为byte。1024(1KB)-65536（64KB）范围内的某个整数值，默认值为65536（64KB）}
                            {--enable-logging : 是否开启日志管理功能，默认false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '设置主题属性';

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
        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');
            return;
        }
        //
        $topic = $client->getTopicRef($name);

        $topicAttr = new TopicAttributes();
        $topicAttr->setMaximumMessageSize($maxSize);
        $topicAttr->setLoggingEnabled($enableLogging);

        try {
            $res = $topic->setAttribute($topicAttr);
            if ($res->isSucceed()) {
                $this->line("Topic $name Updated");
                $this->output->newLine();
                $this->call('mns:list-topic');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
