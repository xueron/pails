<?php

namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use AliyunMNS\Requests\SendMessageRequest;
use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SendQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'mns:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布队列消息';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $message = trim($this->argument('message'));

        $queueName = trim($this->option('queue'));
        $delay = trim($this->option('delay'));
        $pri = trim($this->option('pri'));

        /**
         * @var Client
         */
        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');

            return;
        }

        //
        $queue = $client->getQueueRef($queueName, false);
        try {
            $request = new SendMessageRequest($message, $delay, $pri);
            $res = $queue->sendMessage($request);
            if ($res->isSucceed()) {
                $this->line('Queue Message send');
                $this->output->newLine();
                $this->call('mns:list-queue');
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
            ['queue', null, InputOption::VALUE_REQUIRED, '队列的名称', null],
            ['delay', null, InputOption::VALUE_OPTIONAL, '消息延迟时间，指定的秒数延后可被消费，单位为秒,0-604800秒（7天）范围内某个整数值，默认值为0', 0],
            ['pri', null, InputOption::VALUE_OPTIONAL, '消息优先级，优先级越高的消息，越容易更早被消费.取值范围1~16（其中1为最高优先级），默认优先级为8', 8],
        ];
    }
}
