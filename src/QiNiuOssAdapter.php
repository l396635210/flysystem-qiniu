<?php

namespace Liz\Flysystem\QiNiu;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class QiNiuOssAdapter extends AbstractAdapter
{
    private $client;
    private $auth;
    private $bucket;
    private $host;

    private $bucketManager;
    private $uploadManager;
    private $fopManager;

    /**
     * @return string
     */
    protected function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * QiNiuOssAdapter constructor.
     *
     * @param string $cdnHost
     * @param string $bucket
     * @param string $accessKey
     * @param string $secretKey
     */
    public function __construct($accessKey, $secretKey, $bucket, $cdnHost)
    {
        $this->host = strripos($cdnHost, '/') + 1 === strlen($cdnHost) ? $cdnHost : $cdnHost.'/';
        $this->bucket = $bucket;
        $this->auth = new Auth($accessKey, $secretKey);
        $this->client = new Client();
    }

    /**
     * @return BucketManager
     */
    protected function getBucketManager()
    {
        if (!$this->bucketManager) {
            $this->bucketManager = new BucketManager($this->auth);
        }
        return $this->bucketManager;
    }

    /**
     * @return UploadManager
     */
    protected function getUploadManager()
    {
        if (!$this->uploadManager) {
            $this->uploadManager = new UploadManager();
        }
        return $this->uploadManager;
    }

    protected function getFopManager()
    {
        if (!$this->fopManager) {
            $this->fopManager = new PersistentFop($this->auth);
        }
        return $this->fopManager;
    }

    /**
     * @param null|Error $error
     */
    protected function createExceptionIfError($error = null)
    {
        if ($error instanceof Error) {
            $e = new QiNiuOssAdapterException($error->message(), $error->code());
            throw $e->setResponse($error->getResponse());
        }
    }

