<?php

namespace Pails\Queue;

use Pails\Injectable;

abstract class Worker extends Injectable
{
    public function process(Job $job, ListenerOptions $options)
    {
        $res = $this->handle($job, $options);

        // delete handled job if successfully handled
        if ($res !== false && !$job->isDeleted()) {
            $this->logger->debug('Job handled, delete it: ' . $job->getId() . ", Payload=" . $job->getPayload());
            $job->delete();
        }

        return $res;
    }

    public function log($message)
    {
        $this->eventsManager->fire('listener:logger', $this, $message);
    }

    abstract public function handle(Job $job, ListenerOptions $options);
}
