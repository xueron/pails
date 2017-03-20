<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use Pails\Console\Command;

class ListSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:list-subscription
                            {topic : 主题名称}
                            {--prefix= : 按照该前缀开头的订阅进行查找}
                            {--max=1000 : 单次请求结果的最大返回个数，可以取1-1000范围内的整数值，默认值为1000}
                            {--next= : 请求下一个分页的开始位置，一般从上次分页结果返回的NextMarker获取}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '列出主题的所有订阅Subscriptions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicName = trim($this->argument('topic'));

        $prefix = trim($this->option('prefix'));
        $max = trim($this->option('max'));
        $next = trim($this->option('next'));

        /**
         * @var Client
         */
        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');

            return;
        }

        try {
            $data = [];
            $topic = $client->getTopicRef($topicName);
            $res = $topic->listSubscription($max, $prefix, $next);
            if ($res->isSucceed()) {
                foreach ($res->getSubscriptionNames() as $subscriptionName) {
                    $subscription['name'] = $subscriptionName;

                    $attr = $topic->getSubscriptionAttribute($subscriptionName)->getSubscriptionAttributes();

                    $subscription['endpoint'] = $attr->getEndpoint();
                    $subscription['filterTag'] = $attr->getFilterTag();
                    $subscription['strategy'] = $attr->getStrategy();
                    $subscription['contentFormat'] = $attr->getContentFormat();
                    $subscription['createTime'] = date('Y-m-d H:i:s', $attr->getCreateTime());
                    $subscription['updateTime'] = date('Y-m-d H:i:s', $attr->getLastModifyTime());
                    $data[] = $subscription;
                }
            }


            $subscriptionHeaders = ['Name', 'Endpoint', 'FilterTag', 'Strategy', 'ContentFormat', 'CreateTime', 'UpdateTime'];
            $this->line("List of Subscriptions for Topic: $topicName");
            $this->table($subscriptionHeaders, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
