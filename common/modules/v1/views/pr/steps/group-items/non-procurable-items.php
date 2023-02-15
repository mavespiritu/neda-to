
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
    'id' => 'non-procurable-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<h3 class="panel-title">2.3 Group Non-Procurable Items</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> All items included will be directly obligated and not needed to undergo procurement like TEV etc.</p>
<table class="table table-bordered table-responsive table-hover table-condensed" id="non-procurable-items-table">
    <thead>
        <tr>
            <th>#</th>
            <th>RIS No.</th>
            <th>Unit</th>
            <th>Item</th>
            <th>Specification</th>
            <th>Quantity</th>
            <th>Unit Cost</th>
            <td align=center><b>Total Cost</b></td>
            <td align=center><input type=checkbox name="non-procurable-items" class="check-non-procurable-items" /></td>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php $total = 0; ?>
    <?php if(!empty($prItems)){ ?>
        <?php foreach($prItems as $activity => $activityItems){ ?>
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
                            <?= $this->render('non-procurable-item', [
                                'i' => $i,
                                'id' => $id,
                                'model' => $model,
                                'item' => $item,
                                'nonProcurableItems' => $nonProcurableItems,
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
    <?= !empty($forNonProcurables) ? Html::submitButton('Transfer to APR Items', ['class' => 'btn btn-primary', 'id' => 'transfer-for-agency-procurement-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
    <?= !empty($forNonProcurables) ? Html::submitButton('Transfer to RFQ Items', ['class' => 'btn btn-success', 'id' => 'transfer-for-supplier-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
</div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    $(".check-non-procurable-items").click(function(){
        $(".check-non-procurable-item").not(this).prop("checked", this.checked);
        $("#non-procurable-items-table tr").toggleClass("isChecked", $(".check-non-procurable-item").is(":checked"));
        enableTransferButtons();
    });

    $(document).ready(function(){
        $(".check-non-procurable-item").removeAttr("checked");
        enableTransferButtons();

        $("tr").click(function() {
            var inp = $(this).find(".check-non-procurable-item");
            var tr = $(this).closest("tr");
            inp.prop("checked", !inp.is(":checked"));
         
            tr.toggleClass("isChecked", inp.is(":checked"));
            enableTransferButtons();
        });
        
        // do nothing when clicking on checkbox, but bubble up to tr
        $(".check-non-procurable-item").click(function(e){
            e.preventDefault();
            enableTransferButtons();
        });
    });

    function enableTransferButtons()
    {
        $("#non-procurable-items-form input:checkbox:checked").length > 0 ? $("#transfer-for-agency-procurement-button").attr("disabled", false) : $("#transfer-for-agency-procurement-button").attr("disabled", true);
        $("#non-procurable-items-form input:checkbox:checked").length > 0 ? $("#transfer-for-supplier-button").attr("disabled", false) : $("#transfer-for-supplier-button").attr("disabled", true);
    }

    $("#transfer-for-agency-procurement-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to add these items to APR?");
        if(con == true)
        {
            var form = $("#non-procurable-items-form");
            var formData = form.serialize();

            $.ajax({
                //url: form.attr("action"),
                url: "'.Url::to(['/v1/pr/save-group-items', 'id' => $model->id, 'from' => 'NP', 'to' => 'APR']).'",
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items transferred to APR");
                    groupNonProcurableItems('.$model->id.');
                    manageItems('.$model->id.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
        }

        return false;
    });

    $("#transfer-for-supplier-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to add these items to RFQ?");
        if(con == true)
        {
            var form = $("#non-procurable-items-form");
            var formData = form.serialize();

            $.ajax({
                //url: form.attr("action"),
                url: "'.Url::to(['/v1/pr/save-group-items', 'id' => $model->id, 'from' => 'NP', 'to' => 'RFQ']).'",
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items transferred to RFQ");
                    groupNonProcurableItems('.$model->id.');
                    manageItems('.$model->id.');
                    $("html").animate({ scrollTop: 0 }, "slow");
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
<style>
.isChecked {
  background-color: #F5F5F5;
}
tr{
  background-color: white;
}
/* click-through element */
.check-non-procurable-item {
  pointer-events: none;
}
</style>