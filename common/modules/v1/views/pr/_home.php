<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use  yii\web\View;
?>

<div class="row">
    <div class="col-md-6 col-xs-12">
    <h4>PR Details</h4>
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
        <h4>Reports Available</h4>
        <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;&nbsp;PR No. '.$model->pr_no, ['value' => Url::to(['/v1/pr/pr', 'id' => $model->id]), 'class' => 'btn btn-default btn-xs', 'id' => 'pr-button']) ?>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'pr-modal',
    'size' => "modal-xl",
    'header' => '<div id="pr-modal-header"><h4>Purchase Request (PR)</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="pr-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $("#pr-button").click(function(){
            $("#pr-modal").modal("show").find("#pr-modal-content").load($(this).attr("value"));
        });
    ';

    $this->registerJs($script, View::POS_END);
?>