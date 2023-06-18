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
    'id' => 'assign-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<p>
    <b>Assign signatories for:</b> <br>
    <?= $model->employee->fname.' '.$model->employee->lname ?>
    <br>
</p>


<?= $form->field($model, 'recommending')->widget(Select2::classname(), [
    'data' => $staffs,
    'options' => ['placeholder' => 'Select Recommender','multiple' => false, 'class'=>'recommender-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
]) ?>

<?= $form->field($model, 'final')->widget(Select2::classname(), [
    'data' => $staffs,
    'options' => ['placeholder' => 'Select Final Approver','multiple' => false, 'class'=>'approver-select'],
    'pluginOptions' => [
        'allowClear' =>  true,
    ],
]) ?>


<div class="form-group pull-right"> 
    <?= Html::submitButton('Approve', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    $("#assign-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            error: function (err) {
                console.log(err);
            }
        }); 
        
        return false;
    });
    ';

    $this->registerJs($script, View::POS_END);
?>