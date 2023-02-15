<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use kartik\widgets\TimePicker;
use yii\helpers\Url;
use yii\web\View;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="rfq-form">

<?php $form = ActiveForm::begin([
    //'options' => ['class' => 'disable-submit-buttons'],
    'id' => 'rfq-form',
    //'enableAjaxValidation' => true,
]); ?>

<div class="row">
    <div class="col-md-6 col-xs-12">
        <?= $form->field($rfqModel, 'deadline_date')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ])->label('Deadline of Quotation (Date)'); ?>
    </div>
    <div class="col-md-2 col-xs-12">
        <?= $form->field($rfqModel, 'deadline_time')->textInput(['type' => 'number', 'min' => 1, 'max' => 12,'autocomplete' => 'off', 'placeholder' => 'Hour'])->label('Time') ?>
    </div>
    <div class="col-md-2 col-xs-12">
        <label for="">&nbsp;</label>
        <?= $form->field($rfqModel, 'minute')->textInput(['type' => 'number', 'min' => 0, 'max' => 59,'autocomplete' => 'off', 'placeholder' => 'Minute'])->label(false) ?>
    </div>
    <div class="col-md-2 col-xs-12">
        <label for="">&nbsp;</label>
        <?= $form->field($rfqModel, 'meridian')->dropdownList(['AM' => 'AM', 'PM' => 'PM'])->label(false) ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <?= $form->field($rfqModel, 'delivery_period')->textInput(['type' => 'number', 'min' => 1, 'autocomplete' => 'off'])->label('Delivery Period: No. of Calendar Days') ?>
    </div>
</div>
<div class="row">
    <div class="col-md-8 col-xs-12">
        <?= $form->field($rfqModel, 'supply_warranty')->textInput(['type' => 'number', 'min' => 1, 'autocomplete' => 'off'])->label('Warranty Period: For Supplies and Materials') ?>
    </div>
    <div class="col-md-4 col-xs-12">
        <?= $form->field($rfqModel, 'supply_warranty_unit')->widget(Select2::classname(), [
        'data' => ['days' => 'days', 'weeks' => 'weeks', 'months' => 'months', 'years' => 'years'],
        'options' => ['placeholder' => 'Select Unit','multiple' => false, 'class'=>'supply-warranty-unit-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ])->label('Unit') ?>
    </div>
</div>
<div class="row">
    <div class="col-md-8 col-xs-12">
        <?= $form->field($rfqModel, 'supply_equipment')->textInput(['type' => 'number', 'min' => 1, 'autocomplete' => 'off'])->label('Warranty Period: For Equipment') ?>
    </div>
    <div class="col-md-4 col-xs-12">
        <?= $form->field($rfqModel, 'supply_equipment_unit')->widget(Select2::classname(), [
        'data' => ['days' => 'days', 'weeks' => 'weeks', 'months' => 'months', 'years' => 'years'],
        'options' => ['placeholder' => 'Select Unit','multiple' => false, 'class'=>'supply-warranty-unit-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ])->label('Unit') ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <?= $form->field($rfqModel, 'price_validity')->textInput(['type' => 'number', 'min' => 1, 'autocomplete' => 'off'])->label('Price Validity: No. of Calendar Days') ?>
    </div>
</div>

<div class="pull-right">
    <?= Html::submitButton('Save RFQ', ['class' => 'btn btn-success']) ?>
</div>

<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

</div>
<?php
  $script = '
    $("#rfq-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("RFQ generated successfully");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                rfqQuotation('.$model->id.');
                $("html").animate({ scrollTop: 0 }, "slow");
            },
            error: function (err) {
                console.log(err);
            }
        }); 
        
        return false;
    });
    $(document).ready(function() {

    });
  ';
  $this->registerJs($script, View::POS_END);
?>