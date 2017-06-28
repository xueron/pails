<?php
/**
 * Db.php
 *
 */

namespace Pails\Listeners;

use Pails\Injectable;

class Db extends Injectable
{
    public function beforeQuery($event, $db)
    {

    }

    public function afterQuery($event, $db)
    {

    }

    public function beginTransaction($event, $db)
    {

    }

    public function createSavepoint($event, $db, $savepointName)
    {

    }

    public function rollbackTransaction($event, $db)
    {

    }

    public function rollbackSavepoint($event, $db, $savepointName)
    {

    }

    public function commitTransaction($event, $db)
    {

    }

    public function releaseSavepoint($event, $db, $savepointName)
    {

    }
}
