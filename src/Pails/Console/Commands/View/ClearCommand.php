<?php
/**
 * ClearCommand.php
 *
 */


namespace Pails\Console\Commands\View;


use Pails\Console\Command;

class ClearCommand extends Command
{
    protected $name = 'view:clear';

    protected $description = '清空视图缓存';

    public function handle()
    {
        $res = $this->viewCache->flush();
        if ($res) {
            $this->info("缓存已经清空");
        } else {
            $this->info("缓存清理失败，请手工查看。");
        }
    }
}
