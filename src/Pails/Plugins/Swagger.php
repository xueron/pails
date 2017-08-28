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
        $swagger = \Swagger\scan($this->di->path('Http'));
        return $this->response->setContentType('application/json')
            ->setContent($swagger);
    }
}
