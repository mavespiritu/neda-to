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

<h3 class="panel-title">Lot No. <?= $lot->lot_no ?> - <?= $lot->title ?>
<span class="pull-right"><?= Html::button('Include Items', ['value' => Url::to(['/v1/pr/include-lot-item', 'id' => $lot->id]), 'class' => 'btn btn-success btn-sm', 'id' => 'include-lot-item-button']) ?></span></h3>
<p>Included Items</p>
<?php $form = ActiveForm::begin([
    'id' => 'lot-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<table class="table table-bordered table-responsive table-hover table-condensed" id="lot-items-table">
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
            <td align=center><input type=checkbox name="lot-items" class="check-lot-items" /></td>
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
                            <?= $this->render('lot_item', [
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
    <?= !empty($risItems) ? Html::submitButton('Remove selected from lot', ['class' => 'btn btn-danger', 'id' => 'remove-lot-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
</div>

<?php ActiveForm::end(); ?>
<?php
  Modal::begin([
    'id' => 'include-lot-item-modal',
    'size' => "modal-xl",
    'header' => '<div id="include-lot-item-modal-header"><h4>Include Items to Lot No. '.$lot->lot_no.' - '.$lot->title.'</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="include-lot-item-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
    $(".check-lot-items").click(function(){
        $(".check-lot-item").not(this).prop("checked", this.checked);
        $("#lot-items-table tr").toggleClass("isChecked", $(".check-lot-item").is(":checked"));
        enableRemoveButton();
    });

    $(document).ready(function(){
        $(".check-lot-item").removeAttr("checked");
        enableRemoveButton();

        $("tr").click(function() {
            var inp = $(this).find(".check-lot-item");
            var tr = $(this).closest("tr");
            inp.prop("checked", !inp.is(":checked"));
         
            tr.toggleClass("isChecked", inp.is(":checked"));
            enableRemoveButton();
        });
        
        // do nothing when clicking on checkbox, but bubble up to tr
        $(".check-lot-item").click(function(e){
            e.preventDefault();
            enableRemoveButton();
        });
    });

    function enableRemoveButton()
    {
        $("#lot-items-form input:checkbox:checked").length > 0 ? $("#remove-lot-button").attr("disabled", false) : $("#remove-lot-button").attr("disabled", true);
        $("#lot-items-form input:checkbox:checked").length > 0 ? $("#add-alot-button").attr("disabled", false) : $("#add-alot-button").attr("disabled", true);
    }

    $("#remove-lot-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to remove this item?");
        if(con == true)
        {
            var form = $("#lot-items-form");
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items removed from lot");
                    viewLot('.$lot->id.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
        }

        return false;
    });

    $(document).ready(function(){
        $("#include-lot-item-button").click(function(){
            $("#include-lot-item-modal").modal("show").find("#include-lot-item-modal-content").load($(this).attr("value"));
        });
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
.check-lot-item {
  pointer-events: none;
}
</style>