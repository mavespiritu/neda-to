<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
$asset = AppAsset::register($this);
?>

<p>Select from PR items to include in lot.</p>
<?php $form = ActiveForm::begin([
    'id' => 'include-lot-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<table class="table table-bordered table-responsive table-hover table-condensed" id="include-lot-items-table">
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
            <td align=center><input type=checkbox name="lot-items" class="check-include-lot-items" /></td>
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
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= $item['ris_no'] ?></td>
                                <td><?= $item['unit'] ?></td>
                                <td><?= $item['item'] ?></td>
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
                                    <?= $form->field($prItems[$item['id']], "[$id]id")->checkbox(['value' => $item['id'], 'class' => 'check-include-lot-item', 'label' => '', 'id' => 'check-include-lot-item-'.$item['id'], 'checked' => 'checked']) ?>
                                </td>
                            </tr>
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
    <?= !empty($risItems) ? Html::submitButton('Include selected to lot', ['class' => 'btn btn-success', 'id' => 'include-lot-items-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
    $(".check-include-lot-items").click(function(){
        $(".check-include-lot-item").not(this).prop("checked", this.checked);
        $("#include-lot-items-table tr").toggleClass("isChecked", $(".check-include-lot-item").is(":checked"));
        enableIncludeButton();
    });

    $(document).ready(function(){
        $(".check-include-lot-item").removeAttr("checked");
        enableIncludeButton();

        $("tr").click(function() {
            var inp = $(this).find(".check-include-lot-item");
            var tr = $(this).closest("tr");
            inp.prop("checked", !inp.is(":checked"));
         
            tr.toggleClass("isChecked", inp.is(":checked"));
            enableIncludeButton();
        });
        
        // do nothing when clicking on checkbox, but bubble up to tr
        $(".check-include-lot-item").click(function(e){
            e.preventDefault();
            enableIncludeButton();
        });
    });

    function enableIncludeButton()
    {
        $("#include-lot-items-form input:checkbox:checked").length > 0 ? $("#include-lot-items-button").attr("disabled", false) : $("#include-lot-items-button").attr("disabled", true);
    }

    $("#include-lot-items-button").on("click", function(e) {
        e.preventDefault();

        var form = $("#include-lot-items-form");
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Items included in lot successfully");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                viewLot('.$lot->id.');
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
.check-include-lot-item {
  pointer-events: none;
}
</style>