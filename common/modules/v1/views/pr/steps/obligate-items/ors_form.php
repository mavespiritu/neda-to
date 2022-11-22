<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'ors-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<h5><?= !is_null($po) ? $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'CN No. '.$po->pocnNo : 'Non-procurable Items' ?></h5><br>
<div class="ors-content">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($orsModel, 'ors_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($orsModel, 'ors_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
    </div>
    <?php if(is_null($po)){ ?>
        <div class="row">
            <div class="col-md-12 col-xs-12"><?= $form->field($orsModel, 'payee')->textInput(['maxlength' => true]) ?></div>
            <div class="col-md-12 col-xs-12"><?= $form->field($orsModel, 'office')->textInput(['maxlength' => true]) ?></div>
            <div class="col-md-12 col-xs-12"><?= $form->field($orsModel, 'address')->textInput(['maxlength' => true]) ?></div>
        </div>
    <?php } ?>
    <p><i class="fa fa-exclamation-circle"></i> Select items to obligate.</p>
    <table class="table table-bordered table-striped table-hover table-condensed table-responsive">
        <thead>
            <tr>
                <td align=center><b>Stock/Property No.</b></td>
                <td align=center><b>Unit</b></td>
                <td align=center><b>Description</b></td>
                <td align=center><b>Qty</b></td>
                <td align=center><b>Unit Cost</b></td>
                <td align=center><b>Amount</b></td>
                <td align=center><input type=checkbox name="ors-items" class="check-ors-items" /></td>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($items)){ ?>
            <?php foreach($items as $item){ ?>
                <?php $id = $item['id']; ?>
                <tr>
                    <td align=center><?= $item['id'] ?></td>
                    <td><?= $item['unit'] ?></td>
                    <td><?= $item['item'] ?></td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=right><?= number_format($item['cost'], 2) ?></td>
                    <td align=right><b><?= number_format($item['total'] * $item['cost'], 2) ?></b></td>
                    <td align=center>
                        <?= in_array($item['id'], $existingOrsItemIDs) ? $form->field($itemModels[$item['id']], "[$id]pr_item_id")->checkbox(['value' => $item['id'], 'class' => 'check-ors-item', 'label' => '', 'id' => 'check-ors-item-'.$item['id'], 'checked' => 'checked']) : $form->field($itemModels[$item['id']], "[$id]pr_item_id")->checkbox(['value' => $item['id'], 'class' => 'check-ors-item', 'label' => '', 'id' => 'check-ors-item-'.$item['id']]) ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
    <?= $form->field($orsModel, 'responsibility_center')->textInput(['maxlength' => true]) ?>
    <br>
    <div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success', 'id' => 'save-ors-button']) ?>
    </div>
    <div class="clearfix"></div>
</div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        function enableOrsButtons()
        {
            $("#ors-form input:checkbox:checked").length > 0 ? $("#save-ors-button").attr("disabled", false) : $("#save-ors-button").attr("disabled", true);
        }

        $(".check-ors-items").click(function(){
            $(".check-ors-item").not(this).prop("checked", this.checked);
            enableOrsButtons();
        });
    
        $(".check-ors-item").click(function(){
            enableOrsButtons();
        });

        $(document).ready(function(){
            //$(".check-ors-item").removeAttr("checked");
            enableOrsButtons();
        });
    ';
    !is_null($po) ? $script .= '
        $("#ors-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $("#ors-form");
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Obligation has been saved successfully");
                    $(".modal").remove();
                    $(".modal-backdrop").remove();
                    $("body").removeClass("modal-open");
                    menu('.$model->id.');
                    obligatePo('.$model->id.','.$po->id.','.$i.');
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });
    ' : 
    $script .= '
        $("#ors-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $("#ors-form");
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Obligation has been saved successfully");
                    $(".modal").remove();
                    $(".modal-backdrop").remove();
                    $("body").removeClass("modal-open");
                    menu('.$model->id.');
                    obligatePo('.$model->id.',"null",'.$i.');
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