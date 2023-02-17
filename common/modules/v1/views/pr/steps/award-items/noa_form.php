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

function numberTowords($num)
{
    $ones = array(
        0 =>"ZERO",
        1 => "ONE",
        2 => "TWO",
        3 => "THREE",
        4 => "FOUR",
        5 => "FIVE",
        6 => "SIX",
        7 => "SEVEN",
        8 => "EIGHT",
        9 => "NINE",
        10 => "TEN",
        11 => "ELEVEN",
        12 => "TWELVE",
        13 => "THIRTEEN",
        14 => "FOURTEEN",
        15 => "FIFTEEN",
        16 => "SIXTEEN",
        17 => "SEVENTEEN",
        18 => "EIGHTEEN",
        19 => "NINETEEN",
        "014" => "FOURTEEN"
    );
    
    $tens = array( 
        0 => "ZERO",
        1 => "TEN",
        2 => "TWENTY",
        3 => "THIRTY", 
        4 => "FORTY", 
        5 => "FIFTY", 
        6 => "SIXTY", 
        7 => "SEVENTY", 
        8 => "EIGHTY", 
        9 => "NINETY" 
    );

    $hundreds = array( 
    "HUNDRED", 
    "THOUSAND", 
    "MILLION", 
    "BILLION", 
    "TRILLION", 
    "QUARDRILLION" 
    ); /*limit t quadrillion */

    $num = number_format($num,2,".",","); 
    $num_arr = explode(".",$num); 
    $wholenum = $num_arr[0]; 
    $decnum = $num_arr[1]; 
    $whole_arr = array_reverse(explode(",",$wholenum)); 
    krsort($whole_arr,1); 
    $rettxt = ""; 
    foreach($whole_arr as $key => $i){
        while(substr($i,0,1)=="0"){ $i=substr($i,1,5); }
        if($i < 20){ 
        /* echo "getting:".$i; */
        $rettxt .= $i == "" ? "" : $ones[$i]; 
        }elseif($i < 100){ 
            if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
            if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
        }else{ 
            if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
            if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
            if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
        } 
        if($key > 0){ 
            $rettxt .= " ".$hundreds[$key]." "; 
        }
    } 

    if($decnum > 0){
        $rettxt .= " and ";
        if($decnum < 20){
            $rettxt .= $ones[intval($decnum)];
        }elseif($decnum < 100){
            $rettxt .= $tens[substr($decnum,0,1)];
            $rettxt .= " ".$ones[substr($decnum,1,1)]."/100";
        }
    }
return $rettxt;
}
?>

<h3 class="panel-title"><?= $j ?>.<?= $i ?>.<?= $k ?> NOA for <?= $supplier->business_name ?>
<span class="pull-right">
<?= !$noaModel->isNewRecord ? Html::a('<i class="fa fa-print"></i> Print', null, ['href' => 'javascript:void(0)', 'onClick' => 'printNoa('.$noaModel->id.')', 'class' => 'btn btn-info']) : '' ?>
</span>
</h3>
<br>
<?php $form = ActiveForm::begin([
    'id' => 'noa-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<div class="noa-content">
    <h5 class="text-center"><b>NOTICE OF AWARD</b></h5>
    <br>
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <?= $form->field($noaModel, 'date_created')->widget(DatePicker::classname(), [
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
    <p>We are pleased to notify you that the <b>"<?= $model->purpose ?>"</b> is hereby awarded to you as the bidder with the Lowest Responsive Bid at a Contract Price equivalent to <b><?= strtoupper(numberToWords($bid->getBidTotal($supplier->id))) ?> PESOS  (Php <?= number_format($bid->getBidTotal($supplier->id), 2) ?>).</b></p>
    <br>
    <br>
    <table class="table table-bordered table-striped table-responsive table-condensed">
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
    </table>
    <br>
    <br>
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
        $("#noa-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    alert("NOA has been saved");
                    createNoa('.$model->id.','.$bid->id.','.$supplier->id.','.$j.','.$i.','.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });

        function printNoa(id)
        {
            var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-noa']).'?id="+ id, 
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