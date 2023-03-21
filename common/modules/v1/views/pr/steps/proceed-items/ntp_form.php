<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */

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

<h3 class="panel-title"><?= $j ?>.<?= $i ?>.<?= $k ?> NTP for <?= $po->type == 'PO' ? 'PO No.' : 'Contract No.' ?> <?= $po->pocnNo ?>
<span class="pull-right">
<?= !$ntpModel->isNewRecord ? Html::a('<i class="fa fa-print"></i> Print', null, ['href' => 'javascript:void(0)', 'onClick' => 'printNtp('.$ntpModel->id.')', 'class' => 'btn btn-info']) : '' ?>
</span>
</h3>
<br>
<?php $form = ActiveForm::begin([
    'id' => 'ntp-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<div class="ntp-content">
    <h5 class="text-center"><b>NOTICE TO PROCEED</b></h5>
    <br>
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <?= $form->field($ntpModel, 'date_created')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
    </div>
    <br>
    <b><?= $supplier->business_name ?></b>
    <br>
    <?= $supplier->business_address ?>
    <br>
    <br>
    <b>Dear Ma'am/Sir:</b>
    <br>
    <br>
    <p>We are pleased to inform you to proceed with the implementation of the <b><?= $model->purpose ?> with <?= $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo ?></b> with a Contract Price equivalent to <b><?= strtoupper(numberToWords($total['total'])) ?> PESOS (Php <?= number_format($total['total'], 2) ?>).</b>
    <br>
    <br>
    In this regard, please be directed to proceed on 
    <div class="row">
        <div class="col-md-4 col-xs-12">
        <?= $form->field($ntpModel, 'date_proceeded')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ])->label(false); ?>
        </div>
    </div>
    . We will appreciate your immediate action. Thank you.
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
<br>
<br>
<div class="pull-right">
<?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        $("#ntp-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    alert("NTP has been saved");
                    createNtp('.$model->id.','.$po->id.','.$j.','.$i.','.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });

        function printNtp(id)
        {
            var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-ntp']).'?id="+ id,
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