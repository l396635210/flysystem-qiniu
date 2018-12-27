## QiNiu OSS(七牛云对象存储) Adapter For Flysystem.
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Flysystem 适配器： [七牛云](https://www.qiniu.com/)

## Installation
composer require liz/flysystem-qiniu

## Usage
```php
require 'vendor/autoload.php';


use League\Flysystem\Filesystem;
use Liz\Flysystem\QiNiu\QiNiuOssAdapter;
# cdn [外链默认域名须英文](https://developer.qiniu.com/kodo/kb/5158/how-to-transition-from-test-domain-name-to-a-custom-domain-name)
$cdnHost = 'http://default.cdn.host/';
$bucket = 'bucket'; 
$accessKey = 'access-key';
$secretKey = 'secret-key';

// write file
$result = $flysystem->write('bucket/path/file.txt', 'contents');

// write stream
$stream = fopen('.env', 'r+');
$result = $flysystem->writeStream('bucket/path/filestream.txt', $stream);

// update file
$result = $flysystem->update('bucket/path/file.txt', 'new contents');

// has file
$result = $flysystem->has('bucket/path/file.txt');

// read file
$result = $flysystem->read('bucket/path/file.txt');

// delete file
$result = $flysystem->delete('bucket/path/file.txt');

// rename files
$result = $flysystem->rename('bucket/path/filename.txt', 'bucket/path/newname.txt');

// copy files
$result = $flysystem->copy('bucket/path/file.txt', 'bucket/path/file_copy.txt');

// list the contents
$result = $flysystem->listContents('path', false);

// 转码
$flysystem->addPlugin(new \Liz\Flysystem\QiNiu\Plugins\TransCoder());
$rules = 'm3u8/segtime/10/ab/128k/ar/44100/acodec/libfaac/r/30/vb/640k/vcodec/libx264/stripmeta/0/noDomain/1';
$flysystem->transCoding('test.mp4', $rules,'pipeline', 'notify_url', 'save_as', 'bucket');
```

## Notice
由于七牛云没有文件夹的概念，建议顶级目录同bucket名
`getVisibility()`,`setVisibility()`七牛云没有提供相关操作