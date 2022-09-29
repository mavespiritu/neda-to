<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;

?>

<div>
    <div class="pull-left">
        <?= Html::a('<i class="fa fa-angle-double-left"></i> Back to PPMP List', ['/v1/ppmp/'], ['class' => 'btn btn-app']) ?>
        <div class="btn-group" style="padding-left: 10px;">
            <?= Html::a('<i class="fa fa-list"></i> Manage Items', ['/v1/ppmp/view', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
            <?= Html::a('<i class="fa fa-list"></i> View Summary', ['/v1/ppmp/summary', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
            <?= Html::a('<i class="fa fa-list"></i> Item List', ['/v1/ppmp/item-check', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
        </div>
    </div>
    <div class="pull-right">
        <div class="btn-group" style="padding-left: 10px;">
            <?= (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::a('<i class="fa fa-thumbs-o-up"></i> Approve', ['approve', 'id' => $model->id], [
                'class' => 'btn btn-app',
                'id' => 'approve-button',
                'data' => [
                    'confirm' => 'Items cannot be modified after this action. Would you like to proceed?',
                    'method' => 'post',
                ],
            ]) : '' ?>
            <?= (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::a('<i class="fa fa-thumbs-o-down"></i> Disapprove', ['disapprove', 'id' => $model->id], [
                'class' => 'btn btn-app',
                'id' => 'disapprove-button',
                'data' => [
                    'confirm' => 'Are you sure you want to disapprove PPMP. Would you like to proceed?',
                    'method' => 'post',
                ],
            ]) : '' ?>
        </div>
        <div class="btn-group" style="padding-left: 10px;">
            <?= $model->status ? $model->status->status != 'Approved' ? Html::button('<i class="fa fa-edit"></i> Edit PPMP', ['value' => Url::to(['/v1/ppmp/update', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'update-button']) : '' : Html::button('<i class="fa fa-edit"></i> Edit PPMP', ['value' => Url::to(['/v1/ppmp/update', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'update-button']) ?>
            <?= $model->status ? $model->status->status != 'Approved' ? Html::a('<i class="fa fa-trash"></i> Delete PPMP', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-app',
                'data' => [
                    'confirm' => 'Deleting this PPMP will also delete all included items. Would you like to proceed?',
                    'method' => 'post',
                ],
            ]) : '' :
            Html::a('<i class="fa fa-trash"></i> Delete PPMP', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-app',
                'data' => [
                    'confirm' => 'Deleting this PPMP will also delete all included items. Would you like to proceed?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<?php
  Modal::begin([
    'id' => 'update-modal',
    'size' => "modal-sm",
    'header' => '<div id="update-modal-header"><h4>Edit PPMP</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#update-button").click(function(){
              $("#update-modal").modal("show").find("#update-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>