<?php
namespace Pails\Console\Commands\Model;

use DirectoryIterator;
use Pails\Console\Command;
use Phalcon\Text;

class ListCommand extends Command
{
    protected $name = 'model:list';

    protected $description = '列出所有Model';

    public function handle()
    {
        $iterator = new DirectoryIterator($this->di->path('Models'));
        $data = [];
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && Text::endsWith($fileinfo->getFilename(), '.php') && ($fileinfo->getFilename() != 'ModelBase.php')) {
                $modelName = substr($fileinfo->getFilename(), 0, -4);
                $modelClass = 'App\\Models\\' . $modelName;
                if (class_exists($modelClass)) {
                    $modelInstance = new $modelClass();
                    if ($modelInstance instanceof \Phalcon\Mvc\Model) {
                        $modelTable = $modelInstance->getSource();

                        $row['model'] = $modelName;
                        $row['class'] = $modelClass;
                        $row['table'] = $modelTable;
                        $data[] = $row;
                    }
                }
            }
        }
        $headers = ['ModelName', 'ModelClass', 'Table'];
        $this->table($headers, $data);
    }
}
