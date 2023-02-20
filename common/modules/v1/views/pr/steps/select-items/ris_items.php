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
?>

<?php $form = ActiveForm::begin([
    'id' => 'ris-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<h5>
    List of items under RIS No. <?= $ris->ris_no ?> of <?= $ris->officeName ?>: <?= $ris->purpose ?><br><br>
    
</h5>
<p><i class="fa fa-exclamation-circle"></i> Check items to include in PR.</p>
<table class="table table-bordered table-responsive table-hover table-condensed" id="ris-items-table">
    <thead>
        <tr>
        <th>#</th>
            <th>Unit</th>
            <th>Item</th>
            <th style="width: 20%;">Specification</th>
            <td align=center><b>Quantity</b></td>
            <td align=right><b>Unit Cost</b></td>
            <td align=right><b>Total Cost</b></td>
            <td align=center><input type=checkbox name="items" class="check-ris-items" /></td>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php $total = 0; ?>
        <?php if(!empty($risItems)){ ?>
            <?php foreach($risItems as $activity => $activityItems){ ?>
                <tr>
                    <th>&nbsp;</th>
                    <th colspan=7><?= $activity ?></th>
                </tr>
                <?php if(!empty($activityItems)){ ?>
                    <?php foreach($activityItems as $subActivity => $subActivityItems){ ?>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th colspan=6><?= $subActivity ?> - <?= $model->fundSource->code ?> Funded</th>
                        </tr>
                        <?php if(!empty($subActivityItems)){ ?>
                            <?php foreach($subActivityItems as $item){ ?>
                                <?php $id = $item['id'] ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= $item['unitOfMeasure'] ?></td>
                                    <td style="width: 30%;"><?= $item['itemTitle'] ?></td>
                                    <td>
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
                                        <?= $specifications[$item['id']]->risItemSpecValueString ?>
                                    <?php } ?>
                                    </td>
                                    <td align=center><?= number_format($item['total'], 0) ?></td>
                                    <td align=right><?= number_format($item['cost'], 2) ?></td>
                                    <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
                                    <td align=center>
                                        <?= $form->field($prItems[$item['id']], "[$id]ris_item_id")->checkbox(['value' => $item['id'], 'class' => 'check-ris-item', 'label' => '', 'id' => 'check-item-'.$item['id']]) ?>
                                    </td>
                                </tr>
                                <?php $total += ($item['total'] * $item['cost']); ?>
                                <?php $i++; ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <tr>
                <td align=right colspan=6><b>Grand Total</b></td>
                <td align=right><b><?= number_format($total, 2) ?></b></td>
                <td>&nbsp;</td>
            </tr>
    <?php }else{ ?>
    <tr>
        <td colspan=8 align=center>No items available</td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<div class="form-group pull-right">
    <?= !empty($risItems) ? Html::submitButton('Add to PR', ['class' => 'btn btn-success', 'id' => 'add-pr-item-button', 'data' => ['disabled-text' => 'Please Wait'], 'disabled' => true]) : '' ?>
</div>
<div class="clearfix"></div>
<?php ActiveForm::end(); ?>

<?php
    $script = '
    $(".check-ris-items").click(function(){
        $(".check-ris-item").not(this).prop("checked", this.checked);
        $("#ris-items-table tr").toggleClass("isChecked", $(".check-ris-item").is(":checked"));
        enableRemoveButton();
    });

    $(document).ready(function(){
        enableRemoveButton();

        $("tr").click(function() {
            var inp = $(this).find(".check-ris-item");
            var tr = $(this).closest("tr");
            inp.prop("checked", !inp.is(":checked"));
         
            tr.toggleClass("isChecked", inp.is(":checked"));
            enableRemoveButton();
        });
        
        // do nothing when clicking on checkbox, but bubble up to tr
        $(".check-ris-item").click(function(e){
            e.preventDefault();
            enableRemoveButton();
        });
    });

    function enableRemoveButton()
    {
        $("#ris-items-form input:checkbox:checked").length > 0 ? $("#add-pr-item-button").attr("disabled", false) : $("#add-pr-item-button").attr("disabled", true);
        $("#ris-items-form input:checkbox:checked").length > 0 ? $("#add-pr-item-button").attr("disabled", false) : $("#add-pr-item-button").attr("disabled", true);
    }

    function loadRisItems(id, ris_id)
    {
        $.ajax({
            url: "'.Url::to(['/v1/pr/select-ris-items']).'?id=" + id + "&ris_id=" + ris_id,
            beforeSend: function(){
                $("#ris-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
            },
            success: function (data) {
                console.log(this.data);
                $("#ris-items").empty();
                $("#ris-items").hide();
                $("#ris-items").fadeIn("slow");
                $("#ris-items").html(data);
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    $("#ris-items-form").on("beforeSubmit", function(e) {
        e.preventDefault();
     
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Items has been included in PR");
                loadRisItems('.$model->id.','.$ris->id.');
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
<style>
.isChecked {
  background-color: #F5F5F5;
}
tr{
  background-color: white;
}
/* click-through element */
.check-ris-item {
  pointer-events: none;
}
</style>