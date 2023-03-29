<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Iar */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="iar-form">

    <?php $form = ActiveForm::begin([
    	'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <?= $form->field($model, 'pr_id')->textInput() ?>

    <?= $form->field($model, 'po_id')->textInput() ?>

    <?= $form->field($model, 'iar_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'iar_date')->textInput() ?>

    <?= $form->field($model, 'invoice_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'invoice_date')->textInput() ?>

    <?= $form->field($model, 'inspected_by')->textInput() ?>

    <?= $form->field($model, 'date_inspected')->textInput() ?>

    <?= $form->field($model, 'received_by')->textInput() ?>

    <?= $form->field($model, 'date_received')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
