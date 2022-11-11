<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use  yii\web\View;
?>

<div class="row">
    <div class="col-md-6 col-xs-12">
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-responsive table-condensed table-bordered'],
        'attributes' => [
            'pr_no',
            'officeName',
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => function($model){
                    return $model->type == 'Supply' ? 'Goods' : 'Service/Contract';
                }
            ],
            'year',
            'procurementModeName',
            'fundSourceName',
            'fundClusterName',
            'purpose:ntext',
            'requesterName',
            'date_requested',
            'approverName',
            'date_approved',
            'creatorName',
            'date_created',
        ],
    ]) ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <h5 class="text-right">Total</h5>

        <h5 class="text-right">No. of items</h5>
    </div>
</div>