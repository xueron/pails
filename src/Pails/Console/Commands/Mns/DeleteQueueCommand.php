<?php

namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use Pails\Console\Command;

class DeleteQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:delete-queue
                            {name : 队列名称}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除队列Queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = trim($this->argument('name'));

        $confirm = $this->ask('使用者必须谨慎使用此接口，一旦删除队列成功，队列内所有消息也一并删除，不可恢复。确认删除么？[y/N]', 'N');
        if ($confirm !== 'y') {
            $this->line('操作取消');

            return;
        }

        /**
         * @var Client
         */
        $client = $this->mns;
        if (!$client) {
            $this->error('请先配置阿里云MSN服务，并在DI里面注册');

            return;
        }

        try {
            $res = $client->deleteQueue($name);
            if ($res->isSucceed()) {
                $this->line("Queue $name deleted");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
