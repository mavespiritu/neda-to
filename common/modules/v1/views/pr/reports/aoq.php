<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
$letters = range('A', 'Z');

?>
<style>
    @media print {
        body {-webkit-print-color-adjust: exact; }
        *{
            page-break-before: avoid !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
        }
        table { page-break-inside:avoid !important;}
        tr    { page-break-inside:avoid !important; page-break-after:avoid !important;}
        thead { display:table-header-group !important;}
        tfoot { display:table-footer-group !important;}
    }
    *{ font-family: "Century Gothic"; font-size: 12px;}
    h3, h4{ text-align: center; } 
    p{ font-family: "Century Gothic";}
    table{
        font-family: "Century Gothic";
        border-collapse: collapse;
        width: 100%;
    }
    table.table-bordered{
        font-family: "Century Gothic";
        border-collapse: collapse;
        width: 100%;
    }
    thead{
        font-size: 12px;
    }

    table.table-bordered td{
        font-size: 12px;
        border: 1px solid #555555 !important;
        padding: 3px 3px;
    }

    table.table-bordered th{
        font-size: 12px;
        text-align: center;
        border: 1px solid #555555 !important;
        padding: 3px 3px;
    }
</style>
<div class="aoq-content">
    <div style="width: 100%;" class="text-center flex-center">
        <img src="<?= $asset->baseUrl.'/images/logo.png' ?>" style="height: auto; width: 120px; float: left; z-index: 2; padding-right: 20px;" />
        <p class="text-center" style="float: left;">Republic of the Philippines<br>
        <b><?= $agency->value ?></b><br>
        <?= $regionalOffice->value ?><br>
        <?= $address->value ?><br>  
        Email Add: <?= $email->value ?>, Tel. Nos.: <?= $telephoneNos->value ?></p>
    </div>
    <br>
    <table style="width: 100%;">
       <tr>
           <td style="width: 35%;">&nbsp;</td>
           <td style="width: 35%;">&nbsp;</td>
           <td style="width: 30%;"><b>CANVAS/BID NO. </b><u><?= $bid->bid_no ?></u></td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
           <td><b>DATE OF OPENING: </b><u><?= date("F j, Y", strtotime($bid->date_opened)) ?></u></td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
           <td><b>TIME OF OPENING: </b><u><?= $bid->time_opened ?></u></td>
       </tr>
       <tr>
           <td><b>REQUISITION NO. </b> <u><?= $risNumbers ?></u></td>
           <td><b>OBLIGATION NO. </b> <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>
           <td><b>ABC: </b><u>P<?= number_format($model->rfqTotal, 2) ?></u></td>
       </tr>
    </table>
    <br>
    <table class="table-bordered">
        <thead>
            <tr>
                <td align=center rowspan=3><b>ITEM NO.</b></td>
                <td align=center rowspan=3><b>NOMENCLATURE</b></td>
                <td align=center rowspan=3><b>Qty</b></td>
                <td align=center rowspan=3><b>Unit of Measurement</b></td>
                <td align=center colspan="<?= count($supplierList) * 3 ?>"><b>For identification of participating establishments, please see below</b></td>
                <td align=center rowspan=3 style="width: 10%;"><b>Justification</b></td>
                <td align=center rowspan=3 style="width: 10%;"><b>Award Recommended to</b></td>
                <td align=center rowspan=3><b>Price and Date <br> of Last <br>Purchase</b></td>
            </tr>
            <tr>
                <?php if($supplierList){ ?>
                    <?php foreach($supplierList as $idx => $supplier){ ?>
                        <td align=center style="width: 10%;" colspan=2><b><?= $letters[$idx] ?>.<br> <?= $supplier->business_name ?></b></td>
                        <td align=center style="width: 10%;" rowspan=2><b>Specifications</b></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr>
                <?php if($supplierList){ ?>
                    <?php foreach($supplierList as $idx => $supplier){ ?>
                        <td align=center style="width: 10%;">Unit Price</td>
                        <td align=center style="width: 10%;">Total Amount</td>
                    <?php } ?>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php $total = []; ?>
            <?php if(!empty($lotItems)){ ?>
                <?php $i = 1; ?>
                <?php if($supplierList){ ?>
                    <?php foreach($supplierList as $sup){ ?>
                        <?php $total[$sup->id] = 0; ?>
                    <?php } ?>
                <?php } ?>
                <?php foreach($lotItems as $lot => $items){ ?>
                    <?php if($lot != 0){ ?>
                        <tr>
                            <td colspan=<?= !empty($supplierList) ? 4 + (count($supplierList) * 3) : 4 ?> style="background-color: #D9D9D9 !important;"><b><?= $lot ?></b></td>
                           
                        </tr>
                    <?php } ?>
                    <?php if(!empty($items)){ ?>
                        <?php foreach($items as $item){ ?>
                            <?php if($i == 1){ ?>
                                <tr>
                                    <td align=center><?= $i ?></td>
                                    <td><?= $item['item'] ?></td>
                                    <td align=center><?= number_format($item['total'], 0) ?></td>
                                    <td align=center><?= $item['unit'] ?></td>
                                    <?php if($supplierList){ ?>
                                        <?php foreach($supplierList as $sup){ ?>
                                        <?= !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost > 0 ? '<td align=right>'.number_format($prices[$item['id']][$sup->id]->cost, 2).'</td>' : '<td>&nbsp</td>' : '<td>&nbsp</td>' ?></td>
                                        <?= !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost > 0 ? '<td align=right style="background-color: '.$colors[$item['id']][$sup->id].' !important;"><b>'.number_format($prices[$item['id']][$sup->id]->cost * $item['total'], 2).'</b></td>' : '<td align=right>-</td>' : '<td align=right>-</td>' ?></td>
                                        <td><?= !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost > 0 ? $prices[$item['id']][$sup->id]->specification : 'No quotation received' : 'No quotation received' ?></td>
                                        <?php $total[$sup->id] += !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost * $item['total'] : 0; ?>
                                        <?php } ?>
                                    <?php } ?>
                                    <td rowspan=<?= count($lotItems) + count($rfqItems) - 1 ?> style="vertical-align: top;"><?= $bid->justification ?></td>
                                    <td rowspan=<?= count($lotItems) + count($rfqItems) - 1 ?> style="vertical-align: top;"><?= $bid->recommendation ?></td>
                                    <td align=center>&nbsp;</td>
                                </tr>
                            <?php }else{ ?>
                                <tr>
                                    <td align=center><?= $i ?></td>
                                    <td><?= $item['item'] ?></td>
                                    <td align=center><?= number_format($item['total'], 0) ?></td>
                                    <td align=center><?= $item['unit'] ?></td>
                                    <?php if($supplierList){ ?>
                                        <?php foreach($supplierList as $sup){ ?>
                                        <?= !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost > 0 ? '<td align=right>'.number_format($prices[$item['id']][$sup->id]->cost, 2).'</td>' : '<td>&nbsp</td>' : '<td>&nbsp</td>' ?>
                                        <?= !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost > 0 ? '<td align=right style="background-color: '.$colors[$item['id']][$sup->id].' !important;"><b>'.number_format($prices[$item['id']][$sup->id]->cost * $item['total'], 2).'</b></td>' : '<td align=right>-</td>' : '<td align=right>-</td>' ?>
                                        <td><?= !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost > 0 ? $prices[$item['id']][$sup->id]->specification : 'No quotation received' : 'No quotation received' ?></td>
                                        <?php $total[$sup->id] += !empty($prices[$item['id']][$sup->id]) ? $prices[$item['id']][$sup->id]->cost * $item['total'] : 0; ?>
                                        <?php } ?>
                                    <?php } ?>
                                    <td align=center>&nbsp;</td>
                                </tr>
                            <?php } ?>
                            <?php $i++ ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <tr>
                <td colspan=4 align=right><b>Total Lump Sum Offer</b></td>
                <?php if($supplierList){ ?>
                    <?php foreach($supplierList as $sup){ ?>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($total[$sup->id], 2) ?></b></td>
                        <td>&nbsp;</td>
                    <?php } ?>
                <?php } ?>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <p style="text-indent: 50px;"><i>WE CERTIFY that we opened, read and recorded herein quotations received in response to the Canvass/Bid No. <?= $bid->bid_no ?> and AWARD IS HEREBY RECOMMENDED BY THE COMMITTEE.</i></p>
    <br>
    <table style="width: 100%;">
    <?php if(!empty($bidMembers)){ ?>
        <tr>
        <?php foreach($bidMembers as $member){ ?>
            <td>
                <br>
                <br>
                <b><?= $member->signatory ? $member->signatory->name : '' ?></b><br>
                <i><?= $member->position == 'Provisional Member - End User' ? $member->position.' ('.$model->officeName.')' : $member->position ?></i>
            </td>
        <?php } ?>
        </tr>
    <?php } ?>
    </table>
    <br>
    <br>
    <table style="width: 100%">
        <tr>
            <td style="width: 30%;">
                <p><i>APPROVED:</i></p><br>
                <b><?= $regionalDirector->value ?></b><br>
                <i>Regional Director</i>   
            </td>
            <td style="width: 30%;">
                <p><u>List of Participating Establishments</u></p><br>
                <?php if($supplierList){ ?>
                    <ol type="A">
                    <?php foreach($supplierList as $sup){ ?>
                    <li><?= $sup->business_name ?></li>
                    <?php } ?>    
                    </ol>
                <?php } ?>
            </td>
            <td style="width: 30%;">
                <br>
                <p>NOTE: Committee created in <br>consonance with Department <br>Order No. <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p>
            </td>
        </tr>
    </table>
</div>
