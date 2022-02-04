<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<div>
    <div class="pull-left">
        <?= Html::a('<i class="fa fa-angle-double-left"></i> Back to PR List', ['/v1/pr/'], ['class' => 'btn btn-app']) ?>
    </div>
    <div class="pull-right">
        <?= ($model->status->status == 'Draft' || $model->status->status == 'For Revision') || (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::button('<i class="fa fa-edit"></i> Edit PR', ['value' => Url::to(['/v1/pr/update', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'update-button']) : '' ?>
        <?= ($model->status->status == 'Draft' || $model->status->status == 'For Revision') ||  (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::a('<i class="fa fa-trash"></i> Delete PR', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-app',
            'data' => [
                'confirm' => 'Deleting this PR will also delete all included items. Would you like to proceed?',
                'method' => 'post',
            ],
        ]) : '' ?>
    </div>
    <div class="clearfix"></div>
</div>
<?php
  Modal::begin([
    'id' => 'update-modal',
    'size' => "modal-md",
    'header' => '<div id="update-modal-header"><h4>Edit PR</h4></div>',
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

        function printPr()
        {
          var printWindow = window.open(
            "'.Url::to(['/v1/pr/print', 'id' => $model->id]).'", 
            "Print",
            "left=200", 
            "top=200", 
            "width=950", 
            "height=500", 
            "toolbar=0", 
            "resizable=0"
          );
          printWindow.addEventListener("load", function() {
              printWindow.print();
              setTimeout(function() {
                printWindow.close();
            }, 1);
          }, true);
        }
    ';

    $this->registerJs($script, View::POS_END);
?>