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
$letters = range('A', 'Z');
$abcTotal = 0;
$totals = [];
?>
<?php $form = ActiveForm::begin([
    'id' => 'winner-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<table class="table table-bordered table-condensed table-responsive"> 
    <tr>
        <td style="width: 20%;" align=right><b>Canvas/Bid No.:</b></td>
        <td style="width: 30%;"><?= $bid->bid_no ?></td>
        <td style="width: 20%;" align=right><b>Date of Opening:</b></td>
        <td style="width: 30%;"><?= $bid->date_opened ?></td>
    </tr>
    <tr>
        <td style="width: 20%;" align=right><b>RFQ No.:</b></td>
        <td style="width: 30%;"><?= $rfq->rfq_no ?></td>
        <td style="width: 20%;" align=right><b>Time of Opening:</b></td>
        <td style="width: 30%;"><?= $bid->time_opened ?></td>
    </tr>
</table>

<p style="font-size: 90%;"><b>Participating Establishments: </b></p>
<?php if($supplierList){ ?>
    <p style="font-size: 90%;"><b>
    <?php foreach($supplierList as $idx => $supplier){ ?>
        <?= $letters[$idx].'. '.$supplier->business_name ?><br>
    <?php } ?>
    </b></p>
<?php } ?>
<table class="table table-bordered table-condensed table-striped table-hover table-responsive" style="font-size: 90%;">
    <thead>
        <tr>
            <td rowspan=3 align=center><b>Item No.</b></td>
            <td rowspan=3 align=center style="width: 30%;"><b>Nomenclature</b></td>
            <td rowspan=3 align=center><b>ABC per Item</b></td>
            <td rowspan=3 align=center><b>Qty</b></td>
            <?php if($supplierList){ ?>
                <td colspan=<?= count($supplierList) * 3 ?> align=center><b>Participating<br>Establishments</b></td>
            <?php } ?>
            <td rowspan=3 align=center style="width: 10%;"><b>Awarded to</b></td>
        </tr>
        <tr>
            <?php if($supplierList){ ?>
                <?php foreach($supplierList as $idx => $supplier){ ?>
                    <?php $totals[$supplier->id] = 0; ?>
                    <td align=center><b>Unit Cost</b></td>
                    <td align=center><b><?= $letters[$idx].'<br>'.$supplier->business_name ?></b></td>
                    <td align=center><b>Specifications</b></td>
                <?php } ?>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($lotItems)){ ?>
        <?php foreach($lotItems as $lot => $items){ ?>
            <?php if($lot != 0){ ?>
                <tr>
                    <td colspan="<?= !empty($supplierList) ? 4 + (count($supplierList) * 3) : 4 ?>" style="background-color: #D9D9D9;"><b><?= $lot ?></b></td>
                </tr>
            <?php } ?>
            <?php if(!empty($items)){ ?>
                <?php $i = 1; ?>
                <?php foreach($items as $rfqItem){ ?>
                    <tr>
                        <td align=center><?= $i ?></td>
                        <td><?= $rfqItem['item'] ?></td>
                        <td align=right><b><?= number_format($rfqItem['cost'], 2) ?></b></td>
                        <td align=center><?= number_format($rfqItem['total'], 0) ?></td>
                        <?php if($supplierList){ ?>
                            <?php foreach($supplierList as $supplier){ ?>
                                <td align=right><?= isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $costs[$rfqItem['id']][$supplier->id]['cost'] > $rfqItem['cost'] ? '<span class="text-red">'.number_format($costs[$rfqItem['id']][$supplier->id]['cost'], 2).'</span>' : '<span class="text-green">'.number_format($costs[$rfqItem['id']][$supplier->id]['cost'], 2).'</span>' : '' ?></td>
                                <td align=right style="width: 15%;" id="cell-<?= $rfqItem['id'] ?>-<?= $supplier->id ?>"><b><?= isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $costs[$rfqItem['id']][$supplier->id]['cost'] > 0 ? number_format($rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'], 2) : '-' : '-' ?></b></td>
                                <td><?= isset($costs[$rfqItem['id']][$supplier->id]['specification']) ? $costs[$rfqItem['id']][$supplier->id]['specification'] != '' ? $costs[$rfqItem['id']][$supplier->id]['specification'] : 'No quotation received' : 'No quotation received' ?></td>
                                <?php $totals[$supplier->id] += isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'] : 0; ?>
                            <?php } ?>
                        <?php } ?>
                        <td>
                        <?php $id = $rfqItem['id']; ?>
                        <?= $form->field($winnerModels[$id], "[$id]supplier_id")->dropdownList(['' => '-'] + $suppliers[$id], ['onchange' => 'colorTheCell('.$rfqItem['id'].',this.value,'.json_encode($supplierIDs).')'])->label(false); ?>
                        </td>
                        <?php $abcTotal += $rfqItem['total'] * $rfqItem['cost'] ?>
                    </tr>
                    <?php $i++; ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan=4 align=right><b>Total Cost</b></td>
        <?php if($supplierList){ ?>
            <?php foreach($supplierList as $supplier){ ?>
                <td>&nbsp;</td>
                <td align=right><b><?= number_format($totals[$supplier->id], 2) ?></b></td>
                <td>&nbsp;</td>
            <?php } ?>
        <?php } ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </tbody>
</table>

<?= $form->field($bid, 'recommendation')->textArea(['rows' => 3])->label('Award Recommended to') ?>

<?= $form->field($bid, 'justification')->textArea(['rows' => 3])->label('Justification') ?>

<div class="form-group pull-right"> 
    <?= Html::submitButton('Save Bid Results', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    $("#winner-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Bid results saved successfully");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                $("body").removeAttr("style");
                bidRfq('.$model->id.','.$rfq->id.','.$i.');
                $("html").animate({ scrollTop: 0 }, "slow");
            },
            error: function (err) {
                console.log(err);
            }
        }); 
        
        return false;
    });

    function colorTheCell(pr_item_id, supplier_id, suppliers)
    {
        for(let x in suppliers)
        {
            if(supplier_id == x)
            {
                $("#cell-"+pr_item_id+"-"+x).css("background-color", "yellow");
            }else{
                $("#cell-"+pr_item_id+"-"+x).css("background-color", "transparent");
            }
        }
    }

    function colorCellonLoad(models)
    {
        for(let x in models)
        {
            var value = $("#bidwinner-"+x+"-supplier_id").val();
            $("#cell-"+x+"-"+value).css("background-color", "yellow");
        }
    }

    $(document).ready(function(){
        colorCellonLoad('.json_encode($rfqItemIDs).');
    });     
    ';

    $this->registerJs($script, View::POS_END);
?>