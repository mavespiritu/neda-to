<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;

$asset = AppAsset::register($this);
function numberToWords($number) {

    $hyphen      = ' ';
    $conjunction = ' and ';
    $separator   = ' ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . numberToWords(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .=  ' '. numberToWords($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $separator : $separator;
                $string .= numberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $fraction > 0 ? ' '.$conjunction.' '.$fraction.'/100 ' : $separator;
    }

    return $string;
}
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
    <p>We are pleased to notify you that the <b>"<?= $model->purpose ?>"</b> is hereby awarded to you as the bidder with the Lowest Responsive Bid at a Contract Price equivalent to <b><?= strtoupper(numberToWords($bid->getBidTotal($supplier->id))) ?> PESOS (Php <?= number_format($bid->getBidTotal($supplier->id), 2) ?>).</b></p>
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