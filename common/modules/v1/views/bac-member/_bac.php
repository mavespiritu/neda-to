<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\BacMember */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'Change BAC Member';
$this->params['breadcrumbs'][] = ['label' => 'BAC Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bac-member-form">

    <?php $form = ActiveForm::begin([
    	'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <?= $form->field($member, 'value')->widget(Select2::classname(), [
        'data' => $signatories,
        'options' => ['placeholder' => 'Select Staff','multiple' => false, 'class'=>'signatory-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ])->label($title);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
