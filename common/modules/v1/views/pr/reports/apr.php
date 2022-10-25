<style>
    *{ font-family: "Tahoma"; }
    h4{ text-align: center; } 
    p{ font-size: 10px; font-family: "Tahoma";}
    table{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    thead{
        font-size: 12px;
    }

    td{
        font-size: 10px;
        border: 1px solid black;
        padding: 3px 3px;
    }

    th{
        font-size: 10px;
        text-align: center;
        border: 1px solid black;
        padding: 3px 3px;
    }
</style>

<table class="table table-bordered table-responsive table-hover table-condensed">
    <thead>
        <tr>
            <td rowspan=2 colspan=3>NAME & ADDRESS <br> OF REQUESTING <br> AGENCY <br><br></td>
            <td rowspan=2 colspan=2><b><?= $agency->value ?></b><br><?= $regionalOffice->value ?><br><?= $address->value ?> <br><br></td>
            <td colspan=3 style="vertical-align: bottom;">ACC. CODE: </td>
        </tr>
        <tr>
            <td colspan=3 style="vertical-align: bottom;">Agency Control No. <br><?= $model->pr_no ?></td>
        </tr>
        <tr>
            <td colspan=5 style="vertical-align: bottom;" colspan=2 align=center><b>AGENCY PROCUREMENT REQUEST</b></td>
            <td colspan=3 style="vertical-align: bottom;">PS APR No.</td>
        </tr>
        <tr>
            <td colspan=5 style="width: 80%; border-right: none !important;">
                <p>
                    TO: <br>
                    <?= $supplier->business_name ?> <br>
                    <?= $supplier->business_address ?> <br>
                </p>
                <p style="text-align: center;">ACTION REQUEST ON THE ITEM(S) LISTED BELOW</p>
                <p>
                    [<?= $check_1 == 1 ? '&#10004;' : '' ?>] Please furnish us with Price Estimate (for office equipment/furniture & supplementary items) <br>
                    [<?= $check_2 == 1 ? '&#10004;' : '' ?>] Please purchase for our agency the equipment/furniture/supplementary items per your Price Estimate <br>
                    &nbsp;&nbsp;&nbsp; (PS RAD No. <?= $rad_no != '' ? '<u>'.$rad_no.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?> attached) dated 
                    <?= $rad_month != '' ? '<u>'.$rad_month.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?>-<?= $rad_year != '' ? '<u>'.$rad_year.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?> <br>
                    [<?= $check_3 == 1 ? '&#10004;' : '' ?>] Please issue common-use supplies/materials per PS Price List as of <?= $pl_month != '' ? '<u>'.$pl_month.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?>-<?= $pl_year != '' ? '<u>'.$pl_year.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?> <br>
                    [<?= $check_4 == 1 ? '&#10004;' : '' ?>] Please issue Certificate of Price Reasonableness <br>
                    [<?= $check_5 == 1 ? '&#10004;' : '' ?>] Please furnish us with your latest/updated Price list <br>
                    [<?= $check_6 == 1 ? '&#10004;' : '' ?>] Others (specify) <?= $other != '' ? '<u>'.$other.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?>
                </p>
            </td>
            <td colspan=3 style="text-align: center; vertical-align: top; width: 20%;">
            <?= $date_generated != '' ? date("F j, Y", strtotime($date_generated)) : '' ?>
            <br>
            <i>(Date Prepared)</i>
            </td>
        </tr>
        <tr>
            <td align=center colspan=8>IMPORTANT! PLEASE SEE INSTRUCTIONS/CONDITIONS AT THE BACK OF ORIGINAL COPY</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align=center style="width: 5%;"><b>No.</b></td>
            <td align=center colspan=3 style="width: 50%;"><b>ITEM and DESCRIPTION/SPECIFICATIONS/STOCK No.</b></td>
            <td align=center style="width: 10%;"><b>QUANTITY</b></td>
            <td align=center style="width: 10%;"><b>UNIT</b></td>
            <td align=center style="width: 10%;"><b>UNIT PRICE</b></td>
            <td align=center style="width: 10%;"><b>AMOUNT</b></td>
        </tr>
        <?php if(!empty($aprItems)){ ?>
            <?php $i = 1; ?>
            <?php foreach($aprItems as $item){ ?>
                <tr>
                    <td align=center><?= $i ?></td>
                    <td colspan=3><?= $item['item'] ?><br>
                    <i><?= isset($specifications[$item['id']]) ? $specifications[$item['id']]->risItemSpecValueString : '' ?></i>
                    </td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=center><?= $item['unit'] ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php $i++; ?>
            <?php } ?>
        <?php } ?>
        <?php if(!empty($specifications)){ ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan=3 align=center>(Please see attached specifications for your reference.)</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <?php } ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan=3 align=center>xxxxxxxxxxxxxx NOTHING FOLLOWS xxxxxxxxxxxxxxx</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan=4><?= $shortName->value ?> Office Telefax No: <?= $telefax != '' ? '<u>'.$telefax.'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>' ?></td>
            <td colspan=2 align=right>Total AMOUNT:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan=8 align=center>NOTE: ALL SIGNATURES MUST BE OVER PRINTED NAME</td>
        </tr>
    </tbody>
</table>
<table style="table table-bordered table-responsive table-hover table-condensed">
    <tr>
        <td style="width: 30%">
            STOCKS REQUESTED ARE CERTIFIED <br>
            TO BE WITHIN APPROVED PROGRAM: <br>
            <br>
            <br>
            <p style="text-align: center"><b><?= strtoupper($apr->stockCertifierName) ?></b><br><?= $apr->stockCertifier ? $apr->stockCertifier->position.' (Supply Officer)' : '' ?></p>
        </td>
        <td style="width: 30%">
            FUNDS CERTIFIED AVAILABLE:
            <br>
            <br>
            <br>
            <br>
            <p style="text-align: center"><b><?= strtoupper($apr->fundsCertifierName) ?></b><br><?= $apr->fundsCertifier ? $apr->fundsCertifier->position : '' ?></p>
        </td>
        <td style="width: 30%">
            APPROVED:
            <br>
            <br>
            <br>
            <br>
            <p style="text-align: center"><b><?= strtoupper($apr->approverName) ?></b><br><?= $apr->approver ? $apr->approver->position : '' ?></p>
        </td>
    </tr>
</table>