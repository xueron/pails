<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Model\TopicAttributes;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\SendMessageRequest;
use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PublishTopicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'mns:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布主题消息';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $message = trim($this->argument('message'));

        $topicName = trim($this->option('topic'));
        $tag = trim($this->option('tag'));

        /**
         * @var Client
         */
        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');
            return;
        }

        //
        $topic = $client->getTopicRef($topicName);
        try {
            $request = new PublishMessageRequest($message, $tag);
            $res = $topic->publishMessage($request);
            if ($res->isSucceed()) {
                $this->line("Topic Message published");
                $this->output->newLine();
                $this->call('mns:list-topic');
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
            ['message', InputArgument::REQUIRED, '消息内容'],
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
            ['topic', null, InputOption::VALUE_REQUIRED, '主题的名称', ''],
            ['tag', null, InputOption::VALUE_OPTIONAL, '过滤标签，可选', null],
        ];
    }
}
