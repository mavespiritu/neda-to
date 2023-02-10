<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\View;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="lot-form">

<?php $form = ActiveForm::begin([
    //'options' => ['class' => 'disable-submit-buttons'],
    'id' => 'lot-form',
    //'enableAjaxValidation' => true,
]); ?>

<?= $form->field($lotModel, 'lot_no')->textInput(['type' => 'number', 'min' => 1, 'autocomplete' => 'off']) ?>

<?= $form->field($lotModel, 'title')->textInput(['autocomplete' => 'off']) ?>

<div class="pull-right">
    <?= Html::submitButton('Save Lot', ['class' => 'btn btn-success']) ?>
</div>

<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

</div>
<?php
  $script = '
    $("#lot-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Lot has been created successfully");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                lot('.$model->id.');
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