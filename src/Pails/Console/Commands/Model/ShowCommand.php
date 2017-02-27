<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Pails\Console\Commands\Model;

use DirectoryIterator;
use Pails\Console\Command;
use Phalcon\Db\Column;
use Phalcon\Text;
use Symfony\Component\Console\Input\InputArgument;

class ShowCommand extends Command
{
    protected $name = 'model:show';

    protected $description = '列出Model的详细信息';

    public function handle()
    {
        $name = trim($this->argument('name'));

        $modelClass = 'App\\Models\\' . $name;

        if (class_exists($modelClass)) {
            $modelInstance = new $modelClass();
            if ($modelInstance instanceof \Phalcon\Mvc\Model) {
                $modelSchema = $modelInstance->getSchema();
                $modelTable = $modelInstance->getSource();

                $db = $modelInstance->getReadConnection();

                // keys
                $pri = $uni = $uniNull = $mulNull = $mul = [];

                $indices = $db->describeIndexes($modelTable, $modelSchema);
                $indexData = [];
                foreach ($indices as $index) {
                    $indexColumns = $index->getColumns();
                    $indexType = $index->getType();
                    if ($indexType == 'PRIMARY') {
                        $pri = array_unique(array_merge($pri, $indexColumns));
                    } else if ($indexType == 'UNIQUE') {
                        if (count($indexColumns) == 1) {
                            $uni[] = $indexColumns[0];
                        } else {
                            $mulNull = array_unique(array_merge($mulNull, $indexColumns));
                        }
                    } else {
                        $mul[] = $indexColumns[0];
                    }

                    $row = [];
                    $row['name'] = $index->getName();
                    $row['type'] = $index->getType();
                    $row['columns'] = implode(', ', $index->getColumns());
                    $indexData[] = $row;
                }

                $columns = $db->describeColumns($modelTable, $modelSchema);
                $columnData = [];
                foreach ($columns as $column) {
                    $row = [];
                    $row['field'] = $column->getName();
                    $row['type'] = $this->getTypeName($column->getType());
                    $size = $column->getSize();
                    $scale = $column->getScale();
                    if ($size) {
                        if ($scale) {
                            $row['type'] = $row['type'] . '(' . $size . ',' . $scale . ')';
                        } else {
                            $row['type'] = $row['type'] . '(' . $size . ')';
                        }
                    }
                    $row['null'] = $column->isNotNull() ? 'NO' : 'YES';
                    $row['key'] = '';
                    if (in_array($row['field'], $pri)) {
                        $row['key'] = 'PRI';
                    } elseif (in_array($row['field'], $uni)) {
                        $row['key'] = 'UNI';
                    } elseif (in_array($row['field'], $mulNull) && !$column->isNotNull()) {
                        $row['key'] = 'MUL';
                    } elseif (in_array($row['field'], $mul)) {
                        $row['key'] = 'MUL';
                    }

                    $row['default'] = $column->getDefault() ?: 'NULL';
                    $row['extra'] = $column->isAutoIncrement() ? 'auto_increment' : '';
                    $columnData[] = $row;
                }

                // output
                $columnHeaders = ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'];
                $indexHeaders  = ['Name', 'Type', 'Columns'];

                $this->line("Columns of $modelTable");
                $this->table($columnHeaders, $columnData);
                $this->output->newLine();
                $this->line("Indices of $modelTable");
                $this->table($indexHeaders, $indexData);
            }
        } else {
            $this->error("$modelClass 不存在");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Model的名称（类名，不需要 \'App\\Models\\\' 前缀）'],
        ];
    }

    public function getTypeName($type)
    {
        switch ($type) {
            case Column::TYPE_INTEGER:
                return 'int'; break;
            case Column::TYPE_BIGINTEGER:
                return 'int'; break;
            case Column::TYPE_DECIMAL:
                return 'decimal'; break;
            case Column::TYPE_FLOAT:
                return 'float'; break;
            case Column::TYPE_DATE:
                return 'date'; break;
            case Column::TYPE_DATETIME:
                return 'datetime'; break;
            case Column::TYPE_VARCHAR:
                return 'varchar'; break;
            case Column::TYPE_CHAR:
                return 'char'; break;
            case Column::TYPE_TEXT:
                return 'text'; break;
            default:
                return 'string';
        }
    }
}
