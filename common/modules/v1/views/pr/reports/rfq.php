<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;

$asset = AppAsset::register($this);
?>
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
<link rel="stylesheet" href="<?= $asset->baseUrl.'/css/site.css' ?>" />
<div class="rfq-content">
    <div style="width: 100%;" class="text-center flex-center">
        <img src="<?= $asset->baseUrl.'/images/logo.png' ?>" style="height: auto; width: 100px; float: left; z-index: 2; padding-right: 20px;" />
        <p class="text-center" style="float: left;">Republic of the Philippines<br>
        <b><?= $agency->value ?></b><br>
        <?= $regionalOffice->value ?><br>
        <?= $address->value ?><br>  
        Email Add: <?= $email->value ?>, Tel. Nos.: <?= $telephoneNos->value ?></p>
    </div>
    <h3 class="text-center"><u>REQUEST FOR QUOTATION</u></h3>
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
    <p style="text-indent: 50px;">Very truly yours,</p>
    <br>
    <p style="text-indent: 50px;"><b><?= strtoupper($bacChairperson->name) ?></b><br>
    <span style="margin-left: 50px;"><i>BAC Chairperson</i></span></p>
    <br>
    <div class="row" style="margin-left: 50px;">
        <div class="col-md-1"><b>NOTE:</b></div>
        <div class="col-md-11">
            <ol type="1">
                <li>ALL ENTRIES MUST BE PRINTED LEGIBLY</li>
                <li>DELIVERY PERIOD WITHIN <b><?= $rfq->delivery_period != '' ? '<u>'.$rfq->delivery_period.'</u>' : '<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span>' ?></b></u> CALENDAR DAYS.</li>
                <li>WARRANTY SHALL BE FOR A PERIOD OF <u><?= $rfq->supply_warranty != '' ? $rfq->supply_warranty > 1 ? '<u><b>'.$rfq->supply_warranty.' '.$rfq->supply_warranty_unit.'</b></u>' : '<u><b>'.$rfq->supply_warranty.' '.substr_replace($rfq->supply_warranty_unit, "", -1).'</b></u>' : '<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span>'?></u> FOR SUPPLIES & MATERIALS, <br>
                <?= $rfq->supply_equipment != '' ? $rfq->supply_equipment > 1 ? '<u><b>'.$rfq->supply_equipment.' '.$rfq->supply_equipment_unit.'</b></u>' : '<u><b>'.$rfq->supply_equipment.' '.substr_replace($rfq->supply_equipment_unit, "", -1).'</b></u>' : '<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span>' ?> FOR EQUIPMENT, FROM DATE OF ACCEPTANCE BY THE PROCURING ENTITY.
                </li>
                <li>PRICE VALIDITY SHALL BE FOR A PERIOD OF <?= $rfq->price_validity != '' ?  '<u><b>'.$rfq->price_validity.'</b></u>' : '<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span>' ?> CALENDAR DAYS.</li>
                <li>LEGAL DOCUMENTS STATED IN ANNEX "H" OF RA 9184 AND ITS 2016 REVISED IMPLEMENTING
                    RULES AND REGULATIONS SHALL BE ATTACHED UPON SUBMISSION OF QUOTATIONS.
                </li>
                <li>THIS OFFICE RESERVES THE RIGHT TO REJECT ANY OR ALL QUOTATIONS WITHOUT INCURRING ANY
                LIABILITY AND ACCOUNT SUCH QUOTATIONS AS MAYBE CONSIDERED MOST ADVANTAGEOUS TO 
                THE GOVERNMENT.</li>
                <li>MODE OF PROCUREMENT: <b><?= strtoupper($model->procurementModeName) ?></b></li>
                <li>NUMBER OF LOT(S): <?= $model->getLots()->count() > 0 ? '<u><b>'.$model->getLots()->count().'</b></u>' : '<span style="display: inline-block; border-bottom: 1px solid black; width: 40px;"></span>' ?></li>
                <li>TOTAL ABC: <b>Php <?= number_format($model->rfqTotal, 2) ?></b></li>
            </ol>
        </div>
    </div>

    <table class="table-bordered">
        <thead>
            <tr>
                <td align=center><b>ITEM NO.</b></td>
                <td align=center><b>QTY.</b></td>
                <td align=center><b>UNIT</b></td>
                <td align=center><b>ITEM DESCRIPTION</b></td>
                <td align=center><b>BRAND & MODEL</b></td>
                <td align=center><b>UNIT PRICE</b></td>
                <td align=center><b>TOTAL AMOUNT</b></td>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php if(!empty($lotItems)){ ?>
                <?php foreach($lotItems as $lot => $items){ ?>
                    <?php if($lot != 0){ ?>
                    <tr>
                        <td colspan=8 style="background-color: #D9D9D9 !important;"><b><?= $lot ?></b></td>
                    </tr>
                    <?php } ?>
                    <?php if(!empty($items)){ ?>
                        <?php foreach($items as $item){ ?>
                            <tr>
                                <td align=center><?= $i ?></td>
                                <td align=center><?= number_format($item['total'], 0) ?></td>
                                <td align=center><?= $item['unit'] ?></td>
                                <td><?= $item['item'] ?>
                                <br>
                                <?php if(isset($specifications[$item['id']])){ ?>
                                    <?php if(!empty($specifications[$item['id']]->risItemSpecFiles)){ ?>
                                        <i>(Please see attached Specifications for your reference.)</i>
                                        <br>
                                    <?php } ?>
                                    <i><?= $specifications[$item['id']]->risItemSpecValueString ?></i>
                                <?php } ?>
                                </td>
                                <td>&nbsp;</td>
                                <td align=right>P<span style="display: inline-block; border-bottom: 1px solid black; width: 60%;"></span></td>
                                <td align=right>P<span style="display: inline-block; border-bottom: 1px solid black; width: 60%;"></span></td>
                            </tr>
                            <?php $i++; ?>
                        <?php } ?>
                    <?php } ?>
                    <tr>
                        <td colspan=6 align=right><b>TOTAL AMOUNT</b></td>
                        <td align=right><b>P<span style="display: inline-block; border-bottom: 1px solid black; width: 60%;"></span></b></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align=center><i><b>xxxx NOTHING FOLLOWS xxxxx</b></i></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan=6 align=right><b>GRAND TOTAL</b></td>
                <td align=right><b>P<span style="display: inline-block; border-bottom: 1px solid black; width: 60%;"></span></b></td>
            </tr>
        </tbody>
    </table>
    <p style="text-indent: 50px;">After having carefully read and accepted your General Conditions, I/We quote you the Gross Price (inclusive  of tax) on the item/items stated above.</p>
    <br>
    <br>
    <br>
    <p><span style="display: inline-block; float: right; border-bottom: 1px solid black; width: 300px;"></span></p>
    <p style="clear: both;"></p>
    <p style="float: right; text-align: center;">Signature over Printed Name of Authorized <br> Representative/Owner</p>
    <br>
    <br>
    <br>
    <i>RFQ No.: <?= $rfq->rfq_no ?></i>
</div>