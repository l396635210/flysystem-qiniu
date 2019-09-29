<?php

require 'vendor/autoload.php';


use League\Flysystem\Filesystem;
use Liz\Flysystem\QiNiu\Plugins\PrivateDownloadUrlMaker;
use Liz\Flysystem\QiNiu\Plugins\TransCoder;
use Liz\Flysystem\QiNiu\QiNiuOssAdapter;
use Liz\Flysystem\QiNiu\QiNiuOssAdapterException;

# cdn [外链默认域名须英文](https://developer.qiniu.com/kodo/kb/5158/how-to-transition-from-test-domain-name-to-a-custom-domain-name)
$cdnHost = 'cdn.host.com';
# bucket [七牛对象存储空间列表](https://portal.qiniu.com/bucket)
$bucket = 'bucket';
# $accessKey [用户密钥](https://portal.qiniu.com/user/key)
$accessKey = 'access-key';
$secretKey = 'secret-key';

$flysystem = new Filesystem(new QiNiuOssAdapter($accessKey, $secretKey, $bucket, $cdnHost));
try {

// 创建文件夹
    $flysystem->createDir('path/dir');

// 删除文件夹
    $flysystem->deleteDir('path/dir');

// has file
    $isExist = $flysystem->has('path/file.txt');

// write file
    if (!$isExist){
        $result = $flysystem->write('path/file.txt', 'contents');
    }

// write stream
    if (!$flysystem->has('path/filename.txt')){
        $stream = fopen('.gitignore', 'r+');
        $result = $flysystem->writeStream('path/filename.txt', $stream);
    }

// update file
    $result = $flysystem->update('path/file.txt', 'new contents');

// read file
    $result = $flysystem->read('path/file.txt');

// rename files
    if(!$flysystem->has('path/newname.txt')){
        $result = $flysystem->rename('path/filename.txt', 'path/newname.txt');
    }

// copy files
    if (!$flysystem->has('path/file_copy.txt')){
        $result = $flysystem->copy('path/file.txt', 'path/file_copy.txt');
    }

// list the contents
    $result = $flysystem->listContents('path', false);
var_dump($result);
// delete file
    $result = $flysystem->delete('path/file.txt');

// 转码
    /**
     * @var $flysystem Filesystem|QiNiuOssAdapter
     */
    $flysystem = new Filesystem(new QiNiuOssAdapter($accessKey, $secretKey, $bucket, $cdnHost));

    /**
     * TransCoder constructor.
     * @param null $notifyUrl 处理完毕默认通知地址
     * @param null $pipeLine 默认队列名称 https://portal.qiniu.com/dora/mps/new
     * @param null $toBucket 处理完成默认保存到的bucket
     * @param null $wmImage 水印图片地址
     */
    $flysystem->addPlugin(new TransCoder('notify_url', 'pipeline', 'toBucket', 'wmImage')); //设置转码默认选项

    // 转码样式说明 https://developer.qiniu.com/kodo/kb/5858/the-instructions-on-the-storage-space-of-transcoding-style
    $rules = 'm3u8/segtime/10/ab/128k/ar/44100/acodec/libfaac/r/30/vb/640k/vcodec/libx264/stripmeta/0/noDomain/1';

    /**
     * @param $path 待转码文件路径
     * @param $rules 转码规则[转码规则说明](https://developer.qiniu.com/kodo/kb/5858/the-instructions-on-the-storage-space-of-transcoding-style)
     * @param null $pipeline 队列名称,若不填写使用TransCoder初始化的pipeline https://portal.qiniu.com/dora/mps/new
     * @param null $notifyUrl 处理完毕通知地址,若不填写使用TransCoder初始化的bucket
     * @param null $saveAs 保存全部路径，若不填写则为$path的名称加_trans
     * @param null $bucket 处理完成保存到bucket，若不填写则使用TransCoder初始化的bucket
     *
     * @return array
     *
     * @throws QiNiuOssAdapterException
     */
    $result = $flysystem->transCoding('xxw-community/a.mp4', $rules,  'xxw-community/a.m3u8', 'notify_url', 'first', 'to_bucket');
    var_dump($result);
//获取私有下载地址
    $flysystem->addPlugin(new PrivateDownloadUrlMaker());

    /**
     * @param string $baseUrl 请求url
     * @param bool $isBucketPrivate bucket是否为私有，如果是私有m3u8文件会对相关ts文件进行授权处理(https://developer.qiniu.com/dora/api/1292/private-m3u8-pm3u8)
     * @param int $expires
     * @return string
     */
    $url = $flysystem->privateDownloadUrl('xxw-community/a.m3u8', true);
    var_dump($url);
}catch (Exception $exception){
    echo "<pre>";
    var_dump($exception);
}