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
    <h4>6.<?= $i ?> <?= $supplier->business_name ?><br>
    <small><?= $supplier->business_address ?></small>
    </h4>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <h5>Select Document</h5>
            <a onclick="createPurchaseOrder('<?= $model->id ?>','<?= !is_null($bid) ? $bid->id : 'null' ?>','<?= $supplier->id ?>','<?= $i ?>');" class="btn btn-app">
            <?= $po ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
            <i class="fa fa-inbox"></i> Purchase Order</a>
            <a onclick="createContract('<?= $model->id ?>','<?= !is_null($bid) ? $bid->id : 'null' ?>','<?= $supplier->id ?>','<?= $i ?>');" class="btn btn-app">
            <?= $contract ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
            <i class="fa fa-edit"></i> Contract</a>
        </div>
        <div class="col-md-12 col-xs-12">
            <div id="po_contract_content"></div>
        </div>
    </div>
</div>

<?php
    $script = '
        function createPurchaseOrder(id, bid_id, supplier_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-purchase-order']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&i=" + i,
                beforeSend: function(){
                    $("#po_contract_content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#po_contract_content").empty();
                    $("#po_contract_content").hide();
                    $("#po_contract_content").fadeIn("slow");
                    $("#po_contract_content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function createContract(id, bid_id, supplier_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-contract']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&i=" + i,
                beforeSend: function(){
                    $("#po_contract_content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#po_contract_content").empty();
                    $("#po_contract_content").hide();
                    $("#po_contract_content").fadeIn("slow");
                    $("#po_contract_content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    ';

    $this->registerJs($script, View::POS_END);
?>