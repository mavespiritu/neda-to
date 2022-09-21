<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
        'https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css'
    ];
    public $js = [
        'js/freeze-table/dist/js/freeze-table.js',
        'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        //'dmstr\adminlte\web\AdminLteAsset',
    ];
}
