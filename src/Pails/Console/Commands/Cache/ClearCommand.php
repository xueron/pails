<?php
/**
 * ClearCommand.php
 *
 */


namespace Pails\Console\Commands\Cache;


use Pails\Console\Command;

class ClearCommand extends Command
{
    protected $signature = 'cache:clear {name? : 需要清空的缓存类型，可选 data | view | route，为空则全部清空}';

    protected $description = '清空缓存';

    public function handle()
    {
        $name = $this->argument('name');
        $allowedTypes = ['data', 'volt', 'routes'];

        if (empty($name)) {
            $types = $allowedTypes;
        } else {
            if (!array_has($allowedTypes, $name)) {
                $this->info("请输入正确的缓存类型");
                return false;
            } else {
                $types = [$name];
            }
        }

        foreach ($types as $target) {
            $cachePath = $this->di->tmpPath() . '/cache/' . $target . '/';
            if (!file_exists($cachePath)) {
                $this->info("缓存目录 $cachePath 不存在");
            } else {
                foreach (new \DirectoryIterator($cachePath) as $cacheFile) {
                    if ($cacheFile->isFile()) {
                        @unlink($cacheFile->getFilename());
                    }
                }
                $this->info("$target 缓存已经清空");
            }
        }
    }
}
