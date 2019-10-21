<?php


namespace Liz\Flysystem\QiNiu\Plugins\TransCoderHelpers;


abstract class AbstractTransCoderPolicy
{

    /**
     * @return mixed
     */
    abstract public function getNotifyUrl();

    /**
     * @param mixed $notifyUrl
     */
    abstract public function setNotifyUrl($notifyUrl);

    /**
     * @return mixed
     */
    abstract public function getPipeLine();

    /**
     * @param mixed $pipeLine
     */
    abstract public function setPipeLine($pipeLine);

    /**
     * @return mixed
     */
    abstract public function getToBucket();

    /**
     * @param mixed $toBucket
     */
    abstract public function setToBucket($toBucket);

    /**
     * @return mixed
     */
    abstract public function getWmImage();

    /**
     * @param mixed $wmImage
     */
    abstract public function setWmImage($wmImage);

    abstract public function setRules($rules);

    abstract public function getRules();

}