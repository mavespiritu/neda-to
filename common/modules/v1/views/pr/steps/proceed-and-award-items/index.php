<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

?>
<div class="po-contract-content">
    <h4>7.<?= $i ?> Proceed and Award <?= $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo ?></h4>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <h5>Select Document</h5>
            <a onclick="createNoa('<?= $model->id ?>','<?= $po->id ?>','<?= $i ?>');" class="btn btn-app">
            <?= $noa ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
            <i class="fa fa-handshake-o"></i> Notice of Award</a>
            <a onclick="createNtp('<?= $model->id ?>','<?= $po->id ?>','<?= $i ?>');" class="btn btn-app">
            <?= $ntp ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
            <i class="fa fa-level-up"></i> Notice to Proceed</a>
        </div>
        <div class="col-md-12 col-xs-12">
            <div id="ntp_noa_content"></div>
        </div>
    </div>
</div>

<?php
    $script = '
        function createNoa(id, po_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-noa']).'?id=" + id + "&po_id=" + po_id + "&i=" + i,
                beforeSend: function(){
                    $("#ntp_noa_content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#ntp_noa_content").empty();
                    $("#ntp_noa_content").hide();
                    $("#ntp_noa_content").fadeIn("slow");
                    $("#ntp_noa_content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function createNtp(id, po_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-ntp']).'?id=" + id + "&po_id=" + po_id + "&i=" + i,
                beforeSend: function(){
                    $("#ntp_noa_content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#ntp_noa_content").empty();
                    $("#ntp_noa_content").hide();
                    $("#ntp_noa_content").fadeIn("slow");
                    $("#ntp_noa_content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    ';

    $this->registerJs($script, View::POS_END);
?>