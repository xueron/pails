<?php
namespace Pails\Queue;

use AliyunMNS\Responses\PeekMessageResponse;
use AliyunMNS\Responses\ReceiveMessageResponse;

/**
 * Class Job
 *
 * @package Pails\Queue\Job
 */
class Job
{
    /**
     * 消息实例，有些队列可能用到
     *
     * @var mixed|ReceiveMessageResponse|PeekMessageResponse
     */
    protected $instance;

    /**
     * 消息所在队列
     *
     * @var Queue
     */
    protected $queue;

    /**
     * Indicates if the job has been deleted.
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
     *
     * @var bool
     */
    protected $released = false;

    /**
     * Indicates if the job has failed.
     *
     * @var bool
     */
    protected $failed = false;

    /**
     * Job constructor.
     *
     * @param $queue
     * @param $instance
     */
    public function __construct($queue, $instance)
    {
        $this->queue = $queue;
        $this->instance = $instance;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->instance->getMessageId();
    }

    /**
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return PeekMessageResponse|ReceiveMessageResponse|mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->instance->getMessageBody();
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->instance->getDequeueCount();
    }

    /**
     * The number of seconds the job can run.
     *
     * @return int|null
     */
    public function timeout()
    {
        return $this->queue->getAttribute()->getVisibilityTimeout();
    }

    /**
     * 删除消息
     */
    public function delete()
    {
        $this->deleted = true;

        return $this->queue->delete($this);
    }

    /**
     * @return mixed
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $delay
     *
     * @return mixed
     */
    public function release(int $delay)
    {
        $this->released = true;

        return $this->queue->release($this, $delay);
    }

    /**
     * Determine if the job was released back into the queue.
     *
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * Determine if the job has been marked as a failure.
     *
     * @return bool
     */
    public function hasFailed()
    {
        return $this->failed;
    }

    /**
     * Mark the job as "failed".
     */
    public function markAsFailed()
    {
        $this->failed = true;
    }
}
