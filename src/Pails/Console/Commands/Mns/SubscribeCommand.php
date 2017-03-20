<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Model\SubscriptionAttributes;
use Pails\Console\Command;
use Phalcon\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'mns:subscribe';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订阅一个主题';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicName = trim($this->argument('topic'));
        $name = trim($this->option('name'));
        $endpoint = trim($this->option('endpoint'));
        $filter = trim($this->option('filter'));
        $strategy = trim($this->option('strategy'));
        $contentFormat = trim($this->option('content-format'));

        if (strlen($filter) > 16) {
            throw new \LogicException('filter 长度不可以超过16个字符');
        }
        if (!in_array($strategy, ['EXPONENTIAL_DECAY_RETRY', 'BACKOFF_RETRY'])) {
            throw new \LogicException("strategy 必须是\'EXPONENTIAL_DECAY_RETRY\' 或 \'BACKOFF_RETRY\'");
        }
        if (!in_array($contentFormat, ['SIMPLIFIED', 'XML', 'JSON'])) {
            throw new \LogicException("content-format 必须是\'SIMPLIFIED\' 或 \'XML\' 或 \'JSON\'");
        }

        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');

            return;
        }

        //
        $topic = $client->getTopicRef($topicName);
        if (!Text::startsWith($endpoint, 'http')) {
            $endpoint = $topic->generateQueueEndpoint($endpoint);
        }

        $attrs = new SubscriptionAttributes($name, $endpoint, $strategy, $contentFormat, $filter);
        try {
            $res = $topic->subscribe($attrs);
            if ($res->isSucceed()) {
                $this->line('订阅成功');
                $this->output->newLine();
                $this->call('mns:list-subscription', [
                    'topic' => $topicName,
                ]);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['topic', InputArgument::REQUIRED, '订阅的主题名称'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_REQUIRED, '订阅的名称', ''],
            ['endpoint', null, InputOption::VALUE_REQUIRED, '终端地址，http开头的为HttpEndpoint，其他视为QueueEndpoint', ''],
            ['filter', null, InputOption::VALUE_OPTIONAL, '消息过滤的标签（标签一致的消息才会被推送），不超过16个字符的字符串，默认不进行消息过滤', ''],
            ['strategy', null, InputOption::VALUE_OPTIONAL, '推送消息出现错误时的重试策略，BACKOFF_RETRY 或者 EXPONENTIAL_DECAY_RETRY', 'BACKOFF_RETRY'],
            ['content-format', null, InputOption::VALUE_OPTIONAL, '订阅的名称，推送的消息格式，XML 、JSON 或者 SIMPLIFIED', 'SIMPLIFIED'],
        ];
    }
}
