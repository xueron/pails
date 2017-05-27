<?php

namespace Pails\Queue;

use AliyunMNS\Exception\MessageNotExistException;
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
     * @var \AliyunMNS\Model\QueueAttributes;
     */
    protected $attr;

    /**
     * Queue constructor.
     *
     * @param string $queueName
     */
    public function __construct(string $queueName)
    {
        $this->name = $queueName;
        $this->_queue = $this->mns->getQueueRef($queueName, false);
        $this->attr = $this->_queue->getAttribute()->getQueueAttributes();
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
     * @return \AliyunMNS\Model\QueueAttributes|null
     */
    public function getAttribute()
    {
        return $this->attr;
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
            $this->eventsManager->fire('listener:logger', $this, '删除Queue消息失败:' . $e->getMessage());
        } finally {
            return $result;
        }
    }

    /**
     * 发送一个消息
     *
     * @param string $payload 消息正文, UTF-8字符集
     * @param int    $delay   DelaySeconds 指定的秒数延后可被消费，单位为秒,0-604800秒（7天）范围内某个整数值，默认值为0
     * @param int    $pri     指定消息的优先级权值，优先级越高的消息，越容易更早被消费.取值范围1~16（其中1为最高优先级），默认优先级为8
     *
     * @return bool
     */
    public function push($payload, $delay = 0, $pri = 8)
    {
        $result = false;
        $request = new SendMessageRequest($payload, $delay, $pri);
        try {
            $res = $this->_queue->sendMessage($request);
            $result = $res->isSucceed();
        } catch (\Exception $e) {
            $this->eventsManager->fire('listener:logger', $this, '发送Queue消息失败:' . $e->getMessage());
        } finally {
            return $result;
        }
    }

    /**
     * 取出一个消息
     *
     * @param null|int $waitSeconds 本次 ReceiveMessage 请求最长的Polling等待时间，单位为秒。null则使用队列默认的PollingWait参数
     *
     * @return \Pails\Queue\Job
     */
    public function pop($waitSeconds = null)
    {
        $result = null;
        try {
            $res = $this->_queue->receiveMessage($waitSeconds);
            if ($res->isSucceed()) {
                $result = new Job($this, $res);
            }
        } catch (MessageNotExistException $e) {
            $this->eventsManager->fire('listener:logger', $this, '暂时没有消息');
        } catch (\Exception $e) {
            $this->eventsManager->fire('listener:logger', $this, '获取Queue消息失败：' . $e->getMessage());
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
        } catch (MessageNotExistException $e) {
            $this->eventsManager->fire('listener:logger', $this, '暂时没有消息');
        } catch (\Exception $e) {
            $this->eventsManager->fire('listener:logger', $this, '获取Queue消息失败：' . $e->getMessage());
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
            $this->eventsManager->fire('listener:logger', $this, '释放Queue消息失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }
}
