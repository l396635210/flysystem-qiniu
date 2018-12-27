<?php
/**
 * Created by PhpStorm.
 * User: dljy-technology
 * Date: 2018/12/27
 * Time: 上午11:31
 */

namespace Liz\Flysystem\QiNiu\Plugins;


use League\Flysystem\Plugin\AbstractPlugin;

class TransCoder extends AbstractPlugin
{

    public function getMethod()
    {
        return 'transCoding';
    }

    public function handle($path, $rules, $saveAs=null, $notifyUrl=null, $pipeline=null, $bucket=null){
        return $this->filesystem->getAdapter()->transCoding($path, $rules, $saveAs, $notifyUrl, $pipeline, $bucket);
    }

}