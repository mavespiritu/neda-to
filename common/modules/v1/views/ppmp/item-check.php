<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Ppmp */

$this->title = $model->status ? $model->title.' ['.$model->status->status.']' : $model->title.' - Item Check';
$this->params['breadcrumbs'][] = ['label' => 'PPMPs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ppmp-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <div class="box box-primary">
        <div class="box-header panel-title"><i class="fa fa-search"></i>Search Item</div>
        <div class="box-body">
        <?= $this->render('_item-check', [
            'model' => $model,
            'searchModel' => $searchModel,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'objects' => $objects,
            'items' => $items,
            'fundSources' => $fundSources,
        ]) ?>
        <br>
        <br>
        <h3 class="panel-title"><i class="fa fa-list"></i> Item List</h3>
        <br>
        <?= GridView::widget([
            'options' => [
                'class' => 'table table-hover table-responsive',
            ],
            'dataProvider' => $dataProvider,
            'showFooter' => true,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'activityName',
                'subActivityName',
                'objName',
                [
                    'header' => 'Item',
                    'attribute' => 'itemName',
                    'format' => 'raw',
                    'value' => function($item){
                        return '<a href="javascript:void(0)" onClick="viewItemDetails('.$item->id.')">'.$item->item->title.'</a>';
                    }
                ],
                [
                    'header' => 'Cost',
                    'attribute' => 'cost',
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'text-align: right;'],
                    'value' => function($ppmp){
                        return number_format($ppmp->cost, 2);
                    }
                ],
                'janQty',
                'febQty',
                'marQty',
                'aprQty',
                'mayQty',
                'junQty',
                'julQty',
                'augQty',
                'sepQty',
                'octQty',
                'novQty',
                'decQty',
                'quantities',
                [
                    'header' => 'Total Cost',
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'text-align: right;'],
                    'value' => function($ppmp){
                        return number_format($ppmp->cost * $ppmp->quantities, 2);
                    }
                ],
                'fundSourceName',
                'type'
            ],
        ]); ?>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'view-item-modal',
    'size' => "modal-xl",
    'header' => '<div id="view-item-modal-header"><h4>View Item</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="view-item-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function viewItemDetails(id)
        {   
            $("#view-item-modal").modal("show").find("#view-item-modal-content").load("'.Url::to(['/v1/ppmp/view-item']).'?id=" + id);
        }   
    ';

    $this->registerJs($script, View::POS_END);
?>