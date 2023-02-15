<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Modal;

/* @var $model common\modules\v1\models\Pr */

$this->title = $model->status ? $model->pr_no.' - '.$model->purpose.' ['.$model->status->status.']' : $model->pr_no.' - '.$model->purpose;
$this->title = strlen($this->title) > 70 ? substr($this->title, 0, 70).'...' : $this->title;
$this->params['breadcrumbs'][] = ['label' => 'PRs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pr-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> PR Information</div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                          <div id="pr-main"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-xs-12">
                            <div id="pr-submenu"></div>
                        </div>
                        <div class="col-md-9 col-xs-12">
                            <div id="pr-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .button-link {
        background: none !important;
        border: none;
        padding: 0 !important;
        color: #6192CD;
        text-decoration: none;
        cursor: pointer;
    }
</style>
<?php
    $script = '
        $(document).ready(function(){
          home('.$model->id.');
        });    
    ';

    $this->registerJs($script, View::POS_END);
?>