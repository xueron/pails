<?php
namespace Pails\Plugins;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use League\Flysystem\Util;
use OSS\OSSClient;

class AliOSS extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $bucket;

    /**
     * @var OSSClient $client
     */
    protected $client;

    /**
     * @param $bucket
     * @param OSSClient $client
     */
    public function __construct($bucket, OSSClient $client)
    {
        $this->bucket = $bucket;
        $this->client = $client;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config = null)
    {
        $result = $this->client->putObject([
            "Bucket" => $this->bucket,
            "Key" => $path,
            "Content" => $contents,
            "ContentLength" => strlen($contents)
        ]);
        return $result;
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config = null)
    {
        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config = null)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config = null)
    {
        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $this->client->copyObject([
            'SourceBucket' => $this->bucket,
            'SourceKey' => $path,
            'DestBucket' => $this->bucket,
            'DestKey' => $newpath
        ]);

        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $path
        ]);

        return true;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        return $this->client->copyObject([
            'SourceBucket' => $this->bucket,
            'SourceKey' => $path,
            'DestBucket' => $this->bucket,
            'DestKey' => $newpath
        ]);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $path
        ]);
        return true;
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $dirname
        ]);
        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config = null)
    {
        // 阿里云没有实际文件夹的概念，Object/ 作为虚拟文件夹创建，长度为0
        if (substr($dirname, -1) != '/') {
            $path = $dirname . '/';
        } else {
            $path = $dirname;
        }

        $this->client->putObject([
            "Bucket" => $this->bucket,
            "Key" => $path,
            "Content" => "",
            "ContentLength" => 0
        ]);

        return ['path' => $dirname, 'type' => 'dir'];
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        return ['visibility' => true];
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        if ($this->client->getObjectMetadata([
            "Bucket" => $this->bucket,
            "Key" => $path
        ])) {
            return true;
        }
        return false;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        /* @var $object OSSObject */
        $object = $this->client->getObject([
            "Bucket" => $this->bucket,
            "Key" => $path
        ]);

        return $this->normalizeMetaData($path, $object) + ['contents' => stream_get_contents($object->getObjectContent())];
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $object = $this->client->getObject([
            "Bucket" => $this->bucket,
            "Key" => $path
        ]);

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, stream_get_contents($object->getObjectContent()));
        rewind($stream);

        return $this->normalizeMetaData($path, $object) + ['stream' => $stream];
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        $objectMeta = $this->client->getObjectMetadata([
            "Bucket" => $this->bucket,
            "Key" => $path
        ]);

        return $this->normalizeMetaData($path, $objectMeta);
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        return ['visibility' => true];
    }

    /**
     * Builds the normalized output array from a object.
     *
     * @param string $path
     * @param OSSObject $object
     * @return array
     * @internal param BlobProperties $properties
     *
     */
    protected function normalizeMetaData($path, OSSObject $object)
    {
        return [
            'path'      => $path,
            'timestamp' => (int) $object->getLastModified()->format('U'),
            'dirname'   => Util::dirname($path),
            'mimetype'  => $object->getContentType(),
            'size'      => $object->getContentLength(),
            'type'      => 'file',
        ];
    }

    /**
     * Builds the normalized output array.
     *
     * @param string $path
     * @param int    $timestamp
     * @param mixed  $content
     *
     * @return array
     */
    protected function normalize($path, $timestamp, $content = null)
    {
        $data = [
            'path'      => $path,
            'timestamp' => (int) $timestamp,
            'dirname'   => Util::dirname($path),
            'type'      => 'file',
        ];

        if (is_string($content)) {
            $data['contents'] = $content;
        }

        return $data;
    }
}