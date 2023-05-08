<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;

$asset = AppAsset::register($this);
?>
<link rel="stylesheet" href="<?= $asset->baseUrl.'/css/site.css' ?>" />
<style>
    @media print {
        body {-webkit-print-color-adjust: exact;}
    }
    *{ font-family: "Tahoma"; font-size: 14px;}
    h3, h4{ text-align: center; } 
    p{ font-family: "Tahoma";}
    table{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    table.table-bordered{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    thead{
        font-size: 14px;
    }

    table.table-bordered td{
        font-size: 14px;
        border: 1px solid #555555 !important;
        padding: 3px 3px;
    }

    table.table-bordered th{
        font-size: 14px;
        text-align: center;
        border: 1px solid #555555 !important;
        padding: 3px 3px;
    }
</style>

<div class="ntp-content">
<h5 class="text-center"><b>NOTICE TO PROCEED</b></h5>
    <br>
    <br>
    <br>
    <?= date("F j, Y", strtotime($ntp->date_created)) ?>
    <br>
    <br>
    <b><?= $supplier->business_name ?></b>
    <br>
    <?= $supplier->business_address ?>
    <br>
    <br>
    <b>Dear Ma'am/Sir:</b>
    <br>
    <br>
    <p>We are pleased to inform you to proceed with the implementation of the <b><?= $model->purpose ?> with <?= $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo ?></b> with a Contract Price equivalent to <b><?= strtoupper(Yii::$app->controller->module->getNumberToWords(sprintf('%0.2f',$total['total']))) ?> (Php <?= number_format($total['total'], 2) ?>).</b>
    <br>
    <br>
    In this regard, please be directed to proceed on <u><b><?= date("F j, Y", strtotime($ntp->date_proceeded)) ?></b></u>. We will appreciate your immediate action. Thank you.
    <br>
    <br>
    <br>
    Very Truly Yours,
    <br>
    <br>
    <br>
    <b><?= $rd->value ?></b>
    <br>
    OIC - Regional Director
    <br>
    <br>
    <br>
    Conforme:
    <br>
    <br>
    <br>
    <span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span>
    <br>
    (Name and Signature of Bidder/Authorized Representative)
    <br>
    <br>
    <br>
    <span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span>
    <br>
    (Name of Bidder or Supplier)
    <br>
    <br>
    <br>
    <span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span>
    <br>
    (Date) 
    </p>
</div>