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

<div class="noa-content">
<br>
<h5 class="text-center"><b>NOTICE OF AWARD</b></h5>
    <br>
    <br>
    <?= date("F j, Y", strtotime($noa->date_created)) ?>
    <br>
    <br>
    <br>
    <b><?= $supplier->business_name ?></b><br>
    <?= $supplier->business_address ?>
    <br>
    <br>
    <br>
    <b>Dear Ma'am/Sir:</b>
    <br>
    <br>
    <p>We are pleased to notify you that the <b>"<?= $model->purpose ?>"</b> is hereby awarded to you as the bidder with the Lowest Responsive Bid at a Contract Price equivalent to <b><?= strtoupper(Yii::$app->controller->module->getNumberToWords(sprintf('%0.2f',$bid->getBidTotal($supplier->id)))) ?> (Php <?= number_format($bid->getBidTotal($supplier->id), 2) ?>).</b></p>
    <!-- <table class="table table-bordered table-striped table-responsive table-condensed">
        <thead>
            <tr>
                <td align=center><b>Unit</b></td>
                <td align=center><b>Description</b></td>
                <td align=center><b>Qty</b></td>
                <td align=right><b>Unit Cost</b></td>
                <td align=right><b>Amount</b></td>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($items)){ ?>
                <?php foreach($items as $item){ ?>
                    <tr>
                        <td align=center><?= $item['unit'] ?></td>
                        <td><?= $item['item'] ?></td>
                        <td align=center><?= number_format($item['total'], 0) ?></td>
                        <td align=right><?= number_format($item['cost'], 2) ?></td>
                        <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <td align=right colspan=4><b>TOTAL AMOUNT</b></td>
                <td align=right><b>Php <?= number_format($bid->getBidTotal($supplier->id), 2) ?></b></td>
            </tr>
        </tbody>
    </table> -->
    You are, therefore, requested to enter into a contract with us upon receipt of this notice.
    <br>
    <br>
    <br>
    Very Truly Yours,
    <br>
    <br>
    <br>
    <b><?= $rd->value ?></b>
    <br>
    Regional Director
    <br>
    <br>
    <br>
    <i>Conforme:</i>
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