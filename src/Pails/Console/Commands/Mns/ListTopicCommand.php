<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Requests\ListTopicRequest;
use Pails\Console\Command;

class ListTopicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:list-topic
                            {--prefix= : 按照该前缀开头的 queueName 进行查找}
                            {--max=1000 : 单次请求结果的最大返回个数，可以取1-1000范围内的整数值，默认值为1000}
                            {--next= : 请求下一个分页的开始位置，一般从上次分页结果返回的NextMarker获取}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '列出所有主题Topic';

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

        $request = new ListTopicRequest($max, $prefix, $next);
        try {
            $data = [];
            $res = $client->listTopic($request);
            if ($res->isSucceed()) {
                foreach ($res->getTopicNames() as $topicName) {
                    $topic['name'] = $topicName;
                    $topicRef = $client->getTopicRef($topicName);
                    $attr = $topicRef->getAttribute()->getTopicAttributes();

                    $topic['maxSize'] = $attr->getMaximumMessageSize();
                    $topic['ttl'] = $attr->getMessageRetentionPeriod();
                    $topic['createTime'] = date("Y-m-d H:i:s", $attr->getCreateTime());
                    $topic['updateTime'] = date("Y-m-d H:i:s", $attr->getLastModifyTime());
                    $topic['loggingEnabled'] = $attr->getLoggingEnabled() ? "Y" : "N";
                    $data[] = $topic;
                }
            }


            $topicHeaders = ['Name', 'MaxSize', 'TTL', "CreateTime", "UpdateTime", "EnableLog"];
            $this->line("List of Topics:");
            $this->table($topicHeaders, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
