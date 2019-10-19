<?php
/**
 * Created by PhpStorm.
 * User: dljy-technology
 * Date: 2018/12/27
 * Time: 上午11:31.
 */

namespace Liz\Flysystem\QiNiu\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;
use Liz\Flysystem\QiNiu\QiNiuOssAdapter;

/**
 * Class PrivateDownloadUrlMaker.
 *
 * @method string getUploadToken($pathname, $expires = 3600, array $policy = [])
 */
class UploadTokenMaker extends AbstractPlugin
{
    public function getMethod()
    {
        return 'getUploadToken';
    }

    /**
     * @return QiNiuOssAdapter
     */
    protected function getFlySystemAdapter()
    {
        return $this->filesystem->getAdapter();
    }

    /**
     * @param $pathname
     * @param int $expires
     * @param array $policy
     * @return string
     */
    public function handle($pathname, $expires = 3600, array $policy = [])
    {
        return $this->getFlySystemAdapter()->getUploadToken($pathname, $expires, $policy);
    }
}
