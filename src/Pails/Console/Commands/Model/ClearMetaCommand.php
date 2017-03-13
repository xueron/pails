<?php
namespace Pails\Console\Commands\Model;

use Pails\Console\Command;

class ClearMetaCommand extends Command
{
    protected $name = 'model:clear-meta';

    protected $description = '清空Model元数据缓存';

    public function handle()
    {
        $cachePath = $this->di->tmpPath() . '/cache/metadata/';
        if (!file_exists($cachePath)) {
            $this->info('缓存目录不存在');
        } else {
            foreach (new \DirectoryIterator($cachePath) as $cacheFile) {
                if ($cacheFile->isFile()) {
                    $filePath = $cacheFile->getPathname();
                    @unlink($filePath);
                    $this->line("$filePath deleted");
                }
            }
            $this->info('缓存已经清空');
        }
    }
}
