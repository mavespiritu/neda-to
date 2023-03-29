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
<p style="text-indent: 50px;">This agreement made and entered into this <?= date("F j, Y", strtotime($contractModel->po_date)) ?> by and between the <b><?= $agency->value ?> REGIONAL OFFICE 1</b>, represented by the Regional Director, <b><?= $rd->value ?></b>, hereinafter known as the party of the first part and <b><?= $supplier->business_name ?></b> represented by <b><?= $supplier->owner_name ?></b>, hereinafter known as the party of the second part.</p>
<p class="text-center"><b>W I T N E S S E T H:</b></p>
<p style="text-indent: 50px;">1. That the Party of the Second Part shall provide the following:</p>
<p style="text-indent: 50px;"><b><?= $model->purpose ?>:</b></p>
<span style="text-indent: 50px;"><?= $contractModel->content ?></span>
<p style="text-indent: 50px;">2. That the Party of the First Part shall pay the Party of the Second Part in Philippine Currency the amount of <b><?= strtoupper(Yii::$app->controller->module->getNumberToWords(sprintf('%0.2f',$total['total']))) ?> (Php <?= number_format($total['total'], 2) ?>) ONLY</b> upon satisfactory completion of the service contracted for.</p>
<p style="text-indent: 50px;">3. That this Contract shall automatically cease to be of any force and effect when sooner terminated at
the option of any or both parties. In such case, payment shall be made on the basis of percentage of service
completed.</p>
<p style="text-indent: 50px;"><b>IN WITNESS WHEREOF</b>, both parties sign this Agreement this <b>17th day of October 2022</b> at the <?= ucwords(strtolower($agency->value))?> Regional Office 1, <?= $address->value ?>.</p>
<table style="width: 100%">
    <tr>
        <td style="width: 50%;">
            <i><u>PARTY OF THE FIRST PART</u></i>
            <br>
            <br>
            <b><?= $agency->value ?> <br> REGIONAL OFFICE 1</b>
            <br>
            <br>
            <br>
            <b><?= $rd->value ?></b><br>
            Regional Director
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
            <b><?= $supplier->business_name ?></b><br>
            (Signature)
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