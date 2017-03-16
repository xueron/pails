<?php
/**
 * Topic.
 */
namespace Pails\Queue;

use AliyunMNS\Requests\PublishMessageRequest;
use Pails\Injectable;

/**
 * Class Topic
 *
 * @package Pails\Queue
 */
class Topic extends Injectable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \AliyunMNS\Topic
     */
    protected $_topic;

    /**
     * Topic constructor.
     *
     * @param string $topicName
     */
    public function __construct(string $topicName)
    {
        $this->name = $topicName;
        $this->_topic = $this->mns->getTopicRef($topicName);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取主题实例
     *
     * @return mixed
     */
    public function getTopic()
    {
        return $this->_topic;
    }

    /**
     * 发送消息
     *
     * @param      $message
     * @param null $tag
     *
     * @return bool
     */
    public function publish($message, $tag = null)
    {
        $result = false;
        $request = new PublishMessageRequest($message, $tag);
        try {
            $res = $this->_topic->publishMessage($request);
            return $res->isSucceed();
        } catch (\Exception $e) {
            $this->logger->error('发送消息失败：' . $e->getMessage());
        } finally {
            return $result;
        }
    }
}
