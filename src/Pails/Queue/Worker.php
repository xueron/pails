<?php

namespace Pails\Queue;

use Pails\Injectable;

abstract class Worker extends Injectable
{
    /**
     * @param \Pails\Queue\Job             $job
     * @param \Pails\Queue\ListenerOptions $options
     *
     * @return mixed
     */
    public function process(Job $job, ListenerOptions $options)
    {
        $this->logger->debug('[' . get_class($this) . '] MessageId=' . $job->getId() . ', Payload=' . $job->getPayload());

        $res = $this->handle($job, $options);

        $this->logger->debug('[' . get_class($this) . '] MessageId=' . $job->getId() . ', Result=' . serialize($res));

        // delete handled job if successfully handled
        if ($res !== false && !$job->isDeleted()) {
            $job->delete();
        }

        return $res;
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $this->eventsManager->fire('listener:logger', $this, $message);
    }

    /**
     * @param \Pails\Queue\Job             $job
     * @param \Pails\Queue\ListenerOptions $options
     *
     * @return mixed
     */
    abstract public function handle(Job $job, ListenerOptions $options);
}
