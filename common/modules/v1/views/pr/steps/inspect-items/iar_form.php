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
    'id' => 'iar-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<div class="iar-content">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($iarModel, 'iar_no')->textInput(['maxlength' => true, 'autocomplete' => 'off', 'value' => $iarModel->isNewRecord ? $iarNo : $iarModel->iar_no, 'disabled' => true]) ?>
            <?= $form->field($iarModel, 'iar_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($iarModel, 'invoice_no')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>
            <?= $form->field($iarModel, 'invoice_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
    </div>
    <table class="table table-bordered table-striped table-hover table-condensed table-responsive">
        <thead>
            <tr>
                <td align=center><b>Stock/Property No.</b></td>
                <td align=center><b>Description</b></td>
                <td align=center><b>Unit</b></td>
                <td align=center><b>Qty</b></td>
                <td align=center><b>Balance</b></td>
                <td align=center><b>Delivered</b></td>
                <td align=center><b>Delivery Time</b></td>
                <td align=center><b>Courtesy of <br> Delivery Staff</b></td>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($items)){ ?>
            <?php foreach($items as $item){ ?>
                <?php $id = $item['id']; ?>
                <tr>
                    <td align=center><?= $item['id'] ?></td>
                    <td><?= $item['item'] ?></td>
                    <td><?= $item['unit'] ?></td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=center><?= number_format($item['balance'], 0) ?></td>
                    <td><?= $form->field($itemModels[$item['id']], "[$id]balance")->textInput(['type' => 'number', 'max' => $item['balance'] + $item['delivered'] , 'min' => 0])->label(false) ?></td>
                    <td><?= $form->field($itemModels[$item['id']], "[$id]delivery_time")->dropdownList(['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1'])->label(false) ?></td>
                    <td><?= $form->field($itemModels[$item['id']], "[$id]courtesy")->dropdownList(['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1'])->label(false) ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($iarModel, 'inspected_by')->widget(Select2::classname(), [
                'data' => $inspectors,
                'options' => ['placeholder' => 'Select Staff','multiple' => false],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
            ]) ?>
            <?= $form->field($iarModel, 'date_inspected')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($iarModel, 'received_by')->widget(Select2::classname(), [
                'data' => $supplyOfficers,
                'options' => ['placeholder' => 'Select Staff','multiple' => false],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
            ]) ?>
            <?= $form->field($iarModel, 'date_received')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
    </div>
    <br>
    <div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
    </div>
    <div class="clearfix"></div>
</div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        $("#iar-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Inspection has been saved successfully");
                    $(".modal").remove();
                    $(".modal-backdrop").remove();
                    $("body").removeClass("modal-open");
                    menu('.$model->id.');
                    inspectDelivery('.$model->id.','.$po->id.','.$i.');
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });

        function printIar()
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-iar']).'?id='.$model->id.'&po_id='.$po->id.'", 
                "Print",
                "left=200", 
                "top=200", 
                "width=650", 
                "height=500", 
                "toolbar=0", 
                "resizable=0"
                );
                printWindow.addEventListener("load", function() {
                    printWindow.print();
                    setTimeout(function() {
                    printWindow.close();
                }, 1);
                }, true);
        }
    ';

    $this->registerJs($script, View::POS_END);
?>