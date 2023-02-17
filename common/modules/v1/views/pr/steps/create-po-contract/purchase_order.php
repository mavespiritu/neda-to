<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
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

<h3 class="panel-title"><?= $j ?>.<?= $i ?>.<?= $k ?> Preview Purchase Order for Supplier, <?= $supplier->business_name ?>
<span class="pull-right">
<?= !$poModel->isNewRecord ? Html::a('<i class="fa fa-print"></i> Print', null, ['href' => 'javascript:void(0)', 'onClick' => 'printPo('.$poModel->id.')', 'class' => 'btn btn-info']) : '' ?>
</span></h3>
<br>
<br>
<?php $form = ActiveForm::begin([
    'id' => 'po-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
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
            <td style="width: 35%;"><u><?= !$poModel->isNewRecord ? $poModel->po_no : $model->pr_no.'-'.$no ?></u></td>
        </tr>
        <tr>
            <td>Address:</td>
            <td><u><?= $supplier->business_address ?></u></td>
            <td>Date:</td>
            <td>
                <?= $form->field($poModel, 'po_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ])->label(false); ?>
            </td>
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
            <td><?= $exactAddress->value ?></td>
            <td>Delivery Term:</td>
            <td><?= $form->field($poModel, 'delivery_term')->textInput(['maxlength' => true])->label(false) ?></td>
        </tr>
        <tr>
            <td>Date of Delivery:</td>
            <td>
                <?= $form->field($poModel, 'delivery_date')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                        'clientOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ],
                    ])->label(false); ?>
            </td>
            <td>Payment Term:</td>
            <td>
                <?= $form->field($poModel, 'payment_term_id')->widget(Select2::classname(), [
                    'data' => $paymentTerms,
                    'options' => ['placeholder' => 'Select Payment Term','multiple' => false, 'class'=>'payment-term-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ])->label(false) ?>
            </td>
        </tr>
    </table>
    <table class="table table-bordered table-striped table-hover table-responsive" style="width: 100%;">
        <tr>
            <td align=center><b>Stock/Property No.</b></td>
            <td align=center><b>Unit</b></td>
            <td align=center style="width: 50%" colspan=2><b>Description</b></td>
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
            <td align=center colspan=2><i><?= !empty($specifications) ? '(Please see attached specifications for your reference.)' : '' ?></i></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan=6>Total Amount in words: <b><?= ucwords(strtolower(numberTowords($total))) ?> Pesos</b></td>
            <td align=right><b><?= number_format($total, 2) ?></b></td>
        </tr>
        <tr>
            <td colspan=7>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;In case of failure to make the full delivery within the time specified above, a penalty of one-tenth(1/10) of one percent for every day of delay shall be imposed on the undelivered item/s.
            <br>
            <br>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan=2>
                <b>Conforme:</b>
                <br>
                <br>
                <br>
                <?= $form->field($poModel, 'represented_by')->textInput(['maxlength' => true, 'style' => 'width: 300px;', 'value' => is_null($poModel->represented_by) ? $supplier->owner_name : $poModel->represented_by])->label(false) ?>
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
                <?= $rd->value ?>
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
            <td>
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
    <br>
    <div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
    </div>
    <div class="clearfix"></div>
</div>

<?php ActiveForm::end(); ?>
<?php
    $script = !is_null($bid) ? '
        $("#po-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Purchase order has been saved");
                    createPurchaseOrder('.$model->id.','.$bid->id.','.$supplier->id.', '.$j.', '.$i.', '.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });' : 
        '
        $("#po-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Purchase order has been saved");
                    createPurchaseOrder('.$model->id.',"null",'.$supplier->id.', '.$j.', '.$i.', '.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });';

    $script .= '
        function printPo(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-po']).'?id=" + id, 
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