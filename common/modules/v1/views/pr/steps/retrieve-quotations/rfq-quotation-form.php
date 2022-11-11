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
    'id' => 'retrieve-rfq-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<?php if($action == 'create'){ ?>
<div class="row">
    <div class="col-md-6 col-xs-12">
        <?= $form->field($rfqInfoModel, 'rfq_id')->widget(Select2::classname(), [
            'data' => $rfqs,
            'options' => ['placeholder' => 'Select RFQ','multiple' => false, 'class'=>'rfq-select'],
            'pluginOptions' => [
                'allowClear' =>  true,
            ],
            ]);
        ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <?= $form->field($rfqInfoModel, 'date_retrieved')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ])->label('Date Retrieved'); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-xs-12">
        <?= $form->field($rfqInfoModel, 'supplier_id')->widget(Select2::classname(), [
            'data' => $suppliers,
            'options' => ['placeholder' => 'Select Supplier','multiple' => false, 'class'=>'supplier-select'],
            'pluginOptions' => [
                'allowClear' =>  true,
            ],
            ]);
        ?>
    </div>
</div>
<?php }else{ ?>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <table class="table table-bordered">
            <tr>
                <td style="width: 50%"><b>RFQ No.</b></td>
                <td rowspan=2>
                    <?= $form->field($rfqInfoModel, 'date_retrieved')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                        'clientOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ],
                    ])->label('Date Retrieved'); ?>
                </td>
            </tr>
            <tr>
                <td><?= $rfqInfoModel->rfq->rfq_no ?></td>
            </tr>
            <tr>
                <td colspan=2><b>Supplier</b></td>
            </tr>
            <tr>
                <td colspan=2>
                <?= $rfqInfoModel->supplier->business_name ?><br>
                    <?= $rfqInfoModel->supplier->business_address ?><br>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php } ?>
<p><i class="fa fa-exclamation-circle"></i> Set amount to zero if supplier does not include the item.</p>
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
    <?php if(!empty($rfqItems)){ ?>
        <?php foreach($rfqItems as $item){ ?>
            <?php $id = $item['id']; ?>
            <?= Html::hiddenInput('total-pricing-'.$item['id'].'-hidden', $item['total'] * $costModels[$item['id']]['cost'], ['id' => 'total-pricing-'.$item['id'].'-hidden']) ?>
            <tr>
                <td align=center><?= $i ?></td>
                <td align=center><?= $item['unit'] ?></td>
                <td>
                    <?= $item['item'] ?>
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
                        'onKeyup' => 'getRetrievedPriceTotal('.$item['id'].','.$item['total'].','.json_encode($itemIDs).')',
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
    <?= Html::submitButton('Save Quotation', ['class' => 'btn btn-success', 'id' => 'retrieve-quotation-submit-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>
<div class="clearfix"></div>

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

    function getRetrievedPriceTotal(id, quantity, ids)
    {
      var cost = $("#pritemcost-"+id+"-cost").val().split(",").join("");
          cost = parseFloat(cost);
      var total = quantity * cost;

      $("#total-pricing-"+id+"-hidden").val(total);

      $("#total-pricing-"+id).empty();
      $("#total-pricing-"+id).html(number_format(total, 2, ".", ","));    
      
      getRetrievedPriceGrandTotal(ids);
    }

    function getRetrievedPriceGrandTotal(ids)
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

    $("#retrieve-rfq-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Quotation saved successfully");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                menu('.$model->id.');
                rfqRetrieveQuotation('.$model->id.');
            },
            error: function (err) {
                console.log(err);
            }
        }); 
        
        return false;
    });

    $("#create-supplier-button").click(function(){
        $("#create-supplier-modal").modal("show").find("#create-supplier-modal-content").load($(this).attr("value"));
      });
    ';

    $this->registerJs($script, View::POS_END);
?>