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
        $result = $this->client->putObject($this->bucket, $path, $contents);
        if (!is_null($result)) {
            $type = 'file';
            $size = strlen($contents);
            return compact('contents', 'type', 'size', 'path');
        }
        return false;
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
     * @param string $newPath
     *
     * @return bool
     */
    public function rename($path, $newPath)
    {
        if ($this->client->copyObject($this->bucket, $path, $this->bucket, $newPath) !== null) {
            $result = $this->client->deleteObject($this->bucket, $path);
            if (!is_null($result)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newPath
     *
     * @return bool
     */
    public function copy($path, $newPath)
    {
        $result = $this->client->copyObject($this->bucket, $path, $this->bucket, $newPath);
        if (is_null($result)) {
            return false;
        }
        return true;
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
        $result = $this->client->deleteObject($this->bucket, $path);
        if (is_null($result)) {
            return false;
        }

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
        if (substr($dirname, -1 , 1) != '/') {
            $dirname = $dirname . '/';
        }
        $result = $this->client->deleteObject($this->bucket, $dirname);
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
        $result = $this->client->createObjectDir($this->bucket, $dirname);
        if (is_null($result)) {
            return false;
        }

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
        return $this->client->doesObjectExist($this->bucket, $path);
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
        $contents = $this->client->getObject($this->bucket, $path);

        if ($contents != null) {
            $res = ['path' => $path, 'contents' => $contents];
            return $res;
        }
        return false;
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
        $contents = $this->client->getObject($this->bucket, $path);

        if ($contents != null) {
            $stream = fopen('php://temp', 'w+b');
            fwrite($stream, $contents);
            rewind($stream);
            return ['path' => $path, 'stream' => $stream];
        }
        return false;
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
        return $this->normalizeMetaData($path);
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
     * @return array|boolean
     *
     */
    protected function normalizeMetaData($path)
    {
        $meta = $this->client->getObjectMeta($this->bucket, $path);
        if (is_null($meta)) {
            return false;
        }
        return [
            'path'      => $path,
            'timestamp' => strtotime($meta[strtolower('Last-Modified')]),
            'dirname'   => Util::dirname($path),
            'mimetype'  => $meta[strtolower('Content-Type')],
            'size'      => $meta[strtolower('Content-Length')],
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
