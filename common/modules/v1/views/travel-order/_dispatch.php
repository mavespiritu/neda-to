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
    'id' => 'dispatch-vehicle-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<?= $form->field($vehicleModel, 'vehicle_id')->widget(Select2::classname(), [
    'data' => $vehicles,
    'options' => ['placeholder' => 'Select vehicle','multiple' => false, 'class'=>'vehicle-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ]);
?>

<?= $form->field($vehicleModel, 'driver_id')->widget(Select2::classname(), [
    'data' => $drivers,
    'options' => ['placeholder' => 'Select driver','multiple' => false, 'class'=>'driver-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
    ]);
?>

<?= $form->field($vehicleModel, 'remarks')->textarea(['rows' => 3]) ?>

<div class="form-group pull-right"> 
    <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    $("#dispatch-vehicle-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Dispatched vehicle saved");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                viewVehicleInfo('.$model->TO_NO.');
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