<?php
/**
 * ClearCacheCommand.php
 */


namespace Pails\Console\Commands\Route;

use Pails\Console\Command;

class ClearCommand extends Command
{
    protected $name = 'route:clear';

    protected $description = '清空路由缓存';

    public function handle()
    {
        $cachePath = $this->di->tmpPath() . '/cache/routes/cache.php';
        if (!file_exists($cachePath)) {
            $this->info('缓存文件不存在');
        } else {
            @unlink($cachePath);
            $this->info('缓存已经清空');
        }
    }
}
