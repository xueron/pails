<?php
namespace AliyunMNS\Model;

use AliyunMNS\Constants;

class SubscriptionAttributes
{
    private $endpoint;
    private $strategy;
    private $contentFormat;
    private $filterTag;

    # may change in AliyunMNS\Topic
    private $topicName;

    # the following attributes cannot be changed
    private $subscriptionName;
    private $topicOwner;
    private $createTime;
    private $lastModifyTime;

    public function __construct(
        $subscriptionName = null,
        $endpoint = null,
        $strategy = null,
        $contentFormat = null,
        $filterTag = null,
        $topicName = null,
        $topicOwner = null,
        $createTime = null,
        $lastModifyTime = null
    )
    {
        $this->endpoint = $endpoint;
        $this->strategy = $strategy;
        $this->contentFormat = $contentFormat;
        $this->filterTag = $filterTag;
        $this->subscriptionName = $subscriptionName;
        //cloud change in AliyunMNS\Topic
        $this->topicName = $topicName;
        $this->topicOwner = $topicOwner;
        $this->createTime = $createTime;
        $this->lastModifyTime = $lastModifyTime;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    public function getContentFormat()
    {
        return $this->contentFormat;
    }

    public function setContentFormat($contentFormat)
    {
        $this->contentFormat = $contentFormat;
    }

    public function getFilterTag()
    {
        return $this->filterTag;
    }

    public function setFilterTag($filterTag)
    {
        $this->filterTag = $filterTag;
    }

    public function getTopicName()
    {
        return $this->topicName;
    }

    public function setTopicName($topicName)
    {
        $this->topicName = $topicName;
    }

    public function getTopicOwner()
    {
        return $this->topicOwner;
    }

    public function getSubscriptionName()
    {
        return $this->subscriptionName;
    }

    public function getCreateTime()
    {
        return $this->createTime;
    }

    public function getLastModifyTime()
    {
        return $this->lastModifyTime;
    }

    public function writeXML(\XMLWriter $xmlWriter)
    {
        if ($this->endpoint != null) {
            $xmlWriter->writeElement(Constants::ENDPOINT, $this->endpoint);
        }
        if ($this->strategy != null) {
            $xmlWriter->writeElement(Constants::STRATEGY, $this->strategy);
        }
        if ($this->contentFormat != null) {
            $xmlWriter->writeElement(Constants::CONTENT_FORMAT, $this->contentFormat);
        }
        if ($this->filterTag != null) {
            $xmlWriter->writeElement(Constants::FILTER_TAG, $this->filterTag);
        }
    }

    static public function fromXML(\XMLReader $xmlReader)
    {
        $endpoint = null;
        $strategy = null;
        $contentFormat = null;
        $filterTag = null;
        $topicOwner = null;
        $topicName = null;
        $createTime = null;
        $lastModifyTime = null;
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == \XMLReader::ELEMENT) {
                switch ($xmlReader->name) {
                    case 'TopicOwner':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $topicOwner = $xmlReader->value;
                        }
                        break;
                    case 'TopicName':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $topicName = $xmlReader->value;
                        }
                        break;
                    case 'SubscriptionName':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $subscriptionName = $xmlReader->value;
                        }
                        break;
                    case 'Endpoint':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $endpoint = $xmlReader->value;
                        }
                        break;
                    case 'NotifyStrategy':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $strategy = $xmlReader->value;
                        }
                        break;
                    case 'NotifyContentFormat':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $contentFormat = $xmlReader->value;
                        }
                        break;
                    case 'FilterTag':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $filterTag = $xmlReader->value;
                        }
                        break;
                    case 'CreateTime':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $createTime = $xmlReader->value;
                        }
                        break;
                    case 'LastModifyTime':
                        $xmlReader->read();
                        if ($xmlReader->nodeType == \XMLReader::TEXT) {
                            $lastModifyTime = $xmlReader->value;
                        }
                        break;
                }
            }
        }
        $attributes = new SubscriptionAttributes(
            $subscriptionName,
            $endpoint,
            $strategy,
            $contentFormat,
            $filterTag,
            $topicName,
            $topicOwner,
            $createTime,
            $lastModifyTime);

        return $attributes;
    }
}
