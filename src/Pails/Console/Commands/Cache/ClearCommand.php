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
        $cachePath = $this->di->tmpPath() . '/cache/data/';
        if (!file_exists($cachePath)) {
            $this->info("缓存目录不存在");
        } else {
            foreach (new \DirectoryIterator($cachePath) as $cacheFile) {
                if ($cacheFile->isFile()) {
                    @unlink($cacheFile->getFilename());
                }
            }
            $this->info("缓存已经清空");
        }
    }
}
