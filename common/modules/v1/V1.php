<?php

namespace common\modules\v1;

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
        $urls = explode('/', $return_url);
        $urls = array_splice($urls, 2, count($urls));
        $urls = implode('/', $urls);

        return $urls;
    }
}
