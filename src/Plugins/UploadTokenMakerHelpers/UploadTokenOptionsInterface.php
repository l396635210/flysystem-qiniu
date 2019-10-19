<?php


namespace Liz\Flysystem\QiNiu\Plugins\UploadTokenMakerHelpers;


interface UploadTokenOptionsInterface
{

    public function addCallbackUrl($callbackUrl);

    public function getCallbackUrls();

    public function setCallbackBody($callbackBody);

    public function getCallbackBody();

    public function setCallbackBodyType($bodyType);

    public function getCallbackBodyType($bodyType);


}