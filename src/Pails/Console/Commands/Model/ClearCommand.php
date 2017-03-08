<?php
namespace Pails\Console\Commands\Model;

use Pails\Console\Command;

class ClearCommand extends Command
{
    protected $name = 'model:clear';

    protected $description = '清空Model数据缓存';

    public function handle()
    {
        $res = $this->modelsCache->flush();
        if ($res) {
            $this->info("缓存已经清空");
        } else {
            $this->info("缓存清理失败，请手工查看。");
        }
    }
}
