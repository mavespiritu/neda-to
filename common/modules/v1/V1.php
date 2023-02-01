<?php

namespace common\modules\v1;
use yii\helpers\Url;
/**
 * v1 module definition class
 */
class V1 extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    public function getBackUrl($return_url)
    {
        $choppedUrls = explode('/', $return_url);
        $newUrl = count($choppedUrls) > 1 ? array_splice($choppedUrls, 2, count($choppedUrls)) : array_splice($choppedUrls, 1, count($choppedUrls));
        $newUrl = implode('/', $newUrl);

        return $newUrl;
    }
}
