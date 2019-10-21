<?php


namespace Liz\Flysystem\QiNiu\Plugins\UploadTokenMakerHelpers;


use Liz\Flysystem\QiNiu\Plugins\TransCoderHelpers\AbstractTransCoderPolicy;

abstract class AbstractUploadPolicy
{

    abstract public function addCallbackUrl(string $callbackUrl);

    abstract public function getCallbackUrls();

    abstract public function setCallbackBody(string $callbackBody);

    abstract public function getCallbackBody();

    abstract public function setCallbackBodyType(string $bodyType);

    abstract public function getCallbackBodyType();

    abstract public function setTransCoderPolicy(AbstractTransCoderPolicy $transCoderPolicy=null);

    /**
     * @return AbstractTransCoderPolicy
     */
    abstract public function getTransCoderPolicy();

}