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
        $cachePath = $this->di->tmpPath() . '/cache/view/';
        if (!file_exists($cachePath)) {
            $this->info("缓存目录不存在");
        } else {
            foreach (new \DirectoryIterator($cachePath) as $cacheFile) {
                if ($cacheFile->isFile()) {
                    $filePath = $cacheFile->getPathname();
                    @unlink($filePath);
                    $this->line("$filePath deleted");
                }
            }
            $this->info("缓存已经清空");
        }
    }
}
