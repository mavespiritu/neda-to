
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
    'id' => 'rfq-items-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<h3 class="panel-title">2.2 Group RFQ Items</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> All items included will be checked availability on outside suppliers and service providers</p>
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
            <td align=center><input type=checkbox name="rfq-items" class="check-rfq-items" /></td>
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
                            <?= $this->render('rfq-item', [
                                'i' => $i,
                                'id' => $id,
                                'model' => $model,
                                'item' => $item,
                                'rfqItems' => $rfqItems,
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
    <?= !empty($forRfqs) ? Html::submitButton('Transfer to NP', ['class' => 'btn btn-primary', 'id' => 'transfer-for-obligation-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
    <?= !empty($forRfqs) ? Html::submitButton('Transfer to APR', ['class' => 'btn btn-success', 'id' => 'transfer-for-agency-procurement-button', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ], 'disabled' => true]) : '' ?>
</div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    function enableTransferButtons()
    {
        $("#rfq-items-form input:checkbox:checked").length > 0 ? $("#transfer-for-agency-procurement-button").attr("disabled", false) : $("#transfer-for-agency-procurement-button").attr("disabled", true);
        $("#rfq-items-form input:checkbox:checked").length > 0 ? $("#transfer-for-obligation-button").attr("disabled", false) : $("#transfer-for-obligation-button").attr("disabled", true);
        $("#rfq-items-form input:checkbox:checked").length > 0 ? $("#add-rfq-button").attr("disabled", false) : $("#add-rfq-button").attr("disabled", true);
    }

    $(".check-rfq-items").click(function(){
        $(".check-rfq-item").not(this).prop("checked", this.checked);
        enableTransferButtons();
    });

    $(".check-rfq-item").click(function(){
        enableTransferButtons();
    });

    $(document).ready(function(){
        $(".check-rfq-item").removeAttr("checked");
        enableTransferButtons();
    });

    $("#transfer-for-agency-procurement-button").on("click", function(e) {
        e.preventDefault();

        var con = confirm("Are you sure you want to add these items to APR?");
        if(con == true)
        {
            var form = $("#rfq-items-form");
            var formData = form.serialize();

            $.ajax({
                //url: form.attr("action"),
                url: "'.Url::to(['/v1/pr/save-group-items', 'id' => $model->id, 'from' => 'RFQ', 'to' => 'APR']).'",
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items transferred to APR");
                    menu('.$model->id.');
                    groupItems('.$model->id.');
                    groupRfqItems('.$model->id.');
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

        var con = confirm("Are you sure you want to add these items to non-procurables?");
        if(con == true)
        {
            var form = $("#rfq-items-form");
            var formData = form.serialize();

            $.ajax({
                //url: form.attr("action"),
                url: "'.Url::to(['/v1/pr/save-group-items', 'id' => $model->id, 'from' => 'RFQ', 'to' => 'NP']).'",
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Items transferred to Non-procurables");
                    menu('.$model->id.');
                    groupItems('.$model->id.');
                    groupRfqItems('.$model->id.');
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