
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
    'id' => 'apr-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<h4>2.1 Set Agency Procurement Items</h4>
<p><i class="fa fa-exclamation-circle"></i> All items included will be checked if available in PS-DBM</p>
<table class="table table-bordered table-responsive table-hover table-condensed table-striped">
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
            <td align=center><input type=checkbox name="apr-items" class="check-apr-items" /></td>
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
                            <?= $this->render('apr-item', [
                                'i' => $i,
                                'id' => $id,
                                'model' => $model,
                                'item' => $item,
                                'aprItems' => $aprItems,
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
    <?= !empty($forAprs) ? Html::submitButton('Transfer to Non-procurable', ['class' => 'btn btn-success', 'id' => 'transfer-for-obligation-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
    <?= !empty($forAprs) ? Html::submitButton('Transfer to Supplier', ['class' => 'btn btn-success', 'id' => 'transfer-for-supplier-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
</div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    function enableTransferButtons()
    {
        $("#apr-items-form input:checkbox:checked").length > 0 ? $("#transfer-for-supplier-button").attr("disabled", false) : $("#transfer-for-supplier-button").attr("disabled", true);
        $("#apr-items-form input:checkbox:checked").length > 0 ? $("#transfer-for-obligation-button").attr("disabled", false) : $("#transfer-for-obligation-button").attr("disabled", true);
        $("#apr-items-form input:checkbox:checked").length > 0 ? $("#add-apr-button").attr("disabled", false) : $("#add-apr-button").attr("disabled", true);
    }

    $(".check-apr-items").click(function(){
        $(".check-apr-item").not(this).prop("checked", this.checked);
        enableTransferButtons();
    });

    $(".check-apr-item").click(function(){
        enableTransferButtons();
    });

    $(document).ready(function(){
        $(".check-apr-item").removeAttr("checked");
        enableTransferButtons();
    });

    $("#transfer-for-supplier-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to add these items to RFQ?");
        if(con == true)
        {
            

            var form = $("#apr-items-form");
            var formData = form.serialize();

            $.ajax({
                //url: form.attr("action"),
                url: "'.Url::to(['/v1/pr/save-group-items', 'id' => $model->id, 'from' => 'APR', 'to' => 'RFQ']).'",
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items transferred to RFQ");
                    menu('.$model->id.');
                    groupItems('.$model->id.');
                    groupAprItems('.$model->id.');
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
        }

        return false;
    });

    $("#transfer-for-obligation-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to add these items to ORS?");
        if(con == true)
        {
            

            var form = $("#apr-items-form");
            var formData = form.serialize();

            $.ajax({
                //url: form.attr("action"),
                url: "'.Url::to(['/v1/pr/save-group-items', 'id' => $model->id, 'from' => 'APR', 'to' => 'ORS']).'",
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items transferred to ORS");
                    menu('.$model->id.');
                    groupItems('.$model->id.');
                    groupAprItems('.$model->id.');
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