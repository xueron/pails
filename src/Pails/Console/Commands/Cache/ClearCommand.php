<?php
/**
 * ClearCommand.php
 *
 */
namespace Pails\Console\Commands\Cache;

use Pails\Console\Command;

class ClearCommand extends Command
{
    protected $name = 'cache:clear';

    protected $description = '清空缓存';

    public function handle()
    {
        $res = $this->cache->flush();
        if ($res) {
            $this->info("缓存已经清空");
        } else {
            $this->info("缓存清理失败，请手工查看。");
        }
    }
}
