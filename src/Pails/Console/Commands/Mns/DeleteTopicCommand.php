<?php
namespace Pails\Console\Commands\Mns;

use AliyunMNS\Client;
use Pails\Console\Command;

class DeleteTopicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mns:delete-topic
                            {name : 主题名称}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除主题Topic';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = trim($this->argument('name'));

        /**
         * @var Client
         */
        $client = $this->getDI()->getMns();
        if (!$client) {
            $this->error("请先配置阿里云MSN服务，并在DI里面注册");
            return;
        }

        try {
            $res = $client->deleteTopic($name);
            if ($res->isSucceed()) {
                $this->line("Topic $name deleted");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
