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
<p style="text-align:right;">Contract No: <b><?= $contractModel->pocnNo ?></b></p>
<p><b>KNOW ALL MEN BY THESE PRESENTS:</b></p>
<br>
<p style="text-indent: 50px; text-align: justify;">This agreement made and entered into this <?= date("F j, Y", strtotime($contractModel->po_date)) ?> by and between the <b><?= $agency->value ?> REGIONAL OFFICE 1</b>, represented by the Regional Director, <b><?= $rd->value ?></b>, hereinafter known as the party of the first part and <b><?= $supplier->business_name ?></b> represented by <b><?= $supplier->owner_name ?></b>, hereinafter known as the party of the second part.</p>
<br>
<p class="text-center"><b>W I T N E S S E T H:</b></p>
<br>
<span style="text-indent: 50px; text-align: justify;"><?= $contractModel->content ?></span>
<br>
<table style="width: 100%">
    <tr>
        <td style="width: 50%;">
            <i><u>PARTY OF THE FIRST PART</u></i>
            <br>
            <br>
            <br>
            <b><?= $agency->value ?> <br> REGIONAL OFFICE 1</b>
            <br>
            <br>
            <br>
            <br>
            <b><?= $rd->value ?></b><br>
            Regional Director
            <br>
            <br>
            <br>
            <i><u>REQUISITION OFFICE/DEPT.</u></i>
            <br>
            <br>
            <br>
            <br>
            <b><?= strtoupper($model->requesterName) ?></b><br>
            <?= $model->requester->position ?>
        </td>
        <td style="width: 50%;">
            <i><u>PARTY OF THE SECOND PART</u></i>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <b><?= $supplier->business_name ?></b><br>
            (Signature)
            <br>
            <br>
            <br>
            <i><u>FUNDS AVAILABLE:</u></i>
            <br>
            <br>
            <br>
            <br>
            <b><?= $regionalAccountant ? strtoupper($regionalAccountant->name) : 'Setup Regional Accountant in Signatories Table' ?></b><br>
            Regional Accountant
        </td>
    </tr>
</table>