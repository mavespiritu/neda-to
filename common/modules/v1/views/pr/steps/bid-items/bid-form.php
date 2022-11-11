<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin([
    'id' => 'bid-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<div class="row">
    <div class="col-md-6 col-xs-12">
        <?= $form->field($bidModel, 'date_opened')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ]); ?>
    </div>
    <div class="col-md-2 col-xs-12">
        <?= $form->field($bidModel, 'time_opened')->textInput(['type' => 'number', 'min' => 1, 'max' => 12,'autocomplete' => 'off', 'placeholder' => 'Hour'])->label('Time Opened') ?>
    </div>
    <div class="col-md-2 col-xs-12">
        <label for="">&nbsp;</label>
        <?= $form->field($bidModel, 'minute')->textInput(['type' => 'number', 'min' => 0, 'max' => 59,'autocomplete' => 'off', 'placeholder' => 'Minute'])->label(false) ?>
    </div>
    <div class="col-md-2 col-xs-12">
        <label for="">&nbsp;</label>
        <?= $form->field($bidModel, 'meridian')->dropdownList(['AM' => 'AM', 'PM' => 'PM'])->label(false) ?>
    </div>
</div>

<?= $form->field($memberModels[$chairModel->position], "[$chairModel->position]emp_id")->widget(Select2::classname(), [
    'data' => $signatories,
    'options' => ['placeholder' => 'Select Staff','multiple' => false, 'id' => 'chair-select', 'class'=>'chair-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ])->label('BAC Chairperson');
?>

<?= $form->field($memberModels[$viceChairModel->position], "[$viceChairModel->position]emp_id")->widget(Select2::classname(), [
    'data' => $signatories,
    'options' => ['placeholder' => 'Select Staff','multiple' => false, 'id' => 'vice-chair-select', 'class'=>'vice-chair-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ])->label('BAC Vice-Chairperson');
?>

<?= $form->field($memberModels[$memberModel->position], "[$memberModel->position]emp_id")->widget(Select2::classname(), [
    'data' => $signatories,
    'options' => ['placeholder' => 'Select Staff','multiple' => false, 'id' => 'member-select', 'class'=>'member-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ])->label('BAC Member');
?>

<?= $form->field($memberModels[$expertModel->position], "[$expertModel->position]emp_id")->widget(Select2::classname(), [
    'data' => $experts,
    'options' => ['placeholder' => 'Select Staff','multiple' => false, 'id' => 'expert-select', 'class'=>'expert-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ])->label('Provisional Member with Technical Expertise');
?>

<?= $form->field($memberModels[$endUserModel->position], "[$endUserModel->position]emp_id")->widget(Select2::classname(), [
    'data' => $endUsers,
    'options' => ['placeholder' => 'Select Staff','multiple' => false, 'id' => 'end-user-select', 'class'=>'end-user-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ])->label('Provisional Member - End User');
?>

<div class="form-group pull-right"> 
    <?= Html::submitButton('Save Bid', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    $("#bid-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Bidding information saved successfully");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                menu('.$model->id.');
                bidRfq('.$model->id.','.$rfq->id.','.$i.');
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