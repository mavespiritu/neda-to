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
    *{ font-family: "Tahoma"; font-size: 12px;}
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

<div class="po-content">
<div style="width: 90%;" class="text-center flex-center">
        <img src="<?= $asset->baseUrl.'/images/logo.png' ?>" style="height: auto; width: 120px; float: left; z-index: 2; padding-right: 20px;" />
        <p class="text-center" style="float: left;">
        <b><?= $agency->value ?></b><br>
        <?= $regionalOffice->value ?><br>
        <?= $address->value ?><br><br>
        <u>P U R C H A S E&nbsp;&nbsp;O R D E R</u>
    </div>
    <br>
    <table class="table table-bordered" style="width: 100%;">
        <tr>
            <td style="width: 15%;">Supplier:</td>
            <td style="width: 30%;"><b><u><?= $supplier->business_name ?></u></b></td>
            <td style="width: 20%;">P.O. Number:</td>
            <td style="width: 35%;"><u><?= $poModel->po_no ?></u></td>
        </tr>
        <tr>
            <td>Address:</td>
            <td><u><?= $supplier->business_address ?></u></td>
            <td>Date:</td>
            <td><?= date("F j, Y", strtotime($poModel->po_date)) ?></td>
        </tr>
        <tr>
            <td>TIN:</td>
            <td><u><?= $supplier->tin_no ?></u></td>
            <td>Mode of Procurement:</td>
            <td><u><?= $model->procurementMode->title ?></u></td>
        </tr>
        <tr>
            <td>Gentlemen:</td>
            <td colspan=3>Please furnish this office the following articles subject to the terms and conditions contained herein:</td>
        </tr>
        <tr>
            <td>Place of Delivery:</td>
            <td><?= $poModel->delivery_place ?></td>
            <td>Delivery Term:</td>
            <td><?= $poModel->delivery_term ?></td>
        </tr>
        <tr>
            <td>Date of Delivery:</td>
            <td><?= $poModel->delivery_date ?></td>
            <td>Payment Term:</td>
            <td><?= $poModel->paymentTerm ? $poModel->paymentTerm->title : '' ?></td>
        </tr>
    </table>
    <table class="table table-bordered table-hover table-responsive" style="width: 100%;">
        <tr>
            <td align=center><b>Stock/Property No.</b></td>
            <td align=center><b>Unit</b></td>
            <td align=center style="width: 50%" colspan=2><b>Description</b></td>
            <td align=center><b>Brand/Model</b></td>
            <td align=center><b>Qty</b></td>
            <td align=center><b>Unit Cost</b></td>
            <td align=center><b>Amount</b></td>
        </tr>
        <?php $total = 0 ?>
        <?php if(!empty($items)){ ?>
            <?php foreach($items as $item){ ?>
                <tr>
                    <td align=center><?= $item['id'] ?></td>
                    <td><?= $item['unit'] ?></td>
                    <td colspan=2><?= $item['item'] ?></td>
                    <td><?= $item['specification'] ?></td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=right><?= number_format($item['cost'], 2) ?></td>
                    <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
                </tr>
                <?php $total += $item['total'] * $item['cost']; ?>
            <?php } ?>
        <?php } ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align=center colspan=3><i><?= !empty($specifications) ? '(Please see attached specifications for your reference.)' : '' ?></i></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan=7>Total Amount in words: <b><?= strtoupper(Yii::$app->controller->module->getNumberToWords(sprintf('%0.2f',$total))) ?></b></td>
            <td align=right><b><?= number_format($total, 2) ?></b></td>
        </tr>
        <tr>
            <td colspan=8>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;In case of failure to make the full delivery within the time specified above, a penalty of one-tenth(1/10) of one percent for every day of delay shall be imposed on the undelivered item/s.
            <br>
            <br>
            </td>
        </tr>
        <tr>
            <td colspan=2>&nbsp;</td>
            <td colspan=2>
                <b>Conforme:</b>
                <br>
                <br>
                <span style="display: inline-block; border-bottom: 1px solid black; width: 250px;"><?= $poModel->represented_by ?></span>
                <br>
                (Signature over printed name)
                <br>
                <br>
                <br>
                <span style="display: inline-block; border-bottom: 1px solid black; width: 250px;"></span>
                <br>
                (Date)
            </td>
            <td colspan=4>
                Very truly yours,
                <br>
                <br>
                <br>
                <b><?= $rd->value ?></b>
                <br>
                <i>Regional Director</i>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                Requesitioning Office/Dept.
                <br>
                <br>
                <br>
                <p class="text-center">
                    <b><?= $model->requesterName ?></b>
                    <br>
                    <?= $model->requester->position ?>,&nbsp;<?= $model->officeName ?>
                </p>
            </td>
            <td colspan=2>
                Fund Cluster:&nbsp;<?= $model->fundClusterName ?>
                <br>
                Fund Available:
                <br>
                <br>
                <p class="text-center">
                    <b><?= ucwords(strtolower($accountant->value)) ?></b>
                    <br>
                    <?= $accountantPosition->value ?>
                </p>
            </td>
            <td colspan=4>
                ORS/BURS No: 
                <br>
                Date of ORS/BURS:
                <br>
                <br>
                Amount..... :&nbsp;&nbsp;<b><?= number_format($total, 2) ?></b>
            </td>
        </tr>
    </table>
</div>