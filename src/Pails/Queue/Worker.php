<?php
namespace Pails\Queue;

use Pails\Injectable;

abstract class Worker extends Injectable
{
    public function __construct()
    {
        $this->setEventsManager($this->di->getEventsManager());
    }

    public function process(Job $job, ListenerOptions $options)
    {
        $res = $this->handle($job, $options);

        // delete handled job if successfully handled
        if ($res !== false && !$job->isDeleted()) {
            $job->delete();
        }

        return $res;
    }

    abstract public function handle(Job $job, ListenerOptions $options);
}