    /**
     * @param array $response
     *
     * @throws QiNiuOssAdapterException
     */
    protected function ossResponse(array &$response)
    {
        if ($response[1] instanceof Error) {
            $error = $response['1'];
            $this->createExceptionIfError($error);
        }
        $response = $response[0];
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function urlEncode($path)
    {
        return strtr($path, [
            ' ' => '%20',
        ]);
    }

    /**
     * @param string $path
     * @param array  $normalized
     *
     * @return array
     *
     * @throws QiNiuOssAdapterException
     */
    protected function getFileMeta($path, array $normalized)
    {
        $response = $this->getBucketManager()->stat($this->bucket, $path);
        $this->ossResponse($response);

        $normalized['mimetype'] = $response['mimeType'];
        $normalized['timestamp'] = (int) ceil($response['putTime'] / 1000 / 10000);
        $normalized['size'] = $response['fsize'];
        return $normalized;
    }

    /**
     * @param string $path
     * @param bool   $requireMeta
     * @param array  $options
     *
     * @return array|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    protected function mapFileInfo($path, $requireMeta = false, $options = [])
    {
        $normalized = [
            'type' => 'file',
            'path' => $path,
        ];

        if ($requireMeta) {
            $normalized = $this->getFileMeta($path, $normalized);
        }
        $normalized = array_merge($normalized, $options);
        return $normalized;
    }

    /**
     * @param string $dirname
     *
     * @return array
     */
    protected function mapDirInfo($dirname)
    {
        $normalized = ['path' => $dirname, 'type' => 'dir'];
        return $normalized;
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @return array|false
     *
     * @throws QiNiuOssAdapterException
     */
    public function write($path, $contents, Config $config)
    {
        $uploadToken = $this->auth->uploadToken($this->bucket);
        $response = $this->getUploadManager()->put($uploadToken, $path, $contents);
        $this->ossResponse($response);

        $fileInfo = $this->mapFileInfo($path, true, ['contents' => $contents]);
        return $fileInfo;
    }

    /**
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     *
     * @return array|false
     *
     * @throws QiNiuOssAdapterException
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, stream_get_contents($resource), $config);
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function update($path, $contents, Config $config)
    {
        $uploadToken = $this->auth->uploadToken($this->bucket, $path);
        $response = $this->getUploadManager()->put($uploadToken, $path, $contents);
        $this->ossResponse($response);
        $fileInfo = $this->mapFileInfo($path);
        return $fileInfo;
    }

    /**
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function updateStream($path, $resource, Config $config)
    {
        $fileInfo = $this->update($path, stream_get_contents($resource), $config);
        return $fileInfo;
    }

    /**
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $error = $this->getBucketManager()->rename($this->bucket, $path, $newpath);
        $this->createExceptionIfError($error);
        return !$error;
    }

    /**
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $error = $this->getBucketManager()->copy($this->bucket, $path, $this->bucket, $newpath);
        $this->createExceptionIfError($error);
        return !$error;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $response = $this->getBucketManager()->delete($this->bucket, $path);
        return !$response;
    }

    /**
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        return true;
    }

    /**
     * @param string $dirname
     * @param Config $config
     *
     * @return array|false
     *
     * @throws QiNiuOssAdapterException
     */
    public function createDir($dirname, Config $config)
    {
        $this->write($dirname.'/.init', 'hello world', $config);
        $dirInfo = $this->mapDirInfo($dirname);
        return $dirInfo;
    }

    /**
     * @param string $path
     * @param string $visibility
     *
     * @return array|false|void
     */
    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.  七牛云没有此功能
    }

    /**
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $response = $this->getBucketManager()->stat($this->bucket, $path);
        return !$response[1];
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function read($path)
    {
        $response = $this->getBucketManager()->stat($this->bucket, $path);
        $this->ossResponse($response);
        $path = $this->urlEncode($path);
        $result = $this->mapFileInfo($path, false, [
            'contents' => file_get_contents($this->host.$path),
        ]);
        return $result;
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function readStream($path)
    {
        $path = $this->urlEncode($path);
        $stream = fopen($this->host.$path, 'rb');
        $result = $this->mapFileInfo($path, false, ['stream' => $stream]);
        return $result;
    }

    /**
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     *
     * @throws QiNiuOssAdapterException
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = $recursive ? '' : $directory;
        $response = $this->getBucketManager()->listFiles($this->getBucket(), $directory);
        $this->ossResponse($response);
        $getDir = function ($path, $currentDir) {
            $tmp = strtr($path, [
                $currentDir.'/' => '',
            ]);
            $dir = substr($tmp, 0, stripos($tmp, '/'));
            return $dir;
        };
        $files = $response['items'] ?: [];
        $results = [];
        foreach ($files as $file) {
            $dir = $getDir($file['key'], $directory);
            if ($dir) {
                $result = $this->mapDirInfo($directory.'/'.$dir);
            } else {
                $result = $this->mapFileInfo($file['key'], false, [
                    'timestamp' => (int) ceil($file['putTime'] / 1000 / 10000),
                    'size' => $file['fsize'],
                ]);
            }
            $results[] = $result;
        }
        $results = array_unique($results, SORT_REGULAR);
        return $results;
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function getMetadata($path)
    {
        $metaData = $this->mapFileInfo($path, true);
        return $metaData;
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function getSize($path)
    {
        $metaData = $this->getMetadata($path);
        return $metaData['size'];
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function getMimetype($path)
    {
        $metaData = $this->getMetadata($path);
        return $metaData;
    }

    /**
     * @param string $path
     *
     * @return array|false|mixed
     *
     * @throws QiNiuOssAdapterException
     */
    public function getTimestamp($path)
    {
        $metaData = $this->getMetadata($path);
        return $metaData['timestamp'];
    }

    /**
     * @param string $path
     *
     * @return array|false|void
     */
    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method. 七牛云没有此功能
    }

    /**
     * @param $path
     * @param $rules
     * @param null $pipeline
     * @param null $notifyUrl
     * @param null $saveAs
     * @param null $bucket
     *
     * @return array
     *
     * @throws QiNiuOssAdapterException
     */
    public function transCoding($path, $rules, $pipeline = null, $notifyUrl = null, $saveAs = null, $toBucket = null)
    {
        $dir = '';
        $filename = $path;
        $position = strripos($path, '/');
        if (false !== $position) {
            $dir = substr($path, 0, $position + 1);
            $filename = substr(strrchr($path, '/'), 1);
        }
        if (!$saveAs) {
            list($name, $ext) = explode('.', $filename);
            $saveAs = $dir.$name.'_trans.'.$ext;
        }
        $toBucket = $toBucket ?: $this->bucket;
        $fops = "avthumb/$rules|saveas/".\Qiniu\base64_urlSafeEncode($toBucket.":$saveAs");

        $response = $this->getFopManager()->execute($this->bucket, $path, $fops, $pipeline, $notifyUrl);
        $this->ossResponse($response);

        return $response;
    }
}
