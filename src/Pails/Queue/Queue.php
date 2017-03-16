<?php
namespace Pails\Queue;

use AliyunMNS\Requests\SendMessageRequest;
use Pails\Injectable;

/**
 * Class Queue
 *
 * @package Pails\Queue
 */
class Queue extends Injectable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \AliyunMNS\Queue
     */
    protected $_queue;

    /**
     * Queue constructor.
     *
     * @param string $queueName
     */
    public function __construct(string $queueName)
    {
        $this->name = $queueName;
        $this->_queue = $this->mns->getQueueRef($queueName, false);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * @param Job $job
     *
     * @return bool
     */
    public function delete(Job $job)
    {
        $result = false;
        try {
            $res = $this->_queue->deleteMessage($job->getInstance()->getReceiptHandle());
            $result = $res->isSucceed();
        } catch (\Exception $e) {
            $this->logger->error('删除消息失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }

    /**
     * 发送一个消息
     *
     * @param                 $payload
     * @param ListenerOptions $options
     *
     * @return bool
     */
    public function push($payload, ListenerOptions $options)
    {
        $result = false;
        $request = new SendMessageRequest($payload, $options->delay);
        try {
            $res = $this->_queue->sendMessage($request);
            $result = $res->isSucceed();
        } catch (\Exception $e) {
            $this->logger->error('发送消息失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }

    /**
     * 取出一个消息
     *
     * @param ListenerOptions $options
     *
     * @return Job
     */
    public function pop(ListenerOptions $options)
    {
        $result = null;
        try {
            $res = $this->_queue->receiveMessage($options->timeout);
            if ($res->isSucceed()) {
                $result = new Job($this, $res);
            }
        } catch (\Exception $e) {
            $this->logger->error('获取消息队列失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }

    /**
     * @return Job
     */
    public function peek()
    {
        $result = null;
        try {
            $res = $this->_queue->peekMessage();
            if ($res->isSucceed()) {
                $result = new Job($this, $res);
            }
        } catch (\Exception $e) {
            $this->logger->error('获取消息队列失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }

    /**
     * 设置消息的下次可见时间。处于InActive的消息，通过此方法相当于可以设置Delay时间。
     *
     * @param Job $job
     * @param int $delay
     *
     * @return mixed
     */
    public function release(Job $job, int $delay)
    {
        $result = false;
        $delay = $delay < 1 ? 1 : $delay;
        try {
            $res = $this->_queue->changeMessageVisibility($job->getInstance()->getReceiptHandle(), $delay);
            $result = $res->isSucceed();
        } catch (\Exception $e) {
            $this->logger->error('获取消息队列失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }
}
