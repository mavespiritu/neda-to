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

<h4>8.<?= $i ?> Obligate <?= !is_null($po) ? $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo : 'Non-procurable Items' ?></h4>
<?= !is_null($po) ? Html::button('<i class="fa fa-table"></i> Obligate Items', ['value' => Url::to(['/v1/pr/create-ors', 'id' => $model->id, 'po_id' => $po->id, 'i' => $i]), 'class' => 'btn btn-app', 'id' => 'create-ors-button']) : Html::button('<i class="fa fa-table"></i> Obligate Items', ['value' => Url::to(['/v1/pr/create-ors', 'id' => $model->id, 'po_id' => 'null', 'i' => $i]), 'class' => 'btn btn-app', 'id' => 'create-ors-button']) ?>
<br>
<h4>Obligation List</h4>
<div class="iar-content">
    <table class="table table-bordered table-condensed table-striped table-hover table-responsive">
        <thead>
            <tr>
                <td align=center><b>ORS No.</b></td>
                <td align=center><b>ORS Date</b></td>
                <td align=center><b>Created By</b></td>
                <td align=center><b>Date Created</b></td>
                <td align=center><b>Total</b></td>
                <td>&nbsp;</td>
            </tr>
        </thead>
        <tbody>
        <?php if($ors){ ?>
           
        <?php }else{ ?>
            <td colspan=8 align=center>No obligations found.</td>
        <?php } ?>
        </tbody>
    </table>    
</div>
<div id="ors-content"></div>
<?php
  Modal::begin([
    'id' => 'create-ors-modal',
    'size' => "modal-lg",
    'header' => '<div id="create-ors-modal-header"><h4>Obligate Items</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-ors-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-ors-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-ors-modal-header"><h4>Obligate Items</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-ors-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function printOrs(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-ors']).'?id=" + id, 
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
        
        function deleteOrs(id, ors_id, i)
        {
            if(confirm("Are you sure you want to delete this item?"))
            {
                $.ajax({
                    url: "'.Url::to(['/v1/pr/delete-ors']).'?id="+ id +"&ors_id=" + ors_id,
                    method: "post",
                    beforeSend: function(){
                        $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                    },
                    success: function (data) {
                        console.log(this.data);
                        alert("Obligation has been deleted");
                        obligatePo(id, po_id, i);
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }

        function viewOrs(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/view-ors']).'?id="+ id,
                beforeSend: function(){
                    $("#ors-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#ors-content").empty();
                    $("#ors-content").hide();
                    $("#ors-content").fadeIn("slow");
                    $("#ors-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#create-ors-button").click(function(){
              $("#create-ors-modal").modal("show").find("#create-ors-modal-content").load($(this).attr("value"));
            });
            $(".update-ors-button").click(function(){
                $("#update-ors-modal").modal("show").find("#update-ors-modal-content").load($(this).attr("value"));
              });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>