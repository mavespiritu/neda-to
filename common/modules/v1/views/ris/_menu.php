<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;

?>

<div>
    <div class="pull-left">
      <?= Html::a('<i class="fa fa-angle-double-left"></i> Back to RIS List', ['/'.Yii::$app->session->get('RIS_ReturnURL')], ['class' => 'btn btn-app']) ?>
      <?= Html::a('<i class="fa fa-file-o"></i> View Report', ['/v1/ris/info', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
      <?= Html::a('<i class="fa fa-plus"></i> Add Original', ['/v1/ris/view', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
      <?= Html::a('<i class="fa fa-plus"></i> Add Supplemental', ['/v1/ris/supplemental', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
      <?= $model->getRealignedAmount() > 0 ? Html::a('<span class="badge bg-red"><i class="fa fa-exclamation"></i></span> <i class="fa fa-mail-forward"></i> Re-align', ['/v1/ris/realign', 'id' => $model->id], ['class' => 'btn btn-app']) : Html::a('<i class="fa fa-mail-forward"></i> Realign', ['/v1/ris/realign', 'id' => $model->id], ['class' => 'btn btn-app']) ?>
    </div>
    <div class="pull-right">
      <?= $model->getRealignAmount('Supplemental') <= 0 && $model->risItems && ($model->statusName == 'Draft' || $model->statusName == 'For Revision') ? Html::a('<i class="fa fa-paper-plane"></i> Send For Approval', ['for-approval', 'id' => $model->id], [
            'class' => 'btn btn-app',
            'data' => [
                'confirm' => 'Items cannot be modified after this action. Would you like to proceed?',
                'method' => 'post',
            ],
        ]) : '' ?>
      <?= (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::button('<i class="fa fa-paper-plane"></i> Send For Revision', ['value' => Url::to(['/v1/ris/for-revision', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'revision-button']) : '' ?>
      <?= (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::button('<i class="fa fa-thumbs-o-up"></i> Approve', ['value' => Url::to(['/v1/ris/approve', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'approve-button']) : '' ?>
      <?= (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::button('<i class="fa fa-thumbs-o-down"></i> Disapprove', ['value' => Url::to(['/v1/ris/disapprove', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'disapprove-button']) : '' ?>
      <?= ($model->statusName == 'Draft' || $model->statusName == 'For Revision') || (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::button('<i class="fa fa-edit"></i> Edit RIS', ['value' => Url::to(['/v1/ris/update', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'update-button']) : '' ?>
      <?= ($model->statusName == 'Draft' || $model->statusName == 'For Revision') ||  (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::a('<i class="fa fa-trash"></i> Delete RIS', ['delete', 'id' => $model->id], [
          'class' => 'btn btn-app',
          'data' => [
              'confirm' => 'Deleting this RIS will also delete all included items. Would you like to proceed?',
              'method' => 'post',
          ],
      ]) : '' ?>
    </div>
    <div class="clearfix"></div>
</div>
<?= $model->statusName == 'Disapproved' ? '<div class="alert alert-danger">RIS is disapproved.</div>' : '' ?>
<?= $model->statusName == 'Approved' ? '<div class="alert alert-success">RIS is approved. You cannot add more items.</div>' : '' ?>
<?= $model->statusName == 'For Approval' ? '<div class="alert alert-info">RIS is sent for approval. You cannot add more items.</div>' : '' ?>
<?php
  Modal::begin([
    'id' => 'update-modal',
    'size' => "modal-md",
    'header' => '<div id="update-modal-header"><h4>Edit RIS</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'revision-modal',
    'size' => "modal-md",
    'header' => '<div id="revision-modal-header"><h4>Send For Revision</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="revision-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'supp-modal',
    'size' => "modal-md",
    'header' => '<div id="supp-modal-header"><h4>Add Supplemental Item</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="supp-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'approve-modal',
    'size' => "modal-md",
    'header' => '<div id="approve-modal-header"><h4>Approve RIS</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="approve-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'disapprove-modal',
    'size' => "modal-md",
    'header' => '<div id="disapprove-modal-header"><h4>Disapprove RIS</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="disapprove-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#update-button").click(function(){
              $("#update-modal").modal("show").find("#update-modal-content").load($(this).attr("value"));
            });
            $("#revision-button").click(function(){
                $("#revision-modal").modal("show").find("#revision-modal-content").load($(this).attr("value"));
            });
            $("#supp-button").click(function(){
                $("#supp-modal").modal("show").find("#supp-modal-content").load($(this).attr("value"));
            });
            $("#approve-button").click(function(){
              $("#approve-modal").modal("show").find("#approve-modal-content").load($(this).attr("value"));
          });
          $("#disapprove-button").click(function(){
            $("#disapprove-modal").modal("show").find("#disapprove-modal-content").load($(this).attr("value"));
        });
        });     

        function printRis()
        {
          var printWindow = window.open(
            "'.Url::to(['/v1/ris/print', 'id' => $model->id]).'", 
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