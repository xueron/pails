<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Model\UpdateSubscriptionAttributes;
use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdateSubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'mns:update-subscription';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新订阅的属性';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicName = trim($this->argument('topic'));
        $name = trim($this->option('name'));
        $strategy = trim($this->option('strategy'));

        if (!in_array($strategy, ['EXPONENTIAL_DECAY_RETRY', 'BACKOFF_RETRY'])) {
            throw new \LogicException("strategy 必须是\'EXPONENTIAL_DECAY_RETRY\' 或 \'BACKOFF_RETRY\'");
        }

        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');
            return;
        }

        //
        $topic = $client->getTopicRef($topicName);

        $attrs = new UpdateSubscriptionAttributes($name);
        $attrs->setStrategy($strategy);

        try {
            $res = $topic->setSubscriptionAttribute($attrs);
            if ($res->isSucceed()) {
                $this->line('订阅属性修改成功');
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
            ['strategy', null, InputOption::VALUE_OPTIONAL, '推送消息出现错误时的重试策略，BACKOFF_RETRY 或者 EXPONENTIAL_DECAY_RETRY', 'BACKOFF_RETRY'],
        ];
    }
}
