<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\BacMember */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bac-member-form">

    <?php $form = ActiveForm::begin([
    	'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <?= $form->field($model, 'emp_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'office_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bac_group')->dropDownList([ 'End User' => 'End User', 'Technical Expert' => 'Technical Expert', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'expertise')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
