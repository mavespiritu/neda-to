<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;

/* @var $model common\modules\v1\models\Ppmp */

?>
<div class="ppmp-items">
<div class="flex-start">
    <div><?= $model->status ? $model->status->status != 'Approved' ? Html::button('<i class="fa fa-plus"></i> Add Item', ['value' => Url::to(['/v1/ppmp/create-item', 'id' => $model->id, 'activity_id' => $activity_id, 'fund_source_id' => $fund_source_id]), 'class' => 'btn btn-success', 'id' => 'create-item-button']) : '' : Html::button('<i class="fa fa-plus"></i> Add Item', ['value' => Url::to(['/v1/ppmp/create-item', 'id' => $model->id, 'activity_id' => $activity_id, 'fund_source_id' => $fund_source_id]), 'class' => 'btn btn-success', 'id' => 'create-item-button']) ?></div>
    <div><?= Html::button('<i class="fa fa-times"></i> Close Form', ['class' => 'btn btn-danger', 'id' => 'close-item-form-button', 'style' => 'display: none;']) ?></div>
    <div><?php // Html::button('<i class="fa fa-edit"></i> Update Prices', ['value' => Url::to(['/v1/ppmp/update-price', 'id' => $model->id]), 'class' => 'btn btn-primary', 'style' => 'margin-left: 10px;']) ?></div>
</div>
<br>
<div id="item-form-container"></div>
<h3 class="panel-title">Items</h3><br>
<table class="table table-responsive table-condensed">
    <tr>
        <td align=right style="width: 10%;">Program</td>
        <td><b><?= $activity->pap->codeAndTitle ?></b></td>
    </tr>
    <tr>
        <td align=right style="width: 10%;">Activity:</td>
        <td><b><?= $activity->activityTitle ?></b></td>
    </tr>
    <tr>
        <td align=right>Fund Source:</td>
        <td><b><?= $fundSource->code ?></b></td>
    </tr>
</table>
<?= !empty($items) ? Collapse::widget(['items' => $items, 'encodeLabels' => false, 'autoCloseItems' => true]) : 'No PPAs' ?>
<p class="panel-title pull-right" style="margin-right: 17px;">Total:&nbsp;&nbsp;&nbsp;<b><?= number_format($total, 2) ?></b></p>
</div>
<?php
  Modal::begin([
    'id' => 'create-item-modal',
    'size' => "modal-lg",
    'header' => '<div id="create-item-modal-header"><h4>Add Item</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-item-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function loadItemsInSubActivity(id, sub_activity_id, activity_id, fund_source_id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-items-in-sub-activity']).'",
                data: {
                    id: id,
                    sub_activity_id: sub_activity_id,
                    activity_id: activity_id,
                    fund_source_id: fund_source_id,
                },
                beforeSend: function(){
                    $("#item-list-" + sub_activity_id).html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    $("#item-list-" + sub_activity_id).empty();
                    $("#item-list-" + sub_activity_id).hide();
                    $("#item-list-" + sub_activity_id).fadeIn("slow");
                    $("#item-list-" + sub_activity_id).html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
        $(document).ready(function(){
            $("html").animate({ scrollTop: 0 }, "slow");
            $("#create-item-button").click(function(){
              //$("#item-form-container").modal("show").find("#create-item-modal-content").load($(this).attr("value"));
              $("#item-form-container").load($(this).attr("value"));
              $("#close-item-form-button").css("display", "block");
              $(this).css("display", "none");
            });

            $("#close-item-form-button").click(function(){
                $("#create-item-button").css("display", "block");
                $(this).css("display", "none");
                $("#item-form-container").empty();
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>