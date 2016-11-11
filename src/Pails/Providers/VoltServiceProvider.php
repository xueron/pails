<?php
/**
 * VoltServiceProvider.php
 *
 */
namespace Pails\Providers;

use Pails\Plugins\VoltExtension;
use Phalcon\Mvc\View\Engine\Volt;

class VoltServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'volt';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            function () {
                $compiledPath = $this->tmpPath() . '/cache/volt/';
                mkdir($compiledPath, 0755, true);

                $volt = new Volt($this->get('view'));
                $volt->setOptions([
                    'compiledPath' => $compiledPath,
                    'compiledSeparator' => '_',
                    'compileAlways' => true
                ]);

                $volt->getCompiler()->addExtension(new VoltExtension());

                return $volt;
            }
        );
    }
}
