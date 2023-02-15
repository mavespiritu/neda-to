<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin([
    'id' => 'apr-price-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<table class="table table-bordered table-responsive table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Unit</th>
            <th>Item</th>
            <td align=center><b>Quantity</b></td>
            <td align=right><b>Current Price</b></td>
            <td align=right><b>Unit Price</b></td>
            <td align=right><b>Total Cost</b></td>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php $total = 0; ?>
    <?php if(!empty($aprItems)){ ?>
        <?php foreach($aprItems as $item){ ?>
            <?php $id = $item['id']; ?>
            <?= Html::hiddenInput('total-pricing-'.$item['id'].'-hidden', $item['total'] * $costModels[$item['id']]['cost'], ['id' => 'total-pricing-'.$item['id'].'-hidden']) ?>
            <tr>
                <td align=center><?= $i ?></td>
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
                <td align=center><?= number_format($item['total'], 0) ?></td>
                <td align=right><?= number_format($item['cost'], 2) ?></td>
                <td style="width: 20%;"><?= $form->field($costModels[$item['id']], "[$id]cost")->widget(MaskedInput::classname(), [
                    'options' => [
                        'autocomplete' => 'off',
                        'onKeyup' => 'getPriceTotal('.$item['id'].','.$item['total'].','.json_encode($itemIDs).')',
                    ],
                    'clientOptions' => [
                        'alias' =>  'decimal',
                        'removeMaskOnSubmit' => true,
                        'groupSeparator' => ',',
                        'autoGroup' => true,
                    ],
                ])->label(false) ?>
                </td>
                <td align=right><p id="total-pricing-<?= $item['id'] ?>"><?= isset($costModels[$item['id']]['cost']) ? number_format($item['total'] * $costModels[$item['id']]['cost'], 2) : '0.00' ?></p></td>
            </tr>
            <?php $total += isset($costModels[$item['id']]['cost']) ? $item['total'] * $costModels[$item['id']]['cost'] : 0 ?>
            <?php $i++; ?>
        <?php } ?>
    <?php }else{ ?>
        <tr>
            <td colspan=7 align=center>No items included</td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan=6 align=right><b>ABC:</b></td>
        <td align=right><b><p id="grand-total-pricing"><?= number_format($total, 2) ?></p></b></td>
        <?= Html::hiddenInput('grandtotal-pricing-hidden', 0, ['id' => 'grandtotal-pricing-hidden']) ?>
    </tr>
    </tbody>
</table>

<div class="form-group pull-right"> 
    <?= Html::submitButton('Save Prices', ['class' => 'btn btn-success', 'id' => 'dbm-pricing-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    function number_format (number, decimals, dec_point, thousands_sep) {
        number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === "undefined") ? "," : thousands_sep,
            dec = (typeof dec_point === "undefined") ? "." : dec_point,
            s = "",
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return "" + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || "").length < prec) {
            s[1] = s[1] || "";
            s[1] += new Array(prec - s[1].length + 1).join("0");
        }
        return s.join(dec);
    }

    function getPriceTotal(id, quantity, ids)
    {
      var cost = $("#pritemcost-"+id+"-cost").val().split(",").join("");
          cost = parseFloat(cost);
      var total = quantity * cost;

      $("#total-pricing-"+id+"-hidden").val(total);

      $("#total-pricing-"+id).empty();
      $("#total-pricing-"+id).html(number_format(total, 2, ".", ","));    
      
      getPriceGrandTotal(ids);
    }

    function getPriceGrandTotal(ids)
    {
      var grandTotal = 0;

      if(ids)
      {
        for(var key in ids)
        {
          grandTotal += parseFloat($("#total-pricing-"+key+"-hidden").val());
        }
      }

      $("#grand-total-pricing").empty();
      $("#grand-total-pricing").html(number_format(grandTotal, 2, ".", ","));  
      $("#grandtotal-pricing-hidden").val(grandTotal);  
    }

    $("#apr-price-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("APR quotation saved successfully");
                aprRetrieveQuotation('.$model->id.');
                $("html").animate({ scrollTop: 0 }, "slow");
            },
            error: function (err) {
                console.log(err);
            }
        }); 
        
        return false;
    });
    ';

    $this->registerJs($script, View::POS_END);
?>