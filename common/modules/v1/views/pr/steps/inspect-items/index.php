<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
?>

<h4>9.<?= $i ?> Inspect <?= $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo ?></h4>
<?= $po->deliveryBalance > 0 || empty($iars) ? Html::button('<i class="fa fa-eye"></i> Inspect Items', ['value' => Url::to(['/v1/pr/create-iar', 'id' => $model->id, 'po_id' => $po->id, 'i' => $i]), 'class' => 'btn btn-app', 'id' => 'create-iar-button']) : '' ?>
<br>
<h4>Inspection List</h4>
<div class="iar-content">
    <table class="table table-bordered table-condensed table-striped table-hover table-responsive">
        <thead>
            <tr>
                <td align=center><b>IAR No.</b></td>
                <td align=center><b>IAR Date</b></td>
                <td align=center><b>Invoice No.</b></td>
                <td align=center><b>Invoice Date</b></td>
                <td align=center><b>Inspected By</b></td>
                <td align=center><b>Date Inspected</b></td>
                <td align=center><b>Status</b></td>
                <td>&nbsp;</td>
            </tr>
        </thead>
        <tbody>
        <?php if($iars){ ?>
            <?php foreach($iars as $iar){ ?>
                <tr>
                    <td><b><?= Html::a($iar->iar_no, null, ['href' => 'javascript:void(0)', 'onclick' => 'viewIar('.$iar->id.')']) ?></b></td>
                    <td><?= date("F j, Y", strtotime($iar->iar_date)) ?></td>
                    <td align=center><?= $iar->invoice_no ?></td>
                    <td><?= date("F j, Y", strtotime($iar->invoice_date)) ?></td>
                    <td><?= $iar->inspectorName ?></td>
                    <td><?= date("F j, Y", strtotime($iar->date_inspected)) ?></td>
                    <td><?= $iar->status ?></td>
                    <td>
                        <?= Html::button('<i class="fa fa-print"></i> Print', ['onclick' => 'printIar('.$iar->id.')', 'class' => 'btn btn-xs btn-block btn-info']) ?>
                        <?= Html::button('<i class="fa fa-edit"></i> Edit', ['value' => Url::to(['/v1/pr/update-iar', 'id' => $iar->id, 'i' => $i]), 'class' => 'btn btn-xs btn-block btn-warning update-iar-button']) ?>
                        <?= Html::button('<i class="fa fa-trash"></i> Delete', ['onclick' => 'deleteIar('.$model->id.','.$po->id.','.$iar->id.','.$i.')', 'class' => 'btn btn-xs btn-block btn-danger']) ?>
                    </td>
                </tr>
            <?php } ?>
        <?php }else{ ?>
            <td colspan=8 align=center>No inspections found.</td>
        <?php } ?>
        </tbody>
    </table>    
</div>
<div id="iar-content"></div>
<?php
  Modal::begin([
    'id' => 'create-iar-modal',
    'size' => "modal-lg",
    'header' => '<div id="create-iar-modal-header"><h4>Inspect Items</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-iar-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-iar-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-iar-modal-header"><h4>Inspect Items</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-iar-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function printIar(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-iar']).'?id=" + id, 
                "Print",
                "left=200", 
                "top=200", 
                "width=650", 
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
        
        function deleteIar(id, po_id, iar_id, i)
        {
            if(confirm("Are you sure you want to delete this item?"))
            {
                $.ajax({
                    url: "'.Url::to(['/v1/pr/delete-iar']).'?id="+ id +"&iar_id=" + iar_id,
                    method: "post",
                    beforeSend: function(){
                        $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                    },
                    success: function (data) {
                        console.log(this.data);
                        alert("Inspection has been deleted");
                        inspectDelivery(id, po_id, i);
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }

        function viewIar(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/view-iar']).'?id="+ id,
                beforeSend: function(){
                    $("#iar-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#iar-content").empty();
                    $("#iar-content").hide();
                    $("#iar-content").fadeIn("slow");
                    $("#iar-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#create-iar-button").click(function(){
              $("#create-iar-modal").modal("show").find("#create-iar-modal-content").load($(this).attr("value"));
            });
            $(".update-iar-button").click(function(){
                $("#update-iar-modal").modal("show").find("#update-iar-modal-content").load($(this).attr("value"));
              });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>