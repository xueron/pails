<?php
/**
 * Swagger.php
 *
 */

namespace Pails\Plugins;

use Pails\Injectable;

class Swagger extends Injectable
{
    public function showApi()
    {
        $swagger = \Swagger\scan($this->di->path('Http/Controllers'));
        return $this->response->setContentType('application/json')
            ->setContent($swagger);
    }
}
