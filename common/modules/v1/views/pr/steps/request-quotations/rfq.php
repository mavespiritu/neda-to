<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
?>

<h4>RFQ No. <?= $rfq->rfq_no ?></h4>
<div class="rfq-content">
    <div style="width: 100%;" class="text-center flex-center">
        <img src="<?= $asset->baseUrl.'/images/logo.png' ?>" style="height: auto; width: 100px; float: left; z-index: 2; padding-right: 20px;" />
        <p class="text-center" style="float: left;">Republic of the Philippines<br>
        <b><?= $agency->value ?></b><br>
        <?= $regionalOffice->value ?><br>
        <?= $address->value ?><br>  
        Email Add: <?= $email->value ?>, Tel. Nos.: <?= $telephoneNos->value ?></p>
    </div>
    <h4 class="text-center"><u>REQUEST FOR QUOTATION</u></h4>
    <table style="width: 100%;">
        <tr>
            <td style="width: 20%;">Company Name:</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
        <tr>
            <td>Complete Address:</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
        <tr>
            <td>Telephone No.:</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
        <tr>
            <td>Cellphone No.:</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
        <tr>
            <td>PhilGeps Reg. No.:</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
        <tr>
            <td>TIN:</td>
            <td><span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span></td>
        </tr>
    </table>
    <br>
    <p style="text-indent: 50px;">Please quote in a sealed envelope your lowest  price on the item/s listed below, subject to the General Conditions on the Purchase Request, submit your quotation duly signed not later than <u><b><?= date("F j, Y", strtotime($rfq->deadline_date))?> at <?= $rfq->deadline_time ?></b></u>.</p>
    <p>Very truly yours,</p>
    <br>
    <p style="text-indent: 50px;"><b><?= strtoupper($bacChairperson->name) ?></b><br>
    <span style="margin-left: 50px;"><i>BAC Chairperson</i></span></p>
    <br>
    <div class="row" style="margin-left: 50px;">
        <div class="col-md-1"><b>NOTE:</b></div>
        <div class="col-md-11">
            <ol type="1">
                <li>ALL ENTRIES MUST BE PRINTED LEGIBLY</li>
                <li>DELIVERY PERIOD WITHIN <u><?= $rfq->delivery_period ?></u> CALENDAR DAYS.</li>
                <li>WARRANTY SHALL BE FOR A PERIOD OF <u><?= $rfq->supply_warranty ?> <?= $rfq->supply_warranty > 1 ? $rfq->supply_warranty_unit : substr_replace($rfq->supply_warranty_unit, "", -1) ?></u> FOR SUPPLIES & MATERIALS, <br>
                <u><?= $rfq->supply_equipment ?> <?= $rfq->supply_equipment > 1 ? $rfq->supply_equipment_unit : substr_replace($rfq->supply_equipment_unit, "", -1) ?></u> FOR EQUIPMENT, FROM DATE OF ACCEPTANCE BY THE PROCURING ENTITY.
                </li>
                <li>PRICE VALIDITY SHALL BE FOR A PERIOD OF <u><?= $rfq->price_validity ?></u> CALENDAR DAYS.</li>
                <li>LEGAL DOCUMENTS STATED IN ANNEX "H" OF RA 9184 AND ITS 2016 REVISED IMPLEMENTING RULES AND REGULATIONS SHALL BE ATTACHED UPON SUBMISSION OF QUOTATIONS.</li>
                <li>THIS OFFICE RESERVES THE RIGHT TO REJECT ANY OR ALL QUOTATIONS WITHOUT INCURRING ANY
                LIABILITY AND ACCOUNT SUCH QUOTATIONS AS MAYBE CONSIDERED MOST ADVANTAGEOUS TO 
                THE GOVERNMENT.</li>
                <li>MODE OF PROCUREMENT: <b><?= strtoupper($model->procurementModeName) ?></b></li>
                <li>ABC: <b>P<?= number_format($model->rfqTotal, 2) ?></b></li>
            </ol>
        </div>
    </div>

    <table class="table table-bordered table-condensed table-striped table-hover">
        <thead>
            <tr>
                <td align=center><b>ITEM NO.</b></td>
                <td align=center><b>QTY.</b></td>
                <td align=center><b>UNIT</b></td>
                <td align=center><b>ITEM DESCRIPTION</b></td>
                <td align=center><b>BRAND & MODEL</b></td>
                <td align=center><b>TOTAL ABC PRICE <br> PER ITEM</b></td>
                <td align=center><b>UNIT PRICE</b></td>
                <td align=center><b>TOTAL AMOUNT</b></td>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; ?>
            <?php if(!empty($rfqItems)){ ?>
                <?php foreach($rfqItems as $item){ ?>
                <tr>
                    <td align=center><?= $i ?></td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=center><?= $item['unit'] ?></td>
                    <td><?= $item['item'] ?>
                    <br>
                    <?php if(isset($specifications[$item['id']])){ ?>
                        <?php if(!empty($specifications[$item['id']]->risItemSpecFiles)){ ?>
                        <table style="width: 100%">
                        <?php foreach($specifications[$item['id']]->risItemSpecFiles as $file){ ?>
                            <tr>
                            <td><?= Html::a($file->name.'.'.$file->type, ['/file/file/download', 'id' => $file->id]) ?></td>
                            <!-- <td align=right><?= Html::a('<i class="fa fa-trash"></i>', ['/file/file/delete', 'id' => $file->id], [
                                    'data' => [
                                        'confirm' => 'Are you sure you want to remove this item?',
                                        'method' => 'post',
                                    ],
                                ]) ?></td> -->
                            </tr>
                        <?php } ?>
                        </table>
                        <br>
                        <?php } ?>
                        <i><?= $specifications[$item['id']]->risItemSpecValueString ?></i>
                    <?php } ?>
                    </td>
                    <td>&nbsp;</td>
                    <td align=right>P<?= number_format($item['cost'], 2) ?></td>
                    <td align=center>P<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span></td>
                    <td align=center>P<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span></td>
                </tr>
                <?php $i++; ?>
                <?php } ?>
            <?php } ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align=center><i><b>xxxxx NOTHING FOLLOWS xxxxx</b></i></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <p style="text-indent: 50px;">After having carefully read and accepted your General Conditions, I/We quote you the Gross Price (inclusive  of tax) on the item/items stated above.</p>
    <br>
    <br>
    <br>
    <p><span style="display: inline-block; float: right; border-bottom: 1px solid black; width: 300px;"></span></p>
    <p style="float: right; text-align: center;">Signature over Printed Name of Authorized <br> Representative/Owner</p>
    <br>
    <br>
    <br>
    <i>RFQ No.: <?= $rfq->rfq_no ?></i>
</div>
<?php
    $script = '
        function printRfq(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-rfq']).'?id=" + id, 
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
    ';

    $this->registerJs($script, View::POS_END);
?>