<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin([
    'id' => 'pr-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<table class="table table-bordered table-responsive table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>RIS No.</th>
            <th>Unit</th>
            <th>Item</th>
            <th>Specification</th>
            <td align=center><b>Quantity</b></td>
            <td align=right><b>Unit Cost</b></td>
            <td align=right><b>Total Cost</b></td>
            <td align=center><input type=checkbox name="pr-items" class="check-pr-items" /></td>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php $total = 0; ?>
    <?php if(!empty($risItems)){ ?>
        <?php foreach($risItems as $activity => $activityItems){ ?>
            <tr>
                <th>&nbsp;</th>
                <th colspan=8><?= $activity ?></th>
            </tr>
            <?php if(!empty($activityItems)){ ?>
                <?php foreach($activityItems as $subActivity => $subActivityItems){ ?>
                    <tr>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th colspan=7><?= $subActivity ?> - <?= $model->fundSource->code ?> Funded</th>
                    </tr>
                    <?php if(!empty($subActivityItems)){ ?>
                        <?php foreach($subActivityItems as $item){ ?>
                            <?php $id = $item['id'] ?>
                            <?= $this->render('pr_item', [
                                'i' => $i,
                                'id' => $id,
                                'model' => $model,
                                'item' => $item,
                                'prItems' => $prItems,
                                'specifications' => $specifications,
                                'form' => $form,
                            ]) ?>
                            <?php $total += $item['total'] * $item['cost'] ?>
                            <?php $i++; ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php }else{ ?>
        <tr>
            <td colspan=9 align=center>No items included</td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan=7 align=right><b>ABC:</b></td>
        <td align=right><b><?= number_format($total, 2) ?></b></td>
        <td>&nbsp;</td>
    </tr>
    </tbody>
</table>

<div class="form-group pull-right"> 
    <?= !empty($risItems) ? Html::submitButton('Remove Selected from PR', ['class' => 'btn btn-danger', 'id' => 'remove-pr-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
</div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    function enableRemoveButton()
    {
        $("#pr-items-form input:checkbox:checked").length > 0 ? $("#remove-pr-button").attr("disabled", false) : $("#remove-pr-button").attr("disabled", true);
        $("#pr-items-form input:checkbox:checked").length > 0 ? $("#add-apr-button").attr("disabled", false) : $("#add-apr-button").attr("disabled", true);
    }

    $(".check-pr-items").click(function(){
        $(".check-pr-item").not(this).prop("checked", this.checked);
        enableRemoveButton();
    });

    $(".check-pr-item").click(function(){
        enableRemoveButton();
    });

    $(document).ready(function(){
        $(".check-pr-item").removeAttr("checked");
        enableRemoveButton();
    });

    $("#remove-pr-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to remove this item?");
        if(con == true)
        {
            

            var form = $("#pr-items-form");
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items Removed");
                    home('.$model->id.');
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
        }

        return false;
    });
    ';

    $this->registerJs($script, View::POS_END);
?>