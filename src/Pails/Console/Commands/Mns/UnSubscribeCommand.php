<?php
namespace Pails\Console\Commands\Mns;

use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UnSubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'mns:unsubscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '取消订阅';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicName = trim($this->argument('topic'));
        $name = trim($this->option('name'));

        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');

            return;
        }

        //
        $topic = $client->getTopicRef($topicName);
        try {
            $res = $topic->unsubscribe($name);
            if ($res->isSucceed()) {
                $this->line('取消订阅成功');
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
        ];
    }
}
