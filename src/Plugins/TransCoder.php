<?php
/**
 * Created by PhpStorm.
 * User: dljy-technology
 * Date: 2018/12/27
 * Time: 上午11:31
 */

namespace Liz\Flysystem\QiNiu\Plugins;


use League\Flysystem\Plugin\AbstractPlugin;
use Liz\Flysystem\QiNiu\QiNiuOssAdapter;
use function Qiniu\base64_urlSafeEncode;

/**
 * Class TransCoder
 * @package Liz\Flysystem\QiNiu\Plugins
 * @method int transCoding($path, $rules, $saveAs=null, $notifyUrl=null, $pipeline=null, $bucket=null)
 */
class TransCoder extends AbstractPlugin
{

    protected $notifyUrl;

    protected $pipeLine;

    protected $toBucket;

    protected $wmImage;

    public function __construct($notifyUrl = null, $pipeLine = null, $toBucket = null, $wmImage=null)
    {
        $this->notifyUrl = $notifyUrl;
        $this->pipeLine = $pipeLine;
        $this->toBucket = $toBucket;
        $this->wmImage = $wmImage;
    }

    public function getMethod()
    {
        return 'transCoding';
    }

    /**
     * @return QiNiuOssAdapter
     */
    protected function getFlySystemAdapter(){
        return $this->filesystem->getAdapter();
    }

    /**
     * @param $path
     * @param $rules
     * @param null $saveAs
     * @param null $notifyUrl
     * @param null $pipeline
     * @param null $toBucket
     * @return array
     * @throws \Liz\Flysystem\QiNiu\QiNiuOssAdapterException
     */
    public function handle($path, $rules, $saveAs=null, $notifyUrl=null, $pipeline=null, $toBucket=null){
        $notifyUrl = $notifyUrl ?: $this->notifyUrl;
        $pipeline = $pipeline ?: $this->pipeLine;
        $toBucket = $toBucket ?: $this->toBucket;
        if ($this->wmImage){
            $rules .= '/wmImage/'.base64_urlSafeEncode($this->wmImage);
        }
        return $this->getFlySystemAdapter()->transCoding($path, $rules, $saveAs, $notifyUrl, $pipeline, $toBucket);
    }

}