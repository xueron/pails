<?php
/**
 * Topic.
 */
namespace Pails\Queue;

use AliyunMNS\Requests\PublishMessageRequest;
use Pails\Exception;
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
     * @var string
     */
    protected $_contentType;

    /**
     * Topic constructor.
     *
     * @param string $topicName   主题名称
     * @param string $contentType 消息格式，可以是 SIMPLIFIED 或 XML 或 JSON
     */
    public function __construct(string $topicName, $contentType = 'JSON')
    {
        $this->name = $topicName;
        $this->_topic = $this->mns->getTopicRef($topicName);
        $this->_contentType = $contentType;
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

    /**
     * 接收消息，用于写 HTTP Endpoint
     *
     * @return array|bool|\SimpleXMLElement|\stdClass|string
     * @throws \Pails\Exception
     */
    public function receive()
    {
        if ($this->verify($this->getStringToSign(), $this->getSignature(), $this->getPublicKey())) {
            return $this->getData();
        }
        throw Exception::serverException("MSN message verify failed");
    }

    /**
     * @return array|bool|\SimpleXMLElement|\stdClass|string
     */
    private function getData()
    {
        if ($this->_contentType == 'JSON') {
            return $this->request->getJsonRawBody();
        }
        $content = $this->request->getRawBody();
        if ($this->_contentType == 'XML') {
            return new \SimpleXMLElement($content);
        }

        return $content;
    }

    /**
     * @return string
     */
    private function getSignature()
    {
        $signature = $this->request->getHeader('Authorization');
        $this->debug('MNS:signature=' . $signature);

        return $signature;
    }

    /**
     * @return string
     */
    private function getCanonicalizedMNSHeaders()
    {
        $tmpHeaders = [];
        $headers = $this->request->getHeaders();
        foreach ($headers as $key => $value) {
            if (0 === strpos($key, 'x-mns-')) {
                $tmpHeaders[$key] = $value;
            }
        }
        ksort($tmpHeaders);
        $canonicalizedMNSHeaders = implode("\n", array_map(function ($v, $k) {
            return $k . ":" . $v;
        }, $tmpHeaders, array_keys($tmpHeaders)));

        return $canonicalizedMNSHeaders;
    }

    /**
     * @return string
     */
    private function getStringToSign()
    {
        $method = $this->request->getMethod();
        $contentMd5 = $this->request->getHeader('Content-md5');
        $contentType = $this->request->getHeader('Content-Type');
        $date = $this->request->getHeader('Date');
        $canonicalizedResource = $this->request->getURI();
        $canonicalizedMNSHeaders = $this->getCanonicalizedMNSHeaders();
        $stringToSign = strtoupper($method) . "\n" . $contentMd5 . "\n" . $contentType . "\n" . $date . "\n" . $canonicalizedMNSHeaders . "\n" . $canonicalizedResource;
        $this->debug('MNS:stringToSign=' . $stringToSign);

        return $stringToSign;
    }

    /**
     * @return mixed|string
     */
    private function getPublicKey()
    {
        $url = base64_decode($this->request->getHeader('x-mns-signing-cert-url'));
        $this->debug('MNS:publicKeyUrl=' . $url);
        $cacheKey = 'MNS_TOPIC_PUBLIC_KEY_' . md5($url);
        if ($publicKey = $this->cache->get($cacheKey)) {
            return $publicKey;
        }
        $publicKey = (string) $this->httpClient->get($url)->getBody();
        $this->cache->save($cacheKey, $publicKey, $this->config->get('mns.topic_key_cache_ttl', 86400));

        return $publicKey;
    }

    /**
     * @param $data
     * @param $signature
     * @param $publicKey
     *
     * @return bool
     */
    private function verify($data, $signature, $publicKey)
    {
        $res = openssl_get_publickey($publicKey);
        $result = (bool) openssl_verify($data, base64_decode($signature), $res);
        openssl_free_key($res);

        return $result;
    }
}
